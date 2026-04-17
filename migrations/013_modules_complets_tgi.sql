-- ============================================================================
-- Migration 013 — Modules complets TGI-NY (v3.5)
-- Avocats, Ordonnances JI, Voies de recours, Contrôle judiciaire,
-- Expertises, Commissions rogatoires, Scellés, Casier judiciaire
-- ============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. TABLE avocats
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS avocats (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    matricule       VARCHAR(30)  NOT NULL UNIQUE,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    barreau         VARCHAR(100) NOT NULL DEFAULT 'Barreau de Niamey',
    numero_ordre    VARCHAR(50)  NULL,
    telephone       VARCHAR(30)  NULL,
    email           VARCHAR(150) NULL,
    adresse         TEXT         NULL,
    date_inscription DATE        NULL,
    statut          ENUM('actif','suspendu','radié','honoraire') NOT NULL DEFAULT 'actif',
    observations    TEXT         NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lien avocat ↔ dossier
CREATE TABLE IF NOT EXISTS avocat_dossier (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    avocat_id   INT UNSIGNED NOT NULL,
    dossier_id  INT UNSIGNED NOT NULL,
    role_avocat ENUM('defense','partie_civile','expert','autre') NOT NULL DEFAULT 'defense',
    date_mandat DATE         NULL,
    observations TEXT        NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_avocat_dossier (avocat_id, dossier_id),
    FOREIGN KEY (avocat_id)  REFERENCES avocats(id)  ON DELETE CASCADE,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. TABLE ordonnances (JI)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ordonnances (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_ordonnance   VARCHAR(50)  NOT NULL UNIQUE,
    dossier_id          INT UNSIGNED NOT NULL,
    juge_id             INT UNSIGNED NULL,
    type_ordonnance     ENUM(
        'renvoi','non_lieu','detention','liberation',
        'saisie','perquisition','commission_rogatoire','autre'
    ) NOT NULL,
    date_ordonnance     DATE         NOT NULL,
    contenu             TEXT         NOT NULL,
    observations        TEXT         NULL,
    statut              ENUM('projet','signee','notifiee','executee') NOT NULL DEFAULT 'projet',
    date_signature      DATETIME     NULL,
    date_notification   DATETIME     NULL,
    created_by          INT UNSIGNED NULL,
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (juge_id)    REFERENCES users(id)    ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. TABLE voies_recours
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS voies_recours (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dossier_id          INT UNSIGNED NOT NULL,
    jugement_id         INT UNSIGNED NULL,
    type_recours        ENUM('appel','cassation','opposition','revision') NOT NULL,
    demandeur_nom       VARCHAR(200) NOT NULL,
    demandeur_qualite   ENUM('prevenu','partie_civile','ministere_public','avocat') NULL,
    date_declaration    DATE         NOT NULL,
    juridiction_saisie  VARCHAR(200) NULL,
    motifs              TEXT         NULL,
    decision_rendue     TEXT         NULL,
    date_decision       DATE         NULL,
    statut              ENUM('declare','instruit','juge','irrecevable','desiste') NOT NULL DEFAULT 'declare',
    created_by          INT UNSIGNED NULL,
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)  REFERENCES dossiers(id)  ON DELETE CASCADE,
    FOREIGN KEY (jugement_id) REFERENCES jugements(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 4. TABLE controles_judiciaires
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS controles_judiciaires (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dossier_id      INT UNSIGNED NOT NULL,
    ordonnance_id   INT UNSIGNED NULL,
    type_controle   ENUM('controle_judiciaire','liberte_provisoire','liberte_sous_caution') NOT NULL DEFAULT 'controle_judiciaire',
    personne_nom    VARCHAR(100) NOT NULL,
    personne_prenom VARCHAR(100) NULL,
    date_debut      DATE         NOT NULL,
    date_fin        DATE         NULL,
    obligations     TEXT         NOT NULL,
    observations    TEXT         NULL,
    statut          ENUM('actif','leve','viole','expire') NOT NULL DEFAULT 'actif',
    date_levee      DATETIME     NULL,
    motif_levee     TEXT         NULL,
    violations      TEXT         NULL,
    created_by      INT UNSIGNED NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)    REFERENCES dossiers(id)    ON DELETE CASCADE,
    FOREIGN KEY (ordonnance_id) REFERENCES ordonnances(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)    REFERENCES users(id)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 5. TABLE expertises_judiciaires
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS expertises_judiciaires (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dossier_id          INT UNSIGNED NOT NULL,
    ordonnance_id       INT UNSIGNED NULL,
    type_expertise      ENUM(
        'medico_legale','psychiatrique','comptable','technique',
        'balistique','graphologique','informatique','autre'
    ) NOT NULL,
    expert_nom          VARCHAR(150) NOT NULL,
    expert_qualification VARCHAR(200) NULL,
    date_mission        DATE         NOT NULL,
    delai_depot         DATE         NULL,
    objet_expertise     TEXT         NOT NULL,
    date_depot_rapport  DATE         NULL,
    conclusions         TEXT         NULL,
    statut              ENUM('ordonnee','en_cours','deposee','validee','contestee') NOT NULL DEFAULT 'ordonnee',
    created_by          INT UNSIGNED NULL,
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)    REFERENCES dossiers(id)    ON DELETE CASCADE,
    FOREIGN KEY (ordonnance_id) REFERENCES ordonnances(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)    REFERENCES users(id)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 6. TABLE commissions_rogatoires
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS commissions_rogatoires (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_cr               VARCHAR(50)  NOT NULL UNIQUE,
    dossier_id              INT UNSIGNED NOT NULL,
    type_cr                 ENUM('nationale','internationale') NOT NULL DEFAULT 'nationale',
    autorite_destinataire   VARCHAR(250) NOT NULL,
    date_envoi              DATE         NOT NULL,
    objet                   TEXT         NOT NULL,
    date_retour             DATE         NULL,
    resultats               TEXT         NULL,
    statut                  ENUM('envoyee','executee','retour','classee') NOT NULL DEFAULT 'envoyee',
    created_by              INT UNSIGNED NULL,
    created_at              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 7. TABLE scelles
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS scelles (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_scelle       VARCHAR(50)  NOT NULL UNIQUE,
    dossier_id          INT UNSIGNED NOT NULL,
    categorie           ENUM('arme','drogue','document','argent','electronique','vehicule','autre') NOT NULL,
    description         TEXT         NOT NULL,
    date_depot          DATE         NOT NULL,
    lieu_conservation   VARCHAR(200) NULL DEFAULT 'Greffe du TGI-NY',
    observations        TEXT         NULL,
    statut              ENUM('depose','inventorie','restitue','detruit','confisque') NOT NULL DEFAULT 'depose',
    date_restitution    DATE         NULL,
    beneficiaire_restitution VARCHAR(200) NULL,
    date_destruction    DATE         NULL,
    motif_destruction   TEXT         NULL,
    pv_destruction      VARCHAR(100) NULL,
    created_by          INT UNSIGNED NULL,
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 8. TABLE casier_judiciaire (personnes physiques)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS casier_judiciaire_personnes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nin             VARCHAR(30)  NULL UNIQUE COMMENT 'Numéro d''Identification National',
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NULL,
    date_naissance  DATE         NULL,
    lieu_naissance  VARCHAR(200) NULL,
    nationalite     VARCHAR(100) NULL DEFAULT 'Nigérienne',
    sexe            ENUM('M','F') NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nom   (nom),
    INDEX idx_nin   (nin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS casier_judiciaire_condamnations (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    personne_id         INT UNSIGNED NOT NULL,
    dossier_id          INT UNSIGNED NULL,
    jugement_id         INT UNSIGNED NULL,
    date_condamnation   DATE         NOT NULL,
    juridiction         VARCHAR(200) NULL DEFAULT 'TGI-HC Niamey',
    infraction          TEXT         NOT NULL,
    peine               TEXT         NOT NULL,
    date_fin_peine      DATE         NULL,
    gracie              TINYINT(1)   NOT NULL DEFAULT 0,
    date_grace          DATE         NULL,
    observations        TEXT         NULL,
    created_by          INT UNSIGNED NULL,
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (personne_id) REFERENCES casier_judiciaire_personnes(id) ON DELETE CASCADE,
    FOREIGN KEY (dossier_id)  REFERENCES dossiers(id)  ON DELETE SET NULL,
    FOREIGN KEY (jugement_id) REFERENCES jugements(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 9. Numérotation automatique (séquences pour nouveaux modules)
-- ─────────────────────────────────────────────────────────────────────────────
-- Ajouter les séquences dans la table numerotation si elle existe
INSERT IGNORE INTO numerotation (type_document, annee, sequence)
    SELECT 'ORDO', YEAR(NOW()), 0  WHERE EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='numerotation');

INSERT IGNORE INTO numerotation (type_document, annee, sequence)
    SELECT 'CR',   YEAR(NOW()), 0  WHERE EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='numerotation');

INSERT IGNORE INTO numerotation (type_document, annee, sequence)
    SELECT 'SCE',  YEAR(NOW()), 0  WHERE EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='numerotation');

-- ─────────────────────────────────────────────────────────────────────────────
-- 10. Ajouter index manquants sur dossiers pour les jointures
-- ─────────────────────────────────────────────────────────────────────────────
SET @idx1 = (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dossiers' AND INDEX_NAME='idx_statut');
SET @sql1 = IF(@idx1=0, 'ALTER TABLE dossiers ADD INDEX idx_statut (statut)', 'SELECT 1');
PREPARE stmt FROM @sql1; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx2 = (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dossiers' AND INDEX_NAME='idx_type_affaire');
SET @sql2 = IF(@idx2=0, 'ALTER TABLE dossiers ADD INDEX idx_type_affaire (type_affaire)', 'SELECT 1');
PREPARE stmt FROM @sql2; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET foreign_key_checks = 1;

-- ─────────────────────────────────────────────────────────────────────────────
-- FIN — Migration 013 v3.5
-- ─────────────────────────────────────────────────────────────────────────────
