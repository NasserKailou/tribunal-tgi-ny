<?php
class PVController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();

        $where  = [];
        $params = [];
        $search = trim($_GET['q'] ?? '');
        $statut = $_GET['statut'] ?? '';
        $type   = $_GET['type'] ?? '';
        $antiterro = $_GET['antiterro'] ?? '';

        if ($search) {
            $where[]  = "(p.numero_rg LIKE :q OR p.numero_pv LIKE :q OR p.description_faits LIKE :q)";
            $params['q'] = "%{$search}%";
        }
        if ($statut) {
            $where[] = "p.statut = :statut";
            $params['statut'] = $statut;
        }
        if ($type) {
            $where[] = "p.type_affaire = :type";
            $params['type'] = $type;
        }
        if ($antiterro === '1') {
            $where[] = "p.est_antiterroriste = 1";
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM pv p $whereSQL");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "SELECT p.*, ue.nom as unite_nom,
                       us.nom as substitut_nom, us.prenom as substitut_prenom,
                       r.nom as region_nom, dep.nom as dept_nom, c.nom as commune_nom
                FROM pv p
                LEFT JOIN unites_enquete ue ON p.unite_enquete_id = ue.id
                LEFT JOIN users us ON p.substitut_id = us.id
                LEFT JOIN regions r ON p.region_id = r.id
                LEFT JOIN departements dep ON p.departement_id = dep.id
                LEFT JOIN communes c ON p.commune_id = c.id
                $whereSQL ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $pvList = $stmt->fetchAll();

        $totalPages = ceil($total / $perPage);
        $substituts = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='substitut_procureur' AND u.actif=1")->fetchAll();

        $this->view('pv/index', compact('pvList','total','page','perPage','totalPages','search','statut','type','antiterro','substituts','flash','user'));
    }

    public function create(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','substitut_procureur','president']);
        $user       = Auth::currentUser();
        $unites     = $this->db->query("SELECT * FROM unites_enquete WHERE actif=1 ORDER BY nom")->fetchAll();
        $regions    = $this->db->query("SELECT * FROM regions ORDER BY nom")->fetchAll();
        $primos     = $this->db->query("SELECT * FROM primo_intervenants WHERE actif=1 ORDER BY nom")->fetchAll();
        $infractions= $this->db->query("SELECT id, code, libelle, categorie FROM infractions ORDER BY libelle")->fetchAll();
        $num        = new Numerotation($this->db);
        $suggestRG  = $num->genererRG();
        $this->view('pv/create', compact('unites','regions','primos','infractions','suggestRG','user'));
    }

    public function store(): void {
        Auth::requireLogin();
        CSRF::check();
        $num = new Numerotation($this->db);
        $numeroRG = $num->genererRG();

        $stmt = $this->db->prepare(
            "INSERT INTO pv (numero_pv, numero_rg, unite_enquete_id, date_pv, date_reception,
              type_affaire, infraction_id, est_antiterroriste, region_id, departement_id, commune_id,
              description_faits, statut, created_by)
             VALUES (:pv,:rg,:ue,:dpv,:drec,:type,:infr,:anti,:reg,:dep,:com,:desc,'recu',:by)"
        );
        $stmt->execute([
            'pv'   => $this->sanitize($_POST['numero_pv'] ?? ''),
            'rg'   => $numeroRG,
            'ue'   => $_POST['unite_enquete_id'] ?: null,
            'dpv'  => $_POST['date_pv'],
            'drec' => $_POST['date_reception'],
            'type' => $_POST['type_affaire'],
            'infr' => !empty($_POST['infraction_id']) ? (int)$_POST['infraction_id'] : null,
            'anti' => isset($_POST['est_antiterroriste']) ? 1 : 0,
            'reg'  => $_POST['region_id'] ?: null,
            'dep'  => $_POST['departement_id'] ?: null,
            'com'  => $_POST['commune_id'] ?: null,
            'desc' => $this->sanitize($_POST['description_faits'] ?? ''),
            'by'   => Auth::userId(),
        ]);
        $pvId = (int)$this->db->lastInsertId();

        // Primo intervenants
        if (!empty($_POST['primo_intervenants']) && is_array($_POST['primo_intervenants'])) {
            $insPI = $this->db->prepare("INSERT IGNORE INTO pv_primo_intervenants (pv_id, primo_intervenant_id) VALUES (?,?)");
            foreach ($_POST['primo_intervenants'] as $piId) {
                $insPI->execute([$pvId, (int)$piId]);
            }
        }

        $this->flash('success', "PV enregistré avec le numéro {$numeroRG}.");
        $this->redirect('/pv/show/' . $pvId);
    }

    public function show(string $id): void {
        Auth::requireLogin();
        $pv = $this->getPVDetail((int)$id);
        if (!$pv) { $this->redirect('/pv'); }
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $substituts  = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='substitut_procureur' AND u.actif=1")->fetchAll();
        $cabinets    = $this->db->query("SELECT * FROM cabinets_instruction WHERE actif=1")->fetchAll();
        $infractions = $this->db->query("SELECT id, code, libelle, categorie FROM infractions ORDER BY libelle")->fetchAll();

        // Dossier lié éventuel
        $dossier = null;
        if ($pv['id']) {
            $dossierStmt = $this->db->prepare("SELECT * FROM dossiers WHERE pv_id=? LIMIT 1");
            $dossierStmt->execute([$pv['id']]);
            $dossier = $dossierStmt->fetch() ?: null;
        }

        $this->view('pv/show', compact('pv','flash','user','substituts','cabinets','infractions','dossier'));
    }

    public function edit(string $id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur']);
        $pv          = $this->getPVDetail((int)$id);
        if (!$pv) { $this->redirect('/pv'); }
        $user        = Auth::currentUser();
        $unites      = $this->db->query("SELECT * FROM unites_enquete WHERE actif=1 ORDER BY nom")->fetchAll();
        $regions     = $this->db->query("SELECT * FROM regions ORDER BY nom")->fetchAll();
        $primos      = $this->db->query("SELECT * FROM primo_intervenants WHERE actif=1 ORDER BY nom")->fetchAll();
        $infractions = $this->db->query("SELECT id, code, libelle, categorie FROM infractions ORDER BY libelle")->fetchAll();
        $this->view('pv/edit', compact('pv','unites','regions','primos','infractions','user'));
    }

    public function update(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $stmt = $this->db->prepare(
            "UPDATE pv SET numero_pv=:pv, unite_enquete_id=:ue, date_pv=:dpv, date_reception=:drec,
             type_affaire=:type, infraction_id=:infr, est_antiterroriste=:anti, region_id=:reg, departement_id=:dep,
             commune_id=:com, description_faits=:desc WHERE id=:id"
        );
        $stmt->execute([
            'pv'   => $this->sanitize($_POST['numero_pv'] ?? ''),
            'ue'   => $_POST['unite_enquete_id'] ?: null,
            'dpv'  => $_POST['date_pv'],
            'drec' => $_POST['date_reception'],
            'type' => $_POST['type_affaire'],
            'infr' => !empty($_POST['infraction_id']) ? (int)$_POST['infraction_id'] : null,
            'anti' => isset($_POST['est_antiterroriste']) ? 1 : 0,
            'reg'  => $_POST['region_id'] ?: null,
            'dep'  => $_POST['departement_id'] ?: null,
            'com'  => $_POST['commune_id'] ?: null,
            'desc' => $this->sanitize($_POST['description_faits'] ?? ''),
            'id'   => (int)$id,
        ]);

        // Mise à jour primo intervenants
        $this->db->prepare("DELETE FROM pv_primo_intervenants WHERE pv_id=?")->execute([(int)$id]);
        if (!empty($_POST['primo_intervenants']) && is_array($_POST['primo_intervenants'])) {
            $insPI = $this->db->prepare("INSERT IGNORE INTO pv_primo_intervenants (pv_id, primo_intervenant_id) VALUES (?,?)");
            foreach ($_POST['primo_intervenants'] as $piId) {
                $insPI->execute([(int)$id, (int)$piId]);
            }
        }

        $this->flash('success', 'PV mis à jour avec succès.');
        $this->redirect('/pv/show/' . $id);
    }

    public function affecter(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','president']);
        $substitutId = (int)($_POST['substitut_id'] ?? 0);
        if (!$substitutId) {
            $this->flash('error', 'Veuillez sélectionner un substitut.');
            $this->redirect('/pv/show/' . $id);
        }
        $this->db->prepare("UPDATE pv SET substitut_id=:s, statut='en_traitement', date_affectation_substitut=CURDATE() WHERE id=:id")
            ->execute(['s' => $substitutId, 'id' => (int)$id]);
        $this->flash('success', 'PV affecté au substitut du procureur.');
        $this->redirect('/pv/show/' . $id);
    }

    public function classer(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur']);
        $motif = $this->sanitize($_POST['motif_classement'] ?? '');
        $this->db->prepare("UPDATE pv SET statut='classe', motif_classement=:m, date_classement=CURDATE() WHERE id=:id")
            ->execute(['m' => $motif, 'id' => (int)$id]);
        $this->flash('success', 'PV classé sans suite.');
        $this->redirect('/pv/show/' . $id);
    }

    public function declasser(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur']);
        $id = (int)$id;
        $motif = $this->sanitize($_POST['motif_declassement'] ?? '');
        if (!$motif) {
            $this->flash('error', 'Veuillez indiquer le motif du déclassement.');
            $this->redirect('/pv/show/' . $id);
            return;
        }
        $pv = $this->db->prepare("SELECT statut FROM pv WHERE id=?")->execute([$id])
            ? $this->db->prepare("SELECT statut FROM pv WHERE id=?") : null;
        $stmtPV = $this->db->prepare("SELECT statut FROM pv WHERE id=?");
        $stmtPV->execute([$id]);
        $pv = $stmtPV->fetch();
        if (!$pv || $pv['statut'] !== 'classe') {
            $this->flash('error', 'Ce PV n\'est pas classé sans suite.');
            $this->redirect('/pv/show/' . $id);
            return;
        }
        $this->db->prepare(
            "UPDATE pv SET statut='en_traitement', motif_classement=NULL,
             motif_declassement=:m, date_declassement=CURDATE()
             WHERE id=:id"
        )->execute([':m' => $motif, ':id' => $id]);
        $this->flash('success', 'PV déclassé et remis en traitement.');
        $this->redirect('/pv/show/' . $id);
    }

    public function transferer(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur']);

        $destination = $_POST['destination'] ?? '';
        $stmtPV = $this->db->prepare("SELECT * FROM pv WHERE id=?");
        $stmtPV->execute([(int)$id]);
        $pvData = $stmtPV->fetch();
        if (!$pvData) { $this->redirect('/pv'); }

        $num  = new Numerotation($this->db);
        $annee = date('Y');

        // Mode de poursuite (uniquement pour instruction)
        $modePoursuite = 'aucun';
        if ($destination === 'instruction') {
            $mp = $_POST['mode_poursuite'] ?? 'aucun';
            $modePoursuite = in_array($mp, ['aucun','CD','FD','CRCP','RI']) ? $mp : 'aucun';
        }

        // Créer le dossier
        $numeroRG = $num->genererRG($annee);
        $numeroRP = $num->genererRP($annee);
        $numeroRI = ($destination === 'instruction') ? $num->genererRI($annee) : null;

        $statut   = ($destination === 'instruction') ? 'en_instruction' : 'en_audience';
        $cabinetId = ($destination === 'instruction') ? ($_POST['cabinet_id'] ?: null) : null;
        $dateInstDeb = ($destination === 'instruction') ? date('Y-m-d') : null;
        $dateLimite  = ($destination === 'instruction') 
            ? date('Y-m-d', strtotime('+' . DELAI_INSTRUCTION_MOIS . ' months'))
            : date('Y-m-d', strtotime('+30 days'));

        $ins = $this->db->prepare(
            "INSERT INTO dossiers (pv_id, numero_rg, numero_rp, numero_ri, type_affaire,
             date_enregistrement, objet, statut, substitut_id, cabinet_id, mode_poursuite,
             date_limite_traitement, date_instruction_debut, created_by)
             VALUES (:pvid, :rg, :rp, :ri, :type, CURDATE(), :objet, :statut, :sub, :cab, :mp, :dlim, :dinst, :by)"
        );
        $ins->execute([
            'pvid'  => (int)$id,
            'rg'    => $numeroRG,
            'rp'    => $numeroRP,
            'ri'    => $numeroRI,
            'type'  => $pvData['type_affaire'],
            'objet' => $this->sanitize($_POST['objet'] ?? $pvData['description_faits'] ?? 'À compléter'),
            'statut'=> $statut,
            'sub'   => $pvData['substitut_id'],
            'cab'   => $cabinetId,
            'mp'    => $modePoursuite,
            'dlim'  => $dateLimite,
            'dinst' => $dateInstDeb,
            'by'    => Auth::userId(),
        ]);
        $dossierId = (int)$this->db->lastInsertId();

        // Historique
        $modePoursuiteLabel = [
            'aucun' => 'Aucun', 'CD' => 'Citation Directe', 'FD' => 'Flagrant Délit',
            'CRCP'  => 'CRCP', 'RI' => 'Réquisitoire Introductif'
        ][$modePoursuite] ?? $modePoursuite;
        $histDesc = "Dossier créé depuis PV {$pvData['numero_rg']}";
        if ($destination === 'instruction') {
            $histDesc .= " — Mode de poursuite : {$modePoursuiteLabel}";
        }
        $this->db->prepare("INSERT INTO mouvements_dossier (dossier_id, user_id, type_mouvement, nouveau_statut, description) VALUES (?,?,?,?,?)")
            ->execute([$dossierId, Auth::userId(), 'creation', $statut, $histDesc]);

        // Mettre à jour le PV
        $pvStatut = ($destination === 'instruction') ? 'transfere_instruction' : 'transfere_jugement_direct';
        $this->db->prepare("UPDATE pv SET statut=? WHERE id=?")->execute([$pvStatut, (int)$id]);

        $this->flash('success', "Dossier créé : {$numeroRG}" . ($numeroRI ? " / {$numeroRI}" : '') . ".");
        $this->redirect('/dossiers/show/' . $dossierId);
    }

    // API endpoints AJAX
    public function apiDepartements(string $region_id): void {
        $stmt = $this->db->prepare("SELECT id, nom FROM departements WHERE region_id=? ORDER BY nom");
        $stmt->execute([(int)$region_id]);
        $this->json($stmt->fetchAll());
    }

    public function apiCommunes(string $departement_id): void {
        $stmt = $this->db->prepare("SELECT id, nom FROM communes WHERE departement_id=? ORDER BY nom");
        $stmt->execute([(int)$departement_id]);
        $this->json($stmt->fetchAll());
    }

    private function getPVDetail(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT p.*, ue.nom as unite_nom, ue.type as unite_type,
                    us.nom as substitut_nom, us.prenom as substitut_prenom,
                    r.nom as region_nom, dep.nom as dept_nom, c.nom as commune_nom,
                    cb.nom as created_by_nom, cb.prenom as created_by_prenom,
                    inf.libelle as infraction_libelle, inf.code as infraction_code, inf.categorie as infraction_categorie
             FROM pv p
             LEFT JOIN unites_enquete ue ON p.unite_enquete_id = ue.id
             LEFT JOIN users us ON p.substitut_id = us.id
             LEFT JOIN regions r ON p.region_id = r.id
             LEFT JOIN departements dep ON p.departement_id = dep.id
             LEFT JOIN communes c ON p.commune_id = c.id
             LEFT JOIN users cb ON p.created_by = cb.id
             LEFT JOIN infractions inf ON p.infraction_id = inf.id
             WHERE p.id = ?"
        );
        $stmt->execute([$id]);
        $pv = $stmt->fetch();
        if (!$pv) return null;

        // Primo intervenants
        $piStmt = $this->db->prepare(
            "SELECT pi.* FROM primo_intervenants pi
             JOIN pv_primo_intervenants ppi ON pi.id = ppi.primo_intervenant_id
             WHERE ppi.pv_id = ?"
        );
        $piStmt->execute([$id]);
        $pv['primo_intervenants'] = $piStmt->fetchAll();
        $pv['primo_ids'] = array_column($pv['primo_intervenants'], 'id');

        return $pv;
    }
}
