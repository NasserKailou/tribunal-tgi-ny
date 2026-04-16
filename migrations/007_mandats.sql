-- Migration 007 : Table mandats
-- Mandats de justice (arrêt, dépôt, amener, comparution, perquisition)
-- Compatible MySQL 8.0 strict

CREATE TABLE IF NOT EXISTS mandats (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    numero              VARCHAR(80)  NOT NULL UNIQUE COMMENT 'Ex: MAND N°001/2026/TGI-NY',
    type_mandat         ENUM('arret','depot','amener','comparution','perquisition','liberation') NOT NULL,
    dossier_id          INT          NULL REFERENCES dossiers(id),
    
    -- Personne ciblée (existante ou nouvelle)
    detenu_id           INT          NULL REFERENCES detenus(id),
    partie_id           INT          NULL REFERENCES parties(id),
    
    -- Si nouvelle personne (pas encore dans le système)
    nouveau_nom         VARCHAR(150) NULL,
    nouveau_prenom      VARCHAR(150) NULL,
    nouveau_ddn         DATE         NULL,
    nouveau_nationalite VARCHAR(100) NULL DEFAULT 'Nigérienne',
    nouveau_adresse     TEXT         NULL,
    nouveau_profession  VARCHAR(200) NULL,
    
    -- Contenu du mandat
    motif               TEXT         NOT NULL,
    infraction_libelle  TEXT         NULL,
    lieu_execution      TEXT         NULL,
    
    -- Magistrat émetteur
    emetteur_id         INT          NOT NULL REFERENCES users(id),
    
    -- Validité
    date_emission       DATE         NOT NULL,
    date_expiration     DATE         NULL,
    
    -- Exécution
    statut              ENUM('emis','signifie','execute','annule','expire') NOT NULL DEFAULT 'emis',
    date_execution      DATE         NULL,
    executant_nom       VARCHAR(200) NULL COMMENT 'OPJ ou unité qui a exécuté',
    observations        TEXT         NULL,
    
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by          INT          NULL REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index
CREATE INDEX IF NOT EXISTS idx_mandats_dossier  ON mandats(dossier_id);
CREATE INDEX IF NOT EXISTS idx_mandats_detenu   ON mandats(detenu_id);
CREATE INDEX IF NOT EXISTS idx_mandats_emetteur ON mandats(emetteur_id);
CREATE INDEX IF NOT EXISTS idx_mandats_statut   ON mandats(statut);
