<?php
class DetenusController extends Controller {
    public function index(): void {
        Auth::requireLogin();
        $flash  = $this->getFlash();
        $user   = Auth::currentUser();
        $page   = max(1,(int)($_GET['page']??1));
        $perPage= 20; $offset=($page-1)*$perPage;
        $search = trim($_GET['q']??'');
        $type   = $_GET['type']??'';
        $statut = $_GET['statut']??'incarcere';

        $where=[]; $params=[];
        if($search){$where[]="(d.nom LIKE :q OR d.prenom LIKE :q OR d.numero_ecrou LIKE :q)";$params['q']="%$search%";}
        if($type){$where[]="d.type_detention=:type";$params['type']=$type;}
        if($statut){$where[]="d.statut=:statut";$params['statut']=$statut;}
        $whereSQL=$where?'WHERE '.implode(' AND ',$where):'';

        $cStmt=$this->db->prepare("SELECT COUNT(*) FROM detenus d $whereSQL");
        $cStmt->execute($params);
        $total=(int)$cStmt->fetchColumn();

        $sql="SELECT d.*, dos.numero_rg FROM detenus d LEFT JOIN dossiers dos ON d.dossier_id=dos.id $whereSQL ORDER BY d.date_incarceration DESC LIMIT $perPage OFFSET $offset";
        $stmt=$this->db->prepare($sql);
        $stmt->execute($params);
        $detenus=$stmt->fetchAll();
        $totalPages=ceil($total/$perPage);

        // Stats population
        $statsStmt=$this->db->query("SELECT type_detention,COUNT(*) as nb FROM detenus WHERE statut='incarcere' GROUP BY type_detention");
        $statsPopulation=$statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $totalIncarceres=(int)$this->db->query("SELECT COUNT(*) FROM detenus WHERE statut='incarcere'")->fetchColumn();

        $maisonArrets  = $this->getMaisonsArret();
        $suggestEcrou  = (new Numerotation($this->db))->genererEcrou();
        $dossiers      = $this->db->query("SELECT id,numero_rg,objet FROM dossiers WHERE statut NOT IN ('classe') ORDER BY numero_rg")->fetchAll();
        $this->view('detenus/index',compact('detenus','total','page','perPage','totalPages','search','type','statut','statsPopulation','totalIncarceres','flash','user','maisonArrets','suggestEcrou','dossiers'));
    }

    public function create(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','president']);
        // Redirige vers la liste avec ouverture auto du modal
        $dossier_id = (int)($_GET['dossier_id'] ?? 0);
        $url = '/detenus?open_modal=1' . ($dossier_id ? '&dossier_id=' . $dossier_id : '');
        $this->redirect($url);
    }

    public function store(): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','greffier','procureur','president']);
        $num = new Numerotation($this->db);
        $ecrou = $num->genererEcrou();

        // Traitement upload photo
        $photoPath = null;
        if (!empty($_FILES['photo_identite']['tmp_name'])) {
            $photoPath = $this->handlePhotoUpload($_FILES['photo_identite']);
            if ($photoPath === false) {
                $this->flash('error','Photo invalide : format JPG/PNG requis, taille max 2 Mo.');
                $this->redirect('/detenus/create');
                return;
            }
        }

