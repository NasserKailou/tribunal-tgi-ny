<?php
class CarteController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('carte/index', compact('user','flash'));
    }

    /**
     * API JSON — données PV antiterroristes par commune (GeoJSON-ready)
     */
    public function apiData(): void {
        Auth::requireLogin();

        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin   = $_GET['date_fin']   ?? null;
        $regionFlt = $_GET['region']     ?? null;

        $params    = [];
        $dateWhere = '';
        if ($dateDebut && $dateFin) {
            $dateWhere = "AND p.date_reception BETWEEN :dd AND :df";
            $params['dd'] = $dateDebut;
            $params['df'] = $dateFin;
        }
        $regionWhere = '';
        if ($regionFlt) {
            $regionWhere = "AND cg.region_nom = :rn";
            $params['rn'] = $regionFlt;
        }

        // Toutes les communes GeoJSON + count PV antiterroristes
        // On joint sur le nom de commune (normalize)
        $sql = "
            SELECT
                cg.id,
                cg.nom,
                cg.departement_nom AS dept_nom,
                cg.region_nom,
                cg.latitude,
                cg.longitude,
                cg.code_commune,
                COUNT(p.id) AS pv_count
            FROM communes_geo cg
            LEFT JOIN (
                SELECT p_inner.id, c_inner.nom AS commune_nom
                FROM pv p_inner
                JOIN communes c_inner ON p_inner.commune_id = c_inner.id
                WHERE p_inner.est_antiterroriste = 1
                $dateWhere
            ) p ON p.commune_nom = cg.nom
            WHERE 1=1 $regionWhere
            GROUP BY cg.id, cg.nom, cg.departement_nom, cg.region_nom, cg.latitude, cg.longitude, cg.code_commune
            ORDER BY pv_count DESC, cg.nom ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $communes = $stmt->fetchAll();
        foreach ($communes as &$c) { $c['pv_count'] = (int)$c['pv_count']; }
        unset($c);

        // PV par région
        $sqlR = "
            SELECT cg.region_nom, COUNT(p.id) AS pv_count
            FROM communes_geo cg
            LEFT JOIN (
                SELECT p_inner.id, c_inner.nom AS commune_nom
                FROM pv p_inner
                JOIN communes c_inner ON p_inner.commune_id = c_inner.id
                WHERE p_inner.est_antiterroriste = 1
                $dateWhere
            ) p ON p.commune_nom = cg.nom
            GROUP BY cg.region_nom
            ORDER BY pv_count DESC
        ";
        $stmtR = $this->db->prepare($sqlR);
        $stmtR->execute(array_filter($params, fn($k) => in_array($k,['dd','df']), ARRAY_FILTER_USE_KEY));
        $pvByRegion = [];
        foreach ($stmtR->fetchAll() as $row) {
            $pvByRegion[$row['region_nom']] = (int)$row['pv_count'];
        }

        $totalAnti  = (int)$this->db->query("SELECT COUNT(*) FROM pv WHERE est_antiterroriste=1")->fetchColumn();
        $commActives= (int)$this->db->query("SELECT COUNT(DISTINCT c.nom) FROM pv p JOIN communes c ON p.commune_id=c.id WHERE p.est_antiterroriste=1")->fetchColumn();
        $topCommune = count($communes) > 0 ? $communes[0] : null;

        $this->json([
            'communes'         => $communes,
            'pvByRegion'       => $pvByRegion,
            'total_pv_anti'    => $totalAnti,
            'communes_actives' => $commActives,
            'top_commune'      => $topCommune,
        ]);
    }

    public function data(): void { $this->apiData(); }
}
