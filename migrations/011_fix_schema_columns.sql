-- ============================================================
-- Migration 011 — Correction des noms de colonnes
-- TGI-NY v3.2 — Patch pour bases existantes
-- ============================================================
-- Aligner le schéma avec les noms de colonnes utilisés dans le code PHP.
-- Ce fichier peut être rejoué sans danger (ALTER IF EXISTS / IGNORE).
-- Exécuter après avoir importé tribunal_tgi_ny_COMPLET.sql :
--   mysql -u root -p tribunal_tgi_ny < migrations/011_fix_schema_columns.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TABLE alertes
--    lu          → est_lue
--    ADD destinataire_id (FK users)
-- ============================================================

-- Renommer lu → est_lue (ignore si déjà fait)
SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'alertes'
      AND COLUMN_NAME  = 'lu'
);
SET @sql = IF(@col_exists > 0,
    'ALTER TABLE alertes CHANGE COLUMN lu est_lue TINYINT(1) DEFAULT 0',
    'SELECT ''alertes.lu already renamed'' AS info'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Ajouter destinataire_id si absent
SET @col_exists2 = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'alertes'
      AND COLUMN_NAME  = 'destinataire_id'
);
SET @sql2 = IF(@col_exists2 = 0,
    'ALTER TABLE alertes ADD COLUMN destinataire_id INT NULL AFTER est_lue, ADD CONSTRAINT fk_alertes_destinataire FOREIGN KEY (destinataire_id) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT ''alertes.destinataire_id already exists'' AS info'
);
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- ============================================================
-- 2. TABLE jugements
--    amende           → montant_amende
--    delai_appel_expire → date_limite_appel
--    est_appele       → appel_interjecte
--    observations     → notes (or rename)
--    ADD: duree_peine_mois, sursis, duree_sursis_mois,
--         appel_possible, greffier_id, created_by
-- ============================================================

