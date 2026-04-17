<?php
/**
 * ScelleController — Gestion des scellés judiciaires (schéma v013)
 */
class ScelleController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $user    = Auth::currentUser();
        $search  = trim($_GET['q'] ?? '');
        $statut  = $_GET['statut'] ?? '';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $where   = [];
        $params  = [];

        if ($search) {
            $where[]      = "(s.numero_scelle LIKE :q OR s.description LIKE :q OR d.numero_rg LIKE :q)";
            $params[':q'] = "%{$search}%";
        }
        if ($statut) { $where[] = 's.statut=:statut'; $params[':statut'] = $statut; }
        $wSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM scelles s LEFT JOIN dossiers d ON d.id=s.dossier_id $wSQL");
        $stmtC->execute($params);
        $total  = (int)$stmtC->fetchColumn();
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT s.*, d.numero_rg
             FROM scelles s LEFT JOIN dossiers d ON d.id=s.dossier_id
             $wSQL ORDER BY s.created_at DESC LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $scelles    = $stmt->fetchAll();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $flash      = $this->getFlash();
        $this->view('scelles/index', compact('scelles', 'total', 'page', 'totalPages', 'search', 'statut', 'flash', 'user'));
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'president', 'juge_instruction']);
        $user     = Auth::currentUser();
        $dossiers = $this->db->query("SELECT id, numero_rg FROM dossiers ORDER BY numero_rg")->fetchAll();
        $flash    = $this->getFlash();
        $this->view('scelles/create', compact('dossiers', 'flash', 'user'));
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'president', 'juge_instruction']);
        CSRF::check();

        $numero = $this->genererNumero();
        $stmt   = $this->db->prepare(
            "INSERT INTO scelles
                (numero_scelle, dossier_id, categorie, description,
                 date_depot, lieu_conservation, observations, statut, created_by)
             VALUES
                (:num, :did, :cat, :desc, :date, :lieu, :obs, 'depose', :by)"
        );
        $stmt->execute([
            ':num'  => $numero,
            ':did'  => (int)($_POST['dossier_id'] ?? 0) ?: null,
            ':cat'  => $_POST['categorie'] ?? 'autre',
            ':desc' => $this->sanitize($_POST['description'] ?? ''),
            ':date' => $_POST['date_depot'] ?? date('Y-m-d'),
            ':lieu' => $this->sanitize($_POST['lieu_conservation'] ?? 'Greffe du TGI-NY'),
            ':obs'  => $this->sanitize($_POST['observations'] ?? ''),
            ':by'   => Auth::userId(),
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->flash('success', "Scellé {$numero} enregistré.");
        $this->redirect('/scelles/show/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $scelle = $this->getScelle((int)$id);
        if (!$scelle) { $this->redirect('/scelles'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('scelles/show', compact('scelle', 'flash', 'user'));
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'president', 'juge_instruction']);
        $scelle = $this->getScelle((int)$id);
        if (!$scelle) { $this->redirect('/scelles'); }
        $user     = Auth::currentUser();
        $dossiers = $this->db->query("SELECT id, numero_rg FROM dossiers ORDER BY numero_rg")->fetchAll();
        $this->view('scelles/edit', compact('scelle', 'dossiers', 'user'));
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'president', 'juge_instruction']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE scelles SET
                categorie=:cat, lieu_conservation=:lieu,
                description=:desc, statut=:statut, observations=:obs
             WHERE id=:id"
        )->execute([
            ':cat'    => $_POST['categorie'] ?? 'autre',
            ':lieu'   => $this->sanitize($_POST['lieu_conservation'] ?? ''),
            ':desc'   => $this->sanitize($_POST['description'] ?? ''),
            ':statut' => $_POST['statut'] ?? 'depose',
            ':obs'    => $this->sanitize($_POST['observations'] ?? ''),
            ':id'     => (int)$id,
        ]);
        $this->flash('success', 'Scellé mis à jour.');
        $this->redirect('/scelles/show/' . $id);
    }

    public function restituer(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'president', 'juge_instruction']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE scelles SET statut='restitue', date_restitution=CURDATE(), beneficiaire_restitution=:ben WHERE id=?"
        )->execute([$_POST['beneficiaire'] ?? 'Non précisé', (int)$id]);
        $this->flash('success', 'Scellé restitué.');
        $this->redirect('/scelles/show/' . $id);
    }

    public function detruire(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'president', 'procureur']);
        CSRF::check();
        $motif = $this->sanitize($_POST['motif_destruction'] ?? '');
        if (empty($motif)) {
            $this->flash('error', 'Motif de destruction requis.');
            $this->redirect('/scelles/show/' . $id);
            return;
        }
        $this->db->prepare(
            "UPDATE scelles SET statut='detruit', date_destruction=CURDATE(), motif_destruction=? WHERE id=?"
        )->execute([$motif, (int)$id]);
        $this->flash('success', 'Scellé marqué détruit.');
        $this->redirect('/scelles/show/' . $id);
    }

    private function genererNumero(): string
    {
        $year = date('Y');
        $last = (int)($this->db->query("SELECT MAX(id) FROM scelles")->fetchColumn() ?? 0);
        return 'SCL-' . $year . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    private function getScelle(int $id): ?array
    {
        $s = $this->db->prepare(
            "SELECT sc.*, d.numero_rg FROM scelles sc
             LEFT JOIN dossiers d ON d.id=sc.dossier_id WHERE sc.id=?"
        );
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
}
