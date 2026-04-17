-- ============================================================
-- Migration 010 — Mise à jour des fonctions parquet
-- Ajoute les postes : Procureur de la République,
-- Procureur Adjoint(e), Substituts N°1 à N°21
-- ============================================================
SET NAMES utf8mb4;

-- ────────────────────────────────────────────────────────────
-- Table des fonctions/postes du parquet (paramétrable)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS fonctions_parquet (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(100) NOT NULL UNIQUE,
    libelle     VARCHAR(200) NOT NULL,
    type_role   ENUM('procureur','substitut','autre') NOT NULL DEFAULT 'substitut',
    ordre       INT UNSIGNED DEFAULT 0,
    actif       TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion / mise à jour des fonctions
INSERT INTO fonctions_parquet (code, libelle, type_role, ordre) VALUES
('procureur',         'Procureur de la République',         'procureur',  1),
('procureur_adjoint', 'Procureur de la République Adjoint(e)', 'procureur', 2),
('substitut_1',       'Substitut N°1',                       'substitut',  3),
('substitut_2',       'Substitut N°2',                       'substitut',  4),
('substitut_3',       'Substitut N°3',                       'substitut',  5),
('substitut_4',       'Substitut N°4',                       'substitut',  6),
('substitut_5',       'Substitut N°5',                       'substitut',  7),
('substitut_6',       'Substitut N°6',                       'substitut',  8),
('substitut_7',       'Substitut N°7',                       'substitut',  9),
('substitut_8',       'Substitut N°8',                       'substitut', 10),
('substitut_9',       'Substitut N°9',                       'substitut', 11),
('substitut_10',      'Substitut N°10',                      'substitut', 12),
('substitut_11',      'Substitut N°11',                      'substitut', 13),
('substitut_12',      'Substitut N°12',                      'substitut', 14),
('substitut_13',      'Substitut N°13',                      'substitut', 15),
('substitut_14',      'Substitut N°14',                      'substitut', 16),
('substitut_15',      'Substitut N°15',                      'substitut', 17),
('substitut_16',      'Substitut N°16',                      'substitut', 18),
('substitut_17',      'Substitut N°17',                      'substitut', 19),
('substitut_18',      'Substitut N°18',                      'substitut', 20),
('substitut_19',      'Substitut N°19',                      'substitut', 21),
('substitut_20',      'Substitut N°20',                      'substitut', 22),
('substitut_21',      'Substitut N°21',                      'substitut', 23)
ON DUPLICATE KEY UPDATE libelle=VALUES(libelle), ordre=VALUES(ordre);

-- Ajouter colonne fonction_parquet_id sur la table users (si pas encore présente)
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS fonction_parquet_id INT UNSIGNED AFTER role_id,
    ADD CONSTRAINT IF NOT EXISTS fk_users_fonction_parquet
        FOREIGN KEY (fonction_parquet_id) REFERENCES fonctions_parquet(id) ON DELETE SET NULL;
