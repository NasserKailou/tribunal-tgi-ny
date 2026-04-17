-- ============================================================
-- TGI-NY | Tribunal de Grande Instance Hors Classe de Niamey
-- BASE DE DONNÉES COMPLÈTE — Fichier unique de restauration
-- Version consolidée de TOUTES les migrations (001 à 010)
-- Prêt à être importé dans une base vierge MySQL 8+ / MariaDB 10.6+
-- ============================================================
-- Utilisation :
--   mysql -u root -p --default-character-set=utf8mb4 tribunal_tgi_ny \
--         < tribunal_tgi_ny_COMPLET.sql
-- Ou via phpMyAdmin : Importer > Choisir ce fichier > Encodage utf8mb4
-- ============================================================
-- IMPORTANT — Créer d'abord la base de données :
--   CREATE DATABASE IF NOT EXISTS tribunal_tgi_ny
--       CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   USE tribunal_tgi_ny;
-- ============================================================
-- Mot de passe par défaut (TOUS les comptes) : Admin@2026
-- Hash bcrypt : $2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym
-- ============================================================
-- Créé le : 2026-04-17 | Version : 3.1 (migrations 001-010)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- ============================================================
-- 1. TABLES DE RÉFÉRENCE GÉOGRAPHIQUE
-- ============================================================

