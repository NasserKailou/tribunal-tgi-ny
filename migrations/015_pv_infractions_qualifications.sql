-- ============================================================
-- Migration 015 — Double qualification PV
-- TGI Hors Classe Niamey — 2026-06-07
-- ============================================================
-- Objectif :
--   1. Créer pv_infractions_enquete  : infractions déclarées par l'unité d'enquête
--      (plusieurs, cochées à la création du PV)
--   2. Créer pv_qualifications_substitut : qualification retenue par le substitut
--      (plusieurs, ajoutées lors du traitement)
--   3. Ajouter colonne pv_id dans documents (si absente) pour pièces jointes PV
--   4. Migration des données existantes (pv.infraction_id → pv_infractions_enquete)
-- Toutes les opérations sont idempotentes (IF NOT EXISTS / IF EXISTS)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- TABLE : pv_infractions_enquete
-- Infractions déclarées par l'unité d'enquête lors de la création du PV
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pv_infractions_enquete` (
  `id`            INT(11)     NOT NULL AUTO_INCREMENT,
  `pv_id`         INT(11)     NOT NULL,
  `infraction_id` INT(11)     NOT NULL,
  `created_at`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pv_infraction_enquete` (`pv_id`, `infraction_id`),
  KEY `idx_pie_pv`         (`pv_id`),
  KEY `idx_pie_infraction` (`infraction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Infractions déclarées par l''unité d''enquête (source PV)';

-- ------------------------------------------------------------
-- TABLE : pv_qualifications_substitut
-- Qualification retenue par le substitut du procureur
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pv_qualifications_substitut` (
  `id`            INT(11)     NOT NULL AUTO_INCREMENT,
  `pv_id`         INT(11)     NOT NULL,
  `infraction_id` INT(11)     NOT NULL,
  `loi_applicable`VARCHAR(300) DEFAULT NULL  COMMENT 'Ex: Art. 123 Code Pénal',
  `observations`  TEXT        DEFAULT NULL,
  `created_by`    INT(11)     DEFAULT NULL,
  `created_at`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pv_qual_substitut` (`pv_id`, `infraction_id`),
  KEY `idx_pqs_pv`         (`pv_id`),
  KEY `idx_pqs_infraction` (`infraction_id`),
  KEY `idx_pqs_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Qualifications retenues par le substitut du procureur';

-- ------------------------------------------------------------
-- Clés étrangères pv_infractions_enquete
-- ------------------------------------------------------------
ALTER TABLE `pv_infractions_enquete`
  ADD CONSTRAINT `fk_pie_pv`
    FOREIGN KEY IF NOT EXISTS (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pie_infraction`
    FOREIGN KEY IF NOT EXISTS (`infraction_id`) REFERENCES `infractions` (`id`) ON DELETE CASCADE;

-- ------------------------------------------------------------
-- Clés étrangères pv_qualifications_substitut
-- ------------------------------------------------------------
ALTER TABLE `pv_qualifications_substitut`
  ADD CONSTRAINT `fk_pqs_pv`
    FOREIGN KEY IF NOT EXISTS (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pqs_infraction`
    FOREIGN KEY IF NOT EXISTS (`infraction_id`) REFERENCES `infractions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pqs_user`
    FOREIGN KEY IF NOT EXISTS (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- Migration des données existantes :
-- pv.infraction_id (ancienne colonne mono-valeur) → pv_infractions_enquete
-- On insère uniquement s'il n'existe pas déjà un enregistrement
-- ------------------------------------------------------------
INSERT IGNORE INTO `pv_infractions_enquete` (`pv_id`, `infraction_id`)
SELECT `id`, `infraction_id`
FROM   `pv`
WHERE  `infraction_id` IS NOT NULL;

-- ------------------------------------------------------------
-- Ajouter colonne pv_id dans documents (si absente)
-- Permet d'associer des pièces jointes directement à un PV
-- ------------------------------------------------------------
ALTER TABLE `documents`
  MODIFY COLUMN `pv_id` INT(11) DEFAULT NULL;

-- Index sur documents.pv_id (si absent)
CREATE INDEX IF NOT EXISTS `idx_documents_pv_id` ON `documents` (`pv_id`);

-- ------------------------------------------------------------
-- Route API infraction (ajout AJAX depuis PV/create)
-- Ajouter colonne 'description' dans infractions si absente
-- ------------------------------------------------------------
ALTER TABLE `infractions`
  ADD COLUMN IF NOT EXISTS `description` TEXT DEFAULT NULL
    COMMENT 'Description optionnelle de l''infraction';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Fin migration 015
-- ============================================================
