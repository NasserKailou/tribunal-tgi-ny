<?php
class DossierController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();

        $where  = [];
        $params = [];
        $search = trim($_GET['q'] ?? '');
        $statut = $_GET['statut'] ?? '';
        $type   = $_GET['type'] ?? '';

        if ($search) {
            $where[] = "(d.numero_rg LIKE :q OR d.numero_rp LIKE :q OR d.numero_ri LIKE :q OR d.objet LIKE :q)";
            $params['q'] = "%{$search}%";
        }
        if ($statut) { $where[] = "d.statut=:statut"; $params['statut'] = $statut; }
        if ($type)   { $where[] = "d.type_affaire=:type"; $params['type'] = $type; }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $count = $this->db->prepare("SELECT COUNT(*) FROM dossiers d $whereSQL");
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        $sql = "SELECT d.*, 
                       us.nom as substitut_nom, us.prenom as substitut_prenom,
                       ci.numero as cabinet_num, ci.libelle as cabinet_lib,
                       (SELECT COUNT(*) FROM audiences a WHERE a.dossier_id=d.id AND a.statut='planifiee') as nb_audiences
                FROM dossiers d
                LEFT JOIN users us ON d.substitut_id = us.id
                LEFT JOIN cabinets_instruction ci ON d.cabinet_id = ci.id
                $whereSQL ORDER BY d.created_at DESC LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $dossiers = $stmt->fetchAll();

        $totalPages = ceil($total / $perPage);
        $this->view('dossiers/index', compact('dossiers','total','page','perPage','totalPages','search','statut','type','flash','user'));
    }

    public function create(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','substitut_procureur']);
        $user      = Auth::currentUser();
        $substituts = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='substitut_procureur' AND u.actif=1")->fetchAll();
        $cabinets  = $this->db->query("SELECT * FROM cabinets_instruction WHERE actif=1 ORDER BY numero")->fetchAll();
        $pvs       = $this->db->query("SELECT * FROM pv WHERE statut='recu' OR statut='en_traitement' ORDER BY numero_rg")->fetchAll();
        $num       = new Numerotation($this->db);
        $suggestRG = $num->genererRG();
        $this->view('dossiers/create', compact('substituts','cabinets','pvs','suggestRG','user'));
    }

    public function store(): void {
        Auth::requireLogin();
        CSRF::check();
        $num = new Numerotation($this->db);
        $annee   = date('Y');
        $numeroRG = $num->genererRG($annee);
        $numeroRP = $num->genererRP($annee);
        $cabinetId = $_POST['cabinet_id'] ?: null;
        $numeroRI  = $cabinetId ? $num->genererRI($annee) : null;

        $stmt = $this->db->prepare(
            "INSERT INTO dossiers (pv_id, numero_rg, numero_rp, numero_ri, type_affaire,
             date_enregistrement, objet, statut, substitut_id, cabinet_id,
             date_limite_traitement, date_instruction_debut, created_by)
             VALUES (:pvid,:rg,:rp,:ri,:type,:date,:objet,:statut,:sub,:cab,:dlim,:dinst,:by)"
        );
        $statut  = $cabinetId ? 'en_instruction' : 'parquet';
        $dinst   = $cabinetId ? date('Y-m-d') : null;
        $dlim    = $cabinetId ? date('Y-m-d', strtotime('+6 months')) : date('Y-m-d', strtotime('+30 days'));
        $stmt->execute([
            'pvid'  => $_POST['pv_id'] ?: null,
            'rg'    => $numeroRG,
            'rp'    => $numeroRP,
            'ri'    => $numeroRI,
            'type'  => $_POST['type_affaire'],
            'date'  => $_POST['date_enregistrement'] ?: date('Y-m-d'),
            'objet' => $this->sanitize($_POST['objet']),
            'statut'=> $statut,
            'sub'   => $_POST['substitut_id'] ?: null,
            'cab'   => $cabinetId,
            'dlim'  => $dlim,
            'dinst' => $dinst,
            'by'    => Auth::userId(),
        ]);
        $dossierId = (int)$this->db->lastInsertId();

        $this->db->prepare("INSERT INTO mouvements_dossier (dossier_id,user_id,type_mouvement,nouveau_statut,description) VALUES (?,?,'creation',?,?)")
            ->execute([$dossierId, Auth::userId(), $statut, 'Dossier créé manuellement']);

        $this->flash('success', "Dossier créé : {$numeroRG}");
        $this->redirect('/dossiers/show/' . $dossierId);
    }

    public function show(string $id): void {
        Auth::requireLogin();
        $dossier = $this->getDossierDetail((int)$id);
        if (!$dossier) { $this->redirect('/dossiers'); }
        $flash   = $this->getFlash();
        $user    = Auth::currentUser();

        $parties   = $this->db->prepare("SELECT * FROM parties WHERE dossier_id=? ORDER BY type_partie,nom")->execute([(int)$id]) ? $this->db->prepare("SELECT * FROM parties WHERE dossier_id=? ORDER BY type_partie,nom") : null;
        $partiesStmt = $this->db->prepare("SELECT * FROM parties WHERE dossier_id=? ORDER BY type_partie,nom");
        $partiesStmt->execute([(int)$id]);
        $parties = $partiesStmt->fetchAll();

        $audStmt = $this->db->prepare("SELECT a.*, s.nom as salle_nom, u.nom as president_nom, u.prenom as president_prenom FROM audiences a LEFT JOIN salles_audience s ON a.salle_id=s.id LEFT JOIN users u ON a.president_id=u.id WHERE a.dossier_id=? ORDER BY a.date_audience DESC");
        $audStmt->execute([(int)$id]);
        $audiences = $audStmt->fetchAll();

        $jugStmt = $this->db->prepare("SELECT j.*, u.nom as greffier_nom FROM jugements j LEFT JOIN users u ON j.greffier_id=u.id WHERE j.dossier_id=? ORDER BY j.date_jugement DESC");
        $jugStmt->execute([(int)$id]);
        $jugements = $jugStmt->fetchAll();

        $mvtStmt = $this->db->prepare("SELECT m.*, u.nom, u.prenom FROM mouvements_dossier m LEFT JOIN users u ON m.user_id=u.id WHERE m.dossier_id=? ORDER BY m.created_at DESC");
        $mvtStmt->execute([(int)$id]);
        $mouvements = $mvtStmt->fetchAll();

        $detStmt = $this->db->prepare("SELECT * FROM detenus WHERE dossier_id=?");
        $detStmt->execute([(int)$id]);
        $detenus = $detStmt->fetchAll();

        $cabinets  = $this->db->query("SELECT * FROM cabinets_instruction WHERE actif=1")->fetchAll();
        $salles    = $this->db->query("SELECT * FROM salles_audience WHERE actif=1")->fetchAll();
        $jugesStmt = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code IN ('president','juge_siege','vice_president') AND u.actif=1");
        $juges     = $jugesStmt->fetchAll();
        $greffiers = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='greffier' AND u.actif=1")->fetchAll();

        $this->view('dossiers/show', compact('dossier','parties','audiences','jugements','mouvements','detenus','cabinets','salles','juges','greffiers','flash','user'));
    }

    public function edit(string $id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur']);
        $dossier  = $this->getDossierDetail((int)$id);
        if (!$dossier) { $this->redirect('/dossiers'); }
        $user     = Auth::currentUser();
        $substituts = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='substitut_procureur' AND u.actif=1")->fetchAll();
        $cabinets  = $this->db->query("SELECT * FROM cabinets_instruction WHERE actif=1")->fetchAll();
        $this->view('dossiers/edit', compact('dossier','substituts','cabinets','user'));
    }

    public function update(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $this->db->prepare("UPDATE dossiers SET objet=:objet, type_affaire=:type, statut=:statut WHERE id=:id")
            ->execute([
                'objet'  => $this->sanitize($_POST['objet']),
                'type'   => $_POST['type_affaire'],
                'statut' => $_POST['statut'],
                'id'     => (int)$id,
            ]);
        $this->flash('success', 'Dossier mis à jour.');
        $this->redirect('/dossiers/show/' . $id);
    }

    public function affecterInstruction(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur','president']);
        $cabinetId = (int)($_POST['cabinet_id'] ?? 0);
        if (!$cabinetId) { $this->flash('error','Sélectionner un cabinet.'); $this->redirect('/dossiers/show/'.$id); }

        $num    = new Numerotation($this->db);
        $stmtD  = $this->db->prepare("SELECT numero_ri FROM dossiers WHERE id=?");
        $stmtD->execute([(int)$id]);
        $row    = $stmtD->fetch();
        $numRI  = $row['numero_ri'] ?: $num->genererRI();

        $this->db->prepare("UPDATE dossiers SET cabinet_id=:cab, numero_ri=:ri, statut='en_instruction', date_instruction_debut=CURDATE() WHERE id=:id")
            ->execute(['cab'=>$cabinetId,'ri'=>$numRI,'id'=>(int)$id]);

        $this->db->prepare("INSERT INTO mouvements_dossier (dossier_id,user_id,type_mouvement,ancien_statut,nouveau_statut,description) VALUES (?,?,'affectation_instruction',?,?,'Affecté au cabinet d''instruction')")
            ->execute([(int)$id, Auth::userId(), 'parquet', 'en_instruction']);

        $this->flash('success', "Dossier envoyé en instruction — {$numRI}.");
        $this->redirect('/dossiers/show/' . $id);
    }

    public function envoyerAudience(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $this->db->prepare("UPDATE dossiers SET statut='en_audience' WHERE id=?")->execute([(int)$id]);
        $this->db->prepare("INSERT INTO mouvements_dossier (dossier_id,user_id,type_mouvement,nouveau_statut,description) VALUES (?,?,'renvoi_audience','en_audience','Dossier envoyé en audience')")
            ->execute([(int)$id, Auth::userId()]);
        $this->flash('success', 'Dossier envoyé en audience.');
        $this->redirect('/dossiers/show/' . $id);
    }

    public function addPartie(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $this->db->prepare(
            "INSERT INTO parties (dossier_id,type_partie,nom,prenom,date_naissance,nationalite,profession,adresse,telephone) VALUES (?,?,?,?,?,?,?,?,?)"
        )->execute([
            (int)$id,
            $_POST['type_partie'],
            $this->sanitize($_POST['nom']),
            $this->sanitize($_POST['prenom'] ?? ''),
            $_POST['date_naissance'] ?: null,
            $this->sanitize($_POST['nationalite'] ?? 'Nigérienne'),
            $this->sanitize($_POST['profession'] ?? ''),
            $this->sanitize($_POST['adresse'] ?? ''),
            $this->sanitize($_POST['telephone'] ?? ''),
        ]);
        $this->flash('success', 'Partie ajoutée.');
        $this->redirect('/dossiers/show/' . $id);
    }

    public function deletePartie(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $dossierId = (int)($_POST['dossier_id'] ?? 0);
        $this->db->prepare("DELETE FROM parties WHERE id=?")->execute([(int)$id]);
        $this->flash('success', 'Partie supprimée.');
        $this->redirect('/dossiers/show/' . $dossierId);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /dossiers/classer/{id}
    // ─────────────────────────────────────────────────────────────
    public function classerDossier(string $id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','substitut_procureur']);
        CSRF::check();
        $id     = (int)$id;
        $motif  = $this->sanitize($_POST['motif_classement'] ?? '');
        if (!$motif) { $this->flash('error','Veuillez indiquer le motif de classement.'); $this->redirect('/dossiers/show/'.$id); return; }
        $ancien = $this->db->query("SELECT statut FROM dossiers WHERE id=$id")->fetchColumn();
        $this->db->prepare("UPDATE dossiers SET statut='classe', motif_classement=:m WHERE id=:id")
            ->execute([':m'=>$motif,':id'=>$id]);
        $this->db->prepare("INSERT INTO mouvements_dossier (dossier_id,user_id,type_mouvement,ancien_statut,nouveau_statut,description) VALUES (?,?,'classement',?,?,'Classé sans suite')")
            ->execute([$id, Auth::userId(), $ancien, 'classe']);
        $this->flash('success', 'Dossier classé sans suite.');
        $this->redirect('/dossiers/show/' . $id);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /dossiers/declasser/{id}
    // ─────────────────────────────────────────────────────────────
    public function declasserDossier(string $id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur']);
        CSRF::check();
        $id    = (int)$id;
        $motif = $this->sanitize($_POST['motif_declassement'] ?? '');
        if (!$motif) { $this->flash('error','Veuillez indiquer le motif du déclassement.'); $this->redirect('/dossiers/show/'.$id); return; }
        // Vérifier que le dossier est bien classé
        $dossier = $this->db->query("SELECT statut,motif_classement FROM dossiers WHERE id=$id")->fetch();
        if (!$dossier || $dossier['statut'] !== 'classe') {
            $this->flash('error','Ce dossier n\'est pas classé.'); $this->redirect('/dossiers/show/'.$id); return;
        }
        $this->db->prepare("UPDATE dossiers SET statut='parquet', motif_classement=NULL WHERE id=:id")
            ->execute([':id'=>$id]);
        $this->db->prepare("INSERT INTO mouvements_dossier (dossier_id,user_id,type_mouvement,ancien_statut,nouveau_statut,description) VALUES (?,?,'declassement','classe','parquet',?)")
            ->execute([$id, Auth::userId(), 'Déclassé : '.$motif]);
        $this->flash('success', 'Dossier déclassé et remis au parquet.');
        $this->redirect('/dossiers/show/' . $id);
    }


    // ─────────────────────────────────────────────────────────────
    // GET /api/dossiers/preview/{id}  — Aperçu rapide (JSON)
    // ─────────────────────────────────────────────────────────────
    public function apiPreview(string $id): void {
        Auth::requireLogin();
        $id = (int)$id;

        $stmt = $this->db->prepare(
            "SELECT d.*,
                    us.nom as substitut_nom, us.prenom as substitut_prenom,
                    ci.numero as cabinet_num, ci.libelle as cabinet_lib,
                    ji.nom as juge_instr_nom, ji.prenom as juge_instr_prenom
             FROM dossiers d
             LEFT JOIN users us ON d.substitut_id = us.id
             LEFT JOIN cabinets_instruction ci ON d.cabinet_id = ci.id
             LEFT JOIN users ji ON ci.juge_id = ji.id
             WHERE d.id = ?"
        );
        $stmt->execute([$id]);
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$d) {
            $this->json(['success' => false, 'message' => 'Dossier introuvable.'], 404);
            return;
        }

        // Compter parties, audiences, détenus
        $nbParties = (int)$this->db->prepare("SELECT COUNT(*) FROM parties WHERE dossier_id=?")
            ->execute([$id]) ? $this->db->prepare("SELECT COUNT(*) FROM parties WHERE dossier_id=?")->execute([$id]) && 1 : 0;
        $stmtP = $this->db->prepare("SELECT COUNT(*) FROM parties WHERE dossier_id=?");
        $stmtP->execute([$id]);
        $nbParties = (int)$stmtP->fetchColumn();

        $stmtA = $this->db->prepare("SELECT COUNT(*) FROM audiences WHERE dossier_id=?");
        $stmtA->execute([$id]);
        $nbAudiences = (int)$stmtA->fetchColumn();

        $stmtJ = $this->db->prepare("SELECT COUNT(*) FROM jugements WHERE dossier_id=?");
        $stmtJ->execute([$id]);
        $nbJugements = (int)$stmtJ->fetchColumn();

        $stmtDet = $this->db->prepare("SELECT COUNT(*) FROM detenus WHERE dossier_id=?");
        $stmtDet->execute([$id]);
        $nbDetenus = (int)$stmtDet->fetchColumn();

        // Dernier mouvement
        $stmtM = $this->db->prepare(
            "SELECT m.type_mouvement, m.description, m.created_at, u.prenom, u.nom
             FROM mouvements_dossier m
             LEFT JOIN users u ON m.user_id = u.id
             WHERE m.dossier_id = ?
             ORDER BY m.created_at DESC LIMIT 1"
        );
        $stmtM->execute([$id]);
        $dernierMvt = $stmtM->fetch(PDO::FETCH_ASSOC) ?: null;

        $this->json([
            'success' => true,
            'data'    => [
                'id'              => (int)$d['id'],
                'numero_rg'       => $d['numero_rg'],
                'numero_rp'       => $d['numero_rp'],
                'numero_ri'       => $d['numero_ri'],
                'type_affaire'    => $d['type_affaire'],
                'statut'          => $d['statut'],
                'objet'           => $d['objet'],
                'date_enregistrement' => $d['date_enregistrement'] ? date('d/m/Y', strtotime($d['date_enregistrement'])) : '—',
                'date_limite_traitement' => $d['date_limite_traitement'] ? date('d/m/Y', strtotime($d['date_limite_traitement'])) : null,
                'substitut'       => trim(($d['substitut_prenom'] ?? '') . ' ' . ($d['substitut_nom'] ?? '')) ?: '—',
                'cabinet'         => $d['cabinet_num'] ? ($d['cabinet_num'] . ' — ' . $d['cabinet_lib']) : '—',
                'juge_instruction'=> $d['juge_instr_prenom'] ? ($d['juge_instr_prenom'] . ' ' . $d['juge_instr_nom']) : '—',
                'mode_poursuite'  => $d['mode_poursuite'] ?? null,
                'nb_parties'      => $nbParties,
                'nb_audiences'    => $nbAudiences,
                'nb_jugements'    => $nbJugements,
                'nb_detenus'      => $nbDetenus,
                'dernier_mouvement' => $dernierMvt ? [
                    'type'        => $dernierMvt['type_mouvement'],
                    'description' => $dernierMvt['description'],
                    'date'        => date('d/m/Y H:i', strtotime($dernierMvt['created_at'])),
                    'user'        => trim(($dernierMvt['prenom'] ?? '') . ' ' . ($dernierMvt['nom'] ?? '')),
                ] : null,
            ],
        ]);
    }

    private function getDossierDetail(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT d.*,
                    us.nom as substitut_nom, us.prenom as substitut_prenom,
                    ci.numero as cabinet_num, ci.libelle as cabinet_lib,
                    ji.nom as juge_instr_nom, ji.prenom as juge_instr_prenom,
                    js.nom as juge_siege_nom, js.prenom as juge_siege_prenom
             FROM dossiers d
             LEFT JOIN users us ON d.substitut_id = us.id
             LEFT JOIN cabinets_instruction ci ON d.cabinet_id = ci.id
             LEFT JOIN users ji ON ci.juge_id = ji.id
             LEFT JOIN users js ON d.juge_siege_id = js.id
             WHERE d.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
