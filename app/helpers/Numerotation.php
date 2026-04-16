<?php
class Numerotation {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Génère un numéro RG : RG N°XXX/YYYY/TGI-NY
     */
    public function genererRG(int $annee = 0): string {
        if (!$annee) $annee = (int)date('Y');
        $stmt = $this->db->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(numero_rg, 'N°', -1), '/', 1) AS UNSIGNED)) as max_seq 
             FROM pv WHERE numero_rg LIKE :pattern"
        );
        $stmt->execute(['pattern' => "RG N°%/{$annee}/TGI-NY"]);
        $row = $stmt->fetch();
        $seq = ($row['max_seq'] ?? 0) + 1;

        // Vérifier aussi dans la table dossiers
        $stmt2 = $this->db->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(numero_rg, 'N°', -1), '/', 1) AS UNSIGNED)) as max_seq 
             FROM dossiers WHERE numero_rg LIKE :pattern"
        );
        $stmt2->execute(['pattern' => "RG N°%/{$annee}/TGI-NY"]);
        $row2 = $stmt2->fetch();
        $seq2 = ($row2['max_seq'] ?? 0) + 1;

        $finalSeq = max($seq, $seq2);
        return sprintf("RG N°%03d/%d/TGI-NY", $finalSeq, $annee);
    }

    /**
     * Génère un numéro RP : RP N°XXX/YYYY/PARQUET
     */
    public function genererRP(int $annee = 0): string {
        if (!$annee) $annee = (int)date('Y');
        $stmt = $this->db->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(numero_rp, 'N°', -1), '/', 1) AS UNSIGNED)) as max_seq 
             FROM dossiers WHERE numero_rp LIKE :pattern"
        );
        $stmt->execute(['pattern' => "RP N°%/{$annee}/PARQUET"]);
        $row = $stmt->fetch();
        $seq = ($row['max_seq'] ?? 0) + 1;
        return sprintf("RP N°%03d/%d/PARQUET", $seq, $annee);
    }

    /**
     * Génère un numéro RI : RI N°XXX/YYYY/INSTR
     */
    public function genererRI(int $annee = 0): string {
        if (!$annee) $annee = (int)date('Y');
        $stmt = $this->db->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(numero_ri, 'N°', -1), '/', 1) AS UNSIGNED)) as max_seq 
             FROM dossiers WHERE numero_ri LIKE :pattern"
        );
        $stmt->execute(['pattern' => "RI N°%/{$annee}/INSTR"]);
        $row = $stmt->fetch();
        $seq = ($row['max_seq'] ?? 0) + 1;
        return sprintf("RI N°%03d/%d/INSTR", $seq, $annee);
    }

    /**
     * Génère un numéro jugement : JUG N°XXX/YYYY/TGI-NY
     */
    public function genererJugement(int $annee = 0): string {
        if (!$annee) $annee = (int)date('Y');
        $stmt = $this->db->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(numero_jugement, 'N°', -1), '/', 1) AS UNSIGNED)) as max_seq 
             FROM jugements WHERE numero_jugement LIKE :pattern"
        );
        $stmt->execute(['pattern' => "JUG N°%/{$annee}/TGI-NY"]);
        $row = $stmt->fetch();
        $seq = ($row['max_seq'] ?? 0) + 1;
        return sprintf("JUG N°%03d/%d/TGI-NY", $seq, $annee);
    }

    /**
     * Génère un numéro d'écrou : ECR N°XXX/YYYY
     */
    public function genererEcrou(int $annee = 0): string {
        if (!$annee) $annee = (int)date('Y');
        $stmt = $this->db->prepare(
            "SELECT MAX(CAST(SUBSTRING_INDEX(numero_ecrou, '/', 1) AS UNSIGNED)) as max_seq 
             FROM detenus WHERE numero_ecrou LIKE :pattern"
        );
        $stmt->execute(['pattern' => "ECR%/{$annee}"]);
        $row = $stmt->fetch();
        $seq = ($row['max_seq'] ?? 0) + 1;
        return sprintf("ECR%04d/%d", $seq, $annee);
    }
    /**
     * Génère un numéro de mandat : MAND N°XXX/YYYY/TGI-NY
     */
    public function genererMandat(int $annee = 0): string {
        if (!$annee) $annee = (int)date('Y');
        $stmt = $this->db->prepare(
            "SELECT MAX(CAST(REGEXP_SUBSTR(numero, '[0-9]+') AS UNSIGNED)) as max_seq 
             FROM mandats WHERE numero LIKE :pattern"
        );
        $stmt->execute(['pattern' => "MAND N°%/{$annee}/%"]);
        $row = $stmt->fetch();
        $seq = ($row['max_seq'] ?? 0) + 1;
        return sprintf("MAND N°%03d/%d/TGI-NY", $seq, $annee);
    }

}
