<?php
/**
 * ExpertiseController — Expertises judiciaires (schéma v013)
 * Table: expertises_judiciaires
 */
class ExpertiseController extends Controller
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
            $where[]      = "(e.expert_nom LIKE :q OR e.objet_expertise LIKE :q OR d.numero_rg LIKE :q)";
            $params[':q'] = "%{$search}%";
        }
        if ($statut) { $where[] = 'e.statut=:statut'; $params[':statut'] = $statut; }
        $wSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM expertises_judiciaires e LEFT JOIN dossiers d ON d.id=e.dossier_id $wSQL");
        $stmtC->execute($params);
        $total  = (int)$stmtC->fetchColumn();
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT e.*, d.numero_rg
             FROM expertises_judiciaires e LEFT JOIN dossiers d ON d.id=e.dossier_id
             $wSQL ORDER BY e.date_mission DESC LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $expertises = $stmt->fetchAll();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $flash      = $this->getFlash();
        $this->view('expertises/index', compact('expertises', 'total', 'page', 'totalPages', 'search', 'statut', 'flash', 'user'));
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'procureur', 'greffier']);
        $user     = Auth::currentUser();
        $dossiers = $this->db->query("SELECT id, numero_rg, objet FROM dossiers ORDER BY numero_rg")->fetchAll();
        $flash    = $this->getFlash();
        $this->view('expertises/create', compact('dossiers', 'flash', 'user'));
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'procureur', 'greffier']);
        CSRF::check();

        $stmt = $this->db->prepare(
            "INSERT INTO expertises_judiciaires
                (dossier_id, type_expertise, expert_nom, expert_qualification,
                 date_mission, delai_depot, objet_expertise, statut, created_by)
             VALUES (:did, :type, :exp, :qual, :date, :del, :objet, 'ordonnee', :by)"
        );
        $stmt->execute([
            ':did'   => (int)($_POST['dossier_id'] ?? 0),
            ':type'  => $_POST['type_expertise'] ?? 'autre',
            ':exp'   => $this->sanitize($_POST['expert_nom'] ?? ''),
            ':qual'  => $this->sanitize($_POST['expert_qualification'] ?? ''),
            ':date'  => $_POST['date_mission'] ?? date('Y-m-d'),
            ':del'   => $_POST['delai_depot'] ?: null,
            ':objet' => $this->sanitize($_POST['objet_expertise'] ?? ''),
            ':by'    => Auth::userId(),
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->flash('success', "Expertise judiciaire enregistrée.");
        $this->redirect('/expertises/show/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $expertise = $this->getExpertise((int)$id);
        if (!$expertise) { $this->redirect('/expertises'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('expertises/show', compact('expertise', 'flash', 'user'));
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'greffier']);
        $expertise = $this->getExpertise((int)$id);
        if (!$expertise) { $this->redirect('/expertises'); }
        $user  = Auth::currentUser();
        $this->view('expertises/edit', compact('expertise', 'user'));
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'greffier']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE expertises_judiciaires SET
                expert_nom=:exp, delai_depot=:del,
                objet_expertise=:objet, statut=:statut
             WHERE id=:id"
        )->execute([
            ':exp'    => $this->sanitize($_POST['expert_nom'] ?? ''),
            ':del'    => $_POST['delai_depot'] ?: null,
            ':objet'  => $this->sanitize($_POST['objet_expertise'] ?? ''),
            ':statut' => $_POST['statut'] ?? 'ordonnee',
            ':id'     => (int)$id,
        ]);
        $this->flash('success', 'Expertise mise à jour.');
        $this->redirect('/expertises/show/' . $id);
    }

    public function deposer(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'greffier', 'president']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE expertises_judiciaires SET
                statut='deposee', date_depot_rapport=CURDATE(), conclusions=?
             WHERE id=?"
        )->execute([
            $this->sanitize($_POST['conclusions'] ?? ''),
            (int)$id,
        ]);
        $this->flash('success', 'Dépôt du rapport enregistré.');
        $this->redirect('/expertises/show/' . $id);
    }

    private function getExpertise(int $id): ?array
    {
        $s = $this->db->prepare(
            "SELECT e.*, d.numero_rg FROM expertises_judiciaires e
             LEFT JOIN dossiers d ON d.id=e.dossier_id WHERE e.id=?"
        );
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
}
