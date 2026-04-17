<?php
/**
 * OrdonnanceController — Ordonnances du Juge d'Instruction
 * Schéma : table ordonnances (contenu, observations — v013)
 */
class OrdonnanceController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $user    = Auth::currentUser();
        $search  = trim($_GET['q'] ?? '');
        $type    = $_GET['type'] ?? '';
        $statut  = $_GET['statut'] ?? '';
        $page    = max(1,(int)($_GET['page'] ?? 1));
        $perPage = 20;

        $where  = [];
        $params = [];
        if ($search) {
            $where[]      = "(o.numero_ordonnance LIKE :q OR d.numero_rg LIKE :q)";
            $params[':q'] = "%{$search}%";
        }
        if ($type)   { $where[] = 'o.type_ordonnance=:type';   $params[':type']   = $type; }
        if ($statut) { $where[] = 'o.statut=:statut';          $params[':statut'] = $statut; }
        $whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';

        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM ordonnances o JOIN dossiers d ON d.id=o.dossier_id $whereSQL");
        $stmtC->execute($params); $total = (int)$stmtC->fetchColumn();

        $offset = ($page-1)*$perPage;
        $stmt   = $this->db->prepare(
            "SELECT o.*, d.numero_rg, d.type_affaire,
                    u.prenom AS juge_prenom, u.nom AS juge_nom
             FROM ordonnances o
             JOIN dossiers d ON d.id=o.dossier_id
             LEFT JOIN users u ON u.id=o.juge_id
             $whereSQL ORDER BY o.date_ordonnance DESC, o.id DESC
             LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $ordonnances = $stmt->fetchAll();
        $totalPages  = max(1,(int)ceil($total/$perPage));
        $flash       = $this->getFlash();
        $this->view('ordonnances/index', compact('ordonnances','total','page','totalPages','search','type','statut','flash','user'));
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','juge_instruction','president']);
        $user     = Auth::currentUser();
        $dossiers = $this->db->query(
            "SELECT id,numero_rg,objet FROM dossiers WHERE statut IN ('en_instruction','instruction','parquet','enregistre') ORDER BY numero_rg"
        )->fetchAll();
        $juges = $this->db->query(
            "SELECT u.id, u.prenom, u.nom FROM users u JOIN roles r ON r.id=u.role_id WHERE r.code IN ('juge_instruction','president') AND u.actif=1"
        )->fetchAll();
        $flash = $this->getFlash();
        $this->view('ordonnances/create', compact('dossiers','juges','flash','user'));
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','juge_instruction','president']);
        CSRF::check();

        $dossierId = (int)($_POST['dossier_id'] ?? 0);
        $numero    = $this->genererNumero();

        $stmt = $this->db->prepare(
            "INSERT INTO ordonnances
                (numero_ordonnance, dossier_id, juge_id, type_ordonnance,
                 date_ordonnance, contenu, observations, statut, created_by)
             VALUES (:num,:did,:juge,:type,:date,:contenu,:obs,'projet',:by)"
        );
        $stmt->execute([
            ':num'     => $numero,
            ':did'     => $dossierId,
            ':juge'    => $_POST['juge_id'] ?: null,
            ':type'    => $_POST['type_ordonnance'],
            ':date'    => $_POST['date_ordonnance'],
            ':contenu' => $this->sanitize($_POST['contenu'] ?? ''),
            ':obs'     => $this->sanitize($_POST['observations'] ?? ''),
            ':by'      => Auth::userId(),
        ]);
        $id = (int)$this->db->lastInsertId();

        $this->db->prepare(
            "INSERT INTO mouvements_dossier (dossier_id,user_id,type_mouvement,description) VALUES (?,?,'ordonnance',?)"
        )->execute([$dossierId, Auth::userId(), "Ordonnance {$numero} créée"]);

        $this->flash('success', "Ordonnance {$numero} enregistrée.");
        $this->redirect('/ordonnances/show/'.$id);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $ordonnance = $this->getOrdonnance((int)$id);
        if (!$ordonnance) { $this->redirect('/ordonnances'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('ordonnances/show', compact('ordonnance','flash','user'));
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','juge_instruction','president']);
        $ordonnance = $this->getOrdonnance((int)$id);
        if (!$ordonnance || $ordonnance['statut'] !== 'projet') { $this->redirect('/ordonnances'); }
        $user  = Auth::currentUser();
        $juges = $this->db->query(
            "SELECT u.id, u.prenom, u.nom FROM users u JOIN roles r ON r.id=u.role_id WHERE r.code IN ('juge_instruction','president') AND u.actif=1"
        )->fetchAll();
        $this->view('ordonnances/edit', compact('ordonnance','juges','user'));
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','juge_instruction','president']);
        CSRF::check();

        $this->db->prepare(
            "UPDATE ordonnances SET
                type_ordonnance=:type, date_ordonnance=:date, juge_id=:juge,
                contenu=:contenu, observations=:obs
             WHERE id=:id AND statut='projet'"
        )->execute([
            ':type'    => $_POST['type_ordonnance'],
            ':date'    => $_POST['date_ordonnance'],
            ':juge'    => $_POST['juge_id'] ?: null,
            ':contenu' => $this->sanitize($_POST['contenu'] ?? ''),
            ':obs'     => $this->sanitize($_POST['observations'] ?? ''),
            ':id'      => (int)$id,
        ]);
        $this->flash('success', 'Ordonnance mise à jour.');
        $this->redirect('/ordonnances/show/'.$id);
    }

    public function signer(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','juge_instruction','president']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE ordonnances SET statut='signee', date_signature=NOW() WHERE id=? AND statut='projet'"
        )->execute([(int)$id]);
        $this->flash('success', 'Ordonnance signée.');
        $this->redirect('/ordonnances/show/'.$id);
    }

    public function notifier(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','juge_instruction','president']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE ordonnances SET statut='notifiee', date_notification=NOW() WHERE id=? AND statut='signee'"
        )->execute([(int)$id]);
        $this->flash('success', 'Ordonnance marquée notifiée.');
        $this->redirect('/ordonnances/show/'.$id);
    }

    private function genererNumero(): string
    {
        $year = date('Y');
        $last = (int)($this->db->query("SELECT MAX(id) FROM ordonnances")->fetchColumn() ?? 0);
        return 'ORD-'.$year.'-'.str_pad($last+1, 4, '0', STR_PAD_LEFT);
    }

    private function getOrdonnance(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, d.numero_rg, d.type_affaire, d.id AS dossier_id,
                    u.prenom AS juge_prenom, u.nom AS juge_nom
             FROM ordonnances o
             JOIN dossiers d ON d.id=o.dossier_id
             LEFT JOIN users u ON u.id=o.juge_id
             WHERE o.id=?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
