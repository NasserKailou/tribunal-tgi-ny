<?php
class AlerteHelper {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Vérifie toutes les conditions d'alerte et crée les alertes manquantes
     */
    public function verifierEtCreerAlertes(): void {
        $this->alertesPVEnRetard();
        $this->alertesDossiersInstructionEnRetard();
        $this->alertesAudiencesProches();
        $this->alertesAppelExpirant();
        $this->alertesDetenusProvisoires();
    }

    private function alertesPVEnRetard(): void {
        $sql = "SELECT p.id, p.numero_rg, p.substitut_id, p.date_affectation_substitut
                FROM pv p
                WHERE p.statut = 'en_traitement'
                  AND p.date_affectation_substitut IS NOT NULL
                  AND DATEDIFF(NOW(), p.date_affectation_substitut) > :delai";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['delai' => DELAI_TRAITEMENT_PV_JOURS]);
        $pvs = $stmt->fetchAll();

        foreach ($pvs as $pv) {
            $msg = "PV {$pv['numero_rg']} : sans traitement depuis plus de " . DELAI_TRAITEMENT_PV_JOURS . " jours.";
            $this->createAlertIfNotExists(null, $pv['id'], 'retard_pv', 'warning', $msg, $pv['substitut_id']);
        }
    }

    private function alertesDossiersInstructionEnRetard(): void {
        $sql = "SELECT d.id, d.numero_rg, d.cabinet_id, ci.juge_id, d.date_instruction_debut
                FROM dossiers d
                LEFT JOIN cabinets_instruction ci ON d.cabinet_id = ci.id
                WHERE d.statut = 'instruction'
                  AND d.date_instruction_debut IS NOT NULL
                  AND TIMESTAMPDIFF(MONTH, d.date_instruction_debut, NOW()) > :delai";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['delai' => DELAI_INSTRUCTION_MOIS]);
        $dossiers = $stmt->fetchAll();

        foreach ($dossiers as $d) {
            $msg = "Dossier {$d['numero_rg']} : instruction en cours depuis plus de " . DELAI_INSTRUCTION_MOIS . " mois.";
            $this->createAlertIfNotExists($d['id'], null, 'retard_instruction', 'danger', $msg, $d['juge_id']);
        }
    }

    private function alertesAudiencesProches(): void {
        $sql = "SELECT a.id, a.date_audience, a.greffier_id, a.president_id, d.numero_rg, d.id as dossier_id
                FROM audiences a
                JOIN dossiers d ON a.dossier_id = d.id
                WHERE a.statut = 'planifiee'
                  AND a.date_audience BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :delai DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['delai' => DELAI_ALERTE_AUDIENCE_JOURS]);
        $audiences = $stmt->fetchAll();

        foreach ($audiences as $aud) {
            $dateStr = date('d/m/Y H:i', strtotime($aud['date_audience']));
            $msg = "Audience prévue pour le dossier {$aud['numero_rg']} le {$dateStr}.";
            if ($aud['greffier_id']) {
                $this->createAlertIfNotExists($aud['dossier_id'], null, 'audience_proche', 'info', $msg, $aud['greffier_id']);
            }
            if ($aud['president_id'] && $aud['president_id'] !== $aud['greffier_id']) {
                $this->createAlertIfNotExists($aud['dossier_id'], null, 'audience_proche', 'info', $msg, $aud['president_id']);
            }
        }
    }

    private function alertesAppelExpirant(): void {
        $sql = "SELECT j.id, j.numero_jugement, j.date_limite_appel, j.dossier_id, d.numero_rg
                FROM jugements j
                JOIN dossiers d ON j.dossier_id = d.id
                WHERE j.appel_possible = 1
                  AND j.appel_interjecte = 0
                  AND j.date_limite_appel BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 5 DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $jugements = $stmt->fetchAll();

        foreach ($jugements as $jug) {
            $dateStr = date('d/m/Y', strtotime($jug['date_limite_appel']));
            $msg = "Délai d'appel pour le jugement {$jug['numero_jugement']} (dossier {$jug['numero_rg']}) expire le {$dateStr}.";
            // Alerter le greffier chef
            $this->createAlertIfNotExists($jug['dossier_id'], null, 'appel_expire', 'danger', $msg, null);
        }
    }

    private function alertesDetenusProvisoires(): void {
        $sql = "SELECT d.id, d.nom, d.prenom, d.numero_ecrou, d.date_incarceration, d.dossier_id
                FROM detenus d
                WHERE d.statut = 'incarcere'
                  AND d.type_detention IN ('prevenu', 'detenu_provisoire', 'inculpe')
                  AND TIMESTAMPDIFF(MONTH, d.date_incarceration, NOW()) > :delai";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['delai' => DELAI_DETENTION_PROVISOIRE_MOIS]);
        $detenus = $stmt->fetchAll();

        foreach ($detenus as $det) {
            $mois = DELAI_DETENTION_PROVISOIRE_MOIS;
            $msg = "Détenu {$det['nom']} {$det['prenom']} (écrou {$det['numero_ecrou']}) en détention provisoire depuis plus de {$mois} mois sans jugement.";
            $this->createAlertIfNotExists($det['dossier_id'], null, 'delai_detention', 'danger', $msg, null);
        }
    }

    private function createAlertIfNotExists(?int $dossierId, ?int $pvId, string $type, string $niveau, string $message, ?int $destinataireId): void {
        // Vérifier si une alerte similaire non lue existe déjà
        $sql = "SELECT id FROM alertes 
                WHERE type_alerte = :type 
                  AND est_lue = 0
                  AND message = :msg
                  AND (dossier_id = :did OR (dossier_id IS NULL AND :did IS NULL))
                  AND (pv_id = :pid OR (pv_id IS NULL AND :pid IS NULL))
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'type' => $type,
            'msg'  => $message,
            'did'  => $dossierId,
            'pid'  => $pvId,
        ]);
        if ($stmt->fetch()) return; // déjà créée

        $ins = $this->db->prepare(
            "INSERT INTO alertes (dossier_id, pv_id, type_alerte, niveau, message, destinataire_id) 
             VALUES (:did, :pid, :type, :niveau, :msg, :dest)"
        );
        $ins->execute([
            'did'    => $dossierId,
            'pid'    => $pvId,
            'type'   => $type,
            'niveau' => $niveau,
            'msg'    => $message,
            'dest'   => $destinataireId,
        ]);
    }

    public function countUnread(?int $userId = null): int {
        if ($userId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM alertes WHERE est_lue = 0 AND (destinataire_id = :uid OR destinataire_id IS NULL)");
            $stmt->execute(['uid' => $userId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM alertes WHERE est_lue = 0");
            $stmt->execute();
        }
        return (int)$stmt->fetchColumn();
    }
}
