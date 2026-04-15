-- ============================================================
-- TGI-NY | Tribunal de Grande Instance Hors Classe de Niamey
-- Schéma de base de données v1.0
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Régions du Niger
CREATE TABLE IF NOT EXISTS regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    code VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Départements
CREATE TABLE IF NOT EXISTS departements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Communes
CREATE TABLE IF NOT EXISTS communes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departement_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    code VARCHAR(20),
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    FOREIGN KEY (departement_id) REFERENCES departements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rôles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    libelle VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(30),
    matricule VARCHAR(50),
    actif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cabinets d'instruction
CREATE TABLE IF NOT EXISTS cabinets_instruction (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL,
    libelle VARCHAR(100),
    juge_id INT NULL,
    actif TINYINT(1) DEFAULT 1,
    FOREIGN KEY (juge_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Unités d'enquête
CREATE TABLE IF NOT EXISTS unites_enquete (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    type ENUM('commissariat','brigade_police','gendarmerie','unite_speciale','autre') NOT NULL,
    commune_id INT NULL,
    telephone VARCHAR(30),
    actif TINYINT(1) DEFAULT 1,
    FOREIGN KEY (commune_id) REFERENCES communes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Primo intervenants
CREATE TABLE IF NOT EXISTS primo_intervenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    type VARCHAR(100),
    description TEXT,
    actif TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PV (Procès-Verbaux)
CREATE TABLE IF NOT EXISTS pv (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pv VARCHAR(100) NOT NULL,
    numero_rg VARCHAR(60) UNIQUE NOT NULL,
    unite_enquete_id INT NULL,
    date_pv DATE NOT NULL,
    date_reception DATE NOT NULL,
    type_affaire ENUM('civile','penale','commerciale') NOT NULL DEFAULT 'penale',
    est_antiterroriste TINYINT(1) DEFAULT 0,
    region_id INT NULL,
    departement_id INT NULL,
    commune_id INT NULL,
    description_faits TEXT,
    statut ENUM('recu','en_traitement','classe','transfere_instruction','transfere_jugement_direct') DEFAULT 'recu',
    substitut_id INT NULL,
    date_affectation_substitut DATE NULL,
    motif_classement TEXT NULL,
    date_classement DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (unite_enquete_id) REFERENCES unites_enquete(id) ON DELETE SET NULL,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL,
    FOREIGN KEY (departement_id) REFERENCES departements(id) ON DELETE SET NULL,
    FOREIGN KEY (commune_id) REFERENCES communes(id) ON DELETE SET NULL,
    FOREIGN KEY (substitut_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PV ↔ Primo intervenants
CREATE TABLE IF NOT EXISTS pv_primo_intervenants (
    pv_id INT NOT NULL,
    primo_intervenant_id INT NOT NULL,
    PRIMARY KEY (pv_id, primo_intervenant_id),
    FOREIGN KEY (pv_id) REFERENCES pv(id) ON DELETE CASCADE,
    FOREIGN KEY (primo_intervenant_id) REFERENCES primo_intervenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dossiers
CREATE TABLE IF NOT EXISTS dossiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pv_id INT NULL,
    numero_rg VARCHAR(60) UNIQUE NOT NULL,
    numero_rp VARCHAR(60) NULL,
    numero_ri VARCHAR(60) NULL,
    type_affaire ENUM('civile','penale','commerciale') NOT NULL,
    date_enregistrement DATE NOT NULL,
    objet TEXT NOT NULL,
    statut ENUM('enregistre','parquet','instruction','en_audience','juge','classe','appel') DEFAULT 'enregistre',
    substitut_id INT NULL,
    cabinet_id INT NULL,
    juge_siege_id INT NULL,
    date_limite_traitement DATE NULL,
    date_instruction_debut DATE NULL,
    date_instruction_fin DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (pv_id) REFERENCES pv(id) ON DELETE SET NULL,
    FOREIGN KEY (substitut_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cabinet_id) REFERENCES cabinets_instruction(id) ON DELETE SET NULL,
    FOREIGN KEY (juge_siege_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Parties au dossier
CREATE TABLE IF NOT EXISTS parties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id INT NOT NULL,
    type_partie ENUM('plaignant','defendeur','prevenu','victime','avocat','temoin','mis_en_cause') NOT NULL,
    nom VARCHAR(150) NOT NULL,
    prenom VARCHAR(150),
    date_naissance DATE NULL,
    lieu_naissance VARCHAR(150),
    nationalite VARCHAR(100) DEFAULT 'Nigérienne',
    profession VARCHAR(150),
    adresse TEXT,
    telephone VARCHAR(30),
    email VARCHAR(150),
    user_id INT NULL,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Historique mouvements
CREATE TABLE IF NOT EXISTS mouvements_dossier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id INT NOT NULL,
    user_id INT NULL,
    type_mouvement VARCHAR(100) NOT NULL,
    ancien_statut VARCHAR(60),
    nouveau_statut VARCHAR(60),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Salles d'audience
CREATE TABLE IF NOT EXISTS salles_audience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    capacite INT DEFAULT 50,
    description TEXT,
    actif TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audiences
CREATE TABLE IF NOT EXISTS audiences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id INT NOT NULL,
    salle_id INT NULL,
    date_audience DATETIME NOT NULL,
    type_audience ENUM('correctionnelle','criminelle','civile','commerciale','instruction') NOT NULL,
    president_id INT NULL,
    greffier_id INT NULL,
    statut ENUM('planifiee','tenue','renvoyee','annulee') DEFAULT 'planifiee',
    motif_renvoi TEXT NULL,
    date_renvoi DATE NULL,
    numero_audience VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (salle_id) REFERENCES salles_audience(id) ON DELETE SET NULL,
    FOREIGN KEY (president_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (greffier_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Membres audience
CREATE TABLE IF NOT EXISTS membres_audience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audience_id INT NOT NULL,
    user_id INT NULL,
    nom_externe VARCHAR(200) NULL,
    role_audience ENUM('juge_assesseur','procureur','substitut','avocat_defense','avocat_partie_civile','greffier_adjoint','autre') NOT NULL,
    FOREIGN KEY (audience_id) REFERENCES audiences(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jugements
CREATE TABLE IF NOT EXISTS jugements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id INT NOT NULL,
    audience_id INT NULL,
    numero_jugement VARCHAR(60) NOT NULL,
    date_jugement DATE NOT NULL,
    type_jugement ENUM('condamnation','acquittement','non_lieu','relaxe','renvoi','avant_droit','autre') NOT NULL,
    dispositif TEXT NOT NULL,
    peine_principale TEXT NULL,
    duree_peine_mois INT NULL,
    montant_amende DECIMAL(15,2) NULL,
    sursis TINYINT(1) DEFAULT 0,
    duree_sursis_mois INT NULL,
    appel_possible TINYINT(1) DEFAULT 1,
    date_limite_appel DATE NULL,
    appel_interjecte TINYINT(1) DEFAULT 0,
    date_appel DATE NULL,
    notes TEXT,
    greffier_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (audience_id) REFERENCES audiences(id) ON DELETE SET NULL,
    FOREIGN KEY (greffier_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Population carcérale
CREATE TABLE IF NOT EXISTS detenus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id INT NULL,
    jugement_id INT NULL,
    nom VARCHAR(150) NOT NULL,
    prenom VARCHAR(150) NOT NULL,
    date_naissance DATE NULL,
    lieu_naissance VARCHAR(150),
    nationalite VARCHAR(100) DEFAULT 'Nigérienne',
    profession VARCHAR(150),
    numero_ecrou VARCHAR(50) UNIQUE NOT NULL,
    type_detention ENUM('prevenu','inculpe','condamne','detenu_provisoire','mis_en_examen','autre') NOT NULL,
    date_incarceration DATE NOT NULL,
    date_liberation_prevue DATE NULL,
    date_liberation_effective DATE NULL,
    statut ENUM('incarcere','libere','transfere','evade','decede') DEFAULT 'incarcere',
    cellule VARCHAR(50),
    etablissement VARCHAR(150) DEFAULT 'Maison d Arret de Niamey',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE SET NULL,
    FOREIGN KEY (jugement_id) REFERENCES jugements(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Documents
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id INT NULL,
    pv_id INT NULL,
    audience_id INT NULL,
    jugement_id INT NULL,
    nom_original VARCHAR(300) NOT NULL,
    nom_stockage VARCHAR(300) NOT NULL,
    type_document ENUM('pv','acte_saisine','piece_jointe','jugement','ordonnance','pv_audience','autre') NOT NULL,
    taille_octets INT,
    mime_type VARCHAR(100),
    uploaded_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (pv_id) REFERENCES pv(id) ON DELETE CASCADE,
    FOREIGN KEY (audience_id) REFERENCES audiences(id) ON DELETE CASCADE,
    FOREIGN KEY (jugement_id) REFERENCES jugements(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Alertes système
CREATE TABLE IF NOT EXISTS alertes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dossier_id INT NULL,
    pv_id INT NULL,
    type_alerte ENUM('retard_pv','retard_instruction','retard_jugement','audience_proche','appel_expire','delai_detention','autre') NOT NULL,
    niveau ENUM('info','warning','danger') DEFAULT 'warning',
    message TEXT NOT NULL,
    destinataire_id INT NULL,
    est_lue TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
    FOREIGN KEY (pv_id) REFERENCES pv(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
