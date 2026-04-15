-- ============================================================
-- TGI-NY | Données initiales
-- ============================================================

SET NAMES utf8mb4;

-- Rôles
INSERT INTO roles (code, libelle) VALUES
('admin', 'Administrateur Système'),
('president', 'Président du Tribunal'),
('vice_president', 'Vice-Président'),
('procureur', 'Procureur de la République'),
('substitut_procureur', 'Substitut du Procureur'),
('juge_instruction', 'Juge d\'Instruction'),
('juge_siege', 'Juge du Siège'),
('greffier', 'Greffier'),
('avocat', 'Avocat');

-- Régions du Niger
INSERT INTO regions (nom, code) VALUES
('Agadez', 'AGZ'),
('Diffa', 'DIF'),
('Dosso', 'DOS'),
('Maradi', 'MAR'),
('Tahoua', 'TAH'),
('Tillabéri', 'TIL'),
('Zinder', 'ZND'),
('Niamey', 'NIA');

-- Départements
INSERT INTO departements (region_id, nom, code) VALUES
-- Agadez (1)
(1, 'Agadez', 'AGZ-AGZ'), (1, 'Arlit', 'AGZ-ARL'), (1, 'Bilma', 'AGZ-BIL'), (1, 'Tchirozérine', 'AGZ-TCH'),
-- Diffa (2)
(2, 'Diffa', 'DIF-DIF'), (2, 'Bosso', 'DIF-BOS'), (2, 'Goudoumaria', 'DIF-GOU'), (2, 'Mainé-Soroa', 'DIF-MAI'), (2, 'N''Guigmi', 'DIF-NGU'),
-- Dosso (3)
(3, 'Dosso', 'DOS-DOS'), (3, 'Boboye', 'DOS-BOB'), (3, 'Doutchi', 'DOS-DOU'), (3, 'Gaya', 'DOS-GAY'), (3, 'Loga', 'DOS-LOG'),
-- Maradi (4)
(4, 'Maradi', 'MAR-MAR'), (4, 'Aguié', 'MAR-AGU'), (4, 'Dakoro', 'MAR-DAK'), (4, 'Guidan Roumdji', 'MAR-GUI'), (4, 'Madarounfa', 'MAR-MAD'), (4, 'Mayahi', 'MAR-MAY'), (4, 'Tessaoua', 'MAR-TES'),
-- Tahoua (5)
(5, 'Tahoua', 'TAH-TAH'), (5, 'Abalak', 'TAH-ABA'), (5, 'Birni N''Konni', 'TAH-BIR'), (5, 'Bouza', 'TAH-BOU'), (5, 'Illela', 'TAH-ILL'), (5, 'Keita', 'TAH-KEI'), (5, 'Madaoua', 'TAH-MAD'), (5, 'Malbaza', 'TAH-MAL'), (5, 'Tchintabaraden', 'TAH-TCH'),
-- Tillabéri (6)
(6, 'Tillabéri', 'TIL-TIL'), (6, 'Ayorou', 'TIL-AYO'), (6, 'Filingué', 'TIL-FIL'), (6, 'Gothèye', 'TIL-GOT'), (6, 'Kollo', 'TIL-KOL'), (6, 'Say', 'TIL-SAY'), (6, 'Téra', 'TIL-TER'),
-- Zinder (7)
(7, 'Zinder', 'ZND-ZND'), (7, 'Dungass', 'ZND-DUN'), (7, 'Gouré', 'ZND-GOU'), (7, 'Magaria', 'ZND-MAG'), (7, 'Mirriah', 'ZND-MIR'), (7, 'Tanout', 'ZND-TAN'),
-- Niamey (8)
(8, 'Niamey I', 'NIA-1'), (8, 'Niamey II', 'NIA-2'), (8, 'Niamey III', 'NIA-3'), (8, 'Niamey IV', 'NIA-4'), (8, 'Niamey V', 'NIA-5');

