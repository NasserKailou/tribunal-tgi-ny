<?php
/**
 * CommissionRogatoireController — Commissions rogatoires (schéma v013)
 * Table: commissions_rogatoires
 */
class CommissionRogatoireController extends Controller
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
            $where[]      = "(cr.numero_cr LIKE :q OR d.numero_rg LIKE :q OR cr.autorite_destinataire LIKE :q)";
            $params[':q'] = "%{$search}%";
        }
        if ($statut) { $where[] = 'cr.statut=:statut'; $params[':statut'] = $statut; }
        $wSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM commissions_rogatoires cr LEFT JOIN dossiers d ON d.id=cr.dossier_id $wSQL");
        $stmtC->execute($params);
        $total  = (int)$stmtC->fetchColumn();
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT cr.*, d.numero_rg
             FROM commissions_rogatoires cr LEFT JOIN dossiers d ON d.id=cr.dossier_id
             $wSQL ORDER BY cr.date_envoi DESC LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $commissions = $stmt->fetchAll();
        $totalPages  = max(1, (int)ceil($total / $perPage));
        $flash       = $this->getFlash();
        $this->view('commissions_rogatoires/index', compact('commissions', 'total', 'page', 'totalPages', 'search', 'statut', 'flash', 'user'));
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'greffier']);
        $user     = Auth::currentUser();
        $dossiers = $this->db->query(
            "SELECT id, numero_rg, objet FROM dossiers WHERE statut IN ('en_instruction','instruction','parquet') ORDER BY numero_rg"
        )->fetchAll();
        $flash = $this->getFlash();
        $this->view('commissions_rogatoires/create', compact('dossiers', 'flash', 'user'));
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'greffier']);
        CSRF::check();

        $num  = $this->genNum();
        $stmt = $this->db->prepare(
            "INSERT INTO commissions_rogatoires
                (numero_cr, dossier_id, type_cr, autorite_destinataire,
                 date_envoi, objet, statut, created_by)
             VALUES (:num, :did, :type, :aut, :date, :objet, 'envoyee', :by)"
        );
        $stmt->execute([
            ':num'   => $num,
            ':did'   => (int)($_POST['dossier_id'] ?? 0),
            ':type'  => $_POST['type_cr'] ?? 'nationale',
            ':aut'   => $this->sanitize($_POST['autorite_destinataire'] ?? ''),
            ':date'  => $_POST['date_envoi'] ?? date('Y-m-d'),
            ':objet' => $this->sanitize($_POST['objet'] ?? ''),
            ':by'    => Auth::userId(),
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->flash('success', "Commission rogatoire {$num} émise.");
        $this->redirect('/commissions-rogatoires/show/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $commission = $this->getCR((int)$id);
        if (!$commission) { $this->redirect('/commissions-rogatoires'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('commissions_rogatoires/show', compact('commission', 'flash', 'user'));
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'greffier']);
        $commission = $this->getCR((int)$id);
        if (!$commission) { $this->redirect('/commissions-rogatoires'); }
        $user = Auth::currentUser();
        $this->view('commissions_rogatoires/edit', compact('commission', 'user'));
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'greffier']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE commissions_rogatoires SET
                autorite_destinataire=:aut, statut=:statut, objet=:objet
             WHERE id=:id"
        )->execute([
            ':aut'    => $this->sanitize($_POST['autorite_destinataire'] ?? ''),
            ':statut' => $_POST['statut'] ?? 'envoyee',
            ':objet'  => $this->sanitize($_POST['objet'] ?? ''),
            ':id'     => (int)$id,
        ]);
        $this->flash('success', 'Commission rogatoire mise à jour.');
        $this->redirect('/commissions-rogatoires/show/' . $id);
    }

    public function retour(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'greffier', 'president']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE commissions_rogatoires SET
                statut='retour', date_retour=CURDATE(), resultats=?
             WHERE id=?"
        )->execute([
            $this->sanitize($_POST['resultats'] ?? ''),
            (int)$id,
        ]);
        $this->flash('success', 'Retour de commission rogatoire enregistré.');
        $this->redirect('/commissions-rogatoires/show/' . $id);
    }

    private function genNum(): string
    {
        $year = date('Y');
        $last = (int)($this->db->query("SELECT MAX(id) FROM commissions_rogatoires")->fetchColumn() ?? 0);
        return 'CR-' . $year . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    private function getCR(int $id): ?array
    {
        $s = $this->db->prepare(
            "SELECT cr.*, d.numero_rg FROM commissions_rogatoires cr
             LEFT JOIN dossiers d ON d.id=cr.dossier_id WHERE cr.id=?"
        );
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
}
