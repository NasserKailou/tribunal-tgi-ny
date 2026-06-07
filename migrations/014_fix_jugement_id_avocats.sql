-- ============================================================
-- TGI-NY | Migration 014 — Correctifs critiques v3.7
-- ============================================================
-- Fixes :
--   1. TABLE detenus — ADD jugement_id (manquant dans export global)
--   2. TABLE avocats  — Aligner colonnes avec AvocatController
--   3. TABLE avocat_dossier — Colonnes correctes
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TABLE detenus — ADD jugement_id
-- ============================================================
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'detenus'
      AND COLUMN_NAME  = 'jugement_id');
SET @s = IF(@c = 0,
    'ALTER TABLE detenus ADD COLUMN jugement_id INT NULL AFTER dossier_id',
    'SELECT ''detenus.jugement_id already exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FK jugement_id → jugements(id)
SET @fk = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA     = DATABASE()
      AND TABLE_NAME        = 'detenus'
      AND COLUMN_NAME       = 'jugement_id'
      AND REFERENCED_TABLE_NAME IS NOT NULL);
SET @sf = IF(@fk = 0,
    'ALTER TABLE detenus ADD CONSTRAINT fk_detenus_jugement FOREIGN KEY (jugement_id) REFERENCES jugements(id) ON DELETE SET NULL',
    'SELECT ''fk_detenus_jugement already exists'' AS info');
PREPARE stmtf FROM @sf; EXECUTE stmtf; DEALLOCATE PREPARE stmtf;

-- ============================================================
-- 2. TABLE avocats — Ajouter colonnes manquantes
--    (table créée par migration 013 sans ces colonnes)
-- ============================================================

-- date_naissance
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocats' AND COLUMN_NAME='date_naissance');
SET @s = IF(@c=0, 'ALTER TABLE avocats ADD COLUMN date_naissance DATE NULL AFTER prenom', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- lieu_naissance
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocats' AND COLUMN_NAME='lieu_naissance');
SET @s = IF(@c=0, 'ALTER TABLE avocats ADD COLUMN lieu_naissance VARCHAR(150) NULL AFTER date_naissance', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- nationalite
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocats' AND COLUMN_NAME='nationalite');
SET @s = IF(@c=0, 'ALTER TABLE avocats ADD COLUMN nationalite VARCHAR(100) DEFAULT ''Nigérienne'' AFTER lieu_naissance', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sexe
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocats' AND COLUMN_NAME='sexe');
SET @s = IF(@c=0, 'ALTER TABLE avocats ADD COLUMN sexe ENUM(''M'',''F'') DEFAULT ''M'' AFTER nationalite', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- specialite
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocats' AND COLUMN_NAME='specialite');
SET @s = IF(@c=0, 'ALTER TABLE avocats ADD COLUMN specialite VARCHAR(150) NULL AFTER email', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- notes (alias de observations pour compatibilité)
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocats' AND COLUMN_NAME='notes');
SET @s = IF(@c=0, 'ALTER TABLE avocats ADD COLUMN notes TEXT NULL AFTER specialite', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- created_by
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocats' AND COLUMN_NAME='created_by');
SET @s = IF(@c=0, 'ALTER TABLE avocats ADD COLUMN created_by INT NULL AFTER created_at', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 3. TABLE avocat_dossier — Ajouter colonnes manquantes
--    (AvocatController utilise aussi partie_id, actif, notes)
-- ============================================================

-- partie_id
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocat_dossier' AND COLUMN_NAME='partie_id');
SET @s = IF(@c=0, 'ALTER TABLE avocat_dossier ADD COLUMN partie_id INT NULL AFTER dossier_id', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- actif
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocat_dossier' AND COLUMN_NAME='actif');
SET @s = IF(@c=0, 'ALTER TABLE avocat_dossier ADD COLUMN actif TINYINT(1) DEFAULT 1 AFTER role_avocat', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- notes
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='avocat_dossier' AND COLUMN_NAME='notes');
SET @s = IF(@c=0, 'ALTER TABLE avocat_dossier ADD COLUMN notes TEXT NULL AFTER actif', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
