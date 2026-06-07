<?php
/**
 * PVController — Gestion des Procès-Verbaux
 *
 * Corrections et améliorations v3.8 :
 *  - Filtre accès serveur : substitut ne voit que ses PV affectés
 *  - Greffier : voit tous les PV mais avec distinction créateur/affectant
 *  - Multi-infractions : pv_infractions_enquete (unité d'enquête)
 *  - Qualification substitut : pv_qualifications_substitut
 *  - Pièces jointes PV : upload/suppression via DocumentController
 *  - API AJAX : infraction rapide depuis PV/create
 */
class PVController extends Controller {

    // ══════════════════════════════════════════════════════════════════════
    // INDEX — Liste des PV avec filtres et contrôle d'accès serveur
    // ══════════════════════════════════════════════════════════════════════
    public function index(): void {
        Auth::requireLogin();
        $flash  = $this->getFlash();
        $user   = Auth::currentUser();
        $role   = Auth::roleCode();
        $userId = Auth::userId();

        $where  = [];
        $params = [];
        $search    = trim($_GET['q'] ?? '');
        $statut    = $_GET['statut'] ?? '';
        $type      = $_GET['type'] ?? '';
        $antiterro = $_GET['antiterro'] ?? '';

        // ── Filtre recherche ───────────────────────────────────────────
        if ($search) {
            $where[]        = "(p.numero_rg LIKE :q1 OR p.numero_pv LIKE :q2 OR p.description_faits LIKE :q3)";
            $params['q1']   = "%{$search}%";
            $params['q2']   = "%{$search}%";
            $params['q3']   = "%{$search}%";
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

        // ── FILTRE ACCÈS SERVEUR ───────────────────────────────────────
        // Substitut : ne voit QUE ses PV affectés (contrôle serveur, pas juste UI)
        if ($role === 'substitut_procureur') {
            $where[] = "p.substitut_id = :uid_sub";
            $params['uid_sub'] = $userId;
        }
        // Greffier : voit tous mais on récupère la distinction créateur/affectant
        // (pas de restriction, mais le champ created_by est exposé dans la liste)

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM pv p $whereSQL");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "SELECT p.*,
                       ue.nom  AS unite_nom,
                       us.nom  AS substitut_nom, us.prenom AS substitut_prenom,
                       cb.nom  AS createur_nom,  cb.prenom AS createur_prenom,
                       r.nom   AS region_nom,
                       dep.nom AS dept_nom,
                       c.nom   AS commune_nom
                FROM pv p
                LEFT JOIN unites_enquete ue  ON p.unite_enquete_id = ue.id
                LEFT JOIN users us           ON p.substitut_id = us.id
                LEFT JOIN users cb           ON p.created_by   = cb.id
                LEFT JOIN regions r          ON p.region_id    = r.id
                LEFT JOIN departements dep   ON p.departement_id = dep.id
                LEFT JOIN communes c         ON p.commune_id   = c.id
                $whereSQL
                ORDER BY p.created_at DESC
                LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $pvList = $stmt->fetchAll();

        $totalPages = (int)ceil($total / $perPage);
        $substituts = $this->db->query(
            "SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id
             WHERE r.code='substitut_procureur' AND u.actif=1"
        )->fetchAll();

        $this->view('pv/index', compact(
            'pvList','total','page','perPage','totalPages',
            'search','statut','type','antiterro',
            'substituts','flash','user'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // CREATE
    // ══════════════════════════════════════════════════════════════════════
    public function create(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','substitut_procureur','president']);
        $user        = Auth::currentUser();
        $unites      = $this->db->query("SELECT * FROM unites_enquete WHERE actif=1 ORDER BY nom")->fetchAll();
        $regions     = $this->db->query("SELECT * FROM regions ORDER BY nom")->fetchAll();
        $primos      = $this->db->query("SELECT * FROM primo_intervenants WHERE actif=1 ORDER BY nom")->fetchAll();
        $infractions = $this->db->query(
            "SELECT id, code, libelle, categorie FROM infractions ORDER BY categorie, libelle"
        )->fetchAll();
        $num         = new Numerotation($this->db);
        $suggestRG   = $num->genererRG();
        $this->view('pv/create', compact(
            'unites','regions','primos','infractions','suggestRG','user'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // STORE
    // ══════════════════════════════════════════════════════════════════════
    public function store(): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','greffier','procureur','substitut_procureur','president']);

        $num      = new Numerotation($this->db);
        $numeroRG = $num->genererRG();

        // On garde infraction_id pour compatibilité (première infraction cochée ou null)
        $infractionsPostIds = array_map('intval',
            array_filter((array)($_POST['infraction_ids'] ?? []))
        );
        $primaryInfrId = !empty($infractionsPostIds) ? $infractionsPostIds[0] : null;

        $stmt = $this->db->prepare(
            "INSERT INTO pv (numero_pv, numero_rg, unite_enquete_id, date_pv, date_reception,
              type_affaire, infraction_id, est_antiterroriste, region_id, departement_id, commune_id,
              description_faits, statut, created_by)
             VALUES (:pv,:rg,:ue,:dpv,:drec,:type,:infr,:anti,:reg,:dep,:com,:desc,'recu',:by)"
        );
        $stmt->execute([
            'pv'   => $this->sanitize($_POST['numero_pv']    ?? ''),
            'rg'   => $numeroRG,
            'ue'   => $_POST['unite_enquete_id']             ?: null,
            'dpv'  => $_POST['date_pv'],
            'drec' => $_POST['date_reception'],
            'type' => $_POST['type_affaire'],
            'infr' => $primaryInfrId,
            'anti' => isset($_POST['est_antiterroriste'])     ? 1 : 0,
            'reg'  => $_POST['region_id']                    ?: null,
            'dep'  => $_POST['departement_id']               ?: null,
            'com'  => $_POST['commune_id']                   ?: null,
            'desc' => $this->sanitize($_POST['description_faits'] ?? ''),
            'by'   => Auth::userId(),
        ]);
        $pvId = (int)$this->db->lastInsertId();

        // ── Infractions unité d'enquête (table de liaison) ──────────────
        if (!empty($infractionsPostIds)) {
            $insInfr = $this->db->prepare(
                "INSERT IGNORE INTO pv_infractions_enquete (pv_id, infraction_id) VALUES (?,?)"
            );
            foreach ($infractionsPostIds as $iid) {
                if ($iid > 0) $insInfr->execute([$pvId, $iid]);
            }
        }

        // ── Primo intervenants ──────────────────────────────────────────
        if (!empty($_POST['primo_intervenants']) && is_array($_POST['primo_intervenants'])) {
            $insPI = $this->db->prepare(
                "INSERT IGNORE INTO pv_primo_intervenants (pv_id, primo_intervenant_id) VALUES (?,?)"
            );
            foreach ($_POST['primo_intervenants'] as $piId) {
                $insPI->execute([$pvId, (int)$piId]);
            }
        }

        $this->flash('success', "PV enregistré avec le numéro {$numeroRG}.");
        $this->redirect('/pv/show/' . $pvId);
    }

    // ══════════════════════════════════════════════════════════════════════
    // SHOW
    // ══════════════════════════════════════════════════════════════════════
    public function show(string $id): void {
        Auth::requireLogin();
        $pvId = (int)$id;

        // ── Contrôle d'accès substitut ─────────────────────────────────
        if (Auth::roleCode() === 'substitut_procureur') {
            $chk = $this->db->prepare("SELECT substitut_id FROM pv WHERE id=?");
            $chk->execute([$pvId]);
            $row = $chk->fetch();
            if (!$row || (int)$row['substitut_id'] !== Auth::userId()) {
                $this->flash('error', 'Accès refusé : ce PV ne vous est pas affecté.');
                $this->redirect('/pv');
            }
        }

        $pv = $this->getPVDetail($pvId);
        if (!$pv) { $this->redirect('/pv'); }

        $flash       = $this->getFlash();
        $user        = Auth::currentUser();
        $substituts  = $this->db->query(
            "SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id
             WHERE r.code='substitut_procureur' AND u.actif=1"
        )->fetchAll();
        $cabinets    = $this->db->query("SELECT * FROM cabinets_instruction WHERE actif=1")->fetchAll();
        $infractions = $this->db->query(
            "SELECT id, code, libelle, categorie FROM infractions ORDER BY categorie, libelle"
        )->fetchAll();

        // Dossier lié éventuel
        $dossier = null;
        $dossierStmt = $this->db->prepare("SELECT * FROM dossiers WHERE pv_id=? LIMIT 1");
        $dossierStmt->execute([$pvId]);
        $dossier = $dossierStmt->fetch() ?: null;

        // Pièces jointes du PV
        $pjStmt = $this->db->prepare(
            "SELECT d.id,
                    COALESCE(d.nom_original, d.nom_fichier, d.nom_stockage) AS nom_original,
                    COALESCE(d.mime_type, d.type_mime, 'application/octet-stream') AS mime_type,
                    d.taille_octets, d.description, d.created_at,
                    CONCAT(u.prenom,' ',u.nom) AS uploaded_by_nom
             FROM documents d
             LEFT JOIN users u ON u.id = d.uploaded_by
             WHERE d.pv_id = :pv_id
             ORDER BY d.created_at DESC"
        );
        $pjStmt->execute([':pv_id' => $pvId]);
        $piecesJointes = $pjStmt->fetchAll();

        $this->view('pv/show', compact(
            'pv','flash','user','substituts','cabinets',
            'infractions','dossier','piecesJointes'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    // EDIT
    // ══════════════════════════════════════════════════════════════════════
    public function edit(string $id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur']);
        $pv = $this->getPVDetail((int)$id);
        if (!$pv) { $this->redirect('/pv'); }
        $user        = Auth::currentUser();
        $unites      = $this->db->query("SELECT * FROM unites_enquete WHERE actif=1 ORDER BY nom")->fetchAll();
        $regions     = $this->db->query("SELECT * FROM regions ORDER BY nom")->fetchAll();
        $primos      = $this->db->query("SELECT * FROM primo_intervenants WHERE actif=1 ORDER BY nom")->fetchAll();
        $infractions = $this->db->query(
            "SELECT id, code, libelle, categorie FROM infractions ORDER BY categorie, libelle"
        )->fetchAll();
        $this->view('pv/edit', compact('pv','unites','regions','primos','infractions','user'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // UPDATE
    // ══════════════════════════════════════════════════════════════════════
    public function update(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','greffier','procureur']);
        $pvId = (int)$id;

        $infractionsPostIds = array_map('intval',
            array_filter((array)($_POST['infraction_ids'] ?? []))
        );
        $primaryInfrId = !empty($infractionsPostIds) ? $infractionsPostIds[0] : null;

        $stmt = $this->db->prepare(
            "UPDATE pv SET numero_pv=:pv, unite_enquete_id=:ue, date_pv=:dpv, date_reception=:drec,
             type_affaire=:type, infraction_id=:infr, est_antiterroriste=:anti,
             region_id=:reg, departement_id=:dep, commune_id=:com,
             description_faits=:desc WHERE id=:id"
        );
        $stmt->execute([
            'pv'   => $this->sanitize($_POST['numero_pv']    ?? ''),
            'ue'   => $_POST['unite_enquete_id']             ?: null,
            'dpv'  => $_POST['date_pv'],
            'drec' => $_POST['date_reception'],
            'type' => $_POST['type_affaire'],
            'infr' => $primaryInfrId,
            'anti' => isset($_POST['est_antiterroriste'])     ? 1 : 0,
            'reg'  => $_POST['region_id']                    ?: null,
            'dep'  => $_POST['departement_id']               ?: null,
            'com'  => $_POST['commune_id']                   ?: null,
            'desc' => $this->sanitize($_POST['description_faits'] ?? ''),
            'id'   => $pvId,
        ]);

        // Mise à jour infractions enquête
        $this->db->prepare("DELETE FROM pv_infractions_enquete WHERE pv_id=?")->execute([$pvId]);
        if (!empty($infractionsPostIds)) {
            $insInfr = $this->db->prepare(
                "INSERT IGNORE INTO pv_infractions_enquete (pv_id, infraction_id) VALUES (?,?)"
            );
            foreach ($infractionsPostIds as $iid) {
                if ($iid > 0) $insInfr->execute([$pvId, $iid]);
            }
        }

        // Mise à jour primo intervenants
        $this->db->prepare("DELETE FROM pv_primo_intervenants WHERE pv_id=?")->execute([$pvId]);
        if (!empty($_POST['primo_intervenants']) && is_array($_POST['primo_intervenants'])) {
            $insPI = $this->db->prepare(
                "INSERT IGNORE INTO pv_primo_intervenants (pv_id, primo_intervenant_id) VALUES (?,?)"
            );
            foreach ($_POST['primo_intervenants'] as $piId) {
                $insPI->execute([$pvId, (int)$piId]);
            }
        }

        $this->flash('success', 'PV mis à jour avec succès.');
        $this->redirect('/pv/show/' . $id);
    }

    // ══════════════════════════════════════════════════════════════════════
    // QUALIFICATION SUBSTITUT — Enregistrement depuis show
    // POST /pv/qualifier/{id}
    // ══════════════════════════════════════════════════════════════════════
    public function qualifier(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur']);
        $pvId = (int)$id;

        // Contrôle : substitut ne peut qualifier que ses propres PV
        if (Auth::roleCode() === 'substitut_procureur') {
            $chk = $this->db->prepare("SELECT substitut_id FROM pv WHERE id=?");
            $chk->execute([$pvId]);
            $row = $chk->fetch();
            if (!$row || (int)$row['substitut_id'] !== Auth::userId()) {
                $this->flash('error', 'Accès refusé : ce PV ne vous est pas affecté.');
                $this->redirect('/pv');
            }
        }

        // Vider les qualifications existantes puis réinsérer
        $this->db->prepare("DELETE FROM pv_qualifications_substitut WHERE pv_id=?")->execute([$pvId]);

        $qualifIds = array_map('intval',
            array_filter((array)($_POST['qualification_ids'] ?? []))
        );
        $loi          = $this->sanitize($_POST['loi_applicable']  ?? '');
        $observations = $this->sanitize($_POST['observations_qualification'] ?? '');

        if (!empty($qualifIds)) {
            $ins = $this->db->prepare(
                "INSERT INTO pv_qualifications_substitut
                 (pv_id, infraction_id, loi_applicable, observations, created_by)
                 VALUES (?, ?, ?, ?, ?)"
            );
            foreach ($qualifIds as $qid) {
                if ($qid > 0) {
                    $ins->execute([$pvId, $qid, $loi ?: null, $observations ?: null, Auth::userId()]);
                }
            }
        }

        $this->flash('success', 'Qualification retenue enregistrée.');
        $this->redirect('/pv/show/' . $pvId);
    }

    // ══════════════════════════════════════════════════════════════════════
    // UPLOAD pièce jointe PV — POST /pv/upload/{id}
    // ══════════════════════════════════════════════════════════════════════
    public function uploadDocument(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $pvId = (int)$id;

        // Vérifier accès substitut
        if (Auth::roleCode() === 'substitut_procureur') {
            $chk = $this->db->prepare("SELECT substitut_id FROM pv WHERE id=?");
            $chk->execute([$pvId]);
            $row = $chk->fetch();
            if (!$row || (int)$row['substitut_id'] !== Auth::userId()) {
                $this->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
        }

        // Vérifier que le PV existe
        $stmtPV = $this->db->prepare("SELECT id FROM pv WHERE id=?");
        $stmtPV->execute([$pvId]);
        if (!$stmtPV->fetch()) {
            $this->json(['success' => false, 'message' => 'PV introuvable.'], 404);
        }

        if (empty($_FILES['fichier']) || $_FILES['fichier']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->json(['success' => false, 'message' => 'Aucun fichier reçu.'], 400);
        }

        $file = $_FILES['fichier'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'Erreur upload (code ' . $file['error'] . ').'], 400);
        }

        $typesOk = ['pdf','doc','docx','jpg','jpeg','png','xlsx','odt'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $typesOk)) {
            $this->json(['success' => false, 'message' => 'Type de fichier non autorisé.'], 400);
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            $this->json(['success' => false, 'message' => 'Fichier trop volumineux (max 10 Mo).'], 400);
        }

        // Dossier de stockage
        $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
                   . 'uploads' . DIRECTORY_SEPARATOR . 'pv_' . $pvId . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            $this->json(['success' => false, 'message' => 'Impossible de créer le répertoire.'], 500);
        }

        $nomOriginal = $file['name'];
        $hash        = substr(hash_file('sha256', $file['tmp_name']), 0, 16);
        $nomNettoye  = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nomOriginal);
        $nomStockage = $hash . '_' . $nomNettoye;
        $cheminAbs   = $uploadDir . $nomStockage;

        if (!move_uploaded_file($file['tmp_name'], $cheminAbs)) {
            $this->json(['success' => false, 'message' => 'Échec du déplacement du fichier.'], 500);
        }

        $cheminRelatif = 'uploads/pv_' . $pvId . '/' . $nomStockage;
        $mimeTypes     = ['pdf'=>'application/pdf','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                          'doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                          'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','odt'=>'application/vnd.oasis.opendocument.text'];
        $mimeReel = $mimeTypes[$ext] ?? 'application/octet-stream';

        $description = trim($_POST['description'] ?? '');

        $ins = $this->db->prepare(
            "INSERT INTO documents (pv_id, nom_original, nom_stockage, chemin_fichier,
             type_document, mime_type, taille_octets, description, uploaded_by)
             VALUES (:pvid, :nom_orig, :nom_stock, :chemin, 'piece_jointe', :mime, :taille, :desc, :by)"
        );
        $ins->execute([
            ':pvid'      => $pvId,
            ':nom_orig'  => $nomOriginal,
            ':nom_stock' => $nomStockage,
            ':chemin'    => $cheminRelatif,
            ':mime'      => $mimeReel,
            ':taille'    => $file['size'],
            ':desc'      => $description ?: null,
            ':by'        => Auth::userId(),
        ]);
        $newId = (int)$this->db->lastInsertId();

        $this->json([
            'success'  => true,
            'message'  => 'Pièce jointe ajoutée.',
            'document' => [
                'id'   => $newId,
                'nom'  => $nomOriginal,
                'type' => $mimeReel,
                'url'  => BASE_URL . '/documents/view/' . $newId,
                'date' => date('d/m/Y H:i'),
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // DELETE pièce jointe PV — POST /pv/document/delete/{docId}
    // ══════════════════════════════════════════════════════════════════════
    public function deleteDocument(string $docId): void {
        Auth::requireLogin();
        CSRF::check();
        $docId = (int)$docId;

        $stmt = $this->db->prepare("SELECT * FROM documents WHERE id=? AND pv_id IS NOT NULL");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch();

        if (!$doc) {
            $this->json(['success' => false, 'message' => 'Document introuvable.'], 404);
        }

        // Contrôle d'accès : seul l'uploader, un admin/procureur/greffier peut supprimer
        $canDelete = in_array(Auth::roleCode(), ['admin','procureur','greffier','president'])
                  || (int)$doc['uploaded_by'] === Auth::userId();

        if (!$canDelete) {
            $this->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        // Supprimer fichier physique
        if (!empty($doc['chemin_fichier'])) {
            $cheminAbs = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
                       . str_replace('/', DIRECTORY_SEPARATOR, $doc['chemin_fichier']);
            if (file_exists($cheminAbs)) {
                @unlink($cheminAbs);
            }
        }

        $this->db->prepare("DELETE FROM documents WHERE id=?")->execute([$docId]);
        $this->json(['success' => true, 'message' => 'Pièce jointe supprimée.']);
    }

    // ══════════════════════════════════════════════════════════════════════
    // API AJAX — Ajout rapide d'une infraction depuis PV/create
    // POST /api/infractions/store
    // ══════════════════════════════════════════════════════════════════════
    public function apiInfractionStore(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','substitut_procureur','president']);

        // Vérif CSRF (token en header ou POST)
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!CSRF::verify($token)) {
            $this->json(['success' => false, 'message' => 'Token CSRF invalide.'], 403);
        }

        $code    = strtoupper(trim($_POST['code']      ?? ''));
        $libelle = trim($_POST['libelle']              ?? '');
        $categorie = $_POST['categorie']               ?? 'correctionnelle';

        if (!$libelle) {
            $this->json(['success' => false, 'message' => 'Le libellé est obligatoire.'], 400);
        }

        $cats = ['criminelle','correctionnelle','contraventionnelle'];
        if (!in_array($categorie, $cats)) $categorie = 'correctionnelle';

        // Vérifier doublon libelle
        $chk = $this->db->prepare("SELECT id FROM infractions WHERE libelle=? LIMIT 1");
        $chk->execute([$libelle]);
        if ($chk->fetch()) {
            $this->json(['success' => false, 'message' => 'Une infraction avec ce libellé existe déjà.'], 409);
        }

        $this->db->prepare(
            "INSERT INTO infractions (code, libelle, categorie) VALUES (?, ?, ?)"
        )->execute([$code ?: null, $libelle, $categorie]);

        $newId = (int)$this->db->lastInsertId();
        $this->json([
            'success' => true,
            'infraction' => ['id' => $newId, 'code' => $code, 'libelle' => $libelle, 'categorie' => $categorie],
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // AFFECTER substitut
    // ══════════════════════════════════════════════════════════════════════
    public function affecter(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','president']);
        $substitutId = (int)($_POST['substitut_id'] ?? 0);
        if (!$substitutId) {
            $this->flash('error', 'Veuillez sélectionner un substitut.');
            $this->redirect('/pv/show/' . $id);
        }
        $this->db->prepare(
            "UPDATE pv SET substitut_id=:s, statut='en_traitement',
             date_affectation_substitut=CURDATE() WHERE id=:id"
        )->execute(['s' => $substitutId, 'id' => (int)$id]);
        $this->flash('success', 'PV affecté au substitut du procureur.');
        $this->redirect('/pv/show/' . $id);
    }

    // ══════════════════════════════════════════════════════════════════════
    // CLASSER
    // ══════════════════════════════════════════════════════════════════════
    public function classer(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur']);

        // Substitut : vérif accès
        if (Auth::roleCode() === 'substitut_procureur') {
            $chk = $this->db->prepare("SELECT substitut_id FROM pv WHERE id=?");
            $chk->execute([(int)$id]);
            $row = $chk->fetch();
            if (!$row || (int)$row['substitut_id'] !== Auth::userId()) {
                $this->flash('error', 'Accès refusé.'); $this->redirect('/pv'); }
        }

        $motif = $this->sanitize($_POST['motif_classement'] ?? '');
        $this->db->prepare(
            "UPDATE pv SET statut='classe', motif_classement=:m, date_classement=CURDATE() WHERE id=:id"
        )->execute(['m' => $motif, 'id' => (int)$id]);
        $this->flash('success', 'PV classé sans suite.');
        $this->redirect('/pv/show/' . $id);
    }

    // ══════════════════════════════════════════════════════════════════════
    // DÉCLASSER
    // ══════════════════════════════════════════════════════════════════════
    public function declasser(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur']);
        $id    = (int)$id;
        $motif = $this->sanitize($_POST['motif_declassement'] ?? '');
        if (!$motif) {
            $this->flash('error', 'Veuillez indiquer le motif du déclassement.');
            $this->redirect('/pv/show/' . $id);
            return;
        }
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
             motif_declassement=:m, date_declassement=CURDATE() WHERE id=:id"
        )->execute([':m' => $motif, ':id' => $id]);
        $this->flash('success', 'PV déclassé et remis en traitement.');
        $this->redirect('/pv/show/' . $id);
    }

    // ══════════════════════════════════════════════════════════════════════
    // TRANSFÉRER
    // ══════════════════════════════════════════════════════════════════════
    public function transferer(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur']);

        // Substitut : vérif accès
        if (Auth::roleCode() === 'substitut_procureur') {
            $chk = $this->db->prepare("SELECT substitut_id FROM pv WHERE id=?");
            $chk->execute([(int)$id]);
            $row = $chk->fetch();
            if (!$row || (int)$row['substitut_id'] !== Auth::userId()) {
                $this->flash('error', 'Accès refusé.'); $this->redirect('/pv'); }
        }

        $destination = $_POST['destination'] ?? '';
        $stmtPV = $this->db->prepare("SELECT * FROM pv WHERE id=?");
        $stmtPV->execute([(int)$id]);
        $pvData = $stmtPV->fetch();
        if (!$pvData) { $this->redirect('/pv'); }

        $num    = new Numerotation($this->db);
        $annee  = date('Y');

        $modePoursuite = 'aucun';
        if ($destination === 'instruction') {
            $mp = $_POST['mode_poursuite'] ?? 'aucun';
            $modePoursuite = in_array($mp, ['aucun','CD','FD','CRCP','RI']) ? $mp : 'aucun';
        }

        $numeroRG = $num->genererRG($annee);
        $numeroRP = $num->genererRP($annee);
        $numeroRI = ($destination === 'instruction') ? $num->genererRI($annee) : null;

        $statut      = ($destination === 'instruction') ? 'en_instruction' : 'en_audience';
        $cabinetId   = ($destination === 'instruction') ? ($_POST['cabinet_id'] ?: null) : null;
        $dateInstDeb = ($destination === 'instruction') ? date('Y-m-d') : null;
        $dateLimite  = ($destination === 'instruction')
            ? date('Y-m-d', strtotime('+' . DELAI_INSTRUCTION_MOIS . ' months'))
            : date('Y-m-d', strtotime('+30 days'));

        $ins = $this->db->prepare(
            "INSERT INTO dossiers (pv_id, numero_rg, numero_rp, numero_ri, type_affaire,
             date_enregistrement, objet, statut, substitut_id, cabinet_id, mode_poursuite,
             date_limite_traitement, date_instruction_debut, created_by)
             VALUES (:pvid,:rg,:rp,:ri,:type,CURDATE(),:objet,:statut,:sub,:cab,:mp,:dlim,:dinst,:by)"
        );
        $ins->execute([
            'pvid'  => (int)$id, 'rg' => $numeroRG, 'rp' => $numeroRP,
            'ri'    => $numeroRI, 'type' => $pvData['type_affaire'],
            'objet' => $this->sanitize($_POST['objet'] ?? $pvData['description_faits'] ?? 'À compléter'),
            'statut'=> $statut, 'sub' => $pvData['substitut_id'],
            'cab'   => $cabinetId, 'mp' => $modePoursuite,
            'dlim'  => $dateLimite, 'dinst' => $dateInstDeb,
            'by'    => Auth::userId(),
        ]);
        $dossierId = (int)$this->db->lastInsertId();

        $modePoursuiteLabel = ['aucun'=>'Aucun','CD'=>'Citation Directe','FD'=>'Flagrant Délit',
            'CRCP'=>'CRCP','RI'=>'Réquisitoire Introductif'][$modePoursuite] ?? $modePoursuite;
        $histDesc = "Dossier créé depuis PV {$pvData['numero_rg']}";
        if ($destination === 'instruction') $histDesc .= " — Mode de poursuite : {$modePoursuiteLabel}";

        $this->db->prepare(
            "INSERT INTO mouvements_dossier (dossier_id, user_id, type_mouvement, nouveau_statut, description)
             VALUES (?,?,?,?,?)"
        )->execute([$dossierId, Auth::userId(), 'creation', $statut, $histDesc]);

        $pvStatut = ($destination === 'instruction') ? 'transfere_instruction' : 'transfere_jugement_direct';
        $this->db->prepare("UPDATE pv SET statut=? WHERE id=?")->execute([$pvStatut, (int)$id]);

        $this->flash('success', "Dossier créé : {$numeroRG}" . ($numeroRI ? " / {$numeroRI}" : '') . ".");
        $this->redirect('/dossiers/show/' . $dossierId);
    }

    // ══════════════════════════════════════════════════════════════════════
    // API endpoints AJAX
    // ══════════════════════════════════════════════════════════════════════
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

    // ══════════════════════════════════════════════════════════════════════
    // HELPER — Détail complet d'un PV
    // ══════════════════════════════════════════════════════════════════════
    private function getPVDetail(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT p.*,
                    ue.nom AS unite_nom, ue.type AS unite_type,
                    us.nom AS substitut_nom, us.prenom AS substitut_prenom,
                    r.nom  AS region_nom,
                    dep.nom AS dept_nom,
                    c.nom   AS commune_nom,
                    cb.nom  AS created_by_nom, cb.prenom AS created_by_prenom,
                    inf.libelle  AS infraction_libelle,
                    inf.code     AS infraction_code,
                    inf.categorie AS infraction_categorie
             FROM pv p
             LEFT JOIN unites_enquete ue  ON p.unite_enquete_id = ue.id
             LEFT JOIN users us           ON p.substitut_id = us.id
             LEFT JOIN regions r          ON p.region_id = r.id
             LEFT JOIN departements dep   ON p.departement_id = dep.id
             LEFT JOIN communes c         ON p.commune_id = c.id
             LEFT JOIN users cb           ON p.created_by = cb.id
             LEFT JOIN infractions inf    ON p.infraction_id = inf.id
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
        $pv['primo_ids']          = array_column($pv['primo_intervenants'], 'id');

        // Infractions déclarées par l'unité d'enquête (table pv_infractions_enquete)
        // Avec fallback gracieux si la table n'existe pas encore (avant migration)
        try {
            $ieStmt = $this->db->prepare(
                "SELECT i.id, i.code, i.libelle, i.categorie
                 FROM pv_infractions_enquete pie
                 JOIN infractions i ON i.id = pie.infraction_id
                 WHERE pie.pv_id = ?
                 ORDER BY i.categorie, i.libelle"
            );
            $ieStmt->execute([$id]);
            $pv['infractions_enquete'] = $ieStmt->fetchAll();
            $pv['infractions_enquete_ids'] = array_column($pv['infractions_enquete'], 'id');
        } catch (\Exception $e) {
            $pv['infractions_enquete']     = [];
            $pv['infractions_enquete_ids'] = [];
        }

        // Qualifications retenues par le substitut
        try {
            $qsStmt = $this->db->prepare(
                "SELECT qs.*, i.code, i.libelle, i.categorie,
                        CONCAT(u.prenom,' ',u.nom) AS created_by_nom
                 FROM pv_qualifications_substitut qs
                 JOIN infractions i ON i.id = qs.infraction_id
                 LEFT JOIN users u  ON u.id = qs.created_by
                 WHERE qs.pv_id = ?
                 ORDER BY i.categorie, i.libelle"
            );
            $qsStmt->execute([$id]);
            $pv['qualifications_substitut'] = $qsStmt->fetchAll();
            $pv['qualifications_ids']       = array_column($pv['qualifications_substitut'], 'infraction_id');
        } catch (\Exception $e) {
            $pv['qualifications_substitut'] = [];
            $pv['qualifications_ids']       = [];
        }

        return $pv;
    }
}
