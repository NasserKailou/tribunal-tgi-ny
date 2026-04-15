<?php
class DashboardController extends Controller {
    public function index(): void {
        Auth::requireLogin();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();

        // Stats rapides
        $stats = $this->getStats();
        $alerteHelper = new AlerteHelper($this->db);
        $nbAlertes = $alerteHelper->countUnread($user['id']);

        // Derniers PV
        $derniersPV = $this->db->query(
            "SELECT p.*, u.nom as unite_nom, r.libelle as role_lib
             FROM pv p
             LEFT JOIN unites_enquete u ON p.unite_enquete_id = u.id
             LEFT JOIN users us ON p.substitut_id = us.id
             LEFT JOIN roles r ON us.role_id = r.id
             ORDER BY p.created_at DESC LIMIT 10"
        )->fetchAll();

        // Prochaines audiences
        $prochainesAudiences = $this->db->query(
            "SELECT a.*, d.numero_rg, s.nom as salle_nom,
                    u.nom as president_nom, u.prenom as president_prenom
             FROM audiences a
             JOIN dossiers d ON a.dossier_id = d.id
             LEFT JOIN salles_audience s ON a.salle_id = s.id
             LEFT JOIN users u ON a.president_id = u.id
             WHERE a.statut = 'planifiee' AND a.date_audience >= NOW()
             ORDER BY a.date_audience ASC LIMIT 10"
        )->fetchAll();

        $this->view('dashboard/index', compact('stats','derniersPV','prochainesAudiences','nbAlertes','flash','user'));
    }

    private function getStats(): array {
        $annee = date('Y');
        $mois  = date('m');

        // PVs reçus ce mois
        $pvMois = (int)$this->db->query(
            "SELECT COUNT(*) FROM pv WHERE YEAR(date_reception)=YEAR(CURDATE()) AND MONTH(date_reception)=MONTH(CURDATE())"
        )->fetchColumn();

        // Dossiers en cours
        $dossiersEnCours = (int)$this->db->query(
            "SELECT COUNT(*) FROM dossiers WHERE statut NOT IN ('juge','classe')"
        )->fetchColumn();

        // Audiences planifiées cette semaine
        $audiencesSemaine = (int)$this->db->query(
            "SELECT COUNT(*) FROM audiences WHERE statut='planifiee' AND YEARWEEK(date_audience,1)=YEARWEEK(CURDATE(),1)"
        )->fetchColumn();

        // Population carcérale
        $population = (int)$this->db->query(
            "SELECT COUNT(*) FROM detenus WHERE statut='incarcere'"
        )->fetchColumn();

        // PVs par statut
        $pvStatuts = $this->db->query(
            "SELECT statut, COUNT(*) as nb FROM pv GROUP BY statut"
        )->fetchAll();

        // Dossiers par type
        $dossierTypes = $this->db->query(
            "SELECT type_affaire, COUNT(*) as nb FROM dossiers GROUP BY type_affaire"
        )->fetchAll();

        // Population par type de détention
        $detentionTypes = $this->db->query(
            "SELECT type_detention, COUNT(*) as nb FROM detenus WHERE statut='incarcere' GROUP BY type_detention"
        )->fetchAll();

        // PVs par mois (12 derniers mois)
        $pvParMois = $this->db->query(
            "SELECT DATE_FORMAT(date_reception,'%Y-%m') as mois, type_affaire, COUNT(*) as nb
             FROM pv WHERE date_reception >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(date_reception,'%Y-%m'), type_affaire
             ORDER BY mois"
        )->fetchAll();

        // Alertes non lues
        $nbAlertesTotal = (int)$this->db->query("SELECT COUNT(*) FROM alertes WHERE est_lue=0")->fetchColumn();

        return compact('pvMois','dossiersEnCours','audiencesSemaine','population','pvStatuts','dossierTypes','detentionTypes','pvParMois','nbAlertesTotal');
    }

    public function apiStats(): void {
        Auth::requireLogin();
        $this->json($this->getStats());
    }
}
