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

        $this->view('detenus/index',compact('detenus','total','page','perPage','totalPages','search','type','statut','statsPopulation','totalIncarceres','flash','user'));
    }

    public function create(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','president']);
        $user     = Auth::currentUser();
        $dossiers = $this->db->query("SELECT id,numero_rg,objet FROM dossiers WHERE statut NOT IN ('classe') ORDER BY numero_rg")->fetchAll();
        $num      = new Numerotation($this->db);
        $suggestEcrou = $num->genererEcrou();
        $this->view('detenus/create',compact('dossiers','suggestEcrou','user'));
    }

    public function store(): void {
        Auth::requireLogin();
        CSRF::check();
        $num = new Numerotation($this->db);
        $ecrou = $num->genererEcrou();

        $this->db->prepare(
            "INSERT INTO detenus (dossier_id,jugement_id,nom,prenom,date_naissance,lieu_naissance,
             nationalite,profession,numero_ecrou,type_detention,date_incarceration,
             date_liberation_prevue,statut,cellule,etablissement,notes)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        )->execute([
            $_POST['dossier_id']?:null,
            $_POST['jugement_id']?:null,
            $this->sanitize($_POST['nom']),
            $this->sanitize($_POST['prenom']),
            $_POST['date_naissance']?:null,
            $this->sanitize($_POST['lieu_naissance']??''),
            $this->sanitize($_POST['nationalite']??'Nigérienne'),
            $this->sanitize($_POST['profession']??''),
            $ecrou,
            $_POST['type_detention'],
            $_POST['date_incarceration'],
            $_POST['date_liberation_prevue']?:null,
            'incarcere',
            $this->sanitize($_POST['cellule']??''),
            $this->sanitize($_POST['etablissement']??'Maison d\'Arrêt de Niamey'),
            $this->sanitize($_POST['notes']??''),
        ]);
        $this->flash('success',"Détenu enregistré — Écrou : $ecrou");
        $this->redirect('/detenus');
    }

    public function show(string $id): void {
        Auth::requireLogin();
        $stmt=$this->db->prepare("SELECT d.*,dos.numero_rg,j.numero_jugement FROM detenus d LEFT JOIN dossiers dos ON d.dossier_id=dos.id LEFT JOIN jugements j ON d.jugement_id=j.id WHERE d.id=?");
        $stmt->execute([(int)$id]);
        $detenu=$stmt->fetch();
        if(!$detenu){$this->redirect('/detenus');}
        $flash=$this->getFlash();
        $user=Auth::currentUser();
        $this->view('detenus/show',compact('detenu','flash','user'));
    }

    public function edit(string $id): void {
        Auth::requireLogin();
        $stmt=$this->db->prepare("SELECT * FROM detenus WHERE id=?");
        $stmt->execute([(int)$id]);
        $detenu=$stmt->fetch();
        if(!$detenu){$this->redirect('/detenus');}
        $user=Auth::currentUser();
        $dossiers=$this->db->query("SELECT id,numero_rg FROM dossiers ORDER BY numero_rg")->fetchAll();
        $this->view('detenus/edit',compact('detenu','dossiers','user'));
    }

    public function update(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $this->db->prepare("UPDATE detenus SET nom=?,prenom=?,type_detention=?,cellule=?,etablissement=?,notes=? WHERE id=?")
            ->execute([
                $this->sanitize($_POST['nom']),
                $this->sanitize($_POST['prenom']),
                $_POST['type_detention'],
                $this->sanitize($_POST['cellule']??''),
                $this->sanitize($_POST['etablissement']??''),
                $this->sanitize($_POST['notes']??''),
                (int)$id,
            ]);
        $this->flash('success','Détenu mis à jour.');
        $this->redirect('/detenus/show/'.$id);
    }

    public function liberer(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','greffier','president','procureur']);
        $dateLib = $_POST['date_liberation_effective'] ?? date('Y-m-d');
        $this->db->prepare("UPDATE detenus SET statut='libere', date_liberation_effective=? WHERE id=?")->execute([$dateLib,(int)$id]);
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
}
