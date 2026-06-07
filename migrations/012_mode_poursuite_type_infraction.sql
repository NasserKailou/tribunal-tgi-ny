-- ============================================================
-- Migration 012 — Mode de poursuite + Type d'infraction PV
-- TGI-NY v3.3 — Nouvelles fonctionnalités PV
-- ============================================================
-- Ajouter :
--   1. mode_poursuite sur la table dossiers (lors du transfert vers instruction)
--   2. type_infraction_id sur pv (lien vers la table infractions)
--   3. infractions supplémentaires demandées
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TABLE dossiers — ADD mode_poursuite
-- ============================================================
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dossiers' AND COLUMN_NAME='mode_poursuite');
SET @s = IF(@c=0,
    "ALTER TABLE dossiers ADD COLUMN mode_poursuite ENUM('aucun','CD','FD','CRCP','RI') NULL DEFAULT 'aucun' COMMENT 'Mode de poursuite : AUCUN, Citation Directe, Flagrant délit, CRCP, Réquisitoire Introductif' AFTER cabinet_id",
    "SELECT 'dossiers.mode_poursuite already exists' AS info");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 2. TABLE pv — ADD infraction_id (type d'infraction)
-- ============================================================
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pv' AND COLUMN_NAME='infraction_id');
SET @s = IF(@c=0,
    "ALTER TABLE pv ADD COLUMN infraction_id INT NULL AFTER type_affaire, ADD CONSTRAINT fk_pv_infraction FOREIGN KEY (infraction_id) REFERENCES infractions(id) ON DELETE SET NULL",
    "SELECT 'pv.infraction_id already exists' AS info");
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- 3. Nouvelles infractions demandées
--    On incrémente les codes à partir de INF-016
-- ============================================================
INSERT IGNORE INTO infractions (code, libelle, categorie, peine_min_mois, peine_max_mois) VALUES
('INF-016', 'Vol de nuit dans une habitation',               'criminelle',        24,  120),
('INF-017', 'Escroquerie',                                    'correctionnelle',   12,   60),
('INF-018', 'Vol à main armé',                               'criminelle',        60,  120),
('INF-019', 'Coup et blessures volontaire',                   'correctionnelle',    6,   36),
('INF-020', 'Trafic international de drogue à haut risque',   'criminelle',       120,  999),
('INF-021', 'AMT',                                            'criminelle',        60,  120),
('INF-022', 'Blanchiment',                                    'correctionnelle',   24,   60),
('INF-023', 'Enrichissement illicite',                        'correctionnelle',   24,   60),
('INF-024', 'Détournement des deniers publics',               'correctionnelle',   24,   60),
('INF-025', 'Infanticide',                                    'criminelle',        120, 999),
('INF-026', 'Viol',                                           'criminelle',        60,  120),
('INF-027', 'Abus de confiance',                              'correctionnelle',   12,   60),
('INF-028', 'Faux et usage de faux',                          'correctionnelle',   12,   36),
('INF-029', 'Coup mortel',                                    'criminelle',        120, 999),
('INF-030', 'Assassinat',                                     'criminelle',        240, 999),
('INF-031', 'Détention illégale d''arme à feu',               'correctionnelle',   24,   60),
('INF-032', 'Accès illégal dans un système informatisé',      'correctionnelle',   12,   36),
('INF-033', 'Concussion',                                     'correctionnelle',   24,   60),
('INF-034', 'Viol sur mineur de moins de 13 ans',             'criminelle',        120, 999),
('INF-035', 'Financement du terrorisme',                      'criminelle',        120, 999),
('INF-036', 'Vol avec violence',                              'criminelle',        60,  120),
('INF-037', 'Terrorisme',                                     'criminelle',        120, 999);

-- ============================================================
-- FIN
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TGI-NY — Migration 012 : mode_poursuite + type_infraction PV
-- Généré le : 2026-04-17 | Version 3.3
-- ============================================================
