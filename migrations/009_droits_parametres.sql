-- ============================================================
-- Migration 009 — Paramètres du tribunal, droits, PV déclassement
-- ============================================================
SET NAMES utf8mb4;

-- ────────────────────────────────────────────────────────────
-- Table des paramètres système (tribunal configurable)
-- ────────────────────────────────────────────────────────────
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

-- Données initiales des paramètres
INSERT INTO parametres_tribunal (cle, valeur, groupe, libelle, type_champ) VALUES
-- Identité du tribunal
('tribunal_nom_court',        'TGI-NY',                                                        'identite', 'Nom court (sigle)',                    'text'),
('tribunal_nom_complet',      'Tribunal de Grande Instance Hors Classe de Niamey',              'identite', 'Nom complet du tribunal',              'text'),
('tribunal_ville',            'Niamey',                                                         'identite', 'Ville',                                 'text'),
('tribunal_pays',             'République du Niger',                                            'identite', 'Pays',                                  'text'),
('tribunal_adresse',          'Avenue de la Mairie — B.P. 466 — Niamey, République du Niger',  'identite', 'Adresse postale',                       'text'),
('tribunal_telephone',        '+227 20 73 20 00',                                               'identite', 'Téléphone',                             'tel'),
('tribunal_email',            'contact@tgi-niamey.ne',                                          'identite', 'Email institutionnel',                  'email'),
('tribunal_website',          '',                                                               'identite', 'Site web',                              'url'),
('tribunal_devise',           'Fraternité — Travail — Progrès',                                'identite', 'Devise nationale',                      'text'),
-- En-têtes documents
('doc_entete_ligne1',         'REPUBLIQUE DU NIGER',                                            'documents','En-tête ligne 1',                      'text'),
('doc_entete_ligne2',         'MINISTÈRE DE LA JUSTICE',                                        'documents','En-tête ligne 2',                      'text'),
('doc_entete_ligne3',         'Tribunal de Grande Instance Hors Classe de Niamey',              'documents','En-tête ligne 3',                      'text'),
('doc_pied_page',             'Document officiel — TGI-NY — Niamey — République du Niger',     'documents','Pied de page par défaut',               'text'),
('doc_qr_code_actif',         '1',                                                              'documents','Activer le QR code sur les mandats',   'boolean'),
('doc_qr_code_base_url',      '',                                                               'documents','URL de base pour les QR codes',         'url'),
-- Délais métier
('delai_pv_jours',            '30',                                                             'delais',   'Délai traitement PV (jours)',           'number'),
('delai_instruction_mois',    '6',                                                              'delais',   'Délai instruction (mois)',              'number'),
('delai_alerte_audience_jours','3',                                                             'delais',   'Alerte avant audience (jours)',         'number'),
('delai_appel_jours',         '30',                                                             'delais',   'Délai appel (jours)',                   'number'),
('delai_detention_prov_mois', '6',                                                              'delais',   'Délai max détention provisoire (mois)', 'number'),
-- Numérotation
('num_prefix_rg',             'RG N°',                                                          'numerotation','Préfixe numéro RG',                 'text'),
('num_prefix_rp',             'RP N°',                                                          'numerotation','Préfixe numéro RP',                 'text'),
('num_prefix_ri',             'RI N°',                                                          'numerotation','Préfixe numéro RI',                 'text'),
('num_suffix_rg',             'TGI-NY',                                                         'numerotation','Suffixe numéro RG',                 'text'),
('num_suffix_rp',             'PARQUET',                                                        'numerotation','Suffixe numéro RP',                 'text'),
('num_suffix_ri',             'INSTR',                                                          'numerotation','Suffixe numéro RI',                 'text'),
-- Affichage
('theme_couleur_primaire',    '#0a2342',                                                         'affichage','Couleur primaire',                     'color'),
('items_par_page',            '20',                                                              'affichage','Éléments par page',                    'number')
ON DUPLICATE KEY UPDATE valeur=VALUES(valeur);

