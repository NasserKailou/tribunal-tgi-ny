-- ============================================================
-- TGI-NY | Migration 008 — Classement / Déclassement dossiers
-- ============================================================
-- Ajoute les colonnes nécessaires pour le classement et
-- le déclassement de dossiers (avec motif et traçabilité).
-- ============================================================

SET NAMES utf8mb4;

-- Colonnes classement sur la table dossiers
ALTER TABLE dossiers
    ADD COLUMN IF NOT EXISTS motif_classement TEXT NULL AFTER objet;

ALTER TABLE dossiers
    ADD COLUMN IF NOT EXISTS date_classement DATE NULL AFTER motif_classement;

-- Colonnes déclassement
ALTER TABLE dossiers
    ADD COLUMN IF NOT EXISTS motif_declassement TEXT NULL AFTER date_classement;

ALTER TABLE dossiers
    ADD COLUMN IF NOT EXISTS date_declassement DATETIME NULL AFTER motif_declassement;

ALTER TABLE dossiers
    ADD COLUMN IF NOT EXISTS declasse_par INT NULL AFTER date_declassement;

ALTER TABLE dossiers
    ADD COLUMN IF NOT EXISTS statut_avant_classement VARCHAR(60) NULL AFTER declasse_par;

-- FK sur declasse_par
-- (ignore silencieusement si la FK existe déjà)
ALTER TABLE dossiers
    ADD CONSTRAINT fk_dossiers_declasse_par
    FOREIGN KEY (declasse_par) REFERENCES users(id) ON DELETE SET NULL;
