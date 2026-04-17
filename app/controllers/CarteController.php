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
     *
     * Stratégie de jointure :
     *  1. Priorité à la table communes_geo (plus riche, indexée pour la carte)
     *  2. Fallback automatique sur la table communes si communes_geo est vide
     *
     * Correspondance avec le GeoJSON :
     *  Le GeoJSON stocke NOM_COM en MAJUSCULES ; la jointure se fait en UPPER().
     */
    public function apiData(): void {
        Auth::requireLogin();

        $dateDebut = trim($_GET['date_debut'] ?? '');
        $dateFin   = trim($_GET['date_fin']   ?? '');
        $regionFlt = trim($_GET['region']     ?? '');

        $params    = [];
        $dateWhere = '';
        if ($dateDebut && $dateFin) {
            $dateWhere = "AND p.date_reception BETWEEN :dd AND :df";
            $params['dd'] = $dateDebut;
            $params['df'] = $dateFin;
        } elseif ($dateDebut) {
            $dateWhere = "AND p.date_reception >= :dd";
            $params['dd'] = $dateDebut;
        } elseif ($dateFin) {
            $dateWhere = "AND p.date_reception <= :df";
            $params['df'] = $dateFin;
        }

        // Vérifier si communes_geo est peuplée
        $hasCommunesGeo = (int)$this->db->query("SELECT COUNT(*) FROM communes_geo")->fetchColumn() > 0;

        if ($hasCommunesGeo) {
            $communes   = $this->getDataFromCommunesGeo($params, $dateWhere, $regionFlt);
            $pvByRegion = $this->getPVByRegionFromGeo($params, $dateWhere);
        } else {
            // Fallback : utiliser la table communes directement
            $communes   = $this->getDataFromCommunes($params, $dateWhere, $regionFlt);
            $pvByRegion = $this->getPVByRegionFromCommunes($params, $dateWhere);
        }

        // Totaux globaux
        $totalAnti = (int)$this->db->query(
            "SELECT COUNT(*) FROM pv WHERE est_antiterroriste = 1"
        )->fetchColumn();

        $commActives = (int)$this->db->query(
            "SELECT COUNT(DISTINCT c.nom)
             FROM pv p JOIN communes c ON p.commune_id = c.id
             WHERE p.est_antiterroriste = 1"
        )->fetchColumn();

        $topCommune = !empty($communes) ? $communes[0] : null;

        $this->json([
            'communes'         => $communes,
            'pvByRegion'       => $pvByRegion,
            'total_pv_anti'    => $totalAnti,
            'communes_actives' => $commActives,
            'top_commune'      => $topCommune,
            'source'           => $hasCommunesGeo ? 'communes_geo' : 'communes',
        ]);
    }

    /* ───────────────────────────────────────────────────────────
       Données depuis communes_geo (266 communes GeoJSON)
    ─────────────────────────────────────────────────────────── */
    private function getDataFromCommunesGeo(array $params, string $dateWhere, string $regionFlt): array {
        $regionWhere = '';
        if ($regionFlt) {
            $regionWhere = "AND (cg.region_nom = :rn OR cg.region_nom LIKE :rn_like)";
            $params['rn']      = $regionFlt;
            $params['rn_like'] = '%' . $regionFlt . '%';
        }

        $sql = "
            SELECT
                cg.id,
                cg.nom,
                cg.departement_nom  AS dept_nom,
                cg.region_nom,
                cg.latitude,
                cg.longitude,
                cg.code_commune,
                COUNT(pv_join.id)   AS pv_count
            FROM communes_geo cg
            LEFT JOIN (
                SELECT p.id, c.nom AS commune_nom
                FROM   pv p
                JOIN   communes c ON p.commune_id = c.id
                WHERE  p.est_antiterroriste = 1
                       $dateWhere
            ) pv_join
              ON UPPER(pv_join.commune_nom) = UPPER(cg.nom)
            WHERE 1 = 1
                  $regionWhere
            GROUP BY cg.id, cg.nom, cg.departement_nom, cg.region_nom,
                     cg.latitude, cg.longitude, cg.code_commune
            ORDER BY pv_count DESC, cg.nom ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) { $r['pv_count'] = (int)$r['pv_count']; }
        return $rows;
    }

    private function getPVByRegionFromGeo(array $params, string $dateWhere): array {
        $dateParams = array_filter($params, fn($k) => in_array($k, ['dd', 'df']), ARRAY_FILTER_USE_KEY);
        $sql = "
            SELECT cg.region_nom, COUNT(pv_join.id) AS pv_count
            FROM   communes_geo cg
            LEFT JOIN (
                SELECT p.id, c.nom AS commune_nom
                FROM   pv p
                JOIN   communes c ON p.commune_id = c.id
                WHERE  p.est_antiterroriste = 1
                       $dateWhere
            ) pv_join ON UPPER(pv_join.commune_nom) = UPPER(cg.nom)
            GROUP BY cg.region_nom
            ORDER BY pv_count DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dateParams);
        $pvByRegion = [];
        foreach ($stmt->fetchAll() as $row) {
            if ($row['region_nom']) {
                $pvByRegion[$row['region_nom']] = (int)$row['pv_count'];
            }
        }
        return $pvByRegion;
    }

    /* ───────────────────────────────────────────────────────────
       Fallback : données depuis communes (table principale)
    ─────────────────────────────────────────────────────────── */
    private function getDataFromCommunes(array $params, string $dateWhere, string $regionFlt): array {
        $regionWhere = '';
        if ($regionFlt) {
            $regionWhere = "AND r.nom = :rn";
            $params['rn'] = $regionFlt;
        }

        $sql = "
            SELECT
                c.id,
                c.nom,
                d.nom               AS dept_nom,
                r.nom               AS region_nom,
                c.latitude,
                c.longitude,
                c.code              AS code_commune,
                COUNT(p.id)         AS pv_count
            FROM   communes c
            JOIN   departements d ON c.departement_id = d.id
            JOIN   regions r      ON d.region_id      = r.id
            LEFT JOIN pv p
              ON  p.commune_id = c.id
              AND p.est_antiterroriste = 1
              $dateWhere
            WHERE 1 = 1
                  $regionWhere
            GROUP BY c.id, c.nom, d.nom, r.nom, c.latitude, c.longitude, c.code
            ORDER BY pv_count DESC, c.nom ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) { $r['pv_count'] = (int)$r['pv_count']; }
        return $rows;
    }

    private function getPVByRegionFromCommunes(array $params, string $dateWhere): array {
        $dateParams = array_filter($params, fn($k) => in_array($k, ['dd', 'df']), ARRAY_FILTER_USE_KEY);
        $sql = "
            SELECT r.nom AS region_nom, COUNT(p.id) AS pv_count
            FROM   regions r
            JOIN   departements d  ON d.region_id    = r.id
            JOIN   communes c      ON c.departement_id = d.id
            LEFT JOIN pv p
              ON  p.commune_id = c.id
              AND p.est_antiterroriste = 1
              $dateWhere
            GROUP BY r.id, r.nom
            ORDER BY pv_count DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dateParams);
        $pvByRegion = [];
        foreach ($stmt->fetchAll() as $row) {
            $pvByRegion[$row['region_nom']] = (int)$row['pv_count'];
        }
        return $pvByRegion;
    }

    public function data(): void { $this->apiData(); }
}
