<?php
class CarteController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $user = Auth::currentUser();
        $this->view('carte/index', compact('user'));
    }

    /**
     * Endpoint principal : GET /api/carte-data
     * Retourne pvByCommune, pvByRegion, stats générales
     */
    public function apiData(): void {
        Auth::requireLogin();

        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin   = $_GET['date_fin']   ?? null;

        $params    = [];
        $dateWhere = '';
        if ($dateDebut && $dateFin) {
            $dateWhere  = "AND p.date_reception BETWEEN :dd AND :df";
            $params['dd'] = $dateDebut;
            $params['df'] = $dateFin;
        }

        // ── PV par commune ────────────────────────────────────────────────────
        $sql = "SELECT c.id, c.nom, c.latitude, c.longitude,
                       dep.nom  AS dept_nom,
                       r.nom    AS region_nom,
                       COUNT(p.id) AS pv_count
                FROM communes c
                LEFT JOIN departements dep ON c.departement_id = dep.id
                LEFT JOIN regions r        ON dep.region_id    = r.id
                LEFT JOIN pv p             ON p.commune_id = c.id
                                          AND p.est_antiterroriste = 1
                                          $dateWhere
                WHERE c.latitude IS NOT NULL
                GROUP BY c.id, c.nom, c.latitude, c.longitude, dep.nom, r.nom
                ORDER BY pv_count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $communes = $stmt->fetchAll();

        // Caster les entiers
        foreach ($communes as &$comm) {
            $comm['pv_count'] = (int) $comm['pv_count'];
        }
        unset($comm);

        // ── PV par région ─────────────────────────────────────────────────────
        $sqlRegion = "SELECT r.nom AS region_nom, COUNT(p.id) AS pv_count
                      FROM pv p
                      JOIN communes c   ON p.commune_id     = c.id
                      JOIN departements dep ON c.departement_id = dep.id
                      JOIN regions r    ON dep.region_id    = r.id
                      WHERE p.est_antiterroriste = 1
                      $dateWhere
                      GROUP BY r.nom
                      ORDER BY pv_count DESC";

        $stmtR   = $this->db->prepare($sqlRegion);
        $stmtR->execute($params);
        $rowsR   = $stmtR->fetchAll();
        $pvByRegion = [];
        foreach ($rowsR as $row) {
            $pvByRegion[$row['region_nom']] = (int) $row['pv_count'];
        }

        // ── Stats générales ───────────────────────────────────────────────────
        $totalAntiterro   = (int) $this->db
            ->query("SELECT COUNT(*) FROM pv WHERE est_antiterroriste = 1")
            ->fetchColumn();

        $communesActives  = (int) $this->db
            ->query("SELECT COUNT(DISTINCT commune_id) FROM pv WHERE est_antiterroriste = 1 AND commune_id IS NOT NULL")
            ->fetchColumn();

        // ── Commune la plus touchée ───────────────────────────────────────────
        $topCommune = !empty($communes) ? $communes[0] : null;

        $this->json([
            'communes'        => $communes,
            'pvByRegion'      => $pvByRegion,
            'total_pv_anti'   => $totalAntiterro,
            'communes_actives'=> $communesActives,
            'top_commune'     => $topCommune,
        ]);
    }

    /**
     * Alias route : GET /carte/data  (accès direct depuis la vue)
     */
    public function data(): void {
        $this->apiData();
    }
}