-- ────────────────────────────────────────────────────────────
-- Gestion des droits par menu et fonctionnalité
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS menus (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(100) NOT NULL UNIQUE,
    libelle     VARCHAR(200) NOT NULL,
    icone       VARCHAR(50),
    url         VARCHAR(200),
    parent_id   INT UNSIGNED,
    ordre       INT UNSIGNED DEFAULT 0,
    actif       TINYINT(1) DEFAULT 1,
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
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    menu_id         INT UNSIGNED,
    fonctionnalite_id INT UNSIGNED,
    accorde         TINYINT(1) DEFAULT 1 COMMENT '1=accordé, 0=révoqué',
    accorde_par     INT UNSIGNED,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)              REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id)              REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (fonctionnalite_id)    REFERENCES fonctionnalites(id) ON DELETE CASCADE,
    FOREIGN KEY (accorde_par)          REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_droit_user_menu   (user_id, menu_id),
    UNIQUE KEY uk_droit_user_fonc   (user_id, fonctionnalite_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menus de l'application
INSERT IGNORE INTO menus (code, libelle, icone, url, ordre) VALUES
('dashboard',          'Tableau de bord',        'bi-speedometer2',    '/dashboard',         1),
('pv',                 'Procès-Verbaux',          'bi-file-text',       '/pv',                2),
('dossiers',           'Dossiers',                'bi-folder2-open',    '/dossiers',          3),
('audiences',          'Audiences',               'bi-calendar-week',   '/audiences',         4),
('jugements',          'Jugements',               'bi-hammer',          '/jugements',         5),
('detenus',            'Population Carcérale',    'bi-person-lock',     '/detenus',           6),
('mandats',            'Mandats de Justice',      'bi-file-ruled',      '/mandats',           7),
('carte',              'Carte Antiterroriste',    'bi-map',             '/carte',             8),
('alertes',            'Alertes',                 'bi-bell',            '/alertes',           9),
('users',              'Utilisateurs',            'bi-people',          '/users',             10),
('config',             'Configuration',           'bi-gear-fill',       '/config',            11);

-- Fonctionnalités par menu
INSERT IGNORE INTO fonctionnalites (code, libelle, menu_id) VALUES
-- PV
('pv_creer',        'Créer un PV',             (SELECT id FROM menus WHERE code='pv')),
('pv_modifier',     'Modifier un PV',          (SELECT id FROM menus WHERE code='pv')),
('pv_affecter',     'Affecter un substitut',   (SELECT id FROM menus WHERE code='pv')),
('pv_classer',      'Classer sans suite',      (SELECT id FROM menus WHERE code='pv')),
('pv_declasser',    'Déclasser un PV',         (SELECT id FROM menus WHERE code='pv')),
('pv_transferer',   'Transférer un PV',        (SELECT id FROM menus WHERE code='pv')),
-- Dossiers
('dossier_creer',        'Créer un dossier',        (SELECT id FROM menus WHERE code='dossiers')),
('dossier_modifier',     'Modifier un dossier',     (SELECT id FROM menus WHERE code='dossiers')),
('dossier_classer',      'Classer sans suite',      (SELECT id FROM menus WHERE code='dossiers')),
('dossier_declasser',    'Déclasser un dossier',    (SELECT id FROM menus WHERE code='dossiers')),
('dossier_instruction',  'Envoyer en instruction',  (SELECT id FROM menus WHERE code='dossiers')),
('dossier_pieces',       'Gérer les pièces jointes',(SELECT id FROM menus WHERE code='dossiers')),
-- Audiences
('audience_creer',       'Planifier une audience',  (SELECT id FROM menus WHERE code='audiences')),
-- Jugements
('jugement_creer',       'Saisir un jugement',      (SELECT id FROM menus WHERE code='jugements')),
('jugement_appel',       'Enregistrer un appel',    (SELECT id FROM menus WHERE code='jugements')),
-- Mandats
('mandat_creer',         'Créer un mandat',          (SELECT id FROM menus WHERE code='mandats')),
('mandat_statut',        'Mettre à jour statut',     (SELECT id FROM menus WHERE code='mandats')),
-- Détenus
('detenu_creer',         'Enregistrer un détenu',    (SELECT id FROM menus WHERE code='detenus')),
('detenu_liberer',       'Libérer un détenu',        (SELECT id FROM menus WHERE code='detenus')),
-- Config
('config_cabinets',      'Gérer les cabinets',       (SELECT id FROM menus WHERE code='config')),
('config_substituts',    'Gérer les substituts',     (SELECT id FROM menus WHERE code='config')),
('config_parametres',    'Paramètres du tribunal',   (SELECT id FROM menus WHERE code='config'));

-- ────────────────────────────────────────────────────────────
-- Colonne déclassement PV (si pas encore présente)
-- ────────────────────────────────────────────────────────────
ALTER TABLE pv 
    ADD COLUMN IF NOT EXISTS motif_declassement TEXT AFTER motif_classement,
    ADD COLUMN IF NOT EXISTS date_declassement DATE AFTER date_classement;
