<?php
/**
 * AvocatController — Gestion du barreau et des avocats
 *
 * Routes :
 *   GET  /avocats                   → index()
 *   GET  /avocats/create            → create()
 *   POST /avocats/store             → store()
 *   GET  /avocats/show/{id}         → show()
 *   GET  /avocats/edit/{id}         → edit()
 *   POST /avocats/update/{id}       → update()
 *   POST /avocats/toggle/{id}       → toggle()
 *   GET  /api/avocats/search        → apiSearch()
 */
class AvocatController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $user   = Auth::currentUser();
        $search = trim($_GET['q'] ?? '');
        $statut = $_GET['statut'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $where  = [];
        $params = [];
        if ($search) {
            $where[]       = "(a.nom LIKE :q1 OR a.prenom LIKE :q2 OR a.numero_ordre LIKE :q3 OR a.telephone LIKE :q4)";
            $params[':q1'] = "%{$search}%";
            $params[':q2'] = "%{$search}%";
            $params[':q3'] = "%{$search}%";
            $params[':q4'] = "%{$search}%";
        }
        if ($statut) {
            $where[]           = 'a.statut = :statut';
            $params[':statut'] = $statut;
        }
        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int)$this->db->prepare("SELECT COUNT(*) FROM avocats a $whereSQL")
                     ->execute($params) ? 0 : 0;
        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM avocats a $whereSQL");
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt   = $this->db->prepare(
            "SELECT a.*,
                    (SELECT COUNT(*) FROM avocat_dossier ad WHERE ad.avocat_id = a.id) AS nb_dossiers
             FROM avocats a $whereSQL
             ORDER BY a.nom, a.prenom
             LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $avocats = $stmt->fetchAll();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $flash      = $this->getFlash();

        $this->view('avocats/index', compact('avocats','total','page','perPage','totalPages','search','statut','flash','user'));
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','president']);
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('avocats/create', compact('flash','user'));
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','president']);
        CSRF::check();

        $matricule = strtoupper(trim($_POST['matricule'] ?? ''));
        if (!$matricule) {
            $year      = date('Y');
            $last      = $this->db->query("SELECT MAX(id) FROM avocats")->fetchColumn() ?? 0;
            $matricule = 'AV-' . $year . '-' . str_pad((int)$last + 1, 4, '0', STR_PAD_LEFT);
        }

        $stmt = $this->db->prepare(
            "INSERT INTO avocats
                (matricule,nom,prenom,telephone,email,adresse,
                 barreau,numero_ordre,date_inscription,statut,observations)
             VALUES
                (:mat,:nom,:prenom,:tel,:email,:adr,
                 :bar,:ord,:dins,:statut,:obs)"
        );
        $stmt->execute([
            ':mat'    => $matricule,
            ':nom'    => $this->sanitize($_POST['nom'] ?? ''),
            ':prenom' => $this->sanitize($_POST['prenom'] ?? ''),
            ':tel'    => $this->sanitize($_POST['telephone'] ?? ''),
            ':email'  => strtolower(trim($_POST['email'] ?? '')),
            ':adr'    => $this->sanitize($_POST['adresse'] ?? ''),
            ':bar'    => $this->sanitize($_POST['barreau'] ?? 'Barreau de Niamey'),
            ':ord'    => $this->sanitize($_POST['numero_ordre'] ?? ''),
            ':dins'   => $_POST['date_inscription'] ?: null,
            ':statut' => $_POST['statut'] ?? 'actif',
            ':obs'    => $this->sanitize($_POST['notes'] ?? ''),
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->flash('success', "Avocat enregistré : {$matricule}");
        $this->redirect('/avocats/show/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $avocat = $this->getAvocat((int)$id);
        if (!$avocat) { $this->redirect('/avocats'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();

        $stmt = $this->db->prepare(
            "SELECT ad.*, d.numero_rg, d.statut as dossier_statut, d.type_affaire, d.objet
             FROM avocat_dossier ad
             JOIN dossiers d ON d.id = ad.dossier_id
             WHERE ad.avocat_id = ?
             ORDER BY ad.created_at DESC"
        );
        $stmt->execute([(int)$id]);
        $dossiers = $stmt->fetchAll();

        $this->view('avocats/show', compact('avocat','dossiers','flash','user'));
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','president']);
        $avocat = $this->getAvocat((int)$id);
        if (!$avocat) { $this->redirect('/avocats'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('avocats/edit', compact('avocat','flash','user'));
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','president']);
        CSRF::check();

        $this->db->prepare(
            "UPDATE avocats SET
                nom=:nom, prenom=:prenom, telephone=:tel, email=:email,
                adresse=:adr, barreau=:bar, numero_ordre=:ord, date_inscription=:dins,
                statut=:statut, observations=:obs
             WHERE id=:id"
        )->execute([
            ':nom'    => $this->sanitize($_POST['nom'] ?? ''),
            ':prenom' => $this->sanitize($_POST['prenom'] ?? ''),
            ':tel'    => $this->sanitize($_POST['telephone'] ?? ''),
            ':email'  => strtolower(trim($_POST['email'] ?? '')),
            ':adr'    => $this->sanitize($_POST['adresse'] ?? ''),
            ':bar'    => $this->sanitize($_POST['barreau'] ?? 'Barreau de Niamey'),
            ':ord'    => $this->sanitize($_POST['numero_ordre'] ?? ''),
            ':dins'   => $_POST['date_inscription'] ?: null,
            ':statut' => $_POST['statut'] ?? 'actif',
            ':obs'    => $this->sanitize($_POST['notes'] ?? ''),
            ':id'     => (int)$id,
        ]);
        $this->flash('success', 'Dossier avocat mis à jour.');
        $this->redirect('/avocats/show/' . $id);
    }

    public function toggle(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','president']);
        CSRF::check();

        $a = $this->getAvocat((int)$id);
        if (!$a) { $this->redirect('/avocats'); }
        $newStatut = $a['statut'] === 'actif' ? 'suspendu' : 'actif';
        $this->db->prepare("UPDATE avocats SET statut=? WHERE id=?")->execute([$newStatut, (int)$id]);
        $this->flash('success', 'Statut avocat mis à jour.');
        $this->redirect('/avocats');
    }

    /** POST /dossiers/avocats/add/{dossierId} */
    public function addToDossier(string $dossierId): void
    {
        Auth::requireLogin();
        CSRF::check();
        $dossierId = (int)$dossierId;
        $avocatId  = (int)($_POST['avocat_id'] ?? 0);
        if (!$avocatId) { $this->flash('error','Sélectionner un avocat.'); $this->redirect('/dossiers/show/'.$dossierId); return; }

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO avocat_dossier
                (dossier_id, avocat_id, role_avocat, date_mandat, observations)
             VALUES (:did,:aid,:role,:dm,:obs)"
        );
        $stmt->execute([
            ':did'  => $dossierId,
            ':aid'  => $avocatId,
            ':role' => $_POST['role_avocat'] ?? 'defense',
            ':dm'   => $_POST['date_mandat'] ?: date('Y-m-d'),
            ':obs'  => $this->sanitize($_POST['notes'] ?? ''),
        ]);
        $this->flash('success', 'Avocat ajouté au dossier.');
        $this->redirect('/dossiers/show/'.$dossierId);
    }

    /** POST /dossiers/avocats/remove/{id} */
    public function removeFromDossier(string $id): void
    {
        Auth::requireLogin();
        CSRF::check();
        $dossierId = (int)($_POST['dossier_id'] ?? 0);
        $this->db->prepare("DELETE FROM avocat_dossier WHERE id=?")->execute([(int)$id]);
        $this->flash('success', 'Avocat retiré du dossier.');
        $this->redirect('/dossiers/show/'.$dossierId);
    }

    /** GET /api/avocats/search?q= */
    public function apiSearch(): void
    {
        Auth::requireLogin();
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) { $this->json(['success' => true, 'data' => []]); return; }
        $stmt = $this->db->prepare(
            "SELECT id, matricule, nom, prenom, barreau, numero_ordre, statut
             FROM avocats
             WHERE (nom LIKE :q1 OR prenom LIKE :q2 OR matricule LIKE :q3 OR numero_ordre LIKE :q4)
             ORDER BY nom, prenom LIMIT 20"
        );
        $stmt->execute([':q1' => "%{$q}%", ':q2' => "%{$q}%", ':q3' => "%{$q}%", ':q4' => "%{$q}%"]);
        $this->json(['success'=>true,'data'=>$stmt->fetchAll()]);
    }

    private function getAvocat(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM avocats WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
