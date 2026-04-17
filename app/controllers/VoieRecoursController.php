<?php
/**
 * VoieRecoursController — Appel, Cassation, Opposition, Révision (schéma v013)
 */
class VoieRecoursController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $user    = Auth::currentUser();
        $search  = trim($_GET['q'] ?? '');
        $type    = $_GET['type'] ?? '';
        $statut  = $_GET['statut'] ?? '';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $where   = [];
        $params  = [];

        if ($search) {
            $where[]      = "(vr.demandeur_nom LIKE :q OR d.numero_rg LIKE :q)";
            $params[':q'] = "%{$search}%";
        }
        if ($type)   { $where[] = 'vr.type_recours=:type';  $params[':type']   = $type; }
        if ($statut) { $where[] = 'vr.statut=:statut';      $params[':statut'] = $statut; }
        $wSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM voies_recours vr LEFT JOIN dossiers d ON d.id=vr.dossier_id $wSQL");
        $stmtC->execute($params);
        $total  = (int)$stmtC->fetchColumn();
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT vr.*, d.numero_rg, d.type_affaire
             FROM voies_recours vr LEFT JOIN dossiers d ON d.id=vr.dossier_id
             $wSQL ORDER BY vr.date_declaration DESC LIMIT $perPage OFFSET $offset"
        );
        $stmt->execute($params);
        $recours    = $stmt->fetchAll();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $flash      = $this->getFlash();
        $this->view('voies_recours/index', compact('recours', 'total', 'page', 'totalPages', 'search', 'type', 'statut', 'flash', 'user'));
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'president', 'substitut_procureur']);
        $user     = Auth::currentUser();
        $dossiers = $this->db->query(
            "SELECT id, numero_rg, objet FROM dossiers WHERE statut IN ('juge','en_audience','appel') ORDER BY numero_rg"
        )->fetchAll();
        $flash = $this->getFlash();
        $this->view('voies_recours/create', compact('dossiers', 'flash', 'user'));
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'president', 'substitut_procureur']);
        CSRF::check();

        $dossierId = (int)($_POST['dossier_id'] ?? 0);
        $typeRec   = $_POST['type_recours'] ?? 'appel';

        $stmt = $this->db->prepare(
            "INSERT INTO voies_recours
                (dossier_id, type_recours, demandeur_nom, demandeur_qualite,
                 date_declaration, juridiction_saisie, motifs, statut, created_by)
             VALUES (:did, :type, :nom, :qualite, :date, :jur, :motifs, 'declare', :by)"
        );
        $stmt->execute([
            ':did'    => $dossierId,
            ':type'   => $typeRec,
            ':nom'    => $this->sanitize($_POST['demandeur_nom'] ?? ''),
            ':qualite'=> $_POST['demandeur_qualite'] ?? null,
            ':date'   => $_POST['date_declaration'] ?? date('Y-m-d'),
            ':jur'    => $this->sanitize($_POST['juridiction_saisie'] ?? ''),
            ':motifs' => $this->sanitize($_POST['motifs'] ?? ''),
            ':by'     => Auth::userId(),
        ]);
        $id = (int)$this->db->lastInsertId();

        if (in_array($typeRec, ['appel', 'cassation'])) {
            $this->db->prepare("UPDATE dossiers SET statut='appel' WHERE id=?")->execute([$dossierId]);
            $this->db->prepare(
                "INSERT INTO mouvements_dossier (dossier_id, user_id, type_mouvement, description) VALUES (?,?,'voie_recours',?)"
            )->execute([$dossierId, Auth::userId(), "Voie de recours déclarée : " . $typeRec]);
        }

        $this->flash('success', 'Voie de recours enregistrée.');
        $this->redirect('/voies-recours/show/' . $id);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $recours = $this->getVR((int)$id);
        if (!$recours) { $this->redirect('/voies-recours'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('voies_recours/show', compact('recours', 'flash', 'user'));
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'president']);
        $recours = $this->getVR((int)$id);
        if (!$recours) { $this->redirect('/voies-recours'); }
        $user = Auth::currentUser();
        $this->view('voies_recours/edit', compact('recours', 'user'));
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'president']);
        CSRF::check();
        $this->db->prepare(
            "UPDATE voies_recours SET
                type_recours=:type, date_declaration=:date,
                demandeur_nom=:nom, juridiction_saisie=:jur,
                decision_rendue=:dec, date_decision=:dd,
                motifs=:motifs, statut=:statut
             WHERE id=:id"
        )->execute([
            ':type'   => $_POST['type_recours'] ?? 'appel',
            ':date'   => $_POST['date_declaration'] ?? date('Y-m-d'),
            ':nom'    => $this->sanitize($_POST['demandeur_nom'] ?? ''),
            ':jur'    => $this->sanitize($_POST['juridiction_saisie'] ?? ''),
            ':dec'    => $this->sanitize($_POST['decision_rendue'] ?? ''),
            ':dd'     => $_POST['date_decision'] ?: null,
            ':motifs' => $this->sanitize($_POST['motifs'] ?? ''),
            ':statut' => $_POST['statut'] ?? 'declare',
            ':id'     => (int)$id,
        ]);
        $this->flash('success', 'Voie de recours mise à jour.');
        $this->redirect('/voies-recours/show/' . $id);
    }

    public function clore(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'president']);
        CSRF::check();
        $statutFinal = $_POST['statut_final'] ?? 'juge';
        $this->db->prepare(
            "UPDATE voies_recours SET statut=:s, date_decision=CURDATE() WHERE id=:id"
        )->execute([':s' => $statutFinal, ':id' => (int)$id]);
        $this->flash('success', 'Recours clôturé (' . $statutFinal . ').');
        $this->redirect('/voies-recours/show/' . $id);
    }

    private function getVR(int $id): ?array
    {
        $s = $this->db->prepare(
            "SELECT vr.*, d.numero_rg
             FROM voies_recours vr LEFT JOIN dossiers d ON d.id=vr.dossier_id
             WHERE vr.id=?"
        );
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
}
