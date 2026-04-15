<?php
class AlerteController extends Controller {
    public function index(): void {
        Auth::requireLogin();
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $page  = max(1,(int)($_GET['page']??1));
        $perPage=20; $offset=($page-1)*$perPage;
        $niveau=$_GET['niveau']??'';
        $where=[]; $params=[];
        if($niveau){$where[]="a.niveau=:niv";$params['niv']=$niveau;}
        $whereSQL=$where?'WHERE '.implode(' AND ',$where):'';
        $cStmt=$this->db->prepare("SELECT COUNT(*) FROM alertes a $whereSQL");
        $cStmt->execute($params);
        $total=(int)$cStmt->fetchColumn();
        $sql="SELECT a.*,d.numero_rg,u.nom as dest_nom FROM alertes a LEFT JOIN dossiers d ON a.dossier_id=d.id LEFT JOIN users u ON a.destinataire_id=u.id $whereSQL ORDER BY a.created_at DESC LIMIT $perPage OFFSET $offset";
        $stmt=$this->db->prepare($sql);
        $stmt->execute($params);
        $alertes=$stmt->fetchAll();
        $totalPages=ceil($total/$perPage);
        $this->view('alertes/index',compact('alertes','total','page','perPage','totalPages','niveau','flash','user'));
    }

    public function marquerLue(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $this->db->prepare("UPDATE alertes SET est_lue=1 WHERE id=?")->execute([(int)$id]);
        $this->flash('success','Alerte marquée comme lue.');
        $this->redirect('/alertes');
    }

    public function marquerToutLu(): void {
        Auth::requireLogin();
        CSRF::check();
        $this->db->query("UPDATE alertes SET est_lue=1 WHERE est_lue=0");
        $this->flash('success','Toutes les alertes marquées comme lues.');
        $this->redirect('/alertes');
    }

    public function apiCount(): void {
        Auth::requireLogin();
        $helper = new AlerteHelper($this->db);
        $this->json(['count' => $helper->countUnread(Auth::userId())]);
    }
}
