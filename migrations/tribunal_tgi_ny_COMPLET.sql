-- ============================================================
-- TGI-NY | Tribunal de Grande Instance Hors Classe de Niamey
-- BASE DE DONNÉES COMPLÈTE — Fichier unique de restauration
-- Version consolidée des migrations 001 à 008
-- Prêt à être importé dans une base vierge MySQL/MariaDB
-- ============================================================
-- Utilisation :
--   mysql -u root -p tribunal_tgi_ny < tribunal_tgi_ny_COMPLET.sql
-- Ou via phpMyAdmin : Importer > Choisir ce fichier
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- 1. TABLES DE RÉFÉRENCE GÉOGRAPHIQUE
-- ============================================================

-- Régions du Niger
CREATE TABLE IF NOT EXISTS regions (
    id  INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    code VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Départements
CREATE TABLE IF NOT EXISTS departements (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    region_id INT NOT NULL,
    nom       VARCHAR(100) NOT NULL,
    code      VARCHAR(20),
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Communes
CREATE TABLE IF NOT EXISTS communes (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    departement_id INT NOT NULL,
    nom            VARCHAR(100) NOT NULL,
    code           VARCHAR(20),
    latitude       DECIMAL(10,7) NULL,
    longitude      DECIMAL(10,7) NULL,
    FOREIGN KEY (departement_id) REFERENCES departements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 2. RÔLES ET UTILISATEURS
-- ============================================================

-- Rôles
CREATE TABLE IF NOT EXISTS roles (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    code    VARCHAR(50) UNIQUE NOT NULL,
    libelle VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    role_id    INT NOT NULL,
    nom        VARCHAR(100) NOT NULL,
    prenom     VARCHAR(100) NOT NULL,
    email      VARCHAR(150) UNIQUE NOT NULL,
    password   VARCHAR(255) NOT NULL,
    telephone  VARCHAR(30),
    matricule  VARCHAR(50),
    actif      TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. TABLES DE CONFIGURATION MÉTIER
-- ============================================================

-- Cabinets d'instruction
CREATE TABLE IF NOT EXISTS cabinets_instruction (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    numero  VARCHAR(20) NOT NULL,
    libelle VARCHAR(100),
    juge_id INT NULL,
    actif   TINYINT(1) DEFAULT 1,
    FOREIGN KEY (juge_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Unités d'enquête
CREATE TABLE IF NOT EXISTS unites_enquete (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(200) NOT NULL,
    type       ENUM('commissariat','brigade_police','gendarmerie','unite_speciale','autre') NOT NULL,
    commune_id INT NULL,
    telephone  VARCHAR(30),
    actif      TINYINT(1) DEFAULT 1,
    FOREIGN KEY (commune_id) REFERENCES communes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Primo intervenants
CREATE TABLE IF NOT EXISTS primo_intervenants (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(200) NOT NULL,
    type        VARCHAR(100),
    description TEXT,
    actif       TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Salles d'audience
CREATE TABLE IF NOT EXISTS salles_audience (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL,
    capacite    INT DEFAULT 50,
    description TEXT,
    actif       TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Infractions
CREATE TABLE IF NOT EXISTS infractions (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    code           VARCHAR(20) UNIQUE NOT NULL,
    libelle        VARCHAR(255) NOT NULL,
    categorie      ENUM('criminelle','correctionnelle','contraventionnelle') NOT NULL,
    peine_min_mois INT NULL,
    peine_max_mois INT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Maisons d'arrêt
CREATE TABLE IF NOT EXISTS maisons_arret (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(200) NOT NULL,
    ville               VARCHAR(100) NULL,
    region_id           INT NULL,
    commune_id          INT NULL,
    capacite            INT DEFAULT 0,
    population_actuelle INT DEFAULT 0,
    directeur           VARCHAR(100) NULL,
    telephone           VARCHAR(20)  NULL,
    adresse             TEXT NULL,
    actif               TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id)   REFERENCES regions(id)   ON DELETE SET NULL,
    FOREIGN KEY (commune_id)  REFERENCES communes(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 4. TABLES JUDICIAIRES PRINCIPALES
-- ============================================================

-- PV (Procès-Verbaux)
CREATE TABLE IF NOT EXISTS pv (
    id                        INT AUTO_INCREMENT PRIMARY KEY,
    numero_pv                 VARCHAR(100) NOT NULL,
    numero_rg                 VARCHAR(60) UNIQUE NOT NULL,
    unite_enquete_id          INT NULL,
    date_pv                   DATE NOT NULL,
    date_reception            DATE NOT NULL,
    type_affaire              ENUM('civile','penale','commerciale') NOT NULL DEFAULT 'penale',
    est_antiterroriste        TINYINT(1) DEFAULT 0,
    region_id                 INT NULL,
    departement_id            INT NULL,
    commune_id                INT NULL,
    description_faits         TEXT,
    statut                    ENUM('recu','en_traitement','classe','transfere_instruction','transfere_jugement_direct') DEFAULT 'recu',
    substitut_id              INT NULL,
    date_affectation_substitut DATE NULL,
    motif_classement          TEXT NULL,
    date_classement           DATE NULL,
    created_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at                TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by                INT NULL,
    FOREIGN KEY (unite_enquete_id) REFERENCES unites_enquete(id)  ON DELETE SET NULL,
    FOREIGN KEY (region_id)        REFERENCES regions(id)         ON DELETE SET NULL,
    FOREIGN KEY (departement_id)   REFERENCES departements(id)    ON DELETE SET NULL,
    FOREIGN KEY (commune_id)       REFERENCES communes(id)        ON DELETE SET NULL,
    FOREIGN KEY (substitut_id)     REFERENCES users(id)           ON DELETE SET NULL,
    FOREIGN KEY (created_by)       REFERENCES users(id)           ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PV ↔ Primo intervenants
CREATE TABLE IF NOT EXISTS pv_primo_intervenants (
    pv_id                INT NOT NULL,
    primo_intervenant_id INT NOT NULL,
    PRIMARY KEY (pv_id, primo_intervenant_id),
    FOREIGN KEY (pv_id)                REFERENCES pv(id)                ON DELETE CASCADE,
    FOREIGN KEY (primo_intervenant_id) REFERENCES primo_intervenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dossiers
CREATE TABLE IF NOT EXISTS dossiers (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    pv_id                    INT NULL,
    numero_rg                VARCHAR(60) UNIQUE NOT NULL,
    numero_rp                VARCHAR(60) NULL,
    numero_ri                VARCHAR(60) NULL,
    type_affaire             ENUM('civile','penale','commerciale') NOT NULL,
    date_enregistrement      DATE NOT NULL,
    objet                    TEXT NOT NULL,
    motif_classement         TEXT NULL,
    date_classement          DATE NULL,
    motif_declassement       TEXT NULL,
    date_declassement        DATETIME NULL,
    declasse_par             INT NULL,
    statut_avant_classement  VARCHAR(60) NULL,
    statut                   ENUM('enregistre','parquet','instruction','en_audience','juge','classe','appel') DEFAULT 'enregistre',
    substitut_id             INT NULL,
    cabinet_id               INT NULL,
    juge_siege_id            INT NULL,
    date_limite_traitement   DATE NULL,
    date_instruction_debut   DATE NULL,
    date_instruction_fin     DATE NULL,
    created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by               INT NULL,
    FOREIGN KEY (pv_id)         REFERENCES pv(id)                    ON DELETE SET NULL,
    FOREIGN KEY (substitut_id)  REFERENCES users(id)                 ON DELETE SET NULL,
    FOREIGN KEY (cabinet_id)    REFERENCES cabinets_instruction(id)  ON DELETE SET NULL,
    FOREIGN KEY (juge_siege_id) REFERENCES users(id)                 ON DELETE SET NULL,
    FOREIGN KEY (declasse_par)  REFERENCES users(id)                 ON DELETE SET NULL,
    FOREIGN KEY (created_by)    REFERENCES users(id)                 ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Parties au dossier
CREATE TABLE IF NOT EXISTS parties (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id     INT NOT NULL,
    type_partie    ENUM('plaignant','defendeur','prevenu','victime','avocat','temoin','mis_en_cause') NOT NULL,
    nom            VARCHAR(150) NOT NULL,
    prenom         VARCHAR(150),
    date_naissance DATE NULL,
    lieu_naissance VARCHAR(150),
    nationalite    VARCHAR(100) DEFAULT 'Nigérienne',
    profession     VARCHAR(150),
    adresse        TEXT,
    telephone      VARCHAR(30),
    email          VARCHAR(150),
    user_id        INT NULL,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Historique mouvements
CREATE TABLE IF NOT EXISTS mouvements_dossier (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id      INT NOT NULL,
    user_id         INT NULL,
    type_mouvement  VARCHAR(100) NOT NULL,
    ancien_statut   VARCHAR(60),
    nouveau_statut  VARCHAR(60),
    description     TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audiences
CREATE TABLE IF NOT EXISTS audiences (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id     INT NOT NULL,
    salle_id       INT NULL,
    date_audience  DATETIME NOT NULL,
    type_audience  ENUM('correctionnelle','criminelle','civile','commerciale','instruction') NOT NULL,
    president_id   INT NULL,
    greffier_id    INT NULL,
    statut         ENUM('planifiee','tenue','renvoyee','annulee') DEFAULT 'planifiee',
    motif_renvoi   TEXT NULL,
    date_renvoi    DATE NULL,
    numero_audience VARCHAR(50),
    notes          TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by     INT NULL,
    FOREIGN KEY (dossier_id)  REFERENCES dossiers(id)         ON DELETE CASCADE,
    FOREIGN KEY (salle_id)    REFERENCES salles_audience(id)  ON DELETE SET NULL,
    FOREIGN KEY (president_id) REFERENCES users(id)           ON DELETE SET NULL,
    FOREIGN KEY (greffier_id)  REFERENCES users(id)           ON DELETE SET NULL,
    FOREIGN KEY (created_by)   REFERENCES users(id)           ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Membres audience
CREATE TABLE IF NOT EXISTS membres_audience (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    audience_id   INT NOT NULL,
    user_id       INT NULL,
    nom_externe   VARCHAR(200) NULL,
    role_audience ENUM(
        'president','greffier','assesseur_1','assesseur_2','jure_1','jure_2',
        'procureur','substitut','juge_assesseur','avocat_defense',
        'avocat_partie_civile','greffier_adjoint','autre'
    ) NOT NULL DEFAULT 'autre',
    FOREIGN KEY (audience_id) REFERENCES audiences(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jugements
CREATE TABLE IF NOT EXISTS jugements (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id        INT NOT NULL,
    audience_id       INT NULL,
    numero_jugement   VARCHAR(60) NOT NULL,
    date_jugement     DATE NOT NULL,
    type_jugement     ENUM('condamnation','acquittement','non_lieu','relaxe','renvoi','avant_droit','autre') NOT NULL,
    dispositif        TEXT NOT NULL,
    peine_principale  TEXT NULL,
    duree_peine_mois  INT NULL,
    montant_amende    DECIMAL(15,2) NULL,
    sursis            TINYINT(1) DEFAULT 0,
    duree_sursis_mois INT NULL,
    appel_possible    TINYINT(1) DEFAULT 1,
    date_limite_appel DATE NULL,
    appel_interjecte  TINYINT(1) DEFAULT 0,
    date_appel        DATE NULL,
    notes             TEXT,
    greffier_id       INT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by        INT NULL,
    FOREIGN KEY (dossier_id)  REFERENCES dossiers(id)  ON DELETE CASCADE,
    FOREIGN KEY (audience_id) REFERENCES audiences(id) ON DELETE SET NULL,
    FOREIGN KEY (greffier_id) REFERENCES users(id)     ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Population carcérale (détenus)
CREATE TABLE IF NOT EXISTS detenus (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id           INT NULL,
    jugement_id          INT NULL,
    nom                  VARCHAR(150) NOT NULL,
    prenom               VARCHAR(150) NOT NULL,
    surnom_alias         VARCHAR(100) NULL,
    nom_mere             VARCHAR(100) NULL,
    statut_matrimonial   ENUM('celibataire','marie','divorce','veuf') DEFAULT 'celibataire',
    nombre_enfants       INT DEFAULT 0,
    sexe                 ENUM('M','F') NOT NULL DEFAULT 'M',
    photo_identite       VARCHAR(255) NULL,
    maison_arret_id      INT NULL,
    date_naissance       DATE NULL,
    lieu_naissance       VARCHAR(150),
    nationalite          VARCHAR(100) DEFAULT 'Nigérienne',
    profession           VARCHAR(150),
    numero_ecrou         VARCHAR(50) UNIQUE NOT NULL,
    type_detention       ENUM('prevenu','inculpe','condamne','detenu_provisoire','mis_en_examen','autre') NOT NULL,
    date_incarceration   DATE NOT NULL,
    date_liberation_prevue   DATE NULL,
    date_liberation_effective DATE NULL,
    statut               ENUM('incarcere','libere','transfere','evade','decede') DEFAULT 'incarcere',
    cellule              VARCHAR(50),
    etablissement        VARCHAR(150) DEFAULT 'Maison d''Arret de Niamey',
    notes                TEXT,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)     REFERENCES dossiers(id)     ON DELETE SET NULL,
    FOREIGN KEY (jugement_id)    REFERENCES jugements(id)    ON DELETE SET NULL,
    FOREIGN KEY (maison_arret_id) REFERENCES maisons_arret(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Documents / Pièces jointes
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

-- Mandats de justice
CREATE TABLE IF NOT EXISTS mandats (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    numero              VARCHAR(80) NOT NULL UNIQUE COMMENT 'Ex: MAND N°001/2026/TGI-NY',
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
    executant_nom       VARCHAR(200) NULL COMMENT 'OPJ ou unité qui a exécuté',
    observations        TEXT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by          INT NULL,
    FOREIGN KEY (dossier_id)  REFERENCES dossiers(id)  ON DELETE SET NULL,
    FOREIGN KEY (detenu_id)   REFERENCES detenus(id)   ON DELETE SET NULL,
    FOREIGN KEY (partie_id)   REFERENCES parties(id)   ON DELETE SET NULL,
    FOREIGN KEY (emetteur_id) REFERENCES users(id)     ON DELETE RESTRICT,
    FOREIGN KEY (created_by)  REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alertes système
CREATE TABLE IF NOT EXISTS alertes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id      INT NULL,
    pv_id           INT NULL,
    type_alerte     ENUM('retard_pv','retard_instruction','retard_jugement','audience_proche','appel_expire','delai_detention','autre') NOT NULL,
    niveau          ENUM('info','warning','danger') DEFAULT 'warning',
    message         TEXT NOT NULL,
    destinataire_id INT NULL,
    est_lue         TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id)      REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (pv_id)           REFERENCES pv(id)       ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. INDEX DE PERFORMANCE
-- ============================================================

CREATE INDEX IF NOT EXISTS idx_pv_commune          ON pv(commune_id);
CREATE INDEX IF NOT EXISTS idx_pv_statut           ON pv(statut);
CREATE INDEX IF NOT EXISTS idx_pv_antiterroriste   ON pv(est_antiterroriste);
CREATE INDEX IF NOT EXISTS idx_dossiers_statut     ON dossiers(statut);
CREATE INDEX IF NOT EXISTS idx_dossiers_pv         ON dossiers(pv_id);
CREATE INDEX IF NOT EXISTS idx_audiences_dossier   ON audiences(dossier_id);
CREATE INDEX IF NOT EXISTS idx_audiences_date      ON audiences(date_audience);
CREATE INDEX IF NOT EXISTS idx_detenus_statut      ON detenus(statut);
CREATE INDEX IF NOT EXISTS idx_detenus_ecrou       ON detenus(numero_ecrou);
CREATE INDEX IF NOT EXISTS idx_mandats_dossier     ON mandats(dossier_id);
CREATE INDEX IF NOT EXISTS idx_mandats_detenu      ON mandats(detenu_id);
CREATE INDEX IF NOT EXISTS idx_mandats_emetteur    ON mandats(emetteur_id);
CREATE INDEX IF NOT EXISTS idx_mandats_statut      ON mandats(statut);
CREATE INDEX IF NOT EXISTS idx_documents_dossier   ON documents(dossier_id);
CREATE INDEX IF NOT EXISTS idx_alertes_destinataire ON alertes(destinataire_id);
CREATE INDEX IF NOT EXISTS idx_alertes_lue         ON alertes(est_lue);

-- ============================================================
-- 6. DONNÉES INITIALES (SEED DATA)
-- ============================================================

-- 6.1 Rôles
INSERT INTO roles (code, libelle) VALUES
('admin',               'Administrateur Système'),
('president',           'Président du Tribunal'),
('vice_president',      'Vice-Président'),
('procureur',           'Procureur de la République'),
('substitut_procureur', 'Substitut du Procureur'),
('juge_instruction',    'Juge d''Instruction'),
('juge_siege',          'Juge du Siège'),
('greffier',            'Greffier'),
('avocat',              'Avocat');

-- 6.2 Régions du Niger
INSERT INTO regions (nom, code) VALUES
('Agadez',    'AGZ'),
('Diffa',     'DIF'),
('Dosso',     'DOS'),
('Maradi',    'MAR'),
('Tahoua',    'TAH'),
('Tillabéri', 'TIL'),
('Zinder',    'ZND'),
('Niamey',    'NIA');

-- 6.3 Départements
INSERT INTO departements (region_id, nom, code) VALUES
-- Agadez (1)
(1, 'Agadez',       'AGZ-AGZ'), (1, 'Arlit',          'AGZ-ARL'),
(1, 'Bilma',        'AGZ-BIL'), (1, 'Tchirozérine',   'AGZ-TCH'),
-- Diffa (2)
(2, 'Diffa',        'DIF-DIF'), (2, 'Bosso',          'DIF-BOS'),
(2, 'Goudoumaria',  'DIF-GOU'), (2, 'Mainé-Soroa',    'DIF-MAI'),
(2, 'N''Guigmi',    'DIF-NGU'),
-- Dosso (3)
(3, 'Dosso',        'DOS-DOS'), (3, 'Boboye',         'DOS-BOB'),
(3, 'Doutchi',      'DOS-DOU'), (3, 'Gaya',           'DOS-GAY'),
(3, 'Loga',         'DOS-LOG'),
-- Maradi (4)
(4, 'Maradi',       'MAR-MAR'), (4, 'Aguié',          'MAR-AGU'),
(4, 'Dakoro',       'MAR-DAK'), (4, 'Guidan Roumdji', 'MAR-GUI'),
(4, 'Madarounfa',   'MAR-MAD'), (4, 'Mayahi',         'MAR-MAY'),
(4, 'Tessaoua',     'MAR-TES'),
-- Tahoua (5)
(5, 'Tahoua',       'TAH-TAH'), (5, 'Abalak',         'TAH-ABA'),
(5, 'Birni N''Konni','TAH-BIR'),(5, 'Bouza',          'TAH-BOU'),
(5, 'Illela',       'TAH-ILL'), (5, 'Keita',          'TAH-KEI'),
(5, 'Madaoua',      'TAH-MAD'), (5, 'Malbaza',        'TAH-MAL'),
(5, 'Tchintabaraden','TAH-TCH'),
-- Tillabéri (6)
(6, 'Tillabéri',    'TIL-TIL'), (6, 'Ayorou',         'TIL-AYO'),
(6, 'Filingué',     'TIL-FIL'), (6, 'Gothèye',        'TIL-GOT'),
(6, 'Kollo',        'TIL-KOL'), (6, 'Say',            'TIL-SAY'),
(6, 'Téra',         'TIL-TER'),
-- Zinder (7)
(7, 'Zinder',       'ZND-ZND'), (7, 'Dungass',        'ZND-DUN'),
(7, 'Gouré',        'ZND-GOU'), (7, 'Magaria',        'ZND-MAG'),
(7, 'Mirriah',      'ZND-MIR'), (7, 'Tanout',         'ZND-TAN'),
-- Niamey (8)
(8, 'Niamey I',   'NIA-1'), (8, 'Niamey II',  'NIA-2'),
(8, 'Niamey III', 'NIA-3'), (8, 'Niamey IV',  'NIA-4'),
(8, 'Niamey V',   'NIA-5');

-- 6.4 Communes principales
INSERT INTO communes (departement_id, nom, latitude, longitude) VALUES
(1,  'Agadez',              16.9742, 7.9924),
(2,  'Arlit',               18.7369, 7.3853),
(3,  'Bilma',               18.6875, 12.9164),
(5,  'Diffa',               13.3155, 12.6138),
(6,  'Bosso',               13.7000, 13.3167),
(8,  'Mainé-Soroa',         13.2167, 12.0167),
(10, 'Dosso',               13.0449, 3.1972),
(13, 'Gaya',                11.8833, 3.4500),
(15, 'Maradi',              13.5000, 7.1000),
(16, 'Aguié',               13.5167, 7.7833),
(17, 'Dakoro',              14.5167, 6.7667),
(22, 'Tahoua',              14.8889, 5.2664),
(24, 'Birni N''Konni',      13.7917, 5.2528),
(31, 'Tillabéri',           14.2081, 1.4536),
(32, 'Ayorou',              14.7333, 0.9167),
(34, 'Gothèye',             13.8833, 1.5833),
(35, 'Kollo',               13.3167, 2.3167),
(36, 'Say',                 13.1042, 2.3694),
(37, 'Téra',                14.0167, 0.7500),
(39, 'Zinder',              13.8069, 8.9881),
(42, 'Magaria',             12.9833, 8.9167),
(43, 'Mirriah',             13.7167, 9.1500),
(44, 'Niamey',              13.5137, 2.1098),
(45, 'Niamey Commune II',   13.5200, 2.1050),
(46, 'Niamey Commune III',  13.5080, 2.1180),
(47, 'Niamey Commune IV',   13.5000, 2.1300),
(48, 'Niamey Commune V',    13.5300, 2.0900);

-- 6.5 Utilisateurs initiaux (mot de passe: Admin@2026)
INSERT INTO users (role_id, nom, prenom, email, password, matricule) VALUES
(1, 'SYSTÈME',  'Admin',    'admin@tgi-niamey.ne',       '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'SYS-001'),
(2, 'MAÏGA',    'Ousmane',  'president@tgi-niamey.ne',   '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'PRES-001'),
(4, 'MOUSSA',   'Ibrahim',  'procureur@tgi-niamey.ne',   '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'PROC-001'),
(5, 'ADAMOU',   'Fatouma',  'substitut1@tgi-niamey.ne',  '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'SUB-001'),
(5, 'CHAIBOU',  'Moustapha','substitut2@tgi-niamey.ne',  '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'SUB-002'),
(6, 'SAIDOU',   'Aïssatou', 'juge.instr1@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'JI-001'),
(6, 'HAMIDOU',  'Mariama',  'juge.instr2@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'JI-002'),
(7, 'YACOUBA',  'Hassane',  'juge.siege@tgi-niamey.ne',  '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'JS-001'),
(8, 'ISSA',     'Rahila',   'greffier@tgi-niamey.ne',    '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'GRF-001'),
(9, 'MAHAMANE', 'Alio',     'avocat@barreau-niamey.ne',  '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', 'AVO-001');

-- 6.6 Cabinets d'instruction officiels
INSERT INTO cabinets_instruction (numero, libelle, actif) VALUES
('CAB-01', 'Doyen des Juges d''Instruction',           1),
('CAB-02', 'Cabinet Droit Commun Mineur N°1',          1),
('CAB-03', 'Cabinet Droit Commun Mineur N°2',          1),
('CAB-04', 'Cabinet Droit Commun Majeur N°1',          1),
('CAB-05', 'Cabinet Droit Commun Majeur N°2',          1),
('CAB-06', 'Cabinet Pôle Économique et Financier N°1', 1),
('CAB-07', 'Cabinet Pôle Économique et Financier N°2', 1),
('CAB-08', 'Cabinet Pôle Antiterroriste',              1);

-- 6.7 Salles d'audience
INSERT INTO salles_audience (nom, capacite, description) VALUES
('Grande Salle d''Assises',     150, 'Salle principale pour les affaires criminelles'),
('Salle Correctionnelle N°1',    80, 'Affaires correctionnelles'),
('Salle Correctionnelle N°2',    80, 'Affaires correctionnelles'),
('Salle Civile',                 60, 'Affaires civiles et commerciales'),
('Chambre du Conseil',           20, 'Audiences à huis clos - instruction');

-- 6.8 Primo intervenants
INSERT INTO primo_intervenants (nom, type, description) VALUES
('Unité Spéciale de la Police',           'Police',       'Direction Générale de la Police Nationale - Unité Spéciale'),
('Forces Armées Nigériennes',             'Armée',        'Forces Armées du Niger - intervention militaire'),
('Opération Damissa',                     'Inter-forces', 'Opération sécuritaire inter-forces au Niger'),
('Garde Nationale du Niger',              'Gendarmerie',  'Garde Nationale - missions sécuritaires'),
('Gendarmerie Nationale',                 'Gendarmerie',  'Gendarmerie Nationale du Niger'),
('Direction de la Surveillance du Territoire', 'Renseignement', 'DST - services de renseignement'),
('Police Judiciaire',                     'Police',       'Brigade de Police Judiciaire');

-- 6.9 Unités d'enquête
INSERT INTO unites_enquete (nom, type, telephone) VALUES
('Commissariat Central de Niamey',          'commissariat',  '+227 20 73 20 00'),
('Commissariat du 1er Arrondissement',      'commissariat',  '+227 20 73 21 00'),
('Commissariat du 2ème Arrondissement',     'commissariat',  '+227 20 73 22 00'),
('Brigade de Gendarmerie de Niamey',        'gendarmerie',   '+227 20 73 30 00'),
('Brigade Territoriale de Say',             'gendarmerie',   '+227 20 73 31 00'),
('Brigade de Kollo',                        'gendarmerie',   '+227 20 73 32 00'),
('Police Judiciaire Niamey',                'brigade_police','+227 20 73 25 00'),
('Unité Spéciale Anti-Terrorisme',          'unite_speciale','+227 20 73 40 00');

-- 6.10 Maisons d'arrêt
INSERT INTO maisons_arret (id, nom, ville, capacite, population_actuelle, directeur, telephone, adresse) VALUES
(1, 'Maison d''Arrêt de Niamey',  'Niamey',  600, 450, 'Commandant Seydou MAIGA',    '+227 20 73 40 00', 'Quartier Plateau, Niamey'),
(2, 'Maison d''Arrêt de Zinder',  'Zinder',  300, 210, 'Commandant Moutari HASSANE', '+227 20 51 04 10', 'Vieux Zinder, Zinder'),
(3, 'Maison d''Arrêt de Maradi',  'Maradi',  250, 180, 'Commandant Abdou LAWALI',    '+227 20 41 03 55', 'Quartier Dan Goulbi, Maradi'),
(4, 'Maison d''Arrêt de Tahoua',  'Tahoua',  200, 130, 'Commandant Harouna ISSA',    '+227 20 61 02 80', 'Quartier Administratif, Tahoua'),
(5, 'Maison d''Arrêt d''Agadez',  'Agadez',  100,  60, 'Commandant Souleymane ALI',  '+227 20 44 05 00', 'Centre-ville, Agadez'),
(6, 'Maison d''Arrêt de Dosso',   'Dosso',   150,  90, 'Commandant Ibrahim GARBA',   '+227 20 65 01 12', 'Centre-ville, Dosso'),
(7, 'Maison d''Arrêt de Diffa',   'Diffa',    80,  50, 'Commandant Amadou BELLO',    '+227 20 55 06 10', 'Centre-ville, Diffa'),
(8, 'Centre de Détention de Kollo','Kollo',  100,  70, 'Lieutenant Adamou SOULEY',   '+227 20 47 00 21', 'Route de Dosso, Kollo');

-- 6.11 Infractions
INSERT IGNORE INTO infractions (code, libelle, categorie, peine_min_mois, peine_max_mois) VALUES
('INF-001', 'Meurtre avec préméditation',             'criminelle',        120, 999),
('INF-002', 'Viol',                                   'criminelle',         60, 120),
('INF-003', 'Vol à main armée',                       'criminelle',         60, 120),
('INF-004', 'Terrorisme et association de malfaiteurs','criminelle',        120, 999),
('INF-005', 'Trafic de stupéfiants',                  'criminelle',         60, 120),
('INF-006', 'Enlèvement et séquestration',            'criminelle',         60, 120),
('INF-007', 'Escroquerie et abus de confiance',       'correctionnelle',    12,  60),
('INF-008', 'Détournement de deniers publics',        'correctionnelle',    24,  60),
('INF-009', 'Corruption active et passive',           'correctionnelle',    24,  60),
('INF-010', 'Coups et blessures volontaires',         'correctionnelle',     6,  36),
('INF-011', 'Vol simple',                             'correctionnelle',     3,  24),
('INF-012', 'Faux et usage de faux',                  'correctionnelle',    12,  36),
('INF-013', 'Trafic illicite de migrants',            'correctionnelle',    24,  60),
('INF-014', 'Ivresse publique et manifeste',          'contraventionnelle',  0,   1),
('INF-015', 'Tapage nocturne et trouble à l''ordre public', 'contraventionnelle', 0, 1);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- FIN DU FICHIER — TGI-NY Base complète
-- Créé le : 2026-04-16
-- ============================================================
