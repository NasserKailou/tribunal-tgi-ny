<?php
/**
 * ControleJudiciairController — Contrôles judiciaires & liberté provisoire (schéma v013)
 */
class ControleJudiciairController extends Controller
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
            $where[]      = "(cj.personne_nom LIKE :q OR cj.personne_prenom LIKE :q OR d.numero_rg LIKE :q)";
            $params[':q'] = "%{$search}%";
        }
        if ($statut) { $where[] = 'cj.statut=:statut'; $params[':statut'] = $statut; }
        $wSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM controles_judiciaires cj LEFT JOIN dossiers d ON d.id=cj.dossier_id $wSQL");
        $stmtC->execute($params);
        $total  = (int)$stmtC->fetchColumn();
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT cj.*, d.numero_rg
             FROM controles_judiciaires cj LEFT JOIN dossiers d ON d.id=cj.dossier_id
             $wSQL ORDER BY cj.date_debut DESC LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $controles  = $stmt->fetchAll();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $flash      = $this->getFlash();
        $this->view('controles_judiciaires/index', compact('controles', 'total', 'page', 'totalPages', 'search', 'statut', 'flash', 'user'));
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'juge_instruction', 'procureur', 'president']);
        $user     = Auth::currentUser();
        $dossiers = $this->db->query(
            "SELECT id, numero_rg FROM dossiers WHERE statut IN ('en_instruction','instruction','parquet','en_audience') ORDER BY numero_rg"
        )->fetchAll();
        $flash = $this->getFlash();
        $this->view('controles_judiciaires/create', compact('dossiers', 'flash', 'user'));
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'juge_instruction', 'procureur', 'president']);
        CSRF::check();

        // Fusionner les cases à cocher et le texte libre
        $obligChecks = $_POST['obligations_check'] ?? [];
        $obligText   = $this->sanitize($_POST['obligations'] ?? '');
        $allObligs   = array_merge($obligChecks, $obligText ? [$obligText] : []);
        $obligations = implode("\n", $allObligs);

        $stmt = $this->db->prepare(
            "INSERT INTO controles_judiciaires
                (dossier_id, type_controle, personne_nom, personne_prenom,
                 date_debut, date_fin, obligations, observations, statut, created_by)
             VALUES (:did, :type, :nom, :prenom, :debut, :fin, :oblig, :obs, 'actif', :by)"
        );
        $stmt->execute([
            ':did'    => (int)($_POST['dossier_id'] ?? 0),
            ':type'   => $_POST['type_controle'] ?? 'controle_judiciaire',
            ':nom'    => $this->sanitize($_POST['personne_nom'] ?? ''),
            ':prenom' => $this->sanitize($_POST['personne_prenom'] ?? ''),
            ':debut'  => $_POST['date_debut'] ?? date('Y-m-d'),
            ':fin'    => $_POST['date_fin'] ?: null,
            ':oblig'  => $obligations,
            ':obs'    => $this->sanitize($_POST['observations'] ?? ''),
            ':by'     => Auth::userId(),
        ]);
        $id = (int)$this->db->lastInsertId();
        $this->flash('success', 'Contrôle judiciaire enregistré.');
        $this->redirect('/controles-judiciaires/show/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $controle = $this->getCJ((int)$id);
        if (!$controle) { $this->redirect('/controles-judiciaires'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('controles_judiciaires/show', compact('controle', 'flash', 'user'));
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'juge_instruction', 'president']);
        $controle = $this->getCJ((int)$id);
        if (!$controle) { $this->redirect('/controles-judiciaires'); }
        $user = Auth::currentUser();
        $this->view('controles_judiciaires/edit', compact('controle', 'user'));
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'juge_instruction', 'president']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE controles_judiciaires SET
                date_fin=:fin, statut=:statut,
                obligations=:oblig, observations=:obs
             WHERE id=:id"
        )->execute([
            ':fin'    => $_POST['date_fin'] ?: null,
            ':statut' => $_POST['statut'] ?? 'actif',
            ':oblig'  => $this->sanitize($_POST['obligations'] ?? ''),
            ':obs'    => $this->sanitize($_POST['observations'] ?? ''),
            ':id'     => (int)$id,
        ]);
        $this->flash('success', 'Contrôle judiciaire mis à jour.');
        $this->redirect('/controles-judiciaires/show/' . $id);
    }

    public function lever(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'greffier']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE controles_judiciaires SET statut='leve', date_levee=NOW(), motif_levee='Décision du juge' WHERE id=?"
        )->execute([(int)$id]);
        $this->flash('success', 'Contrôle judiciaire levé.');
        $this->redirect('/controles-judiciaires/show/' . $id);
    }

    public function violation(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'juge_instruction', 'president', 'greffier', 'procureur']);
        CSRF::check();
        $cj = $this->getCJ((int)$id);
        $violations = ($cj['violations'] ?? '') . "\n[" . date('d/m/Y') . "] Violation signalée";
        $this->db->prepare(
            "UPDATE controles_judiciaires SET statut='viole', violations=? WHERE id=?"
        )->execute([trim($violations), (int)$id]);
        $this->flash('warning', 'Violation signalée — dossier marqué comme violé.');
        $this->redirect('/controles-judiciaires/show/' . $id);
    }

    private function getCJ(int $id): ?array
    {
        $s = $this->db->prepare(
            "SELECT cj.*, d.numero_rg FROM controles_judiciaires cj
             LEFT JOIN dossiers d ON d.id=cj.dossier_id WHERE cj.id=?"
        );
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
}
