<?php
class AudienceController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $flash  = $this->getFlash();
        $user   = Auth::currentUser();
        $statut = $_GET['statut'] ?? '';
        $type   = $_GET['type'] ?? '';
        $search = trim($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage= 20;
        $offset = ($page - 1) * $perPage;

        $where  = []; $params = [];
        if ($search) { $where[] = "(d.numero_rg LIKE :q)"; $params['q'] = "%{$search}%"; }
        if ($statut) { $where[] = "a.statut=:statut"; $params['statut']=$statut; }
        if ($type)   { $where[] = "a.type_audience=:type"; $params['type']=$type; }
        $whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';

        $count = $this->db->prepare("SELECT COUNT(*) FROM audiences a JOIN dossiers d ON a.dossier_id=d.id $whereSQL");
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        $sql = "SELECT a.*, d.numero_rg, d.type_affaire,
                       s.nom as salle_nom,
                       u.nom as president_nom, u.prenom as president_prenom,
                       g.nom as greffier_nom, g.prenom as greffier_prenom
                FROM audiences a
                JOIN dossiers d ON a.dossier_id=d.id
                LEFT JOIN salles_audience s ON a.salle_id=s.id
                LEFT JOIN users u ON a.president_id=u.id
                LEFT JOIN users g ON a.greffier_id=g.id
                $whereSQL ORDER BY a.date_audience DESC LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $audiences   = $stmt->fetchAll();
        $totalPages  = ceil($total / $perPage);

        $this->view('audiences/index', compact('audiences','total','page','perPage','totalPages','search','statut','type','flash','user'));
    }

    public function create(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','president','procureur']);
        $user     = Auth::currentUser();
        $dossiers = $this->db->query("SELECT id, numero_rg, objet FROM dossiers WHERE statut NOT IN ('juge','classe') ORDER BY numero_rg")->fetchAll();
        $salles   = $this->db->query("SELECT * FROM salles_audience WHERE actif=1 ORDER BY nom")->fetchAll();
        $juges    = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code IN ('president','juge_siege','vice_president') AND u.actif=1")->fetchAll();
        $greffiers= $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='greffier' AND u.actif=1")->fetchAll();
        $dossierPreselect = (int)($_GET['dossier_id'] ?? 0);
        $parquet  = $this->db->query("SELECT u.*, r.code as role_code FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code IN ('procureur','substitut_procureur') AND u.actif=1 ORDER BY r.id, u.prenom")->fetchAll();
        $this->view('audiences/create', compact('dossiers','salles','juges','greffiers','parquet','dossierPreselect','user'));
    }

    public function store(): void {
        Auth::requireLogin();
        CSRF::check();

        // Valider date obligatoire
        $dateAudience = trim($_POST['date_audience'] ?? '');
        if (!$dateAudience) {
            $this->flash('error', 'La date et heure de l\'audience sont obligatoires.');
            $this->redirect('/audiences/create');
        }
        // Normaliser datetime-local → MySQL DATETIME
        $dateAudience = str_replace('T', ' ', $dateAudience);
        if (strlen($dateAudience) === 16) $dateAudience .= ':00';

        $annee = date('Y', strtotime($dateAudience));
        $seq   = (int)$this->db->query("SELECT COUNT(*)+1 FROM audiences WHERE YEAR(date_audience)={$annee}")->fetchColumn();
        $numAud= sprintf("AUD N°%03d/%d/TGI-NY", $seq, $annee);

        $ins = $this->db->prepare(
            "INSERT INTO audiences (dossier_id, salle_id, date_audience, type_audience,
             president_id, greffier_id, numero_audience, notes, created_by)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $ins->execute([
            (int)$_POST['dossier_id'],
            $_POST['salle_id'] ?: null,
            $dateAudience,
            $_POST['type_audience'],
            $_POST['president_id'] ?: null,
            $_POST['greffier_id'] ?: null,
            $numAud,
            $this->sanitize($_POST['notes'] ?? ''),
            Auth::userId(),
        ]);
        $audId = (int)$this->db->lastInsertId();

        // ── Composition : assesseurs, jurés, parquet ───────────────────────
        $insMem = $this->db->prepare(
            "INSERT INTO membres_audience (audience_id, user_id, nom_externe, role_audience) VALUES (?,?,?,?)"
        );
        // Assesseur 1
        $a1id  = (int)($_POST['assesseur1_id'] ?? 0);
        $a1nom = trim($_POST['assesseur1_nom'] ?? '');
        if ($a1id || $a1nom) {
            $insMem->execute([$audId, $a1id ?: null, $a1nom ?: null, 'assesseur_1']);
        }
        // Assesseur 2
        $a2id  = (int)($_POST['assesseur2_id'] ?? 0);
        $a2nom = trim($_POST['assesseur2_nom'] ?? '');
        if ($a2id || $a2nom) {
            $insMem->execute([$audId, $a2id ?: null, $a2nom ?: null, 'assesseur_2']);
        }
        // Juré 1
        $j1 = trim($_POST['jure1_nom'] ?? '');
        if ($j1) { $insMem->execute([$audId, null, $j1, 'jure_1']); }
        // Juré 2
        $j2 = trim($_POST['jure2_nom'] ?? '');
        if ($j2) { $insMem->execute([$audId, null, $j2, 'jure_2']); }
        // Représentant parquet
        $pqId = (int)($_POST['parquet_id'] ?? 0);
        if ($pqId) { $insMem->execute([$audId, $pqId, null, 'procureur']); }
        // Membres libres supplémentaires (compatibilité ancienne API)
        if (!empty($_POST['membres']) && is_array($_POST['membres'])) {
            foreach ($_POST['membres'] as $m) {
                $insMem->execute([$audId, $m['user_id'] ?: null, $m['nom_externe'] ?? null, $m['role_audience']]);
            }
        }

        $this->db->prepare("UPDATE dossiers SET statut='en_audience' WHERE id=?")->execute([(int)$_POST['dossier_id']]);
        $this->flash('success', "Audience planifiée : {$numAud}");
        $this->redirect('/audiences/show/' . $audId);
    }

    public function show(string $id): void {
        Auth::requireLogin();
        $stmt = $this->db->prepare(
            "SELECT a.*, d.numero_rg, d.objet, d.type_affaire, d.id as dossier_id,
                    s.nom as salle_nom, s.capacite,
                    u.nom as president_nom, u.prenom as president_prenom,
                    g.nom as greffier_nom, g.prenom as greffier_prenom
             FROM audiences a
             JOIN dossiers d ON a.dossier_id=d.id
             LEFT JOIN salles_audience s ON a.salle_id=s.id
             LEFT JOIN users u ON a.president_id=u.id
             LEFT JOIN users g ON a.greffier_id=g.id
             WHERE a.id=?"
        );
        $stmt->execute([(int)$id]);
        $audience = $stmt->fetch();
        if (!$audience) { $this->redirect('/audiences'); }

        $memStmt = $this->db->prepare("SELECT m.*, u.nom, u.prenom FROM membres_audience m LEFT JOIN users u ON m.user_id=u.id WHERE m.audience_id=?");
        $memStmt->execute([(int)$id]);
        $membres = $memStmt->fetchAll();

        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $this->view('audiences/show', compact('audience','membres','flash','user'));
    }

    public function updateStatut(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $statut     = $_POST['statut'];
        $motifRenvoi= $this->sanitize($_POST['motif_renvoi'] ?? '');
        $dateRenvoi = $_POST['date_renvoi'] ?: null;
        $notes      = $this->sanitize($_POST['notes'] ?? '');

        $this->db->prepare("UPDATE audiences SET statut=:s, motif_renvoi=:m, date_renvoi=:dr, notes=:n WHERE id=:id")
            ->execute(['s'=>$statut,'m'=>$motifRenvoi,'dr'=>$dateRenvoi,'n'=>$notes,'id'=>(int)$id]);

        if ($statut === 'tenue') {
            $stmtA = $this->db->prepare("SELECT dossier_id FROM audiences WHERE id=?");
            $stmtA->execute([(int)$id]);
            $row = $stmtA->fetch();
            if ($row) {
                $this->db->prepare("INSERT INTO mouvements_dossier (dossier_id,user_id,type_mouvement,description) VALUES (?,?,'audience_tenue','Audience tenue')")
                    ->execute([$row['dossier_id'], Auth::userId()]);
            }
        }
        $this->flash('success', 'Audience mise à jour.');
        $this->redirect('/audiences/show/' . $id);
    }

    public function calendrier(): void {
        Auth::requireLogin();
        $user = Auth::currentUser();
        $this->view('audiences/calendrier', compact('user'));
    }

    public function apiCalendrier(): void {
        Auth::requireLogin();
        $debut = $_GET['start'] ?? date('Y-m-01');
        $fin   = $_GET['end']   ?? date('Y-m-t');
        $stmt  = $this->db->prepare(
            "SELECT a.id, a.date_audience as start, a.type_audience, a.statut, a.numero_audience,
                    d.numero_rg as title, s.nom as salle_nom
             FROM audiences a
             JOIN dossiers d ON a.dossier_id=d.id
             LEFT JOIN salles_audience s ON a.salle_id=s.id
             WHERE a.date_audience BETWEEN :d AND :f ORDER BY a.date_audience"
        );
        $stmt->execute(['d'=>$debut,'f'=>$fin]);
        $rows = $stmt->fetchAll();

        $colors = ['planifiee'=>'#0d6efd','tenue'=>'#198754','renvoyee'=>'#ffc107','annulee'=>'#dc3545'];
        $events = array_map(fn($r) => [
            'id'        => $r['id'],
            'title'     => $r['title'] . ' (' . $r['type_audience'] . ')',
            'start'     => $r['start'],
            'color'     => $colors[$r['statut']] ?? '#6c757d',
            'extendedProps' => ['statut'=>$r['statut'],'salle'=>$r['salle_nom'],'numero'=>$r['numero_audience']],
            'url'       => BASE_URL . '/audiences/show/' . $r['id'],
        ], $rows);

        $this->json($events);
    }
}
