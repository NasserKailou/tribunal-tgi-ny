<?php
class CarteController extends Controller {
    public function index(): void {
        Auth::requireLogin();
        $user = Auth::currentUser();
        $this->view('carte/index', compact('user'));
    }

    public function apiData(): void {
        Auth::requireLogin();
        $dateDebut = $_GET['date_debut'] ?? null;
        $dateFin   = $_GET['date_fin'] ?? null;

        $params = [];
        $dateWhere = '';
        if ($dateDebut && $dateFin) {
            $dateWhere = "AND p.date_reception BETWEEN :dd AND :df";
            $params['dd'] = $dateDebut;
            $params['df'] = $dateFin;
        }

        $sql = "SELECT c.id, c.nom, c.latitude, c.longitude,
                       dep.nom as dept_nom, r.nom as region_nom,
                       COUNT(p.id) as pv_count
                FROM communes c
                LEFT JOIN departements dep ON c.departement_id = dep.id
                LEFT JOIN regions r ON dep.region_id = r.id
                LEFT JOIN pv p ON p.commune_id = c.id AND p.est_antiterroriste = 1 $dateWhere
                WHERE c.latitude IS NOT NULL
                GROUP BY c.id, c.nom, c.latitude, c.longitude, dep.nom, r.nom
                ORDER BY pv_count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $communes = $stmt->fetchAll();

        // Pour chaque commune avec des PV, récupérer les primo intervenants
        foreach ($communes as &$comm) {
            if ($comm['pv_count'] > 0) {
                $piSql = "SELECT DISTINCT pi.nom FROM primo_intervenants pi
                          JOIN pv_primo_intervenants ppi ON pi.id = ppi.primo_intervenant_id
                          JOIN pv p ON ppi.pv_id = p.id
                          WHERE p.commune_id = :cid AND p.est_antiterroriste = 1";
                $piParams = ['cid' => $comm['id']];
                if ($dateDebut && $dateFin) {
                    $piSql .= " AND p.date_reception BETWEEN :dd AND :df";
                    $piParams['dd'] = $dateDebut;
                    $piParams['df'] = $dateFin;
                }
                $piStmt = $this->db->prepare($piSql);
                $piStmt->execute($piParams);
                $comm['primo_intervenants'] = array_column($piStmt->fetchAll(), 'nom');
            } else {
                $comm['primo_intervenants'] = [];
            }
            $comm['pv_count'] = (int)$comm['pv_count'];
        }

        // Stats générales
        $totalAntiterro = (int)$this->db->query("SELECT COUNT(*) FROM pv WHERE est_antiterroriste=1")->fetchColumn();
        $communesActives = (int)$this->db->query("SELECT COUNT(DISTINCT commune_id) FROM pv WHERE est_antiterroriste=1 AND commune_id IS NOT NULL")->fetchColumn();

        $this->json([
            'communes'        => $communes,
            'total_pv_anti'   => $totalAntiterro,
            'communes_actives'=> $communesActives,
        ]);
    }
}