CREATE TABLE IF NOT EXISTS regions (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    nom  VARCHAR(100) NOT NULL,
    code VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS departements (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    region_id INT NOT NULL,
    nom       VARCHAR(100) NOT NULL,
    code      VARCHAR(20),
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS communes (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    departement_id INT NOT NULL,
    nom            VARCHAR(100) NOT NULL,
    code           VARCHAR(20),
    latitude       DECIMAL(10,7) NULL,
    longitude      DECIMAL(10,7) NULL,
    FOREIGN KEY (departement_id) REFERENCES departements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Communes géographiques (266 communes Niger pour carte choroplèthe)
CREATE TABLE IF NOT EXISTS communes_geo (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    departement_nom VARCHAR(150),
    region_nom      VARCHAR(100),
    longitude       DECIMAL(10,6),
    latitude        DECIMAL(10,6),
    code_commune    VARCHAR(20),
    UNIQUE KEY uk_nom_dept (nom, departement_nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. RÔLES ET UTILISATEURS
-- ============================================================

CREATE TABLE IF NOT EXISTS roles (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    code    VARCHAR(50) UNIQUE NOT NULL,
    libelle VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fonctions / postes du parquet (paramétrable — migration 010)
CREATE TABLE IF NOT EXISTS fonctions_parquet (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code       VARCHAR(100) NOT NULL UNIQUE,
    libelle    VARCHAR(200) NOT NULL,
    type_role  ENUM('procureur','substitut','autre') NOT NULL DEFAULT 'substitut',
    ordre      INT UNSIGNED DEFAULT 0,
    actif      TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    role_id             INT NOT NULL,
    fonction_parquet_id INT UNSIGNED NULL,
    nom                 VARCHAR(100) NOT NULL,
    prenom              VARCHAR(100) NOT NULL,
    email               VARCHAR(150) UNIQUE NOT NULL,
    password            VARCHAR(255) NOT NULL,
    telephone           VARCHAR(30),
    matricule           VARCHAR(50),
    actif               TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id)             REFERENCES roles(id),
    FOREIGN KEY (fonction_parquet_id) REFERENCES fonctions_parquet(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. TABLES DE CONFIGURATION MÉTIER
-- ============================================================

CREATE TABLE IF NOT EXISTS cabinets_instruction (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    numero  VARCHAR(20) NOT NULL,
    libelle VARCHAR(100),
    juge_id INT NULL,
    actif   TINYINT(1) DEFAULT 1,
    FOREIGN KEY (juge_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unites_enquete (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(200) NOT NULL,
    type       ENUM('commissariat','brigade_police','gendarmerie','unite_speciale','autre') NOT NULL,
    commune_id INT NULL,
    telephone  VARCHAR(30),
    actif      TINYINT(1) DEFAULT 1,
    FOREIGN KEY (commune_id) REFERENCES communes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS primo_intervenants (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(200) NOT NULL,
    type        VARCHAR(100),
    description TEXT,
    actif       TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS salles_audience (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(150) NOT NULL,
    capacite    INT DEFAULT 0,
    description TEXT,
    actif       TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS infractions (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    code           VARCHAR(20) UNIQUE NOT NULL,
    libelle        VARCHAR(255) NOT NULL,
    categorie      ENUM('criminelle','correctionnelle','contraventionnelle') NOT NULL,
    peine_min_mois INT NULL,
    peine_max_mois INT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS maisons_arret (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(150) NOT NULL,
    ville               VARCHAR(100) NOT NULL DEFAULT '',
    region_id           INT NULL,
    commune_id          INT NULL,
    capacite            INT DEFAULT 0,
    population_actuelle INT DEFAULT 0,
    population_hommes   INT DEFAULT 0,
    population_femmes   INT DEFAULT 0,
    directeur           VARCHAR(100) NULL,
    telephone           VARCHAR(20)  NULL,
    adresse             TEXT NULL,
    actif               TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id)  REFERENCES regions(id)  ON DELETE SET NULL,
    FOREIGN KEY (commune_id) REFERENCES communes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TABLES JUDICIAIRES PRINCIPALES
-- ============================================================

CREATE TABLE IF NOT EXISTS pv (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    numero_pv            VARCHAR(100) NOT NULL,
    numero_rg            VARCHAR(60) UNIQUE NOT NULL,
    unite_enquete_id     INT NULL,
    date_pv              DATE NOT NULL,
    date_reception       DATE NOT NULL,
    type_affaire         ENUM('civile','penale','commerciale') NOT NULL DEFAULT 'penale',
    est_antiterroriste   TINYINT(1) DEFAULT 0,
    region_id            INT NULL,
    departement_id       INT NULL,
    commune_id           INT NULL,
    description_faits    TEXT,
    statut               ENUM('nouveau','en_traitement','classe','transfere') NOT NULL DEFAULT 'nouveau',
    motif_classement     TEXT NULL,
    date_classement      DATE NULL,
    motif_declassement   TEXT NULL,
    date_declassement    DATE NULL,
    created_by           INT NULL,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unite_enquete_id) REFERENCES unites_enquete(id) ON DELETE SET NULL,
    FOREIGN KEY (region_id)        REFERENCES regions(id)        ON DELETE SET NULL,
    FOREIGN KEY (departement_id)   REFERENCES departements(id)   ON DELETE SET NULL,
    FOREIGN KEY (commune_id)       REFERENCES communes(id)       ON DELETE SET NULL,
    FOREIGN KEY (created_by)       REFERENCES users(id)          ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pv_primo_intervenants (
    pv_id              INT NOT NULL,
    primo_intervenant_id INT NOT NULL,
    PRIMARY KEY (pv_id, primo_intervenant_id),
    FOREIGN KEY (pv_id)               REFERENCES pv(id)               ON DELETE CASCADE,
    FOREIGN KEY (primo_intervenant_id) REFERENCES primo_intervenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dossiers (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    numero_rg               VARCHAR(60) UNIQUE NOT NULL,
    numero_rp               VARCHAR(60) UNIQUE NULL,
    numero_ri               VARCHAR(60) UNIQUE NULL,
    pv_id                   INT NULL,
    substitut_id            INT NULL,
    cabinet_id              INT NULL,
    intitule                VARCHAR(255) NOT NULL,
    objet                   TEXT,
    motif_classement        TEXT NULL,
    date_classement         DATE NULL,
    motif_declassement      TEXT NULL,
    date_declassement       DATETIME NULL,
    declasse_par            INT NULL,
    statut_avant_classement VARCHAR(60) NULL,
    type_affaire            ENUM('civile','penale','commerciale') NOT NULL DEFAULT 'penale',
    nature                  ENUM('correctionnel','instructionnel','civil','commercial','criminel') DEFAULT 'correctionnel',
    statut                  ENUM('nouveau','en_instruction','en_audience','juge','classe','appel','transfere') NOT NULL DEFAULT 'nouveau',
    date_ouverture          DATE NOT NULL,
    date_limite             DATE NULL,
    date_instruction_debut  DATE NULL,
    date_instruction_fin    DATE NULL,
    est_antiterroriste      TINYINT(1) DEFAULT 0,
    region_id               INT NULL,
    departement_id          INT NULL,
    commune_id              INT NULL,
    created_by              INT NULL,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pv_id)          REFERENCES pv(id)                    ON DELETE SET NULL,
    FOREIGN KEY (substitut_id)   REFERENCES users(id)                 ON DELETE SET NULL,
    FOREIGN KEY (cabinet_id)     REFERENCES cabinets_instruction(id)  ON DELETE SET NULL,
    FOREIGN KEY (declasse_par)   REFERENCES users(id)                 ON DELETE SET NULL,
    FOREIGN KEY (region_id)      REFERENCES regions(id)               ON DELETE SET NULL,
    FOREIGN KEY (departement_id) REFERENCES departements(id)          ON DELETE SET NULL,
    FOREIGN KEY (commune_id)     REFERENCES communes(id)              ON DELETE SET NULL,
    FOREIGN KEY (created_by)     REFERENCES users(id)                 ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS parties (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id      INT NOT NULL,
    type_partie     ENUM('prevenu','victime','partie_civile','temoin','expert','autre') NOT NULL,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100),
    date_naissance  DATE NULL,
    nationalite     VARCHAR(100),
    profession      VARCHAR(150),
    adresse         TEXT,
    telephone       VARCHAR(30),
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mouvements_dossier (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id      INT NOT NULL,
    type_mouvement  VARCHAR(100) NOT NULL,
    description     TEXT,
    statut_avant    VARCHAR(60),
    statut_apres    VARCHAR(60),
    effectue_par    INT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)   REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (effectue_par) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audiences (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id      INT NOT NULL,
    salle_id        INT NULL,
    numero_audience VARCHAR(60),
    date_audience   DATETIME NOT NULL,
    type_audience   ENUM('correctionnelle','criminelle','civile','commerciale','instruction','autre') DEFAULT 'correctionnelle',
    statut          ENUM('planifiee','en_cours','terminee','reportee','annulee') DEFAULT 'planifiee',
    president_id    INT NULL,
    greffier_id     INT NULL,
    observations    TEXT,
    created_by      INT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)  REFERENCES dossiers(id)       ON DELETE CASCADE,
    FOREIGN KEY (salle_id)    REFERENCES salles_audience(id) ON DELETE SET NULL,
    FOREIGN KEY (president_id) REFERENCES users(id)          ON DELETE SET NULL,
    FOREIGN KEY (greffier_id)  REFERENCES users(id)          ON DELETE SET NULL,
    FOREIGN KEY (created_by)   REFERENCES users(id)          ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS membres_audience (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    audience_id    INT NOT NULL,
    user_id        INT NULL,
    nom_externe    VARCHAR(200) NULL,
    role_audience  ENUM(
        'president','greffier','assesseur_1','assesseur_2','jure_1','jure_2',
        'procureur','substitut','juge_assesseur','avocat_defense',
        'avocat_partie_civile','greffier_adjoint','autre'
    ) NOT NULL DEFAULT 'autre',
    observations   TEXT,
    FOREIGN KEY (audience_id) REFERENCES audiences(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jugements (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id       INT NOT NULL,
    audience_id      INT NULL,
    numero_jugement  VARCHAR(80) UNIQUE NOT NULL,
    date_jugement    DATE NOT NULL,
    type_jugement    ENUM('correctionnel','criminel','civil','commercial','avant_dire_droit','autre') NOT NULL DEFAULT 'correctionnel',
    nature_jugement  ENUM('condamnation','relaxe','acquittement','non_lieu','renvoi','autre') NOT NULL DEFAULT 'condamnation',
    dispositif       TEXT NOT NULL,
    peine_principale VARCHAR(255) NULL,
    amende           DECIMAL(15,2) NULL,
    dommages_interets DECIMAL(15,2) NULL,
    delai_appel_expire DATE NULL,
    est_appele       TINYINT(1) DEFAULT 0,
    date_appel       DATE NULL,
    observations     TEXT,
    redige_par       INT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)  REFERENCES dossiers(id)  ON DELETE CASCADE,
    FOREIGN KEY (audience_id) REFERENCES audiences(id) ON DELETE SET NULL,
    FOREIGN KEY (redige_par)  REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detenus (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    nom                VARCHAR(100) NOT NULL,
    prenom             VARCHAR(100) NOT NULL,
    surnom_alias       VARCHAR(100) NULL,
    nom_mere           VARCHAR(100) NULL,
    statut_matrimonial ENUM('celibataire','marie','divorce','veuf') DEFAULT 'celibataire',
    nombre_enfants     INT DEFAULT 0,
    sexe               ENUM('M','F') NOT NULL DEFAULT 'M',
    photo_identite     VARCHAR(255) NULL,
    maison_arret_id    INT NULL,
    date_naissance     DATE NULL,
    lieu_naissance     VARCHAR(150),
    nationalite        VARCHAR(100) DEFAULT 'Nigérienne',
    profession         VARCHAR(150),
    adresse            TEXT,
    dossier_id         INT NULL,
    type_detention     ENUM('provisoire','condamne','autre') DEFAULT 'provisoire',
    date_incarceration DATE,
    date_liberation_prevue DATE NULL,
    date_liberation_effective DATE NULL,
    cellule            VARCHAR(50),
    etablissement      VARCHAR(200),
    statut             ENUM('incarcere','libere','transfere','evade','decede') DEFAULT 'incarcere',
    infractions_retenues TEXT,
    notes              TEXT,
    created_by         INT NULL,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)      REFERENCES dossiers(id)      ON DELETE SET NULL,
    FOREIGN KEY (maison_arret_id) REFERENCES maisons_arret(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)      REFERENCES users(id)          ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mandats (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    numero              VARCHAR(80) NOT NULL UNIQUE,
    type_mandat         ENUM('arret','depot','amener','comparution','perquisition','liberation') NOT NULL,
    dossier_id          INT NULL,
    detenu_id           INT NULL,
    partie_id           INT NULL,
    nouveau_nom         VARCHAR(150) NULL,
    nouveau_prenom      VARCHAR(150) NULL,
    nouveau_ddn         DATE NULL,
    nouveau_nationalite VARCHAR(100) NULL DEFAULT 'Nigérienne',
    nouveau_adresse     TEXT NULL,
    nouveau_profession  VARCHAR(200) NULL,
    motif               TEXT NOT NULL,
    infraction_libelle  TEXT NULL,
    lieu_execution      TEXT NULL,
    emetteur_id         INT NOT NULL,
    date_emission       DATE NOT NULL,
    date_expiration     DATE NULL,
    statut              ENUM('emis','signifie','execute','annule','expire') NOT NULL DEFAULT 'emis',
    date_execution      DATE NULL,
    executant_nom       VARCHAR(200) NULL,
    observations        TEXT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by          INT NULL,
    FOREIGN KEY (dossier_id)  REFERENCES dossiers(id) ON DELETE SET NULL,
    FOREIGN KEY (detenu_id)   REFERENCES detenus(id)  ON DELETE SET NULL,
    FOREIGN KEY (partie_id)   REFERENCES parties(id)  ON DELETE SET NULL,
    FOREIGN KEY (emetteur_id) REFERENCES users(id),
    FOREIGN KEY (created_by)  REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS alertes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    type_alerte ENUM('delai_pv','delai_instruction','audience_proche','mandat_expire','autre') NOT NULL,
    titre       VARCHAR(255) NOT NULL,
    message     TEXT,
    niveau      ENUM('info','warning','danger') DEFAULT 'info',
    dossier_id  INT NULL,
    pv_id       INT NULL,
    lu          TINYINT(1) DEFAULT 0,
    user_id     INT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (pv_id)      REFERENCES pv(id)       ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. TABLES DE PARAMÉTRAGE (migration 009)
-- ============================================================

CREATE TABLE IF NOT EXISTS parametres_tribunal (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cle         VARCHAR(100) NOT NULL UNIQUE,
    valeur      TEXT,
    groupe      VARCHAR(50) NOT NULL DEFAULT 'general',
    libelle     VARCHAR(200),
    description TEXT,
    type_champ  ENUM('text','textarea','number','boolean','email','tel','url','color','select') NOT NULL DEFAULT 'text',
    options_json TEXT COMMENT 'JSON pour les selects',
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  INT UNSIGNED,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. GESTION DES DROITS (migration 009)
-- ============================================================

CREATE TABLE IF NOT EXISTS menus (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code      VARCHAR(100) NOT NULL UNIQUE,
    libelle   VARCHAR(200) NOT NULL,
    icone     VARCHAR(50),
    url       VARCHAR(200),
    parent_id INT UNSIGNED,
    ordre     INT UNSIGNED DEFAULT 0,
    actif     TINYINT(1) DEFAULT 1,
    FOREIGN KEY (parent_id) REFERENCES menus(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fonctionnalites (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(100) NOT NULL UNIQUE,
    libelle     VARCHAR(200) NOT NULL,
    menu_id     INT UNSIGNED,
    description TEXT,
    actif       TINYINT(1) DEFAULT 1,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS droits_utilisateurs (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           INT UNSIGNED NOT NULL,
    menu_id           INT UNSIGNED,
    fonctionnalite_id INT UNSIGNED,
    accorde           TINYINT(1) DEFAULT 1 COMMENT '1=accordé, 0=révoqué',
    accorde_par       INT UNSIGNED,
    updated_at        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)              REFERENCES users(id)          ON DELETE CASCADE,
    FOREIGN KEY (menu_id)              REFERENCES menus(id)          ON DELETE CASCADE,
    FOREIGN KEY (fonctionnalite_id)    REFERENCES fonctionnalites(id) ON DELETE CASCADE,
    FOREIGN KEY (accorde_par)          REFERENCES users(id)          ON DELETE SET NULL,
    UNIQUE KEY uk_droit_user_menu (user_id, menu_id),
    UNIQUE KEY uk_droit_user_fonc (user_id, fonctionnalite_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. INDEX DE PERFORMANCE
-- ============================================================

CREATE INDEX IF NOT EXISTS idx_pv_statut          ON pv(statut);
CREATE INDEX IF NOT EXISTS idx_pv_antiterro        ON pv(est_antiterroriste);
CREATE INDEX IF NOT EXISTS idx_pv_commune          ON pv(commune_id);
CREATE INDEX IF NOT EXISTS idx_pv_unite            ON pv(unite_enquete_id);
CREATE INDEX IF NOT EXISTS idx_dossiers_statut     ON dossiers(statut);
CREATE INDEX IF NOT EXISTS idx_dossiers_cabinet    ON dossiers(cabinet_id);
CREATE INDEX IF NOT EXISTS idx_dossiers_substitut  ON dossiers(substitut_id);
CREATE INDEX IF NOT EXISTS idx_dossiers_pv         ON dossiers(pv_id);
CREATE INDEX IF NOT EXISTS idx_audiences_dossier   ON audiences(dossier_id);
CREATE INDEX IF NOT EXISTS idx_audiences_date      ON audiences(date_audience);
CREATE INDEX IF NOT EXISTS idx_jugements_dossier   ON jugements(dossier_id);
CREATE INDEX IF NOT EXISTS idx_detenus_dossier     ON detenus(dossier_id);
CREATE INDEX IF NOT EXISTS idx_detenus_statut      ON detenus(statut);
CREATE INDEX IF NOT EXISTS idx_detenus_maison      ON detenus(maison_arret_id);
CREATE INDEX IF NOT EXISTS idx_mandats_dossier     ON mandats(dossier_id);
CREATE INDEX IF NOT EXISTS idx_mandats_statut      ON mandats(statut);
CREATE INDEX IF NOT EXISTS idx_documents_dossier   ON documents(dossier_id);
CREATE INDEX IF NOT EXISTS idx_alertes_user        ON alertes(user_id);
CREATE INDEX IF NOT EXISTS idx_alertes_lu          ON alertes(lu);

-- ============================================================
-- 8. DONNÉES INITIALES — SEED DATA
-- ============================================================

-- 8.1 Rôles
INSERT IGNORE INTO roles (code, libelle) VALUES
('admin',               'Administrateur Système'),
('president',           'Président du Tribunal'),
('vice_president',      'Vice-Président'),
('procureur',           'Procureur de la République'),
('substitut_procureur', 'Substitut du Procureur'),
('juge_instruction',    'Juge d''Instruction'),
('juge_siege',          'Juge du Siège'),
('greffier',            'Greffier'),
('avocat',              'Avocat');

-- 8.2 Fonctions du parquet
INSERT INTO fonctions_parquet (code, libelle, type_role, ordre) VALUES
('procureur',         'Procureur de la République',            'procureur',  1),
('procureur_adjoint', 'Procureur de la République Adjoint(e)', 'procureur',  2),
('substitut_1',       'Substitut N°1',                         'substitut',  3),
('substitut_2',       'Substitut N°2',                         'substitut',  4),
('substitut_3',       'Substitut N°3',                         'substitut',  5),
('substitut_4',       'Substitut N°4',                         'substitut',  6),
('substitut_5',       'Substitut N°5',                         'substitut',  7),
('substitut_6',       'Substitut N°6',                         'substitut',  8),
('substitut_7',       'Substitut N°7',                         'substitut',  9),
('substitut_8',       'Substitut N°8',                         'substitut', 10),
('substitut_9',       'Substitut N°9',                         'substitut', 11),
('substitut_10',      'Substitut N°10',                        'substitut', 12),
('substitut_11',      'Substitut N°11',                        'substitut', 13),
('substitut_12',      'Substitut N°12',                        'substitut', 14),
('substitut_13',      'Substitut N°13',                        'substitut', 15),
('substitut_14',      'Substitut N°14',                        'substitut', 16),
('substitut_15',      'Substitut N°15',                        'substitut', 17),
('substitut_16',      'Substitut N°16',                        'substitut', 18),
('substitut_17',      'Substitut N°17',                        'substitut', 19),
('substitut_18',      'Substitut N°18',                        'substitut', 20),
('substitut_19',      'Substitut N°19',                        'substitut', 21),
('substitut_20',      'Substitut N°20',                        'substitut', 22),
('substitut_21',      'Substitut N°21',                        'substitut', 23)
ON DUPLICATE KEY UPDATE libelle=VALUES(libelle), ordre=VALUES(ordre);

-- 8.3 Régions du Niger
INSERT IGNORE INTO regions (nom, code) VALUES
('Agadez',    'AGZ'),
('Diffa',     'DIF'),
('Dosso',     'DOS'),
('Maradi',    'MAR'),
('Tahoua',    'TAH'),
('Tillabéri', 'TIL'),
('Zinder',    'ZND'),
('Niamey',    'NIA');

-- 8.4 Départements
INSERT IGNORE INTO departements (region_id, nom, code) VALUES
-- Agadez (id=1)
(1,'Agadez','AGZ-AGZ'),(1,'Arlit','AGZ-ARL'),(1,'Bilma','AGZ-BIL'),(1,'Tchirozérine','AGZ-TCH'),
(1,'Aderbissinat','AGZ-ADE'),
-- Diffa (id=2)
(2,'Diffa','DIF-DIF'),(2,'Bosso','DIF-BOS'),(2,'Goudoumaria','DIF-GOU'),(2,'Mainé-Soroa','DIF-MAI'),(2,'N''Guigmi','DIF-NGU'),
-- Dosso (id=3)
(3,'Dosso','DOS-DOS'),(3,'Boboye','DOS-BOB'),(3,'Dogondoutchi','DOS-DOU'),(3,'Gaya','DOS-GAY'),(3,'Loga','DOS-LOG'),
(3,'Dioundiou','DOS-DIO'),(3,'Falmey','DOS-FAL'),(3,'Tibiri','DOS-TIB'),
-- Maradi (id=4)
(4,'Maradi','MAR-MAR'),(4,'Aguié','MAR-AGU'),(4,'Dakoro','MAR-DAK'),(4,'Guidan Roumdji','MAR-GUI'),
(4,'Madarounfa','MAR-MAD'),(4,'Mayahi','MAR-MAY'),(4,'Tessaoua','MAR-TES'),
-- Tahoua (id=5)
(5,'Tahoua','TAH-TAH'),(5,'Abalak','TAH-ABA'),(5,'Birni N''Konni','TAH-BIR'),(5,'Bouza','TAH-BOU'),
(5,'Illéla','TAH-ILL'),(5,'Keita','TAH-KEI'),(5,'Madaoua','TAH-MAD'),(5,'Malbaza','TAH-MAL'),
(5,'Tchintabaraden','TAH-TCH'),(5,'Bagaroua','TAH-BAG'),(5,'Tillia','TAH-TIL'),
-- Tillabéri (id=6)
(6,'Tillabéri','TIL-TIL'),(6,'Ayorou','TIL-AYO'),(6,'Filingué','TIL-FIL'),(6,'Gothèye','TIL-GOT'),
(6,'Kollo','TIL-KOL'),(6,'Say','TIL-SAY'),(6,'Téra','TIL-TER'),(6,'Abala','TIL-ABA'),
(6,'Balleyara','TIL-BAL'),(6,'Banibangou','TIL-BAN'),(6,'Ouallam','TIL-OUA'),(6,'Torodi','TIL-TOR'),
-- Zinder (id=7)
(7,'Zinder','ZND-ZND'),(7,'Dungass','ZND-DUN'),(7,'Gouré','ZND-GOU'),(7,'Magaria','ZND-MAG'),
(7,'Mirriah','ZND-MIR'),(7,'Tanout','ZND-TAN'),(7,'Damagaram Takaya','ZND-DAM'),(7,'Kantché','ZND-KAN'),
-- Niamey (id=8)
(8,'Ville de Niamey','NIA-VIL');

-- 8.5 Communes principales
INSERT IGNORE INTO communes (departement_id, nom, latitude, longitude) VALUES
(1, 'Agadez', 16.9742, 7.9924),
(2, 'Arlit', 18.7369, 7.3853),
(3, 'Bilma', 18.6875, 12.9164),
(6, 'Diffa', 13.3155, 12.6138),
(11, 'Dosso', 13.0449, 3.1972),
(14, 'Gaya', 11.8833, 3.4500),
(19, 'Maradi', 13.5000, 7.1000),
(24, 'Tahoua', 14.8889, 5.2664),
(26, 'Birni N''Konni', 13.7917, 5.2528),
(32, 'Tillabéri', 14.2081, 1.4536),
(33, 'Ayorou', 14.7333, 0.9167),
(35, 'Gothèye', 13.8833, 1.5833),
(36, 'Kollo', 13.3167, 2.3167),
(37, 'Say', 13.1042, 2.3694),
(38, 'Téra', 14.0167, 0.7500),
(44, 'Zinder', 13.8069, 8.9881),
(46, 'Magaria', 12.9833, 8.9167),
(47, 'Mirriah', 13.7167, 9.1500),
(49, 'Niamey', 13.5137, 2.1098),
(49, 'Niamey II', 13.5200, 2.1050),
(49, 'Niamey III', 13.5080, 2.1180),
(49, 'Niamey IV', 13.5000, 2.1300),
(49, 'Niamey V', 13.5300, 2.0900);

-- 8.6 Utilisateurs initiaux (mot de passe: Admin@2026)
INSERT IGNORE INTO users (role_id, nom, prenom, email, password, matricule) VALUES
(1, 'SYSTÈME',  'Admin',     'admin@tgi-niamey.ne',       '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'SYS-001'),
(2, 'MAÏGA',    'Ousmane',   'president@tgi-niamey.ne',   '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'PRES-001'),
(3, 'HASSANE',  'Aminatou',  'vice.president@tgi-niamey.ne','$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym','VP-001'),
(4, 'MOUSSA',   'Ibrahim',   'procureur@tgi-niamey.ne',   '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'PROC-001'),
(5, 'ADAMOU',   'Fatouma',   'substitut1@tgi-niamey.ne',  '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'SUB-001'),
(5, 'CHAIBOU',  'Moustapha', 'substitut2@tgi-niamey.ne',  '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'SUB-002'),
(5, 'MAHAMADOU','Salissou',  'substitut3@tgi-niamey.ne',  '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'SUB-003'),
(6, 'SAIDOU',   'Aïssatou',  'juge.instr1@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'JI-001'),
(6, 'HAMIDOU',  'Mariama',   'juge.instr2@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'JI-002'),
(7, 'YACOUBA',  'Hassane',   'juge.siege@tgi-niamey.ne',  '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'JS-001'),
(8, 'ISSA',     'Rahila',    'greffier@tgi-niamey.ne',    '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'GRF-001'),
(9, 'MAHAMANE', 'Alio',      'avocat@barreau-niamey.ne',  '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'AVO-001');

-- 8.7 Cabinets d'instruction officiels
DELETE FROM cabinets_instruction;
INSERT INTO cabinets_instruction (numero, libelle, actif) VALUES
('CAB-01', 'Doyen des Juges d''Instruction',           1),
('CAB-02', 'Cabinet Droit Commun Mineur N°1',          1),
('CAB-03', 'Cabinet Droit Commun Mineur N°2',          1),
('CAB-04', 'Cabinet Droit Commun Majeur N°1',          1),
('CAB-05', 'Cabinet Droit Commun Majeur N°2',          1),
('CAB-06', 'Cabinet Pôle Économique et Financier N°1', 1),
('CAB-07', 'Cabinet Pôle Économique et Financier N°2', 1),
('CAB-08', 'Cabinet Pôle Antiterroriste',              1);

-- 8.8 Salles d'audience
INSERT IGNORE INTO salles_audience (nom, capacite, description) VALUES
('Grande Salle d''Assises',       150, 'Salle principale pour les affaires criminelles'),
('Salle Correctionnelle N°1',      80, 'Affaires correctionnelles'),
('Salle Correctionnelle N°2',      80, 'Affaires correctionnelles'),
('Salle Civile',                   60, 'Affaires civiles et commerciales'),
('Chambre du Conseil',             20, 'Audiences à huis clos — instruction');

-- 8.9 Primo intervenants
INSERT IGNORE INTO primo_intervenants (nom, type, description) VALUES
('Unité Spéciale de la Police',                  'Police',        'DGPN — Unité Spéciale'),
('Forces Armées Nigériennes',                    'Armée',         'Forces Armées du Niger'),
('Opération Damissa',                            'Inter-forces',  'Opération sécuritaire inter-forces'),
('Garde Nationale du Niger',                     'Gendarmerie',   'Garde Nationale — missions sécuritaires'),
('Gendarmerie Nationale',                        'Gendarmerie',   'Gendarmerie Nationale du Niger'),
('Direction de la Surveillance du Territoire',   'Renseignement', 'DST — services de renseignement'),
('Police Judiciaire',                            'Police',        'Brigade de Police Judiciaire');

-- 8.10 Unités d'enquête
INSERT IGNORE INTO unites_enquete (nom, type, telephone) VALUES
('Commissariat Central de Niamey',        'commissariat',   '+227 20 73 20 00'),
('Commissariat du 1er Arrondissement',    'commissariat',   '+227 20 73 21 00'),
('Commissariat du 2ème Arrondissement',   'commissariat',   '+227 20 73 22 00'),
('Brigade de Gendarmerie de Niamey',      'gendarmerie',    '+227 20 73 30 00'),
('Brigade Territoriale de Say',           'gendarmerie',    '+227 20 73 31 00'),
('Brigade de Kollo',                      'gendarmerie',    '+227 20 73 32 00'),
('Police Judiciaire Niamey',              'brigade_police', '+227 20 73 25 00'),
('Unité Spéciale Anti-Terrorisme',        'unite_speciale', '+227 20 73 40 00');

-- 8.11 Maisons d'arrêt
INSERT INTO maisons_arret (id, nom, ville, capacite, population_actuelle, population_hommes, population_femmes, directeur, telephone, adresse) VALUES
(1, 'Maison d''Arrêt de Niamey',    'Niamey',  600, 450, 420, 30, 'Commandant Seydou MAIGA',    '+227 20 73 40 00', 'Quartier Plateau, Niamey'),
(2, 'Maison d''Arrêt de Zinder',    'Zinder',  300, 210, 195, 15, 'Commandant Moutari HASSANE', '+227 20 51 04 10', 'Vieux Zinder, Zinder'),
(3, 'Maison d''Arrêt de Maradi',    'Maradi',  250, 180, 165, 15, 'Commandant Abdou LAWALI',    '+227 20 41 03 55', 'Quartier Dan Goulbi, Maradi'),
(4, 'Maison d''Arrêt de Tahoua',    'Tahoua',  200, 130, 120, 10, 'Commandant Harouna ISSA',    '+227 20 61 02 80', 'Quartier Administratif, Tahoua'),
(5, 'Maison d''Arrêt d''Agadez',    'Agadez',  100,  60,  55,  5, 'Commandant Souleymane ALI',  '+227 20 44 05 00', 'Centre-ville, Agadez'),
(6, 'Maison d''Arrêt de Dosso',     'Dosso',   150,  90,  82,  8, 'Commandant Ibrahim GARBA',   '+227 20 65 01 12', 'Centre-ville, Dosso'),
(7, 'Maison d''Arrêt de Diffa',     'Diffa',    80,  50,  46,  4, 'Commandant Amadou BELLO',    '+227 20 55 06 10', 'Centre-ville, Diffa'),
(8, 'Centre de Détention de Kollo', 'Kollo',   100,  70,  65,  5, 'Lieutenant Adamou SOULEY',   '+227 20 47 00 21', 'Route de Dosso, Kollo')
ON DUPLICATE KEY UPDATE
    population_actuelle=VALUES(population_actuelle),
    population_hommes=VALUES(population_hommes),
    population_femmes=VALUES(population_femmes),
    directeur=VALUES(directeur),
    telephone=VALUES(telephone),
    adresse=VALUES(adresse);

-- 8.12 Infractions
INSERT IGNORE INTO infractions (code, libelle, categorie, peine_min_mois, peine_max_mois) VALUES
('INF-001', 'Meurtre avec préméditation',                        'criminelle',        120, 999),
('INF-002', 'Viol',                                              'criminelle',         60, 120),
('INF-003', 'Vol à main armée',                                  'criminelle',         60, 120),
('INF-004', 'Terrorisme et association de malfaiteurs',          'criminelle',        120, 999),
('INF-005', 'Trafic de stupéfiants',                            'criminelle',         60, 120),
('INF-006', 'Enlèvement et séquestration',                      'criminelle',         60, 120),
('INF-007', 'Escroquerie et abus de confiance',                 'correctionnelle',    12,  60),
('INF-008', 'Détournement de deniers publics',                  'correctionnelle',    24,  60),
('INF-009', 'Corruption active et passive',                     'correctionnelle',    24,  60),
('INF-010', 'Coups et blessures volontaires',                   'correctionnelle',     6,  36),
('INF-011', 'Vol simple',                                       'correctionnelle',     3,  24),
('INF-012', 'Faux et usage de faux',                            'correctionnelle',    12,  36),
('INF-013', 'Trafic illicite de migrants',                      'correctionnelle',    24,  60),
('INF-014', 'Ivresse publique et manifeste',                    'contraventionnelle',  0,   1),
('INF-015', 'Tapage nocturne et trouble à l''ordre public',     'contraventionnelle',  0,   1);

-- ============================================================
-- 9. PARAMÈTRES DU TRIBUNAL
-- ============================================================

INSERT INTO parametres_tribunal (cle, valeur, groupe, libelle, type_champ) VALUES
('tribunal_nom_court',          'TGI-NY',                                                       'identite',    'Nom court (sigle)',                   'text'),
('tribunal_nom_complet',        'Tribunal de Grande Instance Hors Classe de Niamey',             'identite',    'Nom complet du tribunal',             'text'),
('tribunal_ville',              'Niamey',                                                        'identite',    'Ville',                                'text'),
('tribunal_pays',               'République du Niger',                                           'identite',    'Pays',                                 'text'),
('tribunal_adresse',            'Avenue de la Mairie — B.P. 466 — Niamey, République du Niger', 'identite',    'Adresse postale',                      'text'),
('tribunal_telephone',          '+227 20 73 20 00',                                              'identite',    'Téléphone',                            'tel'),
('tribunal_email',              'contact@tgi-niamey.ne',                                         'identite',    'Email institutionnel',                 'email'),
('tribunal_website',            '',                                                              'identite',    'Site web',                             'url'),
('tribunal_devise',             'Fraternité — Travail — Progrès',                               'identite',    'Devise nationale',                     'text'),
('doc_entete_ligne1',           'REPUBLIQUE DU NIGER',                                           'documents',   'En-tête ligne 1',                     'text'),
('doc_entete_ligne2',           'MINISTÈRE DE LA JUSTICE',                                       'documents',   'En-tête ligne 2',                     'text'),
('doc_entete_ligne3',           'Tribunal de Grande Instance Hors Classe de Niamey',             'documents',   'En-tête ligne 3',                     'text'),
('doc_pied_page',               'Document officiel — TGI-NY — Niamey — République du Niger',    'documents',   'Pied de page par défaut',              'text'),
('doc_qr_code_actif',           '1',                                                             'documents',   'Activer le QR code sur les mandats',  'boolean'),
('doc_qr_code_base_url',        '',                                                              'documents',   'URL de base pour les QR codes',        'url'),
('delai_pv_jours',              '30',                                                            'delais',      'Délai traitement PV (jours)',          'number'),
('delai_instruction_mois',      '6',                                                             'delais',      'Délai instruction (mois)',             'number'),
('delai_alerte_audience_jours', '3',                                                             'delais',      'Alerte avant audience (jours)',        'number'),
('delai_appel_jours',           '30',                                                            'delais',      'Délai appel (jours)',                  'number'),
('delai_detention_prov_mois',   '6',                                                             'delais',      'Délai max détention provisoire (mois)','number'),
('num_prefix_rg',               'RG N°',                                                         'numerotation','Préfixe numéro RG',                   'text'),
('num_prefix_rp',               'RP N°',                                                         'numerotation','Préfixe numéro RP',                   'text'),
('num_prefix_ri',               'RI N°',                                                         'numerotation','Préfixe numéro RI',                   'text'),
('num_suffix_rg',               'TGI-NY',                                                        'numerotation','Suffixe numéro RG',                   'text'),
('num_suffix_rp',               'PARQUET',                                                       'numerotation','Suffixe numéro RP',                   'text'),
('num_suffix_ri',               'INSTR',                                                         'numerotation','Suffixe numéro RI',                   'text'),
('theme_couleur_primaire',      '#0a2342',                                                        'affichage',   'Couleur primaire',                    'color'),
('items_par_page',              '20',                                                             'affichage',   'Éléments par page',                   'number')
ON DUPLICATE KEY UPDATE valeur=VALUES(valeur);

-- ============================================================
-- 10. MENUS ET FONCTIONNALITÉS
-- ============================================================

INSERT IGNORE INTO menus (code, libelle, icone, url, ordre) VALUES
('dashboard', 'Tableau de bord',     'bi-speedometer2',  '/dashboard',  1),
('pv',        'Procès-Verbaux',       'bi-file-text',     '/pv',         2),
('dossiers',  'Dossiers',             'bi-folder2-open',  '/dossiers',   3),
('audiences', 'Audiences',            'bi-calendar-week', '/audiences',  4),
('jugements', 'Jugements',            'bi-hammer',        '/jugements',  5),
('detenus',   'Population Carcérale', 'bi-person-lock',   '/detenus',    6),
('mandats',   'Mandats de Justice',   'bi-file-ruled',    '/mandats',    7),
('carte',     'Carte Antiterroriste', 'bi-map',           '/carte',      8),
('alertes',   'Alertes',              'bi-bell',          '/alertes',    9),
('users',     'Utilisateurs',         'bi-people',        '/users',     10),
('config',    'Configuration',        'bi-gear-fill',     '/config',    11);

INSERT IGNORE INTO fonctionnalites (code, libelle, menu_id) VALUES
('pv_creer',           'Créer un PV',              (SELECT id FROM menus WHERE code='pv')),
('pv_modifier',        'Modifier un PV',            (SELECT id FROM menus WHERE code='pv')),
('pv_affecter',        'Affecter un substitut',     (SELECT id FROM menus WHERE code='pv')),
('pv_classer',         'Classer sans suite',        (SELECT id FROM menus WHERE code='pv')),
('pv_declasser',       'Déclasser un PV',           (SELECT id FROM menus WHERE code='pv')),
('pv_transferer',      'Transférer un PV',          (SELECT id FROM menus WHERE code='pv')),
('dossier_creer',      'Créer un dossier',          (SELECT id FROM menus WHERE code='dossiers')),
('dossier_modifier',   'Modifier un dossier',       (SELECT id FROM menus WHERE code='dossiers')),
('dossier_classer',    'Classer sans suite',        (SELECT id FROM menus WHERE code='dossiers')),
('dossier_declasser',  'Déclasser un dossier',      (SELECT id FROM menus WHERE code='dossiers')),
('dossier_instruction','Envoyer en instruction',    (SELECT id FROM menus WHERE code='dossiers')),
('dossier_pieces',     'Gérer les pièces jointes',  (SELECT id FROM menus WHERE code='dossiers')),
('audience_creer',     'Planifier une audience',    (SELECT id FROM menus WHERE code='audiences')),
('jugement_creer',     'Saisir un jugement',        (SELECT id FROM menus WHERE code='jugements')),
('jugement_appel',     'Enregistrer un appel',      (SELECT id FROM menus WHERE code='jugements')),
('mandat_creer',       'Créer un mandat',           (SELECT id FROM menus WHERE code='mandats')),
('mandat_statut',      'Mettre à jour statut',      (SELECT id FROM menus WHERE code='mandats')),
('detenu_creer',       'Enregistrer un détenu',     (SELECT id FROM menus WHERE code='detenus')),
('detenu_liberer',     'Libérer un détenu',         (SELECT id FROM menus WHERE code='detenus')),
('config_cabinets',    'Gérer les cabinets',        (SELECT id FROM menus WHERE code='config')),
('config_substituts',  'Gérer les substituts',      (SELECT id FROM menus WHERE code='config')),
('config_parametres',  'Paramètres du tribunal',    (SELECT id FROM menus WHERE code='config'));

-- 8.13 Communes géographiques (266 communes — carte choroplèthe)
INSERT IGNORE INTO communes_geo (nom, departement_nom, region_nom, longitude, latitude, code_commune) VALUES
('BANKILARE','Bankilaré','Tillabéri',2.949,14.067,'NER006005001'),
('GOROUOL','Téra','Tillabéri',2.9682,14.53,'NER006011002'),
('KOKOROU','Téra','Tillabéri',3.3182,14.53,'NER006011003'),
('TERA','Téra','Tillabéri',2.9682,14.88,'NER006011005'),
('DESSA','Tillabéri','Tillabéri',2.3358,13.429,'NER006012003'),
('MEHANA','Téra','Tillabéri',2.6182,14.88,'NER006011004'),
('SINDER','Tillabéri','Tillabéri',2.3358,13.779,'NER006012006'),
('SAKOIRA','Tillabéri','Tillabéri',1.9858,13.779,'NER006012005'),
('BIBIYERGOU','Tillabéri','Tillabéri',1.9858,13.429,'NER006012002'),
('TILLABERI','Tillabéri','Tillabéri',1.6358,14.129,'NER006012007'),
('DIAGOUROU','Téra','Tillabéri',2.6182,14.53,'NER006011001'),
('GOTHEYE','Gothèye','Tillabéri',2.9816,13.145,'NER006007002'),
('DARGOL','Gothèye','Tillabéri',2.6316,13.145,'NER006007001'),
('KOURTEYE','Tillabéri','Tillabéri',1.6358,13.779,'NER006012004'),
('KARMA','Kollo','Tillabéri',2.5236,14.567,'NER006008004'),
('NAMARO','Kollo','Tillabéri',1.8236,15.267,'NER006008010'),
('TORODI','Torodi','Tillabéri',3.1928,14.905,'NER006013002'),
('MAKALONDI','Torodi','Tillabéri',2.8428,14.905,'NER006013001'),
('OURO GUELADJO','Say','Tillabéri',2.7628,14.076,'NER006010001'),
('TAMOU','Say','Tillabéri',2.7628,14.426,'NER006010003'),
('KIRTACHI','Kollo','Tillabéri',1.4736,14.917,'NER006008005'),
('FALMEY','Falmey','Dosso',2.9812,12.8154,'NER003005001'),
('GAYA','Gaya','Dosso',3.0902,13.0844,'NER003006003'),
('BENGOU','Gaya','Dosso',2.7402,13.0844,'NER003006002'),
('TOUNOUGA','Gaya','Dosso',2.7402,13.4344,'NER003006005'),
('BANA','Gaya','Dosso',2.3902,13.0844,'NER003006001'),
('TANDA','Gaya','Dosso',2.3902,13.4344,'NER003006004'),
('YELOU','Gaya','Dosso',3.0902,13.4344,'NER003006006'),
('SAMBERA','Dosso','Dosso',1.8568,13.7294,'NER003004008'),
('GUILLADJE','Falmey','Dosso',3.3312,12.8154,'NER003005002'),
('GOLLE','Dosso','Dosso',2.9068,13.0294,'NER003004004'),
('FAREY','Dosso','Dosso',2.2068,13.0294,'NER003004002'),
('TESSA','Dosso','Dosso',2.2068,13.7294,'NER003004009'),
('DIOUNDIOU','Dioundiou','Dosso',3.4292,12.7628,'NER003002001'),
('ZABORI','Dioundiou','Dosso',3.4292,13.1128,'NER003002003'),
('KARAKARA','Dioundiou','Dosso',3.7792,12.7628,'NER003002002'),
('GUECHEME','Tibiri','Dosso',2.5136,12.2348,'NER003008002'),
('KARGUIBANGOU','Dosso','Dosso',2.2068,13.3794,'NER003004006'),
('DOUMEGA','Tibiri','Dosso',2.1636,12.2348,'NER003008001'),
('DOSSO','Dosso','Dosso',1.8568,13.0294,'NER003004001'),
('GOROUBANKASSAM','Dosso','Dosso',1.8568,13.3794,'NER003004005'),
('BIRNI NGAOURE','Boboye','Dosso',3.3966,13.0486,'NER003001001'),
('KANKANDI','Boboye','Dosso',3.7466,13.3986,'NER003001005'),
('FABIDJI','Boboye','Dosso',3.7466,13.0486,'NER003001002'),
('FAKARA','Boboye','Dosso',4.0966,13.0486,'NER003001003'),
('BITINKODJI','Kollo','Tillabéri',1.4736,14.567,'NER006008001'),
('YOURI','Kollo','Tillabéri',2.1736,15.267,'NER006008011'),
('SAY','Say','Tillabéri',3.1128,14.076,'NER006010002'),
('KOLLO','Kollo','Tillabéri',1.8236,14.917,'NER006008006'),
('KOURE','Kollo','Tillabéri',2.1736,14.917,'NER006008007'),
('NDOUNGA','Kollo','Tillabéri',1.4736,15.267,'NER006008009'),
('LIBORE','Kollo','Tillabéri',2.5236,14.917,'NER006008008'),
('NIAMEY 4','Ville de Niamey','Niamey',1.858,13.6022,'NER008001004'),
('NIAMEY 5','Ville de Niamey','Niamey',0,0,'NER008001005'),
('NIAMEY 1','Ville de Niamey','Niamey',0,0,'NER008001001'),
('NIAMEY 2','Ville de Niamey','Niamey',0,0,'NER008001002'),
('NIAMEY 3','Ville de Niamey','Niamey',0,0,'NER008001003'),
('HAMDALLAYE','Kollo','Tillabéri',2.1736,14.567,'NER006008003'),
('DIANTCHANDOU','Kollo','Tillabéri',1.8236,14.567,'NER006008002'),
('KOYGOLO','Boboye','Dosso',3.3966,13.7486,'NER003001007'),
('TAGAZAR','Balleyara','Tillabéri',2.2306,13.635,'NER006003001'),
('LOGA','Loga','Dosso',2.4176,12.95,'NER003007002'),
('FALWEL','Loga','Dosso',2.0676,12.95,'NER003007001'),
('MOKKO','Dosso','Dosso',2.5568,13.3794,'NER003004007'),
('SOKORBE','Loga','Dosso',2.0676,13.3,'NER003007003'),
('GARANKEDEY','Dosso','Dosso',2.5568,13.0294,'NER003004003'),
('KIOTA','Boboye','Dosso',4.0966,13.3986,'NER003001006'),
('HARIKANASSOU','Boboye','Dosso',3.3966,13.3986,'NER003001004'),
('NGONGA','Boboye','Dosso',3.7466,13.7486,'NER003001008'),
('SIMIRI','Ouallam','Tillabéri',1.8828,14.082,'NER006009003'),
('OUALLAM','Ouallam','Tillabéri',2.2328,13.732,'NER006009002'),
('DINGAZI','Ouallam','Tillabéri',1.8828,13.732,'NER006009001'),
('FILINGUE','Filingué','Tillabéri',2.6152,14.702,'NER006006001'),
('IMANAN','Filingué','Tillabéri',2.2652,15.052,'NER006006002'),
('TONDIKANDIA','Filingué','Tillabéri',0,0,'NER006006004'),
('KOURFEYE CENTRE','Filingué','Tillabéri',2.6152,15.052,'NER006006003'),
('TONDIKIWINDI','Ouallam','Tillabéri',2.2328,14.082,'NER006009004'),
('ANZOUROU','Tillabéri','Tillabéri',1.6358,13.429,'NER006012001'),
('INATES','Ayorou','Tillabéri',1.7096,14.411,'NER006002002'),
('AYEROU','Ayorou','Tillabéri',1.3596,14.411,'NER006002001'),
('BANIBANGOU','Banibangou','Tillabéri',2.8242,13.713,'NER006004001'),
('ABALA','Abala','Tillabéri',1.5308,13.729,'NER006001001'),
('SANAM','Abala','Tillabéri',1.8808,13.729,'NER006001002'),
('TILLIA','Tillia','Tahoua',4.4146,15.069,'NER005012001'),
('TIBIRI DOUTCHI','Tibiri','Dosso',2.5136,12.5848,'NER003008004'),
('KORE MAIROUA','Tibiri','Dosso',2.1636,12.5848,'NER003008003'),
('TOMBOKOIREY 1','Dosso','Dosso',2.5568,13.7294,'NER003004010'),
('TOMBOKOIREY 2','Dosso','Dosso',0,0,'NER003004011'),
('MATANKARI','Dogondoutchi','Dosso',2.2442,13.192,'NER003003005'),
('DOGONDOUTCHI','Dogondoutchi','Dosso',2.5942,12.842,'NER003003002'),
('DAN KASSARI','Dogondoutchi','Dosso',1.8942,12.842,'NER003003001'),
('KIECHE','Dogondoutchi','Dosso',1.8942,13.192,'NER003003004'),
('SOUCOUCOUTANE','Dogondoutchi','Dosso',2.5942,13.192,'NER003003006'),
('DOGONKIRIA','Dogondoutchi','Dosso',2.2442,12.842,'NER003003003'),
('ALLELA','Birni N''Konni','Tahoua',5.5092,14.602,'NER005003001'),
('BIRNI N''KONNI','Birni N''Konni','Tahoua',5.5092,14.952,'NER005003003'),
('BAZAGA','Birni N''Konni','Tahoua',5.8592,14.602,'NER005003002'),
('ILLELA','Illéla','Tahoua',4.6496,15.228,'NER005005002'),
('BAGAROUA','Bagaroua','Tahoua',3.9874,14.959,'NER005002001'),
('TEBARAM','Tahoua','Tahoua',4.431,14.842,'NER005009006'),
('BAMBEYE','Tahoua','Tahoua',4.081,14.492,'NER005009002'),
('TAKANAMAT','Tahoua','Tahoua',4.081,14.842,'NER005009005'),
('AFFALA','Tahoua','Tahoua',3.731,14.492,'NER005009001'),
('TAHOUA1','Ville de Tahoua','Tahoua',5.9004,13.855,'NER005013001'),
('TAHOUA2','Ville de Tahoua','Tahoua',6.2504,13.855,'NER005013002'),
('KALFOU','Tahoua','Tahoua',3.731,14.842,'NER005009004'),
('BARMOU','Tahoua','Tahoua',4.431,14.492,'NER005009003'),
('TABALAK','Abalak','Tahoua',3.9182,15.592,'NER005001004'),
('KEITA','Keita','Tahoua',4.5636,13.958,'NER005006003'),
('AKOUBOUNOU','Abalak','Tahoua',4.2682,15.242,'NER005001002'),
('KAO','Tchintabaraden','Tahoua',8.598,20.2954,'NER005011001'),
('ABALAK','Abalak','Tahoua',3.9182,15.242,'NER005001001'),
('TAMASKE','Keita','Tahoua',4.9136,13.958,'NER005006004'),
('IBOHAMANE','Keita','Tahoua',4.9136,13.608,'NER005006002'),
('GARHANGA','Keita','Tahoua',4.5636,13.608,'NER005006001'),
('ALLAKAYE','Bouza','Tahoua',5.1854,14.241,'NER005004001'),
('DEOULE','Bouza','Tahoua',5.1854,14.591,'NER005004004'),
('BOUZA','Bouza','Tahoua',5.8854,14.241,'NER005004003'),
('TAMA','Bouza','Tahoua',5.1854,14.941,'NER005004007'),
('BADAGUICHIRI','Illola','Tahoua',4.2996,15.228,'NER005005001'),
('TAJAE','Illéla','Tahoua',4.2996,15.578,'NER005005003'),
('MALBAZA','Malbaza','Tahoua',6.128,15.563,'NER005008002'),
('DOGUERAWA','Malbaza','Tahoua',5.778,15.563,'NER005008001'),
('TSERNAOUA','Birni N''Konni','Tahoua',7.2206,14.073,'NER005003004'),
('GALMA KOUDAWATCHE','Madaoua','Tahoua',4.8078,14.922,'NER005007003'),
('SABON GUIDA','Madaoua','Tahoua',4.8078,15.272,'NER005007006'),
('BANGUI','Madaoua','Tahoua',4.4578,14.922,'NER005007002'),
('TABOTAKI','Bouza','Tahoua',5.8854,14.591,'NER005004006'),
('BABANKATAMI','Bouza','Tahoua',5.5354,14.241,'NER005004002'),
('KAROFANE','Bouza','Tahoua',5.5354,14.591,'NER005004005'),
('MADAOUA','Madaoua','Tahoua',9.1592,13.4848,'NER005007004'),
('OURNO','Madaoua','Tahoua',4.4578,15.272,'NER005007005'),
('ADJEKORIA','Dakoro','Maradi',5.8208,12.5646,'NER004003001'),
('AZARORI','Madaoua','Tahoua',4.1078,14.922,'NER005007001'),
('KORNAKA','Dakoro','Maradi',6.8708,12.9146,'NER004003008'),
('BIRNI LALLE','Dakoro','Maradi',6.8708,12.5646,'NER004003004'),
('DAN GOULBI','Dakoro','Maradi',6.1708,12.9146,'NER004003006'),
('GUIDAN ROUMDJI','Guidan Roumdji','Maradi',7.101,13.7164,'NER004005002'),
('CHADAKORI','Guidan Roumdji','Maradi',6.751,13.7164,'NER004005001'),
('GUIDAN SORI','Guidan Roumdji','Maradi',7.451,13.7164,'NER004005003'),
('TIBIRI MARADI','Guidan Roumdji','Maradi',7.101,14.0664,'NER004005005'),
('SARKIN YAMMA','Madarounfa','Maradi',7.435,13.9968,'NER004006006'),
('SAFO','Madarounfa','Maradi',7.085,13.9968,'NER004006005'),
('GABI','Madarounfa','Maradi',7.435,13.6468,'NER004006003'),
('MADAROUNFA','Madarounfa','Maradi',6.735,13.9968,'NER004006004'),
('DAN ISSA','Madarounfa','Maradi',6.735,13.6468,'NER004006001'),
('MARADI 3','Ville de Maradi','Maradi',6.2012,13.8612,'NER004007003'),
('DJIRATAWA','Madarounfa','Maradi',7.085,13.6468,'NER004006002'),
('SAE SABOUA','Guidan Roumdji','Maradi',6.751,14.0664,'NER004005004'),
('TCHADOUA','Aguié','Maradi',7.0728,13.0754,'NER004001002'),
('AGUIE','Aguié','Maradi',6.7228,13.0754,'NER004001001'),
('GANGARA AGUIE','Gazaoua','Maradi',0,0,'NER004004001'),
('GAZAOUA','Gazaoua','Maradi',6.38,13.6022,'NER004004002'),
('SABON MACHI','Dakoro','Maradi',6.5208,13.2646,'NER004003011'),
('MAIYARA','Dakoro','Maradi',5.8208,13.2646,'NER004003009'),
('SARKIN HAOUSSA','Mayahi','Maradi',7.1046,14.233,'NER004008007'),
('MAYAHI','Mayahi','Maradi',7.8046,13.883,'NER004008006'),
('KANAN BAKACHE','Mayahi','Maradi',7.4546,13.883,'NER004008005'),
('ATTANTANE','Mayahi','Maradi',7.1046,13.533,'NER004008001'),
('GUIDAN AMOUMOUNE','Mayahi','Maradi',7.8046,13.533,'NER004008003'),
('TESSAOUA','Tessaoua','Maradi',6.4006,13.7842,'NER004009007'),
('MAIJIRGUI','Tessaoua','Maradi',6.7506,13.4342,'NER004009005'),
('GARAGOUMSA','Takeita','Zinder',6.1304,15.35,'NER007008002'),
('BAOUDETTA','Tessaoua','Maradi',6.4006,13.0842,'NER004009001'),
('KOONA','Tessaoua','Maradi',7.1006,13.0842,'NER004009003'),
('KORGOM','Tessaoua','Maradi',6.4006,13.4342,'NER004009004'),
('KANTCHE','Kantché','Zinder',7.5706,14.073,'NER007005005'),
('DAOUCHE','Kantché','Zinder',7.5706,13.723,'NER007005002'),
('ICHIRNAWA','Kantché','Zinder',0,0,'NER007005004'),
('MATAMEY','Kantché','Zinder',7.2206,14.423,'NER007005007'),
('TSAOUNI','Kantché','Zinder',7.5706,14.423,'NER007005008'),
('HAWANDAWAKI','Tessaoua','Maradi',6.7506,13.0842,'NER004009002'),
('DOUNGOU','Kantché','Zinder',7.9206,13.723,'NER007005003'),
('DROUM','Mirriah','Zinder',8.6886,13.7774,'NER007007002'),
('TIRMINI','Takeita','Zinder',5.7804,15.7,'NER007008003'),
('DOGO','Mirriah','Zinder',8.3386,13.7774,'NER007007001'),
('GOUNA','Mirriah','Zinder',8.3386,14.1274,'NER007007004'),
('WACHA','Magaria','Zinder',9.5358,13.7818,'NER007006006'),
('DUNGASS','Dungass','Zinder',9.1592,13.1348,'NER007003002'),
('GOUCHI','Dungass','Zinder',8.8092,13.4848,'NER007003003'),
('MALLAWA','Dungass','Zinder',0,0,'NER007003004'),
('GUIDIMOUNI','Damagaram Takaya','Zinder',9.4422,13.0132,'NER007002003'),
('HAMDARA','Mirriah','Zinder',8.6886,14.1274,'NER007007005'),
('MIRRIAH','Mirriah','Zinder',7.9886,14.4774,'NER007007007'),
('KOLLERAM','Mirriah','Zinder',7.9886,13.7774,'NER007007006'),
('ZINDER 5','Ville de Zinder','Zinder',8.485,14.128,'NER007011005'),
('ZINDER 4','Ville de Zinder','Zinder',0,0,'NER007011004'),
('GAFFATI','Mirriah','Zinder',7.9886,14.1274,'NER007007003'),
('ZERMOU','Mirriah','Zinder',8.3386,14.4774,'NER007007008'),
('ZINDER 3','Ville de Zinder','Zinder',0,0,'NER007011003'),
('ZINDER 2','Ville de Zinder','Zinder',0,0,'NER007011002'),
('ZINDER 1','Ville de Zinder','Zinder',0,0,'NER007011001'),
('DAKOUSSA','Takeita','Zinder',5.7804,15.35,'NER007008001'),
('WAME','Damagaram Takaya','Zinder',8.7422,13.3632,'NER007002006'),
('ALBARKARAM','Damagaram Takaya','Zinder',8.7422,13.0132,'NER007002001'),
('DAMAGARAM TAKAYA','Damagaram Takaya','Zinder',9.0922,13.0132,'NER007002002'),
('MAZAMNI','Damagaram Takaya','Zinder',9.0922,13.3632,'NER007002004'),
('OLLELEWA','Tanout','Zinder',8.379,12.7348,'NER007009003'),
('GOURE','Gouré','Zinder',9.335,13.2624,'NER007004004'),
('GUIDIGUIR','Gouré','Zinder',9.685,13.2624,'NER007004005'),
('KELLE','Gouré','Zinder',10.035,13.2624,'NER007004006'),
('GAMOU','Gouré','Zinder',10.035,12.9124,'NER007004003'),
('MOA','Damagaram Takaya','Zinder',9.4422,13.3632,'NER007002005'),
('BOUNE','Gouré','Zinder',9.685,12.9124,'NER007004002'),
('GOUDOUMARIA','Goudoumaria','Diffa',13.839,12.7346,'NER002003001'),
('MAINE SOROA','Mainé-Soroa','Diffa',12.84,13.1116,'NER002004002'),
('FOULATARI','Mainé-Soroa','Diffa',12.49,13.1116,'NER002004001'),
('CHETIMARI','Diffa','Diffa',13.396,13.1308,'NER002002001'),
('NGUELBELY','Mainé-Soroa','Diffa',12.49,13.4616,'NER002004003'),
('KABLEWA','N''Guigmi','Diffa',13.106,13.8994,'NER002006001'),
('GUESKEROU','Diffa','Diffa',13.396,13.4808,'NER002002003'),
('DIFFA','Diffa','Diffa',13.746,13.1308,'NER002002002'),
('TOUMOUR','Bosso','Diffa',12.468,12.389,'NER002001002'),
('BOSSO','Bosso','Diffa',12.118,12.389,'NER002001001'),
('NGUIGMI','N''Guigmi','Diffa',13.456,13.8994,'NER002006002'),
('NGOURTI','N''Gourti','Diffa',12.371,12.9746,'NER002005001'),
('TESKER','Tesker','Zinder',9.6106,14.3306,'NER007010001'),
('AZEYE','Abalak','Tahoua',4.6182,15.242,'NER005001003'),
('DAKORO','Dakoro','Maradi',5.8208,12.9146,'NER004003005'),
('BADER GOULA','Dakoro','Maradi',6.5208,12.5646,'NER004003003'),
('ROUMBOU 1','Dakoro','Maradi',6.1708,13.2646,'NER004003010'),
('AZAGOR','Dakoro','Maradi',6.1708,12.5646,'NER004003002'),
('BERMO','Bermo','Maradi',8.55,14.1866,'NER004002001'),
('KORAHANE','Dakoro','Maradi',6.5208,12.9146,'NER004003007'),
('GADABEDJI','Bermo','Maradi',8.9,14.1866,'NER004002002'),
('GANGARA TANOUT','Tanout','Zinder',0,0,'NER007009002'),
('FALENKO','Tanout','Zinder',7.679,12.7348,'NER007009001'),
('OURAFANE','Tessaoua','Maradi',7.1006,13.4342,'NER004009006'),
('EL ALLASSANE MAIREYREY','Mayahi','Maradi',7.4546,13.533,'NER004008002'),
('ISSAWANE','Mayahi','Maradi',7.1046,13.883,'NER004008004'),
('TCHAKE','Mayahi','Maradi',7.4546,14.233,'NER004008008'),
('TANOUT','Tanout','Zinder',7.679,13.0848,'NER007009004'),
('ALAKOSS','Gouré','Zinder',9.335,12.9124,'NER007004001'),
('TAGRISS','Dakoro','Maradi',6.8708,13.2646,'NER004003012'),
('TARKA','Belbedji','Zinder',9.6394,14.401,'NER007001001'),
('TENHYA','Tanout','Zinder',8.029,13.0848,'NER007009005'),
('ADERBISSINAT','Aderbissinat','Agadez',6.973,20.177,'NER001001001'),
('TABELOT','Tchirozerine','Agadez',6.308,18.5492,'NER001006003'),
('DABAGA','Tchirozerine','Agadez',5.958,18.5492,'NER001006002'),
('TCHIROZERINE','Tchirozerine','Agadez',5.958,18.8992,'NER001006004'),
('AGADEZ','Tchirozerine','Agadez',7.881,18.6474,'NER001006001'),
('INGALL','Ingall','Agadez',9.009,18.6346,'NER001005001'),
('TASSARA','Tassara','Tahoua',9.105,20.289,'NER005010001'),
('TCHINTABARADEN','Tchintabaraden','Tahoua',8.948,20.2954,'NER005011002'),
('TAMAYA','Abalak','Tahoua',4.2682,15.592,'NER005001005'),
('DJADO','Bilma','Agadez',8.482,20.7328,'NER001003003'),
('DIRKOU','Bilma','Agadez',8.832,20.3828,'NER001003002'),
('BILMA','Bilma','Agadez',8.482,20.3828,'NER001003001'),
('DOGO DOGO','Dungass','Zinder',8.8092,13.1348,'NER007003001'),
('DANTCHIAO','Magaria','Zinder',9.1858,13.4318,'NER007006002'),
('MAGARIA','Magaria','Zinder',8.029,12.7348,'NER007006004'),
('BANDE','Magaria','Zinder',8.8358,13.4318,'NER007006001'),
('YEKOUA','Magaria','Zinder',8.8358,14.1318,'NER007006007'),
('SASSOUMBROUM','Magaria','Zinder',9.1858,13.7818,'NER007006005'),
('DAN BARTO','Kantché','Zinder',7.2206,13.723,'NER007005001'),
('KOURNI','Kantché','Zinder',7.9206,14.073,'NER007005006'),
('YAOURI','Kantché','Zinder',7.9206,14.423,'NER007005009'),
('KWAYA','Magaria','Zinder',9.5358,13.4318,'NER007006003'),
('FACHI','Bilma','Agadez',8.832,20.7328,'NER001003004'),
('TIMIA','Iférouane','Agadez',8.784,21.3482,'NER001004002'),
('IFEROUANE','Iférouane','Agadez',8.434,21.3482,'NER001004001'),
('GOUGARAM','Arlit','Agadez',6.446,20.9024,'NER001002003'),
('DANNET','Arlit','Agadez',6.796,20.5524,'NER001002002'),
('MARADI 1','Ville de Maradi','Maradi',6.2012,13.5112,'NER004007001'),
('MARADI 2','Ville de Maradi','Maradi',6.5512,13.5112,'NER004007002'),
('ARLIT','Arlit','Agadez',6.446,20.5524,'NER001002001');
-- ============================================================
-- FIN — Réactivation des contraintes
-- ============================================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TGI-NY — Base de données complète v3.1
-- Généré le : 2026-04-17
-- Migrations incluses : 001 à 010
-- ============================================================
