-- ============================================================
-- TGI-NY | Migration 003 — Tables de configuration
-- Infractions, Maisons d'arrêt, seeds Niger
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Table infractions
CREATE TABLE IF NOT EXISTS infractions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(20) UNIQUE NOT NULL,
    libelle         VARCHAR(255) NOT NULL,
    categorie       ENUM('criminelle','correctionnelle','contraventionnelle') NOT NULL,
    peine_min_mois  INT NULL,
    peine_max_mois  INT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table maisons_arret
CREATE TABLE IF NOT EXISTS maisons_arret (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(150) NOT NULL,
    ville               VARCHAR(100) NOT NULL,
    region_id           INT NULL,
    capacite            INT DEFAULT 0,
    population_actuelle INT DEFAULT 0,
    directeur           VARCHAR(100) NULL,
    telephone           VARCHAR(20)  NULL,
    adresse             TEXT NULL,
    actif               TINYINT DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rattachement détenus à une maison d'arrêt
ALTER TABLE detenus
    ADD COLUMN IF NOT EXISTS maison_arret_id INT NULL AFTER etablissement;

-- Ajout FK si elle n'existe pas encore (ignore l'erreur si déjà présente)
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'detenus'
      AND CONSTRAINT_NAME = 'fk_detenus_maison_arret'
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE detenus ADD CONSTRAINT fk_detenus_maison_arret FOREIGN KEY (maison_arret_id) REFERENCES maisons_arret(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- SEED : 15 infractions nigériennes courantes
-- ============================================================
INSERT IGNORE INTO infractions (code, libelle, categorie, peine_min_mois, peine_max_mois) VALUES
('INF-001', 'Meurtre avec préméditation',                         'criminelle',        120, 999),
('INF-002', 'Viol',                                               'criminelle',         60, 120),
('INF-003', 'Vol à main armée',                                   'criminelle',         60, 120),
('INF-004', 'Terrorisme et association de malfaiteurs',           'criminelle',        120, 999),
('INF-005', 'Trafic de stupéfiants',                             'criminelle',         60, 120),
('INF-006', 'Enlèvement et séquestration',                       'criminelle',         60, 120),
('INF-007', 'Escroquerie et abus de confiance',                  'correctionnelle',    12,  60),
('INF-008', 'Détournement de deniers publics',                   'correctionnelle',    24,  60),
('INF-009', 'Corruption active et passive',                      'correctionnelle',    24,  60),
('INF-010', 'Coups et blessures volontaires',                    'correctionnelle',     6,  36),
('INF-011', 'Vol simple',                                        'correctionnelle',     3,  24),
('INF-012', 'Faux et usage de faux',                             'correctionnelle',    12,  36),
('INF-013', 'Trafic illicite de migrants',                       'correctionnelle',    24,  60),
('INF-014', 'Ivresse publique et manifeste',                     'contraventionnelle',  0,   1),
('INF-015', 'Tapage nocturne et trouble à l'ordre public',       'contraventionnelle',  0,   1);

-- ============================================================
-- SEED : 6 maisons d'arrêt du Niger
-- ============================================================
INSERT IGNORE INTO maisons_arret (nom, ville, capacite, population_actuelle, directeur, telephone, adresse) VALUES
('Maison d\'Arrêt de Niamey',     'Niamey',  600, 450, 'Commandant Seydou MAIGA',   '+227 20 73 40 00', 'Quartier Plateau, Niamey'),
('Maison d\'Arrêt de Kollo',      'Kollo',   100,  70, 'Lieutenant Adamou SOULEY',   '+227 20 47 00 21', 'Route de Dosso, Kollo'),
('Maison d\'Arrêt de Dosso',      'Dosso',   150,  90, 'Commandant Ibrahim GARBA',   '+227 20 65 01 12', 'Centre-ville, Dosso'),
('Maison d\'Arrêt de Tahoua',     'Tahoua',  200, 130, 'Commandant Harouna ISSA',    '+227 20 61 02 80', 'Quartier Administratif, Tahoua'),
('Maison d\'Arrêt de Maradi',     'Maradi',  250, 180, 'Commandant Abdou LAWALI',    '+227 20 41 03 55', 'Quartier Dan Goulbi, Maradi'),
('Maison d\'Arrêt de Zinder',     'Zinder',  300, 210, 'Commandant Moutari HASSANE', '+227 20 51 04 10', 'Vieux Zinder, Zinder');

SET FOREIGN_KEY_CHECKS = 1;
