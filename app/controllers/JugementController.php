<?php
class JugementController extends Controller {
    public function index(): void {
        Auth::requireLogin();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $page  = max(1,(int)($_GET['page']??1));
        $perPage = 20;
        $offset  = ($page-1)*$perPage;
        $search  = trim($_GET['q']??'');
        $type    = $_GET['type']??'';

        $where=[]; $params=[];
        if($search){$where[]="(d.numero_rg LIKE :q OR j.numero_jugement LIKE :q)";$params['q']="%$search%";}
        if($type){$where[]="j.type_jugement=:type";$params['type']=$type;}
        $whereSQL=$where?'WHERE '.implode(' AND ',$where):'';

        $total=(int)$this->db->prepare("SELECT COUNT(*) FROM jugements j JOIN dossiers d ON j.dossier_id=d.id $whereSQL")->execute($params)?$this->db->prepare("SELECT COUNT(*) FROM jugements j JOIN dossiers d ON j.dossier_id=d.id $whereSQL"):null;
        $countStmt=$this->db->prepare("SELECT COUNT(*) FROM jugements j JOIN dossiers d ON j.dossier_id=d.id $whereSQL");
        $countStmt->execute($params);
        $total=(int)$countStmt->fetchColumn();

        $sql="SELECT j.*, d.numero_rg, d.type_affaire, g.nom as greffier_nom
              FROM jugements j JOIN dossiers d ON j.dossier_id=d.id
              LEFT JOIN users g ON j.greffier_id=g.id
              $whereSQL ORDER BY j.date_jugement DESC LIMIT $perPage OFFSET $offset";
        $stmt=$this->db->prepare($sql);
        $stmt->execute($params);
        $jugements=$stmt->fetchAll();
        $totalPages=ceil($total/$perPage);

        $this->view('jugements/index',compact('jugements','total','page','perPage','totalPages','search','type','flash','user'));
    }

    public function create(string $dossier_id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','president','juge_siege']);
        $user    = Auth::currentUser();
        $dossier = $this->db->prepare("SELECT * FROM dossiers WHERE id=?")->execute([(int)$dossier_id])?null:null;
        $dosStmt = $this->db->prepare("SELECT d.*, u.nom as substitut_nom FROM dossiers d LEFT JOIN users u ON d.substitut_id=u.id WHERE d.id=?");
        $dosStmt->execute([(int)$dossier_id]);
        $dossier = $dosStmt->fetch();
        if(!$dossier){$this->redirect('/dossiers');}

        $audiences = $this->db->prepare("SELECT * FROM audiences WHERE dossier_id=? AND statut='tenue' ORDER BY date_audience DESC");
        $audiences->execute([(int)$dossier_id]);
        $audiences = $audiences->fetchAll();

        $greffiers = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='greffier' AND u.actif=1")->fetchAll();
        $num = new Numerotation($this->db);
        $suggestNum = $num->genererJugement();

        $this->view('jugements/create',compact('dossier','audiences','greffiers','suggestNum','user'));
    }

    public function store(): void {
        Auth::requireLogin();
        CSRF::check();
        $num = new Numerotation($this->db);
        $numJug = $num->genererJugement();

        $delaiAppel = $_POST['appel_possible'] ? date('Y-m-d', strtotime($_POST['date_jugement'].' +'.DELAI_APPEL_JOURS.' days')) : null;

        $ins = $this->db->prepare(
            "INSERT INTO jugements (dossier_id,audience_id,numero_jugement,date_jugement,type_jugement,
             dispositif,peine_principale,duree_peine_mois,montant_amende,sursis,duree_sursis_mois,
             appel_possible,date_limite_appel,notes,greffier_id,created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $ins->execute([
            (int)$_POST['dossier_id'],
            $_POST['audience_id']?:null,
            $numJug,
            $_POST['date_jugement'],
            $_POST['type_jugement'],
            $this->sanitize($_POST['dispositif']),
            $this->sanitize($_POST['peine_principale']??''),
            $_POST['duree_peine_mois']?:null,
            $_POST['montant_amende']?:null,
            isset($_POST['sursis'])?1:0,
            $_POST['duree_sursis_mois']?:null,
            isset($_POST['appel_possible'])?1:0,
            $delaiAppel,
            $this->sanitize($_POST['notes']??''),
            $_POST['greffier_id']?:null,
            Auth::userId(),
        ]);
        $jugId = (int)$this->db->lastInsertId();

        $this->db->prepare("UPDATE dossiers SET statut='juge' WHERE id=?")->execute([(int)$_POST['dossier_id']]);
        $this->db->prepare("INSERT INTO mouvements_dossier (dossier_id,user_id,type_mouvement,nouveau_statut,description) VALUES (?,?,?,?,?)")
            ->execute([(int)$_POST['dossier_id'],Auth::userId(),'jugement','juge',"Jugement rendu : $numJug"]);

        $this->flash('success',"Jugement enregistré : $numJug");
        $this->redirect('/jugements/show/'.$jugId);
    }

    public function show(string $id): void {
        Auth::requireLogin();
        $stmt=$this->db->prepare("SELECT j.*,d.numero_rg,d.objet,d.type_affaire,d.id as dossier_id,g.nom as greffier_nom,g.prenom as greffier_prenom FROM jugements j JOIN dossiers d ON j.dossier_id=d.id LEFT JOIN users g ON j.greffier_id=g.id WHERE j.id=?");
        $stmt->execute([(int)$id]);
        $jugement=$stmt->fetch();
        if(!$jugement){$this->redirect('/jugements');}
        $flash=$this->getFlash();
        $user=Auth::currentUser();
        $this->view('jugements/show',compact('jugement','flash','user'));
    }

    public function enregistrerAppel(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $this->db->prepare("UPDATE jugements SET appel_interjecte=1, date_appel=CURDATE() WHERE id=?")->execute([(int)$id]);
        $stmtJ=$this->db->prepare("SELECT dossier_id FROM jugements WHERE id=?");
        $stmtJ->execute([(int)$id]);
        $row=$stmtJ->fetch();
        if($row){
            $this->db->prepare("UPDATE dossiers SET statut='appel' WHERE id=?")->execute([$row['dossier_id']]);
        }
        $this->flash('success','Appel enregistré.');
        $this->redirect('/jugements/show/'.$id);
    }
}
