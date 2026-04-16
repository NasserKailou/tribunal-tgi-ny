-- ============================================================
-- TGI-NY | Migration 004 — Enrichissement table detenus
-- ============================================================

SET NAMES utf8mb4;

-- Création table maisons_arret si elle n'existe pas
CREATE TABLE IF NOT EXISTS maisons_arret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    commune_id INT NULL,
    capacite INT DEFAULT 0,
    actif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commune_id) REFERENCES communes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données initiales maisons d'arrêt principales du Niger
INSERT IGNORE INTO maisons_arret (id, nom) VALUES
(1, 'Maison d''Arrêt de Niamey'),
(2, 'Maison d''Arrêt de Zinder'),
(3, 'Maison d''Arrêt de Maradi'),
(4, 'Maison d''Arrêt de Tahoua'),
(5, 'Maison d''Arrêt d''Agadez'),
(6, 'Maison d''Arrêt de Dosso'),
(7, 'Maison d''Arrêt de Diffa'),
(8, 'Centre de détention de Kollo');

-- Ajout des nouvelles colonnes à la table detenus
ALTER TABLE detenus
    ADD COLUMN IF NOT EXISTS surnom_alias VARCHAR(100) NULL AFTER prenom,
    ADD COLUMN IF NOT EXISTS nom_mere VARCHAR(100) NULL AFTER surnom_alias,
    ADD COLUMN IF NOT EXISTS statut_matrimonial ENUM('celibataire','marie','divorce','veuf') DEFAULT 'celibataire' AFTER nom_mere,
    ADD COLUMN IF NOT EXISTS nombre_enfants INT DEFAULT 0 AFTER statut_matrimonial,
    ADD COLUMN IF NOT EXISTS sexe ENUM('M','F') NOT NULL DEFAULT 'M' AFTER nombre_enfants,
    ADD COLUMN IF NOT EXISTS photo_identite VARCHAR(255) NULL AFTER sexe,
    ADD COLUMN IF NOT EXISTS maison_arret_id INT NULL AFTER photo_identite;

-- Ajout FK maison_arret_id → maisons_arret(id)
-- (exécuté séparément pour compatibilité si ALTER précédent échoue partiellement)
ALTER TABLE detenus
    ADD CONSTRAINT fk_detenus_maison_arret
    FOREIGN KEY (maison_arret_id) REFERENCES maisons_arret(id) ON DELETE SET NULL;
