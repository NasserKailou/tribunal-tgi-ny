-- ============================================================
-- TGI-NY | Migration 005 — Pièces jointes par dossier
-- ============================================================
-- La table `documents` existe déjà dans le schéma (001_schema.sql).
-- On vérifie / complète les colonnes nécessaires pour le module
-- pièces jointes. Les colonnes ci-dessous s'ajoutent seulement
-- si elles n'existent pas encore (ALTER TABLE ... ADD COLUMN IF NOT EXISTS
-- est supporté par MySQL 8+ et MariaDB 10.3+).
--
-- Structure cible :
--   id              INT AUTO_INCREMENT PK
--   dossier_id      INT NULL  FK → dossiers(id)
--   nom_fichier     VARCHAR(300)   (nom original affiché)
--   chemin_fichier  VARCHAR(500)   (chemin relatif stockage)
--   type_mime       VARCHAR(100)
--   taille_octets   INT
--   description     TEXT NULL
--   uploaded_by     INT NULL  FK → users(id)
--   created_at      TIMESTAMP
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------
-- Si la table n'existe pas du tout, la créer complète
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS documents (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id     INT NULL,
    pv_id          INT NULL,
    audience_id    INT NULL,
    jugement_id    INT NULL,
    nom_fichier    VARCHAR(300) NOT NULL,
    nom_stockage   VARCHAR(300) NOT NULL,
    chemin_fichier VARCHAR(500) NULL,
    type_document  ENUM('pv','acte_saisine','piece_jointe','jugement','ordonnance','pv_audience','autre') NOT NULL DEFAULT 'piece_jointe',
    type_mime      VARCHAR(100) NULL,
    taille_octets  INT NULL,
    description    TEXT NULL,
    uploaded_by    INT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)  REFERENCES dossiers(id)  ON DELETE CASCADE,
    FOREIGN KEY (pv_id)       REFERENCES pv(id)        ON DELETE CASCADE,
    FOREIGN KEY (audience_id) REFERENCES audiences(id) ON DELETE CASCADE,
    FOREIGN KEY (jugement_id) REFERENCES jugements(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- Ajouter les colonnes manquantes si elles n'existent pas encore
-- (MySQL 8+ / MariaDB 10.3+ supporte IF NOT EXISTS sur ADD COLUMN)
-- ----------------------------------------------------------------

-- chemin_fichier
ALTER TABLE documents
    ADD COLUMN IF NOT EXISTS chemin_fichier VARCHAR(500) NULL AFTER nom_stockage;

-- type_mime (alias de mime_type déjà présent dans le schéma d'origine)
-- On s'assure que la colonne existe sous le nom type_mime
ALTER TABLE documents
    ADD COLUMN IF NOT EXISTS type_mime VARCHAR(100) NULL AFTER chemin_fichier;

-- description
ALTER TABLE documents
    ADD COLUMN IF NOT EXISTS description TEXT NULL AFTER type_mime;

-- Renommer mime_type → type_mime si l'ancienne colonne existe
-- (idempotent : échoue silencieusement si mime_type n'existe plus)
-- Désactivé pour XAMPP MySQL 5.7 — à exécuter manuellement si besoin :
-- ALTER TABLE documents RENAME COLUMN mime_type TO type_mime;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------
-- Index utiles
-- ----------------------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_documents_dossier ON documents(dossier_id);
CREATE INDEX IF NOT EXISTS idx_documents_uploaded_by ON documents(uploaded_by);