-- Communes principales avec coordonnées
INSERT INTO communes (departement_id, nom, latitude, longitude) VALUES
-- Agadez département
(1, 'Agadez', 16.9742, 7.9924),
(2, 'Arlit', 18.7369, 7.3853),
(3, 'Bilma', 18.6875, 12.9164),
-- Diffa département
(5, 'Diffa', 13.3155, 12.6138),
(6, 'Bosso', 13.7000, 13.3167),
(8, 'Mainé-Soroa', 13.2167, 12.0167),
-- Dosso département
(10, 'Dosso', 13.0449, 3.1972),
(13, 'Gaya', 11.8833, 3.4500),
-- Maradi département
(15, 'Maradi', 13.5000, 7.1000),
(16, 'Aguié', 13.5167, 7.7833),
(17, 'Dakoro', 14.5167, 6.7667),
-- Tahoua département
(22, 'Tahoua', 14.8889, 5.2664),
(24, 'Birni N''Konni', 13.7917, 5.2528),
-- Tillabéri département
(31, 'Tillabéri', 14.2081, 1.4536),
(32, 'Ayorou', 14.7333, 0.9167),
(34, 'Gothèye', 13.8833, 1.5833),
(35, 'Kollo', 13.3167, 2.3167),
(36, 'Say', 13.1042, 2.3694),
(37, 'Téra', 14.0167, 0.7500),
-- Zinder département
(39, 'Zinder', 13.8069, 8.9881),
(42, 'Magaria', 12.9833, 8.9167),
(43, 'Mirriah', 13.7167, 9.1500),
-- Niamey
(44, 'Niamey', 13.5137, 2.1098),
(45, 'Niamey Commune II', 13.5200, 2.1050),
(46, 'Niamey Commune III', 13.5080, 2.1180),
(47, 'Niamey Commune IV', 13.5000, 2.1300),
(48, 'Niamey Commune V', 13.5300, 2.0900);

-- Utilisateurs (mot de passe: Admin@2026)
INSERT INTO users (role_id, nom, prenom, email, password, matricule) VALUES
(1, 'SYSTÈME', 'Admin', 'admin@tgi-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'SYS-001'),
(2, 'MAÏGA', 'Ousmane', 'president@tgi-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'PRES-001'),
(4, 'MOUSSA', 'Ibrahim', 'procureur@tgi-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'PROC-001'),
(5, 'ADAMOU', 'Fatouma', 'substitut1@tgi-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'SUB-001'),
(5, 'CHAIBOU', 'Moustapha', 'substitut2@tgi-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'SUB-002'),
(6, 'SAIDOU', 'Aïssatou', 'juge.instr1@tgi-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'JI-001'),
(6, 'HAMIDOU', 'Mariama', 'juge.instr2@tgi-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'JI-002'),
(7, 'YACOUBA', 'Hassane', 'juge.siege@tgi-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'JS-001'),
(8, 'ISSA', 'Rahila', 'greffier@tgi-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'GRF-001'),
(9, 'MAHAMANE', 'Alio', 'avocat@barreau-niamey.ne', '$2y$12$LKpXuMqVJnE3oe4vJFv4fueBUiSFAfxEhc.o6vkOHrFjfB8ZpV4ki', 'AVO-001');

-- Cabinets d'instruction
INSERT INTO cabinets_instruction (numero, libelle, juge_id) VALUES
('CAB-01', 'Cabinet d''Instruction N°1', 6),
('CAB-02', 'Cabinet d''Instruction N°2', 7),
('CAB-03', 'Cabinet d''Instruction N°3', NULL);

-- Salles d'audience
INSERT INTO salles_audience (nom, capacite, description) VALUES
('Grande Salle d''Assises', 150, 'Salle principale pour les affaires criminelles'),
('Salle Correctionnelle N°1', 80, 'Affaires correctionnelles'),
('Salle Correctionnelle N°2', 80, 'Affaires correctionnelles'),
('Salle Civile', 60, 'Affaires civiles et commerciales'),
('Chambre du Conseil', 20, 'Audiences à huis clos - instruction');

-- Primo intervenants
INSERT INTO primo_intervenants (nom, type, description) VALUES
('Unité Spéciale de la Police', 'Police', 'Direction Générale de la Police Nationale - Unité Spéciale'),
('Forces Armées Nigériennes', 'Armée', 'Forces Armées du Niger - intervention militaire'),
('Opération Damissa', 'Inter-forces', 'Opération sécuritaire inter-forces au Niger'),
('Garde Nationale du Niger', 'Gendarmerie', 'Garde Nationale - missions sécuritaires'),
('Gendarmerie Nationale', 'Gendarmerie', 'Gendarmerie Nationale du Niger'),
('Direction de la Surveillance du Territoire', 'Renseignement', 'DST - services de renseignement'),
('Police Judiciaire', 'Police', 'Brigade de Police Judiciaire');

-- Unités d'enquête
INSERT INTO unites_enquete (nom, type, telephone) VALUES
('Commissariat Central de Niamey', 'commissariat', '+227 20 73 20 00'),
('Commissariat du 1er Arrondissement', 'commissariat', '+227 20 73 21 00'),
('Commissariat du 2ème Arrondissement', 'commissariat', '+227 20 73 22 00'),
('Brigade de Gendarmerie de Niamey', 'gendarmerie', '+227 20 73 30 00'),
('Brigade Territoriale de Say', 'gendarmerie', '+227 20 73 31 00'),
('Brigade de Kollo', 'gendarmerie', '+227 20 73 32 00'),
('Police Judiciaire Niamey', 'brigade_police', '+227 20 73 25 00'),
('Unité Spéciale Anti-Terrorisme', 'unite_speciale', '+227 20 73 40 00');