        $this->db->prepare(
            "INSERT INTO detenus (dossier_id, jugement_id, nom, prenom, surnom_alias, nom_mere,
             statut_matrimonial, nombre_enfants, sexe, date_naissance, lieu_naissance,
             nationalite, profession, numero_ecrou, type_detention, date_incarceration,
             date_liberation_prevue, statut, cellule, etablissement, maison_arret_id,
             photo_identite, notes)
             VALUES (:dossier_id, :jugement_id, :nom, :prenom, :surnom_alias, :nom_mere,
             :statut_matrimonial, :nombre_enfants, :sexe, :date_naissance, :lieu_naissance,
             :nationalite, :profession, :numero_ecrou, :type_detention, :date_incarceration,
             :date_liberation_prevue, 'incarcere', :cellule, :etablissement, :maison_arret_id,
             :photo_identite, :notes)"
        )->execute([
            ':dossier_id'         => $_POST['dossier_id'] ?: null,
            ':jugement_id'        => $_POST['jugement_id'] ?? null ?: null,
            ':nom'                => $this->sanitize($_POST['nom']),
            ':prenom'             => $this->sanitize($_POST['prenom']),
            ':surnom_alias'       => $this->sanitize($_POST['surnom_alias'] ?? '') ?: null,
            ':nom_mere'           => $this->sanitize($_POST['nom_mere'] ?? '') ?: null,
            ':statut_matrimonial' => $_POST['statut_matrimonial'] ?? 'celibataire',
            ':nombre_enfants'     => (int)($_POST['nombre_enfants'] ?? 0),
            ':sexe'               => $_POST['sexe'] === 'F' ? 'F' : 'M',
            ':date_naissance'     => $_POST['date_naissance'] ?: null,
            ':lieu_naissance'     => $this->sanitize($_POST['lieu_naissance'] ?? ''),
            ':nationalite'        => $this->sanitize($_POST['nationalite'] ?? 'Nigérienne'),
            ':profession'         => $this->sanitize($_POST['profession'] ?? ''),
            ':numero_ecrou'       => $ecrou,
            ':type_detention'     => $_POST['type_detention'],
            ':date_incarceration' => $_POST['date_incarceration'],
            ':date_liberation_prevue' => $_POST['date_liberation_prevue'] ?: null,
            ':cellule'            => $this->sanitize($_POST['cellule'] ?? ''),
            ':etablissement'      => $this->sanitize($_POST['etablissement'] ?? 'Maison d\'Arrêt de Niamey'),
            ':maison_arret_id'    => $_POST['maison_arret_id'] ?: null,
            ':photo_identite'     => $photoPath,
            ':notes'              => $this->sanitize($_POST['notes'] ?? ''),
        ]);
        $this->flash('success',"Détenu enregistré — Écrou : $ecrou");
        $this->redirect('/detenus');
    }

    public function show(string $id): void {
        Auth::requireLogin();
        $stmt=$this->db->prepare(
            "SELECT d.*, dos.numero_rg, j.numero_jugement,
                    ma.nom AS maison_arret_nom, ma.id AS maison_arret_real_id
             FROM detenus d
             LEFT JOIN dossiers dos ON d.dossier_id=dos.id
             LEFT JOIN jugements j ON d.jugement_id=j.id
             LEFT JOIN maisons_arret ma ON d.maison_arret_id=ma.id
             WHERE d.id=:id"
        );
        $stmt->execute([':id' => (int)$id]);
        $detenu=$stmt->fetch();
        if(!$detenu){$this->redirect('/detenus');}
        $flash=$this->getFlash();
        $user=Auth::currentUser();
        $this->view('detenus/show',compact('detenu','flash','user'));
    }

    public function edit(string $id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','president']);
        $stmt=$this->db->prepare("SELECT * FROM detenus WHERE id=:id");
        $stmt->execute([':id' => (int)$id]);
        $detenu=$stmt->fetch();
        if(!$detenu){$this->redirect('/detenus');}
        $user=Auth::currentUser();
        $dossiers=$this->db->query("SELECT id,numero_rg FROM dossiers ORDER BY numero_rg")->fetchAll();
        $maisonArrets=$this->getMaisonsArret();
        $this->view('detenus/edit',compact('detenu','dossiers','user','maisonArrets'));
    }

    public function update(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','greffier','procureur','president']);

        // Récupérer la photo existante
        $stmtEx = $this->db->prepare("SELECT photo_identite FROM detenus WHERE id=:id");
        $stmtEx->execute([':id' => (int)$id]);
        $existing = $stmtEx->fetch();
        $photoPath = $existing['photo_identite'] ?? null;

        // Traitement upload photo
        if (!empty($_FILES['photo_identite']['tmp_name'])) {
            $newPhoto = $this->handlePhotoUpload($_FILES['photo_identite']);
            if ($newPhoto === false) {
                $this->flash('error','Photo invalide : format JPG/PNG requis, taille max 2 Mo.');
                $this->redirect('/detenus/edit/'.$id);
                return;
            }
            // Supprimer ancienne photo si elle existe
            if ($photoPath) {
                $oldFile = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($photoPath, '/'));
                if (file_exists($oldFile)) { @unlink($oldFile); }
            }
            $photoPath = $newPhoto;
        }

        $this->db->prepare(
            "UPDATE detenus SET
                nom=:nom, prenom=:prenom, surnom_alias=:surnom_alias, nom_mere=:nom_mere,
                statut_matrimonial=:statut_matrimonial, nombre_enfants=:nombre_enfants,
                sexe=:sexe, date_naissance=:date_naissance, lieu_naissance=:lieu_naissance,
                nationalite=:nationalite, profession=:profession,
                type_detention=:type_detention, cellule=:cellule, etablissement=:etablissement,
                maison_arret_id=:maison_arret_id, photo_identite=:photo_identite, notes=:notes
             WHERE id=:id"
        )->execute([
            ':nom'                => $this->sanitize($_POST['nom']),
            ':prenom'             => $this->sanitize($_POST['prenom']),
            ':surnom_alias'       => $this->sanitize($_POST['surnom_alias'] ?? '') ?: null,
            ':nom_mere'           => $this->sanitize($_POST['nom_mere'] ?? '') ?: null,
            ':statut_matrimonial' => $_POST['statut_matrimonial'] ?? 'celibataire',
            ':nombre_enfants'     => (int)($_POST['nombre_enfants'] ?? 0),
            ':sexe'               => $_POST['sexe'] === 'F' ? 'F' : 'M',
            ':date_naissance'     => $_POST['date_naissance'] ?: null,
            ':lieu_naissance'     => $this->sanitize($_POST['lieu_naissance'] ?? ''),
            ':nationalite'        => $this->sanitize($_POST['nationalite'] ?? 'Nigérienne'),
            ':profession'         => $this->sanitize($_POST['profession'] ?? ''),
            ':type_detention'     => $_POST['type_detention'],
            ':cellule'            => $this->sanitize($_POST['cellule'] ?? ''),
            ':etablissement'      => $this->sanitize($_POST['etablissement'] ?? ''),
            ':maison_arret_id'    => $_POST['maison_arret_id'] ?: null,
            ':photo_identite'     => $photoPath,
            ':notes'              => $this->sanitize($_POST['notes'] ?? ''),
            ':id'                 => (int)$id,
        ]);
        $this->flash('success','Détenu mis à jour.');
        $this->redirect('/detenus/show/'.$id);
    }

    public function liberer(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','greffier','president','procureur']);
        $dateLib = $_POST['date_liberation_effective'] ?? date('Y-m-d');
        $this->db->prepare("UPDATE detenus SET statut='libere', date_liberation_effective=:dl WHERE id=:id")
            ->execute([':dl' => $dateLib, ':id' => (int)$id]);
        $this->flash('success','Libération enregistrée.');
        $this->redirect('/detenus/show/'.$id);
    }

    public function stats(): void {
        Auth::requireLogin();
        $user = Auth::currentUser();

        $byType = $this->db->query("SELECT type_detention, COUNT(*) as nb FROM detenus WHERE statut='incarcere' GROUP BY type_detention")->fetchAll();
        $byMonthIncarc = $this->db->query("SELECT DATE_FORMAT(date_incarceration,'%Y-%m') as mois, COUNT(*) as nb FROM detenus WHERE date_incarceration >= DATE_SUB(CURDATE(),INTERVAL 12 MONTH) GROUP BY mois ORDER BY mois")->fetchAll();
        $longDetention = $this->db->query("SELECT d.*, dos.numero_rg, TIMESTAMPDIFF(MONTH,d.date_incarceration,NOW()) as duree_mois FROM detenus d LEFT JOIN dossiers dos ON d.dossier_id=dos.id WHERE d.statut='incarcere' AND d.type_detention IN ('prevenu','detenu_provisoire','inculpe') AND TIMESTAMPDIFF(MONTH,d.date_incarceration,NOW()) > 6 ORDER BY d.date_incarceration")->fetchAll();

        $this->view('detenus/stats',compact('byType','byMonthIncarc','longDetention','user'));
    }

    /**
     * API : recherche de détenus pour import dans les parties de dossier
     * GET /api/detenus/search?q=...
     */
    public function apiSearch(): void {
        Auth::requireLogin();
        $q = trim($_GET['q'] ?? '');
        header('Content-Type: application/json; charset=utf-8');
        if (strlen($q) < 2) {
            echo json_encode([]);
            return;
        }
        $stmt = $this->db->prepare(
            "SELECT id, nom, prenom, nationalite,
                    COALESCE(
                        (SELECT CONCAT(ma2.nom) FROM maisons_arret ma2 WHERE ma2.id=detenus.maison_arret_id),
                        etablissement
                    ) AS adresse_detention,
                    numero_ecrou, date_incarceration
             FROM detenus
             WHERE (nom LIKE :q OR prenom LIKE :q OR numero_ecrou LIKE :q)
               AND statut='incarcere'
             ORDER BY nom, prenom
             LIMIT 30"
        );
        $stmt->execute([':q' => "%$q%"]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows);
    }

    // ─── Méthodes privées ────────────────────────────────────────────

    /**
     * Retourne la liste des maisons d'arrêt actives.
     */
    private function getMaisonsArret(): array {
        try {
            return $this->db->query("SELECT id, nom FROM maisons_arret WHERE actif=1 ORDER BY nom")->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Traite l'upload de la photo d'identité.
     * Retourne le chemin relatif stocké en base, ou false en cas d'erreur.
     */
    private function handlePhotoUpload(array $file): string|false {
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize      = 2 * 1024 * 1024; // 2 Mo

        if ($file['error'] !== UPLOAD_ERR_OK) { return false; }
        if ($file['size'] > $maxSize) { return false; }

        // Vérification MIME réelle (finfo)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedMimes, true)) { return false; }

        // Dossier de destination
        $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public'
                   . DIRECTORY_SEPARATOR . 'uploads'
                   . DIRECTORY_SEPARATOR . 'photos_detenus';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = $mime === 'image/png' ? 'png' : 'jpg';
        $filename = 'det_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        $dest     = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) { return false; }

        return 'uploads/photos_detenus/' . $filename;
    }
}