-- amende → montant_amende
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='amende');
SET @s = IF(@c>0, 'ALTER TABLE jugements CHANGE COLUMN amende montant_amende DECIMAL(15,2) NULL', 'SELECT ''amende already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- delai_appel_expire → date_limite_appel
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='delai_appel_expire');
SET @s = IF(@c>0, 'ALTER TABLE jugements CHANGE COLUMN delai_appel_expire date_limite_appel DATE NULL', 'SELECT ''delai_appel_expire already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- est_appele → appel_interjecte
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='est_appele');
SET @s = IF(@c>0, 'ALTER TABLE jugements CHANGE COLUMN est_appele appel_interjecte TINYINT(1) DEFAULT 0', 'SELECT ''est_appele already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- observations → notes
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='observations');
SET @s = IF(@c>0, 'ALTER TABLE jugements CHANGE COLUMN observations notes TEXT NULL', 'SELECT ''observations already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ADD duree_peine_mois
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='duree_peine_mois');
SET @s = IF(@c=0, 'ALTER TABLE jugements ADD COLUMN duree_peine_mois INT NULL AFTER peine_principale', 'SELECT ''duree_peine_mois exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ADD sursis
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='sursis');
SET @s = IF(@c=0, 'ALTER TABLE jugements ADD COLUMN sursis TINYINT(1) DEFAULT 0 AFTER dommages_interets', 'SELECT ''sursis exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ADD duree_sursis_mois
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='duree_sursis_mois');
SET @s = IF(@c=0, 'ALTER TABLE jugements ADD COLUMN duree_sursis_mois INT NULL AFTER sursis', 'SELECT ''duree_sursis_mois exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ADD appel_possible
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='appel_possible');
SET @s = IF(@c=0, 'ALTER TABLE jugements ADD COLUMN appel_possible TINYINT(1) DEFAULT 0 AFTER duree_sursis_mois', 'SELECT ''appel_possible exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ADD greffier_id
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='greffier_id');
SET @s = IF(@c=0, 'ALTER TABLE jugements ADD COLUMN greffier_id INT NULL AFTER notes, ADD CONSTRAINT fk_jugements_greffier FOREIGN KEY (greffier_id) REFERENCES users(id) ON DELETE SET NULL', 'SELECT ''greffier_id exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ADD created_by
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jugements' AND COLUMN_NAME='created_by');
SET @s = IF(@c=0, 'ALTER TABLE jugements ADD COLUMN created_by INT NULL AFTER greffier_id, ADD CONSTRAINT fk_jugements_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL', 'SELECT ''created_by exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 3. TABLE audiences
--    observations → notes
--    ADD: motif_renvoi, date_renvoi
--    Fix ENUM statut (ajouter tenue, renvoyee)
-- ============================================================

-- observations → notes
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='audiences' AND COLUMN_NAME='observations');
SET @s = IF(@c>0, 'ALTER TABLE audiences CHANGE COLUMN observations notes TEXT NULL', 'SELECT ''audiences.observations already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ADD motif_renvoi
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='audiences' AND COLUMN_NAME='motif_renvoi');
SET @s = IF(@c=0, 'ALTER TABLE audiences ADD COLUMN motif_renvoi TEXT NULL AFTER notes', 'SELECT ''motif_renvoi exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ADD date_renvoi
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='audiences' AND COLUMN_NAME='date_renvoi');
SET @s = IF(@c=0, 'ALTER TABLE audiences ADD COLUMN date_renvoi DATE NULL AFTER motif_renvoi', 'SELECT ''date_renvoi exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Extend statut ENUM to include tenue and renvoyee
ALTER TABLE audiences
    MODIFY COLUMN statut ENUM('planifiee','en_cours','terminee','reportee','annulee','tenue','renvoyee') DEFAULT 'planifiee';

-- ============================================================
-- 4. TABLE detenus — ADD numero_ecrou
-- ============================================================
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='detenus' AND COLUMN_NAME='numero_ecrou');
SET @s = IF(@c=0, 'ALTER TABLE detenus ADD COLUMN numero_ecrou VARCHAR(50) UNIQUE NULL AFTER id', 'SELECT ''numero_ecrou exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 5. TABLE documents
--    nom_fichier  → nom_original
--    type_mime    → mime_type
-- ============================================================
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='documents' AND COLUMN_NAME='nom_fichier');
SET @s = IF(@c>0, 'ALTER TABLE documents CHANGE COLUMN nom_fichier nom_original VARCHAR(300) NOT NULL', 'SELECT ''nom_fichier already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='documents' AND COLUMN_NAME='type_mime');
SET @s = IF(@c>0, 'ALTER TABLE documents CHANGE COLUMN type_mime mime_type VARCHAR(100) NULL', 'SELECT ''type_mime already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 6. TABLE dossiers
--    date_ouverture         → date_enregistrement
--    date_limite            → date_limite_traitement
--    ADD juge_siege_id
-- ============================================================
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dossiers' AND COLUMN_NAME='date_ouverture');
SET @s = IF(@c>0, 'ALTER TABLE dossiers CHANGE COLUMN date_ouverture date_enregistrement DATE NOT NULL', 'SELECT ''date_ouverture already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dossiers' AND COLUMN_NAME='date_limite');
SET @s = IF(@c>0, 'ALTER TABLE dossiers CHANGE COLUMN date_limite date_limite_traitement DATE NULL', 'SELECT ''date_limite already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dossiers' AND COLUMN_NAME='juge_siege_id');
SET @s = IF(@c=0, 'ALTER TABLE dossiers ADD COLUMN juge_siege_id INT NULL AFTER commune_id, ADD CONSTRAINT fk_dossiers_juge_siege FOREIGN KEY (juge_siege_id) REFERENCES users(id) ON DELETE SET NULL', 'SELECT ''juge_siege_id exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 7. TABLE pv
--    ADD substitut_id, date_affectation_substitut
-- ============================================================
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pv' AND COLUMN_NAME='substitut_id');
SET @s = IF(@c=0, 'ALTER TABLE pv ADD COLUMN substitut_id INT NULL AFTER commune_id, ADD CONSTRAINT fk_pv_substitut FOREIGN KEY (substitut_id) REFERENCES users(id) ON DELETE SET NULL', 'SELECT ''pv.substitut_id exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pv' AND COLUMN_NAME='date_affectation_substitut');
SET @s = IF(@c=0, 'ALTER TABLE pv ADD COLUMN date_affectation_substitut DATE NULL AFTER substitut_id', 'SELECT ''date_affectation_substitut exists'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 8. TABLE mouvements_dossier
--    effectue_par  → user_id
--    statut_avant  → ancien_statut
--    statut_apres  → nouveau_statut
-- ============================================================
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mouvements_dossier' AND COLUMN_NAME='effectue_par');
SET @s = IF(@c>0, 'ALTER TABLE mouvements_dossier CHANGE COLUMN effectue_par user_id INT NULL', 'SELECT ''effectue_par already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mouvements_dossier' AND COLUMN_NAME='statut_avant');
SET @s = IF(@c>0, 'ALTER TABLE mouvements_dossier CHANGE COLUMN statut_avant ancien_statut VARCHAR(60)', 'SELECT ''statut_avant already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mouvements_dossier' AND COLUMN_NAME='statut_apres');
SET @s = IF(@c>0, 'ALTER TABLE mouvements_dossier CHANGE COLUMN statut_apres nouveau_statut VARCHAR(60)', 'SELECT ''statut_apres already renamed'' AS info');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- FIN — Réactivation des contraintes
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TGI-NY — Migration 011 : correction noms de colonnes
-- Généré le : 2026-04-17 | Version 3.2
-- ============================================================
