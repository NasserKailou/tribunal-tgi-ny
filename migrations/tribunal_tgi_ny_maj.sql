-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 19 avr. 2026 à 08:41
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `tribunal_tgi_ny_maj`
--

-- --------------------------------------------------------

--
-- Structure de la table `alertes`
--

CREATE TABLE `alertes` (
  `id` int(11) NOT NULL,
  `type_alerte` enum('delai_pv','delai_instruction','audience_proche','mandat_expire','retard_pv','retard_instruction','appel_expire','delai_detention','autre') NOT NULL,
  `titre` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `niveau` enum('info','warning','danger') DEFAULT 'info',
  `dossier_id` int(11) DEFAULT NULL,
  `pv_id` int(11) DEFAULT NULL,
  `est_lue` tinyint(1) DEFAULT 0,
  `destinataire_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `audiences`
--

CREATE TABLE `audiences` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `salle_id` int(11) DEFAULT NULL,
  `numero_audience` varchar(60) DEFAULT NULL,
  `date_audience` datetime NOT NULL,
  `type_audience` enum('correctionnelle','criminelle','civile','commerciale','instruction','autre') DEFAULT 'correctionnelle',
  `statut` enum('planifiee','en_cours','terminee','reportee','annulee','tenue','renvoyee') DEFAULT 'planifiee',
  `president_id` int(11) DEFAULT NULL,
  `greffier_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `motif_renvoi` text DEFAULT NULL,
  `date_renvoi` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `audiences`
--

INSERT INTO `audiences` (`id`, `dossier_id`, `salle_id`, `numero_audience`, `date_audience`, `type_audience`, `statut`, `president_id`, `greffier_id`, `notes`, `motif_renvoi`, `date_renvoi`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 'AUD N°001/2026/TGI-NY', '2026-04-18 10:00:00', 'correctionnelle', 'planifiee', 10, 11, '', NULL, NULL, 1, '2026-04-18 05:43:41', '2026-04-18 05:43:41');

-- --------------------------------------------------------

--
-- Structure de la table `avocats`
--

CREATE TABLE `avocats` (
  `id` int(11) NOT NULL,
  `matricule` varchar(30) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `barreau` varchar(100) NOT NULL DEFAULT 'Barreau de Niamey',
  `numero_ordre` varchar(50) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `date_inscription` date DEFAULT NULL,
  `statut` enum('actif','suspendu','radié','honoraire') NOT NULL DEFAULT 'actif',
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `avocat_dossier`
--

CREATE TABLE `avocat_dossier` (
  `id` int(11) NOT NULL,
  `avocat_id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `role_avocat` enum('defense','partie_civile','expert','autre') NOT NULL DEFAULT 'defense',
  `date_mandat` date DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cabinets_instruction`
--

CREATE TABLE `cabinets_instruction` (
  `id` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `libelle` varchar(100) DEFAULT NULL,
  `juge_id` int(11) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cabinets_instruction`
--

INSERT INTO `cabinets_instruction` (`id`, `numero`, `libelle`, `juge_id`, `actif`) VALUES
(1, 'CAB-01', 'Doyen des Juges d\'Instruction', NULL, 1),
(2, 'CAB-02', 'Cabinet Droit Commun Mineur N°1', NULL, 1),
(3, 'CAB-03', 'Cabinet Droit Commun Mineur N°2', NULL, 1),
(4, 'CAB-04', 'Cabinet Droit Commun Majeur N°1', NULL, 1),
(5, 'CAB-05', 'Cabinet Droit Commun Majeur N°2', NULL, 1),
(6, 'CAB-06', 'Cabinet Pôle Économique et Financier N°1', NULL, 1),
(7, 'CAB-07', 'Cabinet Pôle Économique et Financier N°2', NULL, 1),
(8, 'CAB-08', 'Cabinet Pôle Antiterroriste', NULL, 1),
(9, 'VVVV', 'VVVV', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `casier_judiciaire_condamnations`
--

CREATE TABLE `casier_judiciaire_condamnations` (
  `id` int(11) NOT NULL,
  `personne_id` int(11) NOT NULL,
  `dossier_id` int(11) DEFAULT NULL,
  `jugement_id` int(11) DEFAULT NULL,
  `date_condamnation` date NOT NULL,
  `juridiction` varchar(200) DEFAULT 'TGI-HC Niamey',
  `infraction` text NOT NULL,
  `peine` text NOT NULL,
  `date_fin_peine` date DEFAULT NULL,
  `gracie` tinyint(1) NOT NULL DEFAULT 0,
  `date_grace` date DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `casier_judiciaire_personnes`
--

CREATE TABLE `casier_judiciaire_personnes` (
  `id` int(11) NOT NULL,
  `nin` varchar(30) DEFAULT NULL COMMENT 'Numéro d''Identification National',
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(200) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT 'Nigérienne',
  `sexe` enum('M','F') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commissions_rogatoires`
--

CREATE TABLE `commissions_rogatoires` (
  `id` int(11) NOT NULL,
  `numero_cr` varchar(50) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `type_cr` enum('nationale','internationale') NOT NULL DEFAULT 'nationale',
  `autorite_destinataire` varchar(250) NOT NULL,
  `date_envoi` date NOT NULL,
  `objet` text NOT NULL,
  `date_retour` date DEFAULT NULL,
  `resultats` text DEFAULT NULL,
  `statut` enum('envoyee','executee','retour','classee') NOT NULL DEFAULT 'envoyee',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `communes`
--

CREATE TABLE `communes` (
  `id` int(11) NOT NULL,
  `departement_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `communes`
--

INSERT INTO `communes` (`id`, `departement_id`, `nom`, `code`, `latitude`, `longitude`) VALUES
(1, 1, 'Agadez', NULL, 16.9742000, 7.9924000),
(2, 2, 'Arlit', NULL, 18.7369000, 7.3853000),
(3, 3, 'Bilma', NULL, 18.6875000, 12.9164000),
(4, 6, 'Diffa', NULL, 13.3155000, 12.6138000),
(5, 11, 'Dosso', NULL, 13.0449000, 3.1972000),
(6, 14, 'Gaya', NULL, 11.8833000, 3.4500000),
(7, 19, 'Maradi', NULL, 13.5000000, 7.1000000),
(8, 24, 'Tahoua', NULL, 14.8889000, 5.2664000),
(9, 26, 'Birni N\'Konni', NULL, 13.7917000, 5.2528000),
(10, 32, 'Tillabéri', NULL, 14.2081000, 1.4536000),
(11, 33, 'Ayorou', NULL, 14.7333000, 0.9167000),
(12, 35, 'Gothèye', NULL, 13.8833000, 1.5833000),
(13, 36, 'Kollo', NULL, 13.3167000, 2.3167000),
(14, 37, 'Say', NULL, 13.1042000, 2.3694000),
(15, 38, 'Téra', NULL, 14.0167000, 0.7500000),
(16, 44, 'Zinder', NULL, 13.8069000, 8.9881000),
(17, 46, 'Magaria', NULL, 12.9833000, 8.9167000),
(18, 47, 'Mirriah', NULL, 13.7167000, 9.1500000),
(19, 49, 'Niamey', NULL, 13.5137000, 2.1098000),
(20, 49, 'Niamey II', NULL, 13.5200000, 2.1050000),
(21, 49, 'Niamey III', NULL, 13.5080000, 2.1180000),
(22, 49, 'Niamey IV', NULL, 13.5000000, 2.1300000),
(23, 49, 'Niamey V', NULL, 13.5300000, 2.0900000);

-- --------------------------------------------------------

--
-- Structure de la table `communes_geo`
--

CREATE TABLE `communes_geo` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `departement_nom` varchar(150) DEFAULT NULL,
  `region_nom` varchar(100) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `code_commune` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `communes_geo`
--

INSERT INTO `communes_geo` (`id`, `nom`, `departement_nom`, `region_nom`, `longitude`, `latitude`, `code_commune`) VALUES
(1, 'BANKILARE', 'Bankilaré', 'Tillabéri', 2.949000, 14.067000, 'NER006005001'),
(2, 'GOROUOL', 'Téra', 'Tillabéri', 2.968200, 14.530000, 'NER006011002'),
(3, 'KOKOROU', 'Téra', 'Tillabéri', 3.318200, 14.530000, 'NER006011003'),
(4, 'TERA', 'Téra', 'Tillabéri', 2.968200, 14.880000, 'NER006011005'),
(5, 'DESSA', 'Tillabéri', 'Tillabéri', 2.335800, 13.429000, 'NER006012003'),
(6, 'MEHANA', 'Téra', 'Tillabéri', 2.618200, 14.880000, 'NER006011004'),
(7, 'SINDER', 'Tillabéri', 'Tillabéri', 2.335800, 13.779000, 'NER006012006'),
(8, 'SAKOIRA', 'Tillabéri', 'Tillabéri', 1.985800, 13.779000, 'NER006012005'),
(9, 'BIBIYERGOU', 'Tillabéri', 'Tillabéri', 1.985800, 13.429000, 'NER006012002'),
(10, 'TILLABERI', 'Tillabéri', 'Tillabéri', 1.635800, 14.129000, 'NER006012007'),
(11, 'DIAGOUROU', 'Téra', 'Tillabéri', 2.618200, 14.530000, 'NER006011001'),
(12, 'GOTHEYE', 'Gothèye', 'Tillabéri', 2.981600, 13.145000, 'NER006007002'),
(13, 'DARGOL', 'Gothèye', 'Tillabéri', 2.631600, 13.145000, 'NER006007001'),
(14, 'KOURTEYE', 'Tillabéri', 'Tillabéri', 1.635800, 13.779000, 'NER006012004'),
(15, 'KARMA', 'Kollo', 'Tillabéri', 2.523600, 14.567000, 'NER006008004'),
(16, 'NAMARO', 'Kollo', 'Tillabéri', 1.823600, 15.267000, 'NER006008010'),
(17, 'TORODI', 'Torodi', 'Tillabéri', 3.192800, 14.905000, 'NER006013002'),
(18, 'MAKALONDI', 'Torodi', 'Tillabéri', 2.842800, 14.905000, 'NER006013001'),
(19, 'OURO GUELADJO', 'Say', 'Tillabéri', 2.762800, 14.076000, 'NER006010001'),
(20, 'TAMOU', 'Say', 'Tillabéri', 2.762800, 14.426000, 'NER006010003'),
(21, 'KIRTACHI', 'Kollo', 'Tillabéri', 1.473600, 14.917000, 'NER006008005'),
(22, 'FALMEY', 'Falmey', 'Dosso', 2.981200, 12.815400, 'NER003005001'),
(23, 'GAYA', 'Gaya', 'Dosso', 3.090200, 13.084400, 'NER003006003'),
(24, 'BENGOU', 'Gaya', 'Dosso', 2.740200, 13.084400, 'NER003006002'),
(25, 'TOUNOUGA', 'Gaya', 'Dosso', 2.740200, 13.434400, 'NER003006005'),
(26, 'BANA', 'Gaya', 'Dosso', 2.390200, 13.084400, 'NER003006001'),
(27, 'TANDA', 'Gaya', 'Dosso', 2.390200, 13.434400, 'NER003006004'),
(28, 'YELOU', 'Gaya', 'Dosso', 3.090200, 13.434400, 'NER003006006'),
(29, 'SAMBERA', 'Dosso', 'Dosso', 1.856800, 13.729400, 'NER003004008'),
(30, 'GUILLADJE', 'Falmey', 'Dosso', 3.331200, 12.815400, 'NER003005002'),
(31, 'GOLLE', 'Dosso', 'Dosso', 2.906800, 13.029400, 'NER003004004'),
(32, 'FAREY', 'Dosso', 'Dosso', 2.206800, 13.029400, 'NER003004002'),
(33, 'TESSA', 'Dosso', 'Dosso', 2.206800, 13.729400, 'NER003004009'),
(34, 'DIOUNDIOU', 'Dioundiou', 'Dosso', 3.429200, 12.762800, 'NER003002001'),
(35, 'ZABORI', 'Dioundiou', 'Dosso', 3.429200, 13.112800, 'NER003002003'),
(36, 'KARAKARA', 'Dioundiou', 'Dosso', 3.779200, 12.762800, 'NER003002002'),
(37, 'GUECHEME', 'Tibiri', 'Dosso', 2.513600, 12.234800, 'NER003008002'),
(38, 'KARGUIBANGOU', 'Dosso', 'Dosso', 2.206800, 13.379400, 'NER003004006'),
(39, 'DOUMEGA', 'Tibiri', 'Dosso', 2.163600, 12.234800, 'NER003008001'),
(40, 'DOSSO', 'Dosso', 'Dosso', 1.856800, 13.029400, 'NER003004001'),
(41, 'GOROUBANKASSAM', 'Dosso', 'Dosso', 1.856800, 13.379400, 'NER003004005'),
(42, 'BIRNI NGAOURE', 'Boboye', 'Dosso', 3.396600, 13.048600, 'NER003001001'),
(43, 'KANKANDI', 'Boboye', 'Dosso', 3.746600, 13.398600, 'NER003001005'),
(44, 'FABIDJI', 'Boboye', 'Dosso', 3.746600, 13.048600, 'NER003001002'),
(45, 'FAKARA', 'Boboye', 'Dosso', 4.096600, 13.048600, 'NER003001003'),
(46, 'BITINKODJI', 'Kollo', 'Tillabéri', 1.473600, 14.567000, 'NER006008001'),
(47, 'YOURI', 'Kollo', 'Tillabéri', 2.173600, 15.267000, 'NER006008011'),
(48, 'SAY', 'Say', 'Tillabéri', 3.112800, 14.076000, 'NER006010002'),
(49, 'KOLLO', 'Kollo', 'Tillabéri', 1.823600, 14.917000, 'NER006008006'),
(50, 'KOURE', 'Kollo', 'Tillabéri', 2.173600, 14.917000, 'NER006008007'),
(51, 'NDOUNGA', 'Kollo', 'Tillabéri', 1.473600, 15.267000, 'NER006008009'),
(52, 'LIBORE', 'Kollo', 'Tillabéri', 2.523600, 14.917000, 'NER006008008'),
(53, 'NIAMEY 4', 'Ville de Niamey', 'Niamey', 1.858000, 13.602200, 'NER008001004'),
(54, 'NIAMEY 5', 'Ville de Niamey', 'Niamey', 0.000000, 0.000000, 'NER008001005'),
(55, 'NIAMEY 1', 'Ville de Niamey', 'Niamey', 0.000000, 0.000000, 'NER008001001'),
(56, 'NIAMEY 2', 'Ville de Niamey', 'Niamey', 0.000000, 0.000000, 'NER008001002'),
(57, 'NIAMEY 3', 'Ville de Niamey', 'Niamey', 0.000000, 0.000000, 'NER008001003'),
(58, 'HAMDALLAYE', 'Kollo', 'Tillabéri', 2.173600, 14.567000, 'NER006008003'),
(59, 'DIANTCHANDOU', 'Kollo', 'Tillabéri', 1.823600, 14.567000, 'NER006008002'),
(60, 'KOYGOLO', 'Boboye', 'Dosso', 3.396600, 13.748600, 'NER003001007'),
(61, 'TAGAZAR', 'Balleyara', 'Tillabéri', 2.230600, 13.635000, 'NER006003001'),
(62, 'LOGA', 'Loga', 'Dosso', 2.417600, 12.950000, 'NER003007002'),
(63, 'FALWEL', 'Loga', 'Dosso', 2.067600, 12.950000, 'NER003007001'),
(64, 'MOKKO', 'Dosso', 'Dosso', 2.556800, 13.379400, 'NER003004007'),
(65, 'SOKORBE', 'Loga', 'Dosso', 2.067600, 13.300000, 'NER003007003'),
(66, 'GARANKEDEY', 'Dosso', 'Dosso', 2.556800, 13.029400, 'NER003004003'),
(67, 'KIOTA', 'Boboye', 'Dosso', 4.096600, 13.398600, 'NER003001006'),
(68, 'HARIKANASSOU', 'Boboye', 'Dosso', 3.396600, 13.398600, 'NER003001004'),
(69, 'NGONGA', 'Boboye', 'Dosso', 3.746600, 13.748600, 'NER003001008'),
(70, 'SIMIRI', 'Ouallam', 'Tillabéri', 1.882800, 14.082000, 'NER006009003'),
(71, 'OUALLAM', 'Ouallam', 'Tillabéri', 2.232800, 13.732000, 'NER006009002'),
(72, 'DINGAZI', 'Ouallam', 'Tillabéri', 1.882800, 13.732000, 'NER006009001'),
(73, 'FILINGUE', 'Filingué', 'Tillabéri', 2.615200, 14.702000, 'NER006006001'),
(74, 'IMANAN', 'Filingué', 'Tillabéri', 2.265200, 15.052000, 'NER006006002'),
(75, 'TONDIKANDIA', 'Filingué', 'Tillabéri', 0.000000, 0.000000, 'NER006006004'),
(76, 'KOURFEYE CENTRE', 'Filingué', 'Tillabéri', 2.615200, 15.052000, 'NER006006003'),
(77, 'TONDIKIWINDI', 'Ouallam', 'Tillabéri', 2.232800, 14.082000, 'NER006009004'),
(78, 'ANZOUROU', 'Tillabéri', 'Tillabéri', 1.635800, 13.429000, 'NER006012001'),
(79, 'INATES', 'Ayorou', 'Tillabéri', 1.709600, 14.411000, 'NER006002002'),
(80, 'AYEROU', 'Ayorou', 'Tillabéri', 1.359600, 14.411000, 'NER006002001'),
(81, 'BANIBANGOU', 'Banibangou', 'Tillabéri', 2.824200, 13.713000, 'NER006004001'),
(82, 'ABALA', 'Abala', 'Tillabéri', 1.530800, 13.729000, 'NER006001001'),
(83, 'SANAM', 'Abala', 'Tillabéri', 1.880800, 13.729000, 'NER006001002'),
(84, 'TILLIA', 'Tillia', 'Tahoua', 4.414600, 15.069000, 'NER005012001'),
(85, 'TIBIRI DOUTCHI', 'Tibiri', 'Dosso', 2.513600, 12.584800, 'NER003008004'),
(86, 'KORE MAIROUA', 'Tibiri', 'Dosso', 2.163600, 12.584800, 'NER003008003'),
(87, 'TOMBOKOIREY 1', 'Dosso', 'Dosso', 2.556800, 13.729400, 'NER003004010'),
(88, 'TOMBOKOIREY 2', 'Dosso', 'Dosso', 0.000000, 0.000000, 'NER003004011'),
(89, 'MATANKARI', 'Dogondoutchi', 'Dosso', 2.244200, 13.192000, 'NER003003005'),
(90, 'DOGONDOUTCHI', 'Dogondoutchi', 'Dosso', 2.594200, 12.842000, 'NER003003002'),
(91, 'DAN KASSARI', 'Dogondoutchi', 'Dosso', 1.894200, 12.842000, 'NER003003001'),
(92, 'KIECHE', 'Dogondoutchi', 'Dosso', 1.894200, 13.192000, 'NER003003004'),
(93, 'SOUCOUCOUTANE', 'Dogondoutchi', 'Dosso', 2.594200, 13.192000, 'NER003003006'),
(94, 'DOGONKIRIA', 'Dogondoutchi', 'Dosso', 2.244200, 12.842000, 'NER003003003'),
(95, 'ALLELA', 'Birni N\'Konni', 'Tahoua', 5.509200, 14.602000, 'NER005003001'),
(96, 'BIRNI N\'KONNI', 'Birni N\'Konni', 'Tahoua', 5.509200, 14.952000, 'NER005003003'),
(97, 'BAZAGA', 'Birni N\'Konni', 'Tahoua', 5.859200, 14.602000, 'NER005003002'),
(98, 'ILLELA', 'Illéla', 'Tahoua', 4.649600, 15.228000, 'NER005005002'),
(99, 'BAGAROUA', 'Bagaroua', 'Tahoua', 3.987400, 14.959000, 'NER005002001'),
(100, 'TEBARAM', 'Tahoua', 'Tahoua', 4.431000, 14.842000, 'NER005009006'),
(101, 'BAMBEYE', 'Tahoua', 'Tahoua', 4.081000, 14.492000, 'NER005009002'),
(102, 'TAKANAMAT', 'Tahoua', 'Tahoua', 4.081000, 14.842000, 'NER005009005'),
(103, 'AFFALA', 'Tahoua', 'Tahoua', 3.731000, 14.492000, 'NER005009001'),
(104, 'TAHOUA1', 'Ville de Tahoua', 'Tahoua', 5.900400, 13.855000, 'NER005013001'),
(105, 'TAHOUA2', 'Ville de Tahoua', 'Tahoua', 6.250400, 13.855000, 'NER005013002'),
(106, 'KALFOU', 'Tahoua', 'Tahoua', 3.731000, 14.842000, 'NER005009004'),
(107, 'BARMOU', 'Tahoua', 'Tahoua', 4.431000, 14.492000, 'NER005009003'),
(108, 'TABALAK', 'Abalak', 'Tahoua', 3.918200, 15.592000, 'NER005001004'),
(109, 'KEITA', 'Keita', 'Tahoua', 4.563600, 13.958000, 'NER005006003'),
(110, 'AKOUBOUNOU', 'Abalak', 'Tahoua', 4.268200, 15.242000, 'NER005001002'),
(111, 'KAO', 'Tchintabaraden', 'Tahoua', 8.598000, 20.295400, 'NER005011001'),
(112, 'ABALAK', 'Abalak', 'Tahoua', 3.918200, 15.242000, 'NER005001001'),
(113, 'TAMASKE', 'Keita', 'Tahoua', 4.913600, 13.958000, 'NER005006004'),
(114, 'IBOHAMANE', 'Keita', 'Tahoua', 4.913600, 13.608000, 'NER005006002'),
(115, 'GARHANGA', 'Keita', 'Tahoua', 4.563600, 13.608000, 'NER005006001'),
(116, 'ALLAKAYE', 'Bouza', 'Tahoua', 5.185400, 14.241000, 'NER005004001'),
(117, 'DEOULE', 'Bouza', 'Tahoua', 5.185400, 14.591000, 'NER005004004'),
(118, 'BOUZA', 'Bouza', 'Tahoua', 5.885400, 14.241000, 'NER005004003'),
(119, 'TAMA', 'Bouza', 'Tahoua', 5.185400, 14.941000, 'NER005004007'),
(120, 'BADAGUICHIRI', 'Illola', 'Tahoua', 4.299600, 15.228000, 'NER005005001'),
(121, 'TAJAE', 'Illéla', 'Tahoua', 4.299600, 15.578000, 'NER005005003'),
(122, 'MALBAZA', 'Malbaza', 'Tahoua', 6.128000, 15.563000, 'NER005008002'),
(123, 'DOGUERAWA', 'Malbaza', 'Tahoua', 5.778000, 15.563000, 'NER005008001'),
(124, 'TSERNAOUA', 'Birni N\'Konni', 'Tahoua', 7.220600, 14.073000, 'NER005003004'),
(125, 'GALMA KOUDAWATCHE', 'Madaoua', 'Tahoua', 4.807800, 14.922000, 'NER005007003'),
(126, 'SABON GUIDA', 'Madaoua', 'Tahoua', 4.807800, 15.272000, 'NER005007006'),
(127, 'BANGUI', 'Madaoua', 'Tahoua', 4.457800, 14.922000, 'NER005007002'),
(128, 'TABOTAKI', 'Bouza', 'Tahoua', 5.885400, 14.591000, 'NER005004006'),
(129, 'BABANKATAMI', 'Bouza', 'Tahoua', 5.535400, 14.241000, 'NER005004002'),
(130, 'KAROFANE', 'Bouza', 'Tahoua', 5.535400, 14.591000, 'NER005004005'),
(131, 'MADAOUA', 'Madaoua', 'Tahoua', 9.159200, 13.484800, 'NER005007004'),
(132, 'OURNO', 'Madaoua', 'Tahoua', 4.457800, 15.272000, 'NER005007005'),
(133, 'ADJEKORIA', 'Dakoro', 'Maradi', 5.820800, 12.564600, 'NER004003001'),
(134, 'AZARORI', 'Madaoua', 'Tahoua', 4.107800, 14.922000, 'NER005007001'),
(135, 'KORNAKA', 'Dakoro', 'Maradi', 6.870800, 12.914600, 'NER004003008'),
(136, 'BIRNI LALLE', 'Dakoro', 'Maradi', 6.870800, 12.564600, 'NER004003004'),
(137, 'DAN GOULBI', 'Dakoro', 'Maradi', 6.170800, 12.914600, 'NER004003006'),
(138, 'GUIDAN ROUMDJI', 'Guidan Roumdji', 'Maradi', 7.101000, 13.716400, 'NER004005002'),
(139, 'CHADAKORI', 'Guidan Roumdji', 'Maradi', 6.751000, 13.716400, 'NER004005001'),
(140, 'GUIDAN SORI', 'Guidan Roumdji', 'Maradi', 7.451000, 13.716400, 'NER004005003'),
(141, 'TIBIRI MARADI', 'Guidan Roumdji', 'Maradi', 7.101000, 14.066400, 'NER004005005'),
(142, 'SARKIN YAMMA', 'Madarounfa', 'Maradi', 7.435000, 13.996800, 'NER004006006'),
(143, 'SAFO', 'Madarounfa', 'Maradi', 7.085000, 13.996800, 'NER004006005'),
(144, 'GABI', 'Madarounfa', 'Maradi', 7.435000, 13.646800, 'NER004006003'),
(145, 'MADAROUNFA', 'Madarounfa', 'Maradi', 6.735000, 13.996800, 'NER004006004'),
(146, 'DAN ISSA', 'Madarounfa', 'Maradi', 6.735000, 13.646800, 'NER004006001'),
(147, 'MARADI 3', 'Ville de Maradi', 'Maradi', 6.201200, 13.861200, 'NER004007003'),
(148, 'DJIRATAWA', 'Madarounfa', 'Maradi', 7.085000, 13.646800, 'NER004006002'),
(149, 'SAE SABOUA', 'Guidan Roumdji', 'Maradi', 6.751000, 14.066400, 'NER004005004'),
(150, 'TCHADOUA', 'Aguié', 'Maradi', 7.072800, 13.075400, 'NER004001002'),
(151, 'AGUIE', 'Aguié', 'Maradi', 6.722800, 13.075400, 'NER004001001'),
(152, 'GANGARA AGUIE', 'Gazaoua', 'Maradi', 0.000000, 0.000000, 'NER004004001'),
(153, 'GAZAOUA', 'Gazaoua', 'Maradi', 6.380000, 13.602200, 'NER004004002'),
(154, 'SABON MACHI', 'Dakoro', 'Maradi', 6.520800, 13.264600, 'NER004003011'),
(155, 'MAIYARA', 'Dakoro', 'Maradi', 5.820800, 13.264600, 'NER004003009'),
(156, 'SARKIN HAOUSSA', 'Mayahi', 'Maradi', 7.104600, 14.233000, 'NER004008007'),
(157, 'MAYAHI', 'Mayahi', 'Maradi', 7.804600, 13.883000, 'NER004008006'),
(158, 'KANAN BAKACHE', 'Mayahi', 'Maradi', 7.454600, 13.883000, 'NER004008005'),
(159, 'ATTANTANE', 'Mayahi', 'Maradi', 7.104600, 13.533000, 'NER004008001'),
(160, 'GUIDAN AMOUMOUNE', 'Mayahi', 'Maradi', 7.804600, 13.533000, 'NER004008003'),
(161, 'TESSAOUA', 'Tessaoua', 'Maradi', 6.400600, 13.784200, 'NER004009007'),
(162, 'MAIJIRGUI', 'Tessaoua', 'Maradi', 6.750600, 13.434200, 'NER004009005'),
(163, 'GARAGOUMSA', 'Takeita', 'Zinder', 6.130400, 15.350000, 'NER007008002'),
(164, 'BAOUDETTA', 'Tessaoua', 'Maradi', 6.400600, 13.084200, 'NER004009001'),
(165, 'KOONA', 'Tessaoua', 'Maradi', 7.100600, 13.084200, 'NER004009003'),
(166, 'KORGOM', 'Tessaoua', 'Maradi', 6.400600, 13.434200, 'NER004009004'),
(167, 'KANTCHE', 'Kantché', 'Zinder', 7.570600, 14.073000, 'NER007005005'),
(168, 'DAOUCHE', 'Kantché', 'Zinder', 7.570600, 13.723000, 'NER007005002'),
(169, 'ICHIRNAWA', 'Kantché', 'Zinder', 0.000000, 0.000000, 'NER007005004'),
(170, 'MATAMEY', 'Kantché', 'Zinder', 7.220600, 14.423000, 'NER007005007'),
(171, 'TSAOUNI', 'Kantché', 'Zinder', 7.570600, 14.423000, 'NER007005008'),
(172, 'HAWANDAWAKI', 'Tessaoua', 'Maradi', 6.750600, 13.084200, 'NER004009002'),
(173, 'DOUNGOU', 'Kantché', 'Zinder', 7.920600, 13.723000, 'NER007005003'),
(174, 'DROUM', 'Mirriah', 'Zinder', 8.688600, 13.777400, 'NER007007002'),
(175, 'TIRMINI', 'Takeita', 'Zinder', 5.780400, 15.700000, 'NER007008003'),
(176, 'DOGO', 'Mirriah', 'Zinder', 8.338600, 13.777400, 'NER007007001'),
(177, 'GOUNA', 'Mirriah', 'Zinder', 8.338600, 14.127400, 'NER007007004'),
(178, 'WACHA', 'Magaria', 'Zinder', 9.535800, 13.781800, 'NER007006006'),
(179, 'DUNGASS', 'Dungass', 'Zinder', 9.159200, 13.134800, 'NER007003002'),
(180, 'GOUCHI', 'Dungass', 'Zinder', 8.809200, 13.484800, 'NER007003003'),
(181, 'MALLAWA', 'Dungass', 'Zinder', 0.000000, 0.000000, 'NER007003004'),
(182, 'GUIDIMOUNI', 'Damagaram Takaya', 'Zinder', 9.442200, 13.013200, 'NER007002003'),
(183, 'HAMDARA', 'Mirriah', 'Zinder', 8.688600, 14.127400, 'NER007007005'),
(184, 'MIRRIAH', 'Mirriah', 'Zinder', 7.988600, 14.477400, 'NER007007007'),
(185, 'KOLLERAM', 'Mirriah', 'Zinder', 7.988600, 13.777400, 'NER007007006'),
(186, 'ZINDER 5', 'Ville de Zinder', 'Zinder', 8.485000, 14.128000, 'NER007011005'),
(187, 'ZINDER 4', 'Ville de Zinder', 'Zinder', 0.000000, 0.000000, 'NER007011004'),
(188, 'GAFFATI', 'Mirriah', 'Zinder', 7.988600, 14.127400, 'NER007007003'),
(189, 'ZERMOU', 'Mirriah', 'Zinder', 8.338600, 14.477400, 'NER007007008'),
(190, 'ZINDER 3', 'Ville de Zinder', 'Zinder', 0.000000, 0.000000, 'NER007011003'),
(191, 'ZINDER 2', 'Ville de Zinder', 'Zinder', 0.000000, 0.000000, 'NER007011002'),
(192, 'ZINDER 1', 'Ville de Zinder', 'Zinder', 0.000000, 0.000000, 'NER007011001'),
(193, 'DAKOUSSA', 'Takeita', 'Zinder', 5.780400, 15.350000, 'NER007008001'),
(194, 'WAME', 'Damagaram Takaya', 'Zinder', 8.742200, 13.363200, 'NER007002006'),
(195, 'ALBARKARAM', 'Damagaram Takaya', 'Zinder', 8.742200, 13.013200, 'NER007002001'),
(196, 'DAMAGARAM TAKAYA', 'Damagaram Takaya', 'Zinder', 9.092200, 13.013200, 'NER007002002'),
(197, 'MAZAMNI', 'Damagaram Takaya', 'Zinder', 9.092200, 13.363200, 'NER007002004'),
(198, 'OLLELEWA', 'Tanout', 'Zinder', 8.379000, 12.734800, 'NER007009003'),
(199, 'GOURE', 'Gouré', 'Zinder', 9.335000, 13.262400, 'NER007004004'),
(200, 'GUIDIGUIR', 'Gouré', 'Zinder', 9.685000, 13.262400, 'NER007004005'),
(201, 'KELLE', 'Gouré', 'Zinder', 10.035000, 13.262400, 'NER007004006'),
(202, 'GAMOU', 'Gouré', 'Zinder', 10.035000, 12.912400, 'NER007004003'),
(203, 'MOA', 'Damagaram Takaya', 'Zinder', 9.442200, 13.363200, 'NER007002005'),
(204, 'BOUNE', 'Gouré', 'Zinder', 9.685000, 12.912400, 'NER007004002'),
(205, 'GOUDOUMARIA', 'Goudoumaria', 'Diffa', 13.839000, 12.734600, 'NER002003001'),
(206, 'MAINE SOROA', 'Mainé-Soroa', 'Diffa', 12.840000, 13.111600, 'NER002004002'),
(207, 'FOULATARI', 'Mainé-Soroa', 'Diffa', 12.490000, 13.111600, 'NER002004001'),
(208, 'CHETIMARI', 'Diffa', 'Diffa', 13.396000, 13.130800, 'NER002002001'),
(209, 'NGUELBELY', 'Mainé-Soroa', 'Diffa', 12.490000, 13.461600, 'NER002004003'),
(210, 'KABLEWA', 'N\'Guigmi', 'Diffa', 13.106000, 13.899400, 'NER002006001'),
(211, 'GUESKEROU', 'Diffa', 'Diffa', 13.396000, 13.480800, 'NER002002003'),
(212, 'DIFFA', 'Diffa', 'Diffa', 13.746000, 13.130800, 'NER002002002'),
(213, 'TOUMOUR', 'Bosso', 'Diffa', 12.468000, 12.389000, 'NER002001002'),
(214, 'BOSSO', 'Bosso', 'Diffa', 12.118000, 12.389000, 'NER002001001'),
(215, 'NGUIGMI', 'N\'Guigmi', 'Diffa', 13.456000, 13.899400, 'NER002006002'),
(216, 'NGOURTI', 'N\'Gourti', 'Diffa', 12.371000, 12.974600, 'NER002005001'),
(217, 'TESKER', 'Tesker', 'Zinder', 9.610600, 14.330600, 'NER007010001'),
(218, 'AZEYE', 'Abalak', 'Tahoua', 4.618200, 15.242000, 'NER005001003'),
(219, 'DAKORO', 'Dakoro', 'Maradi', 5.820800, 12.914600, 'NER004003005'),
(220, 'BADER GOULA', 'Dakoro', 'Maradi', 6.520800, 12.564600, 'NER004003003'),
(221, 'ROUMBOU 1', 'Dakoro', 'Maradi', 6.170800, 13.264600, 'NER004003010'),
(222, 'AZAGOR', 'Dakoro', 'Maradi', 6.170800, 12.564600, 'NER004003002'),
(223, 'BERMO', 'Bermo', 'Maradi', 8.550000, 14.186600, 'NER004002001'),
(224, 'KORAHANE', 'Dakoro', 'Maradi', 6.520800, 12.914600, 'NER004003007'),
(225, 'GADABEDJI', 'Bermo', 'Maradi', 8.900000, 14.186600, 'NER004002002'),
(226, 'GANGARA TANOUT', 'Tanout', 'Zinder', 0.000000, 0.000000, 'NER007009002'),
(227, 'FALENKO', 'Tanout', 'Zinder', 7.679000, 12.734800, 'NER007009001'),
(228, 'OURAFANE', 'Tessaoua', 'Maradi', 7.100600, 13.434200, 'NER004009006'),
(229, 'EL ALLASSANE MAIREYREY', 'Mayahi', 'Maradi', 7.454600, 13.533000, 'NER004008002'),
(230, 'ISSAWANE', 'Mayahi', 'Maradi', 7.104600, 13.883000, 'NER004008004'),
(231, 'TCHAKE', 'Mayahi', 'Maradi', 7.454600, 14.233000, 'NER004008008'),
(232, 'TANOUT', 'Tanout', 'Zinder', 7.679000, 13.084800, 'NER007009004'),
(233, 'ALAKOSS', 'Gouré', 'Zinder', 9.335000, 12.912400, 'NER007004001'),
(234, 'TAGRISS', 'Dakoro', 'Maradi', 6.870800, 13.264600, 'NER004003012'),
(235, 'TARKA', 'Belbedji', 'Zinder', 9.639400, 14.401000, 'NER007001001'),
(236, 'TENHYA', 'Tanout', 'Zinder', 8.029000, 13.084800, 'NER007009005'),
(237, 'ADERBISSINAT', 'Aderbissinat', 'Agadez', 6.973000, 20.177000, 'NER001001001'),
(238, 'TABELOT', 'Tchirozerine', 'Agadez', 6.308000, 18.549200, 'NER001006003'),
(239, 'DABAGA', 'Tchirozerine', 'Agadez', 5.958000, 18.549200, 'NER001006002'),
(240, 'TCHIROZERINE', 'Tchirozerine', 'Agadez', 5.958000, 18.899200, 'NER001006004'),
(241, 'AGADEZ', 'Tchirozerine', 'Agadez', 7.881000, 18.647400, 'NER001006001'),
(242, 'INGALL', 'Ingall', 'Agadez', 9.009000, 18.634600, 'NER001005001'),
(243, 'TASSARA', 'Tassara', 'Tahoua', 9.105000, 20.289000, 'NER005010001'),
(244, 'TCHINTABARADEN', 'Tchintabaraden', 'Tahoua', 8.948000, 20.295400, 'NER005011002'),
(245, 'TAMAYA', 'Abalak', 'Tahoua', 4.268200, 15.592000, 'NER005001005'),
(246, 'DJADO', 'Bilma', 'Agadez', 8.482000, 20.732800, 'NER001003003'),
(247, 'DIRKOU', 'Bilma', 'Agadez', 8.832000, 20.382800, 'NER001003002'),
(248, 'BILMA', 'Bilma', 'Agadez', 8.482000, 20.382800, 'NER001003001'),
(249, 'DOGO DOGO', 'Dungass', 'Zinder', 8.809200, 13.134800, 'NER007003001'),
(250, 'DANTCHIAO', 'Magaria', 'Zinder', 9.185800, 13.431800, 'NER007006002'),
(251, 'MAGARIA', 'Magaria', 'Zinder', 8.029000, 12.734800, 'NER007006004'),
(252, 'BANDE', 'Magaria', 'Zinder', 8.835800, 13.431800, 'NER007006001'),
(253, 'YEKOUA', 'Magaria', 'Zinder', 8.835800, 14.131800, 'NER007006007'),
(254, 'SASSOUMBROUM', 'Magaria', 'Zinder', 9.185800, 13.781800, 'NER007006005'),
(255, 'DAN BARTO', 'Kantché', 'Zinder', 7.220600, 13.723000, 'NER007005001'),
(256, 'KOURNI', 'Kantché', 'Zinder', 7.920600, 14.073000, 'NER007005006'),
(257, 'YAOURI', 'Kantché', 'Zinder', 7.920600, 14.423000, 'NER007005009'),
(258, 'KWAYA', 'Magaria', 'Zinder', 9.535800, 13.431800, 'NER007006003'),
(259, 'FACHI', 'Bilma', 'Agadez', 8.832000, 20.732800, 'NER001003004'),
(260, 'TIMIA', 'Iférouane', 'Agadez', 8.784000, 21.348200, 'NER001004002'),
(261, 'IFEROUANE', 'Iférouane', 'Agadez', 8.434000, 21.348200, 'NER001004001'),
(262, 'GOUGARAM', 'Arlit', 'Agadez', 6.446000, 20.902400, 'NER001002003'),
(263, 'DANNET', 'Arlit', 'Agadez', 6.796000, 20.552400, 'NER001002002'),
(264, 'MARADI 1', 'Ville de Maradi', 'Maradi', 6.201200, 13.511200, 'NER004007001'),
(265, 'MARADI 2', 'Ville de Maradi', 'Maradi', 6.551200, 13.511200, 'NER004007002'),
(266, 'ARLIT', 'Arlit', 'Agadez', 6.446000, 20.552400, 'NER001002001');

-- --------------------------------------------------------

--
-- Structure de la table `controles_judiciaires`
--

CREATE TABLE `controles_judiciaires` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `ordonnance_id` int(11) DEFAULT NULL,
  `type_controle` enum('controle_judiciaire','liberte_provisoire','liberte_sous_caution') NOT NULL DEFAULT 'controle_judiciaire',
  `personne_nom` varchar(100) NOT NULL,
  `personne_prenom` varchar(100) DEFAULT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `obligations` text NOT NULL,
  `observations` text DEFAULT NULL,
  `statut` enum('actif','leve','viole','expire') NOT NULL DEFAULT 'actif',
  `date_levee` datetime DEFAULT NULL,
  `motif_levee` text DEFAULT NULL,
  `violations` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

CREATE TABLE `departements` (
  `id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`id`, `region_id`, `nom`, `code`) VALUES
(1, 1, 'Agadez', 'AGZ-AGZ'),
(2, 1, 'Arlit', 'AGZ-ARL'),
(3, 1, 'Bilma', 'AGZ-BIL'),
(4, 1, 'Tchirozérine', 'AGZ-TCH'),
(5, 1, 'Aderbissinat', 'AGZ-ADE'),
(6, 2, 'Diffa', 'DIF-DIF'),
(7, 2, 'Bosso', 'DIF-BOS'),
(8, 2, 'Goudoumaria', 'DIF-GOU'),
(9, 2, 'Mainé-Soroa', 'DIF-MAI'),
(10, 2, 'N\'Guigmi', 'DIF-NGU'),
(11, 3, 'Dosso', 'DOS-DOS'),
(12, 3, 'Boboye', 'DOS-BOB'),
(13, 3, 'Dogondoutchi', 'DOS-DOU'),
(14, 3, 'Gaya', 'DOS-GAY'),
(15, 3, 'Loga', 'DOS-LOG'),
(16, 3, 'Dioundiou', 'DOS-DIO'),
(17, 3, 'Falmey', 'DOS-FAL'),
(18, 3, 'Tibiri', 'DOS-TIB'),
(19, 4, 'Maradi', 'MAR-MAR'),
(20, 4, 'Aguié', 'MAR-AGU'),
(21, 4, 'Dakoro', 'MAR-DAK'),
(22, 4, 'Guidan Roumdji', 'MAR-GUI'),
(23, 4, 'Madarounfa', 'MAR-MAD'),
(24, 4, 'Mayahi', 'MAR-MAY'),
(25, 4, 'Tessaoua', 'MAR-TES'),
(26, 5, 'Tahoua', 'TAH-TAH'),
(27, 5, 'Abalak', 'TAH-ABA'),
(28, 5, 'Birni N\'Konni', 'TAH-BIR'),
(29, 5, 'Bouza', 'TAH-BOU'),
(30, 5, 'Illéla', 'TAH-ILL'),
(31, 5, 'Keita', 'TAH-KEI'),
(32, 5, 'Madaoua', 'TAH-MAD'),
(33, 5, 'Malbaza', 'TAH-MAL'),
(34, 5, 'Tchintabaraden', 'TAH-TCH'),
(35, 5, 'Bagaroua', 'TAH-BAG'),
(36, 5, 'Tillia', 'TAH-TIL'),
(37, 6, 'Tillabéri', 'TIL-TIL'),
(38, 6, 'Ayorou', 'TIL-AYO'),
(39, 6, 'Filingué', 'TIL-FIL'),
(40, 6, 'Gothèye', 'TIL-GOT'),
(41, 6, 'Kollo', 'TIL-KOL'),
(42, 6, 'Say', 'TIL-SAY'),
(43, 6, 'Téra', 'TIL-TER'),
(44, 6, 'Abala', 'TIL-ABA'),
(45, 6, 'Balleyara', 'TIL-BAL'),
(46, 6, 'Banibangou', 'TIL-BAN'),
(47, 6, 'Ouallam', 'TIL-OUA'),
(48, 6, 'Torodi', 'TIL-TOR'),
(49, 7, 'Zinder', 'ZND-ZND'),
(50, 7, 'Dungass', 'ZND-DUN'),
(51, 7, 'Gouré', 'ZND-GOU'),
(52, 7, 'Magaria', 'ZND-MAG'),
(53, 7, 'Mirriah', 'ZND-MIR'),
(54, 7, 'Tanout', 'ZND-TAN'),
(55, 7, 'Damagaram Takaya', 'ZND-DAM'),
(56, 7, 'Kantché', 'ZND-KAN'),
(57, 8, 'Ville de Niamey', 'NIA-VIL');

-- --------------------------------------------------------

--
-- Structure de la table `detenus`
--

CREATE TABLE `detenus` (
  `id` int(11) NOT NULL,
  `numero_ecrou` varchar(50) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `surnom_alias` varchar(100) DEFAULT NULL,
  `nom_mere` varchar(100) DEFAULT NULL,
  `statut_matrimonial` enum('celibataire','marie','divorce','veuf') DEFAULT 'celibataire',
  `nombre_enfants` int(11) DEFAULT 0,
  `sexe` enum('M','F') NOT NULL DEFAULT 'M',
  `photo_identite` varchar(255) DEFAULT NULL,
  `maison_arret_id` int(11) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(150) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT 'Nigérienne',
  `profession` varchar(150) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `dossier_id` int(11) DEFAULT NULL,
  `type_detention` enum('provisoire','condamne','prevenu','inculpe','detenu_provisoire','mis_en_examen','autre') DEFAULT 'provisoire',
  `date_incarceration` date DEFAULT NULL,
  `date_liberation_prevue` date DEFAULT NULL,
  `date_liberation_effective` date DEFAULT NULL,
  `cellule` varchar(50) DEFAULT NULL,
  `etablissement` varchar(200) DEFAULT NULL,
  `statut` enum('incarcere','libere','transfere','evade','decede') DEFAULT 'incarcere',
  `infractions_retenues` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `detenus`
--

INSERT INTO `detenus` (`id`, `numero_ecrou`, `nom`, `prenom`, `surnom_alias`, `nom_mere`, `statut_matrimonial`, `nombre_enfants`, `sexe`, `photo_identite`, `maison_arret_id`, `date_naissance`, `lieu_naissance`, `nationalite`, `profession`, `adresse`, `dossier_id`, `type_detention`, `date_incarceration`, `date_liberation_prevue`, `date_liberation_effective`, `cellule`, `etablissement`, `statut`, `infractions_retenues`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'ECR0001/2026', 'Ali', 'ARZIKA', 'KOUDIZE', 'MINTOU SINKA', 'marie', 5, 'M', 'uploads/photos_detenus/det_dae062d59f963628_1776491861.jpg', 1, '1990-04-18', 'SOKORBE', 'Nigérienne', 'REVENDEUR', NULL, 3, 'prevenu', '2026-04-18', '2026-12-18', NULL, '', 'Maison d&#039;Arrêt de Niamey', 'incarcere', NULL, '', NULL, '2026-04-18 05:57:41', '2026-04-18 05:57:41');

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) DEFAULT NULL,
  `pv_id` int(11) DEFAULT NULL,
  `audience_id` int(11) DEFAULT NULL,
  `jugement_id` int(11) DEFAULT NULL,
  `nom_original` varchar(300) NOT NULL,
  `nom_stockage` varchar(300) NOT NULL,
  `chemin_fichier` varchar(500) DEFAULT NULL,
  `type_document` enum('pv','acte_saisine','piece_jointe','jugement','ordonnance','pv_audience','autre') NOT NULL DEFAULT 'piece_jointe',
  `mime_type` varchar(100) DEFAULT NULL,
  `taille_octets` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id`, `dossier_id`, `pv_id`, `audience_id`, `jugement_id`, `nom_original`, `nom_stockage`, `chemin_fichier`, `type_document`, `mime_type`, `taille_octets`, `description`, `uploaded_by`, `created_at`) VALUES
(1, 1, NULL, NULL, NULL, 'MANDAT D\'ARRÊT — MAND N°001_2026_TGI-NY.pdf', '677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'uploads/documents/dossier_1/677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'piece_jointe', 'application/pdf', 234142, 'test', 1, '2026-04-17 17:01:17'),
(2, 2, NULL, NULL, NULL, 'MANDAT D\'ARRÊT — MAND N°001_2026_TGI-NY.pdf', '677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'uploads/documents/dossier_2/677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'piece_jointe', 'application/pdf', 234142, 'TEST', 1, '2026-04-17 20:54:09'),
(3, 3, NULL, NULL, NULL, 'MANDAT D\'ARRÊT — MAND N°001_2026_TGI-NY.pdf', '677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'uploads/documents/dossier_3/677c960acb6a72df_MANDAT_D_ARR__T_____MAND_N__001_2026_TGI-NY.pdf', 'piece_jointe', 'application/pdf', 234142, 'TEST', 1, '2026-04-17 21:01:43'),
(4, 3, NULL, NULL, NULL, 'whatsapp_image_2025-11-25_at_17.20_40.jpg', 'b83d439bf4ec2e05_whatsapp_image_2025-11-25_at_17.20_40.jpg', 'uploads/documents/dossier_3/b83d439bf4ec2e05_whatsapp_image_2025-11-25_at_17.20_40.jpg', 'piece_jointe', 'image/jpeg', 153485, 'gg', 1, '2026-04-17 21:25:27'),
(5, 3, NULL, NULL, NULL, 'whatsapp_image_2025-11-25_at_17.20_40.jpg', 'b83d439bf4ec2e05_whatsapp_image_2025-11-25_at_17.20_40.jpg', 'uploads/documents/dossier_3/b83d439bf4ec2e05_whatsapp_image_2025-11-25_at_17.20_40.jpg', 'piece_jointe', 'image/jpeg', 153485, NULL, 1, '2026-04-17 23:11:17'),
(6, 3, NULL, NULL, NULL, 'WhatsApp Image 2026-04-14 at 16.51.49.jpeg', '0e827f44df1fa653_WhatsApp_Image_2026-04-14_at_16.51.49.jpeg', 'uploads/documents/dossier_3/0e827f44df1fa653_WhatsApp_Image_2026-04-14_at_16.51.49.jpeg', 'piece_jointe', 'image/jpeg', 260836, NULL, 1, '2026-04-18 06:05:03');

-- --------------------------------------------------------

--
-- Structure de la table `dossiers`
--

CREATE TABLE `dossiers` (
  `id` int(11) NOT NULL,
  `numero_rg` varchar(60) NOT NULL,
  `numero_rp` varchar(60) DEFAULT NULL,
  `numero_ri` varchar(60) DEFAULT NULL,
  `pv_id` int(11) DEFAULT NULL,
  `substitut_id` int(11) DEFAULT NULL,
  `cabinet_id` int(11) DEFAULT NULL,
  `mode_poursuite` enum('aucun','CD','FD','CRCP','RI') DEFAULT 'aucun' COMMENT 'Mode de poursuite : AUCUN, Citation Directe, Flagrant délit, CRCP, Réquisitoire Introductif',
  `intitule` varchar(255) NOT NULL,
  `objet` text DEFAULT NULL,
  `motif_classement` text DEFAULT NULL,
  `date_classement` date DEFAULT NULL,
  `motif_declassement` text DEFAULT NULL,
  `date_declassement` datetime DEFAULT NULL,
  `declasse_par` int(11) DEFAULT NULL,
  `statut_avant_classement` varchar(60) DEFAULT NULL,
  `type_affaire` enum('civile','penale','commerciale') NOT NULL DEFAULT 'penale',
  `nature` enum('correctionnel','instructionnel','civil','commercial','criminel') DEFAULT 'correctionnel',
  `statut` enum('nouveau','parquet','en_instruction','instruction','en_audience','juge','classe','appel','transfere') NOT NULL DEFAULT 'nouveau',
  `date_enregistrement` date NOT NULL,
  `date_limite_traitement` date DEFAULT NULL,
  `date_instruction_debut` date DEFAULT NULL,
  `date_instruction_fin` date DEFAULT NULL,
  `est_antiterroriste` tinyint(1) DEFAULT 0,
  `region_id` int(11) DEFAULT NULL,
  `departement_id` int(11) DEFAULT NULL,
  `commune_id` int(11) DEFAULT NULL,
  `juge_siege_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dossiers`
--

INSERT INTO `dossiers` (`id`, `numero_rg`, `numero_rp`, `numero_ri`, `pv_id`, `substitut_id`, `cabinet_id`, `mode_poursuite`, `intitule`, `objet`, `motif_classement`, `date_classement`, `motif_declassement`, `date_declassement`, `declasse_par`, `statut_avant_classement`, `type_affaire`, `nature`, `statut`, `date_enregistrement`, `date_limite_traitement`, `date_instruction_debut`, `date_instruction_fin`, `est_antiterroriste`, `region_id`, `departement_id`, `commune_id`, `juge_siege_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'RG N°002/2026/TGI-NY', 'RP N°001/2026/PARQUET', 'RI N°001/2026/INSTR', 1, 5, 1, 'aucun', '', 'examen', NULL, NULL, NULL, NULL, NULL, NULL, 'penale', 'correctionnel', 'en_instruction', '2026-04-17', '2026-10-17', '2026-04-17', NULL, 0, NULL, NULL, NULL, NULL, 1, '2026-04-17 16:09:56', '2026-04-17 20:02:34'),
(2, 'RG N°004/2026/TGI-NY', 'RP N°002/2026/PARQUET', 'RI N°002/2026/INSTR', 2, 6, 2, 'RI', '', 'test', NULL, NULL, NULL, NULL, NULL, NULL, 'penale', 'correctionnel', 'en_instruction', '2026-04-17', '2026-10-17', '2026-04-17', NULL, 0, NULL, NULL, NULL, NULL, 1, '2026-04-17 20:53:43', '2026-04-17 20:53:43'),
(3, 'RG N°006/2026/TGI-NY', 'RP N°003/2026/PARQUET', 'RI N°003/2026/INSTR', 3, 7, 3, 'FD', '', 'TESTT', NULL, NULL, NULL, NULL, NULL, NULL, 'penale', 'correctionnel', 'en_audience', '2026-04-17', '2026-10-17', '2026-04-17', NULL, 0, NULL, NULL, NULL, NULL, 1, '2026-04-17 21:01:24', '2026-04-18 05:43:41');

-- --------------------------------------------------------

--
-- Structure de la table `droits_utilisateurs`
--

CREATE TABLE `droits_utilisateurs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `fonctionnalite_id` int(11) DEFAULT NULL,
  `accorde` tinyint(1) DEFAULT 1 COMMENT '1=accordé, 0=révoqué',
  `accorde_par` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `droits_utilisateurs`
--

INSERT INTO `droits_utilisateurs` (`id`, `user_id`, `menu_id`, `fonctionnalite_id`, `accorde`, `accorde_par`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, 1, '2026-04-17 17:37:52'),
(2, 1, 2, NULL, 1, 1, '2026-04-17 17:37:52'),
(3, 1, 3, NULL, 1, 1, '2026-04-17 17:37:52'),
(4, 1, 4, NULL, 1, 1, '2026-04-17 17:37:52'),
(5, 1, 5, NULL, 1, 1, '2026-04-17 17:37:52'),
(6, 1, 6, NULL, 1, 1, '2026-04-17 17:37:52'),
(7, 1, 7, NULL, 1, 1, '2026-04-17 17:37:52'),
(8, 1, 8, NULL, 1, 1, '2026-04-17 17:37:52'),
(9, 1, 9, NULL, 1, 1, '2026-04-17 17:37:52'),
(10, 1, 10, NULL, 1, 1, '2026-04-17 17:37:52'),
(11, 1, 11, NULL, 1, 1, '2026-04-17 17:37:52'),
(12, 1, NULL, 1, 1, 1, '2026-04-17 17:37:52'),
(13, 1, NULL, 2, 1, 1, '2026-04-17 17:37:52'),
(14, 1, NULL, 3, 1, 1, '2026-04-17 17:37:52'),
(15, 1, NULL, 4, 1, 1, '2026-04-17 17:37:52'),
(16, 1, NULL, 5, 1, 1, '2026-04-17 17:37:52'),
(17, 1, NULL, 6, 1, 1, '2026-04-17 17:37:52'),
(18, 1, NULL, 7, 1, 1, '2026-04-17 17:37:52'),
(19, 1, NULL, 8, 1, 1, '2026-04-17 17:37:52'),
(20, 1, NULL, 9, 1, 1, '2026-04-17 17:37:52'),
(21, 1, NULL, 10, 1, 1, '2026-04-17 17:37:52'),
(22, 1, NULL, 11, 1, 1, '2026-04-17 17:37:52'),
(23, 1, NULL, 12, 1, 1, '2026-04-17 17:37:52'),
(24, 1, NULL, 13, 1, 1, '2026-04-17 17:37:52'),
(25, 1, NULL, 14, 1, 1, '2026-04-17 17:37:52'),
(26, 1, NULL, 15, 1, 1, '2026-04-17 17:37:52'),
(27, 1, NULL, 18, 1, 1, '2026-04-17 17:37:52'),
(28, 1, NULL, 19, 1, 1, '2026-04-17 17:37:52'),
(29, 1, NULL, 16, 1, 1, '2026-04-17 17:37:52'),
(30, 1, NULL, 17, 1, 1, '2026-04-17 17:37:52'),
(31, 1, NULL, 20, 1, 1, '2026-04-17 17:37:52'),
(32, 1, NULL, 21, 1, 1, '2026-04-17 17:37:52'),
(33, 1, NULL, 22, 1, 1, '2026-04-17 17:37:52'),
(34, 11, 2, NULL, 1, 1, '2026-04-19 07:26:34'),
(35, 11, NULL, 1, 1, 1, '2026-04-19 07:26:34'),
(36, 11, NULL, 2, 1, 1, '2026-04-19 07:26:34');

-- --------------------------------------------------------

--
-- Structure de la table `expertises_judiciaires`
--

CREATE TABLE `expertises_judiciaires` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `ordonnance_id` int(11) DEFAULT NULL,
  `type_expertise` enum('medico_legale','psychiatrique','comptable','technique','balistique','graphologique','informatique','autre') NOT NULL,
  `expert_nom` varchar(150) NOT NULL,
  `expert_qualification` varchar(200) DEFAULT NULL,
  `date_mission` date NOT NULL,
  `delai_depot` date DEFAULT NULL,
  `objet_expertise` text NOT NULL,
  `date_depot_rapport` date DEFAULT NULL,
  `conclusions` text DEFAULT NULL,
  `statut` enum('ordonnee','en_cours','deposee','validee','contestee') NOT NULL DEFAULT 'ordonnee',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `expertises_judiciaires`
--

INSERT INTO `expertises_judiciaires` (`id`, `dossier_id`, `ordonnance_id`, `type_expertise`, `expert_nom`, `expert_qualification`, `date_mission`, `delai_depot`, `objet_expertise`, `date_depot_rapport`, `conclusions`, `statut`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'psychiatrique', 'uuu', '', '2026-04-18', NULL, 'hjhj', NULL, NULL, 'ordonnee', 1, '2026-04-17 23:08:02', '2026-04-17 23:08:02');

-- --------------------------------------------------------

--
-- Structure de la table `fonctionnalites`
--

CREATE TABLE `fonctionnalites` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fonctionnalites`
--

INSERT INTO `fonctionnalites` (`id`, `code`, `libelle`, `menu_id`, `description`, `actif`) VALUES
(1, 'pv_creer', 'Créer un PV', 2, NULL, 1),
(2, 'pv_modifier', 'Modifier un PV', 2, NULL, 1),
(3, 'pv_affecter', 'Affecter un substitut', 2, NULL, 1),
(4, 'pv_classer', 'Classer sans suite', 2, NULL, 1),
(5, 'pv_declasser', 'Déclasser un PV', 2, NULL, 1),
(6, 'pv_transferer', 'Transférer un PV', 2, NULL, 1),
(7, 'dossier_creer', 'Créer un dossier', 3, NULL, 1),
(8, 'dossier_modifier', 'Modifier un dossier', 3, NULL, 1),
(9, 'dossier_classer', 'Classer sans suite', 3, NULL, 1),
(10, 'dossier_declasser', 'Déclasser un dossier', 3, NULL, 1),
(11, 'dossier_instruction', 'Envoyer en instruction', 3, NULL, 1),
(12, 'dossier_pieces', 'Gérer les pièces jointes', 3, NULL, 1),
(13, 'audience_creer', 'Planifier une audience', 4, NULL, 1),
(14, 'jugement_creer', 'Saisir un jugement', 5, NULL, 1),
(15, 'jugement_appel', 'Enregistrer un appel', 5, NULL, 1),
(16, 'mandat_creer', 'Créer un mandat', 7, NULL, 1),
(17, 'mandat_statut', 'Mettre à jour statut', 7, NULL, 1),
(18, 'detenu_creer', 'Enregistrer un détenu', 6, NULL, 1),
(19, 'detenu_liberer', 'Libérer un détenu', 6, NULL, 1),
(20, 'config_cabinets', 'Gérer les cabinets', 11, NULL, 1),
(21, 'config_substituts', 'Gérer les substituts', 11, NULL, 1),
(22, 'config_parametres', 'Paramètres du tribunal', 11, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `fonctions_parquet`
--

CREATE TABLE `fonctions_parquet` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `type_role` enum('procureur','substitut','autre') NOT NULL DEFAULT 'substitut',
  `ordre` int(10) UNSIGNED DEFAULT 0,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fonctions_parquet`
--

INSERT INTO `fonctions_parquet` (`id`, `code`, `libelle`, `type_role`, `ordre`, `actif`, `created_at`) VALUES
(1, 'procureur', 'Procureur de la République', 'procureur', 1, 1, '2026-04-17 16:04:24'),
(2, 'procureur_adjoint', 'Procureur de la République Adjoint(e)', 'procureur', 2, 1, '2026-04-17 16:04:24'),
(3, 'substitut_1', 'Substitut N°1', 'substitut', 3, 1, '2026-04-17 16:04:24'),
(4, 'substitut_2', 'Substitut N°2', 'substitut', 4, 1, '2026-04-17 16:04:24'),
(5, 'substitut_3', 'Substitut N°3', 'substitut', 5, 1, '2026-04-17 16:04:24'),
(6, 'substitut_4', 'Substitut N°4', 'substitut', 6, 1, '2026-04-17 16:04:24'),
(7, 'substitut_5', 'Substitut N°5', 'substitut', 7, 1, '2026-04-17 16:04:24'),
(8, 'substitut_6', 'Substitut N°6', 'substitut', 8, 1, '2026-04-17 16:04:24'),
(9, 'substitut_7', 'Substitut N°7', 'substitut', 9, 1, '2026-04-17 16:04:24'),
(10, 'substitut_8', 'Substitut N°8', 'substitut', 10, 1, '2026-04-17 16:04:24'),
(11, 'substitut_9', 'Substitut N°9', 'substitut', 11, 1, '2026-04-17 16:04:24'),
(12, 'substitut_10', 'Substitut N°10', 'substitut', 12, 1, '2026-04-17 16:04:24'),
(13, 'substitut_11', 'Substitut N°11', 'substitut', 13, 1, '2026-04-17 16:04:24'),
(14, 'substitut_12', 'Substitut N°12', 'substitut', 14, 1, '2026-04-17 16:04:24'),
(15, 'substitut_13', 'Substitut N°13', 'substitut', 15, 1, '2026-04-17 16:04:24'),
(16, 'substitut_14', 'Substitut N°14', 'substitut', 16, 1, '2026-04-17 16:04:24'),
(17, 'substitut_15', 'Substitut N°15', 'substitut', 17, 1, '2026-04-17 16:04:24'),
(18, 'substitut_16', 'Substitut N°16', 'substitut', 18, 1, '2026-04-17 16:04:24'),
(19, 'substitut_17', 'Substitut N°17', 'substitut', 19, 1, '2026-04-17 16:04:24'),
(20, 'substitut_18', 'Substitut N°18', 'substitut', 20, 1, '2026-04-17 16:04:24'),
(21, 'substitut_19', 'Substitut N°19', 'substitut', 21, 1, '2026-04-17 16:04:24'),
(22, 'substitut_20', 'Substitut N°20', 'substitut', 22, 1, '2026-04-17 16:04:24'),
(23, 'substitut_21', 'Substitut N°21', 'substitut', 23, 1, '2026-04-17 16:04:24');

-- --------------------------------------------------------

--
-- Structure de la table `infractions`
--

CREATE TABLE `infractions` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `categorie` enum('criminelle','correctionnelle','contraventionnelle') NOT NULL,
  `peine_min_mois` int(11) DEFAULT NULL,
  `peine_max_mois` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `infractions`
--

INSERT INTO `infractions` (`id`, `code`, `libelle`, `categorie`, `peine_min_mois`, `peine_max_mois`, `created_at`) VALUES
(1, 'INF-001', 'Meurtre avec préméditation', 'criminelle', 120, 999, '2026-04-17 16:04:24'),
(2, 'INF-002', 'Viol', 'criminelle', 60, 120, '2026-04-17 16:04:24'),
(3, 'INF-003', 'Vol à main armée', 'criminelle', 60, 120, '2026-04-17 16:04:24'),
(4, 'INF-004', 'Terrorisme et association de malfaiteurs', 'criminelle', 120, 999, '2026-04-17 16:04:24'),
(5, 'INF-005', 'Trafic de stupéfiants', 'criminelle', 60, 120, '2026-04-17 16:04:24'),
(6, 'INF-006', 'Enlèvement et séquestration', 'criminelle', 60, 120, '2026-04-17 16:04:24'),
(7, 'INF-007', 'Escroquerie et abus de confiance', 'correctionnelle', 12, 60, '2026-04-17 16:04:24'),
(8, 'INF-008', 'Détournement de deniers publics', 'correctionnelle', 24, 60, '2026-04-17 16:04:24'),
(9, 'INF-009', 'Corruption active et passive', 'correctionnelle', 24, 60, '2026-04-17 16:04:24'),
(10, 'INF-010', 'Coups et blessures volontaires', 'correctionnelle', 6, 36, '2026-04-17 16:04:24'),
(11, 'INF-011', 'Vol simple', 'correctionnelle', 3, 24, '2026-04-17 16:04:24'),
(12, 'INF-012', 'Faux et usage de faux', 'correctionnelle', 12, 36, '2026-04-17 16:04:24'),
(13, 'INF-013', 'Trafic illicite de migrants', 'correctionnelle', 24, 60, '2026-04-17 16:04:24'),
(14, 'INF-014', 'Ivresse publique et manifeste', 'contraventionnelle', 0, 1, '2026-04-17 16:04:24'),
(15, 'INF-015', 'Tapage nocturne et trouble à l\'ordre public', 'contraventionnelle', 0, 1, '2026-04-17 16:04:24'),
(16, 'INF-016', 'Vol de nuit dans une habitation', 'criminelle', 24, 120, '2026-04-17 20:52:30'),
(17, 'INF-017', 'Escroquerie', 'correctionnelle', 12, 60, '2026-04-17 20:52:30'),
(18, 'INF-018', 'Vol à main armé', 'criminelle', 60, 120, '2026-04-17 20:52:30'),
(19, 'INF-019', 'Coup et blessures volontaire', 'correctionnelle', 6, 36, '2026-04-17 20:52:30'),
(20, 'INF-020', 'Trafic international de drogue à haut risque', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(21, 'INF-021', 'AMT', 'criminelle', 60, 120, '2026-04-17 20:52:30'),
(22, 'INF-022', 'Blanchiment', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(23, 'INF-023', 'Enrichissement illicite', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(24, 'INF-024', 'Détournement des deniers publics', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(25, 'INF-025', 'Infanticide', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(26, 'INF-026', 'Viol', 'criminelle', 60, 120, '2026-04-17 20:52:30'),
(27, 'INF-027', 'Abus de confiance', 'correctionnelle', 12, 60, '2026-04-17 20:52:30'),
(28, 'INF-028', 'Faux et usage de faux', 'correctionnelle', 12, 36, '2026-04-17 20:52:30'),
(29, 'INF-029', 'Coup mortel', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(30, 'INF-030', 'Assassinat', 'criminelle', 240, 999, '2026-04-17 20:52:30'),
(31, 'INF-031', 'Détention illégale d\'arme à feu', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(32, 'INF-032', 'Accès illégal dans un système informatisé', 'correctionnelle', 12, 36, '2026-04-17 20:52:30'),
(33, 'INF-033', 'Concussion', 'correctionnelle', 24, 60, '2026-04-17 20:52:30'),
(34, 'INF-034', 'Viol sur mineur de moins de 13 ans', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(35, 'INF-035', 'Financement du terrorisme', 'criminelle', 120, 999, '2026-04-17 20:52:30'),
(36, 'INF-036', 'Vol avec violence', 'criminelle', 60, 120, '2026-04-17 20:52:30'),
(37, 'INF-037', 'Terrorisme', 'criminelle', 120, 999, '2026-04-17 20:52:30');

-- --------------------------------------------------------

--
-- Structure de la table `jugements`
--

CREATE TABLE `jugements` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `audience_id` int(11) DEFAULT NULL,
  `numero_jugement` varchar(80) NOT NULL,
  `date_jugement` date NOT NULL,
  `type_jugement` enum('correctionnel','criminel','civil','commercial','avant_dire_droit','autre') NOT NULL DEFAULT 'correctionnel',
  `nature_jugement` enum('condamnation','relaxe','acquittement','non_lieu','renvoi','autre') NOT NULL DEFAULT 'condamnation',
  `dispositif` text NOT NULL,
  `peine_principale` varchar(255) DEFAULT NULL,
  `duree_peine_mois` int(11) DEFAULT NULL,
  `montant_amende` decimal(15,2) DEFAULT NULL,
  `dommages_interets` decimal(15,2) DEFAULT NULL,
  `sursis` tinyint(1) DEFAULT 0,
  `duree_sursis_mois` int(11) DEFAULT NULL,
  `appel_possible` tinyint(1) DEFAULT 0,
  `appel_interjecte` tinyint(1) DEFAULT 0,
  `date_limite_appel` date DEFAULT NULL,
  `date_appel` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `greffier_id` int(11) DEFAULT NULL,
  `redige_par` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `maisons_arret`
--

CREATE TABLE `maisons_arret` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `ville` varchar(100) NOT NULL DEFAULT '',
  `region_id` int(11) DEFAULT NULL,
  `commune_id` int(11) DEFAULT NULL,
  `capacite` int(11) DEFAULT 0,
  `population_actuelle` int(11) DEFAULT 0,
  `population_hommes` int(11) DEFAULT 0,
  `population_femmes` int(11) DEFAULT 0,
  `directeur` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `maisons_arret`
--

INSERT INTO `maisons_arret` (`id`, `nom`, `ville`, `region_id`, `commune_id`, `capacite`, `population_actuelle`, `population_hommes`, `population_femmes`, `directeur`, `telephone`, `adresse`, `actif`, `created_at`) VALUES
(1, 'Maison d\'Arrêt de Niamey', 'Niamey', NULL, NULL, 600, 450, 420, 30, 'Commandant Seydou MAIGA', '+227 20 73 40 00', 'Quartier Plateau, Niamey', 1, '2026-04-17 16:04:24'),
(2, 'Maison d\'Arrêt de Zinder', 'Zinder', NULL, NULL, 300, 210, 195, 15, 'Commandant Moutari HASSANE', '+227 20 51 04 10', 'Vieux Zinder, Zinder', 1, '2026-04-17 16:04:24'),
(3, 'Maison d\'Arrêt de Maradi', 'Maradi', NULL, NULL, 250, 180, 165, 15, 'Commandant Abdou LAWALI', '+227 20 41 03 55', 'Quartier Dan Goulbi, Maradi', 1, '2026-04-17 16:04:24'),
(4, 'Maison d\'Arrêt de Tahoua', 'Tahoua', NULL, NULL, 200, 130, 120, 10, 'Commandant Harouna ISSA', '+227 20 61 02 80', 'Quartier Administratif, Tahoua', 1, '2026-04-17 16:04:24'),
(5, 'Maison d\'Arrêt d\'Agadez', 'Agadez', NULL, NULL, 100, 0, 55, 5, 'Commandant Souleymane ALI', '+227 20 44 05 00', 'Centre-ville, Agadez', 1, '2026-04-17 16:04:24'),
(6, 'Maison d\'Arrêt de Dosso', 'Dosso', NULL, NULL, 150, 90, 82, 8, 'Commandant Ibrahim GARBA', '+227 20 65 01 12', 'Centre-ville, Dosso', 1, '2026-04-17 16:04:24'),
(7, 'Maison d\'Arrêt de Diffa', 'Diffa', NULL, NULL, 80, 50, 46, 4, 'Commandant Amadou BELLO', '+227 20 55 06 10', 'Centre-ville, Diffa', 1, '2026-04-17 16:04:24'),
(8, 'Centre de Détention de Kollo', 'Kollo', NULL, NULL, 100, 70, 65, 5, 'Lieutenant Adamou SOULEY', '+227 20 47 00 21', 'Route de Dosso, Kollo', 1, '2026-04-17 16:04:24');

-- --------------------------------------------------------

--
-- Structure de la table `mandats`
--

CREATE TABLE `mandats` (
  `id` int(11) NOT NULL,
  `numero` varchar(80) NOT NULL,
  `type_mandat` enum('arret','depot','amener','comparution','perquisition','liberation') NOT NULL,
  `dossier_id` int(11) DEFAULT NULL,
  `detenu_id` int(11) DEFAULT NULL,
  `partie_id` int(11) DEFAULT NULL,
  `nouveau_nom` varchar(150) DEFAULT NULL,
  `nouveau_prenom` varchar(150) DEFAULT NULL,
  `nouveau_ddn` date DEFAULT NULL,
  `nouveau_nationalite` varchar(100) DEFAULT 'Nigérienne',
  `nouveau_adresse` text DEFAULT NULL,
  `nouveau_profession` varchar(200) DEFAULT NULL,
  `motif` text NOT NULL,
  `infraction_libelle` text DEFAULT NULL,
  `lieu_execution` text DEFAULT NULL,
  `emetteur_id` int(11) NOT NULL,
  `date_emission` date NOT NULL,
  `date_expiration` date DEFAULT NULL,
  `statut` enum('emis','signifie','execute','annule','expire') NOT NULL DEFAULT 'emis',
  `date_execution` date DEFAULT NULL,
  `executant_nom` varchar(200) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mandats`
--

INSERT INTO `mandats` (`id`, `numero`, `type_mandat`, `dossier_id`, `detenu_id`, `partie_id`, `nouveau_nom`, `nouveau_prenom`, `nouveau_ddn`, `nouveau_nationalite`, `nouveau_adresse`, `nouveau_profession`, `motif`, `infraction_libelle`, `lieu_execution`, `emetteur_id`, `date_emission`, `date_expiration`, `statut`, `date_execution`, `executant_nom`, `observations`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 'MAND N°001/2026/TGI-NY', 'arret', NULL, NULL, NULL, 'DG CAIMA', 'LAOUALI', '1993-04-17', 'Nigérienne', NULL, NULL, 'test', 'ttt', 'tttt', 1, '2026-04-17', '2026-10-17', 'execute', '2026-04-17', NULL, NULL, '2026-04-17 16:07:29', '2026-04-17 21:31:50', 1);

-- --------------------------------------------------------

--
-- Structure de la table `membres_audience`
--

CREATE TABLE `membres_audience` (
  `id` int(11) NOT NULL,
  `audience_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nom_externe` varchar(200) DEFAULT NULL,
  `role_audience` enum('president','greffier','assesseur_1','assesseur_2','jure_1','jure_2','procureur','substitut','juge_assesseur','avocat_defense','avocat_partie_civile','greffier_adjoint','autre') NOT NULL DEFAULT 'autre',
  `observations` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `membres_audience`
--

INSERT INTO `membres_audience` (`id`, `audience_id`, `user_id`, `nom_externe`, `role_audience`, `observations`) VALUES
(1, 1, 2, NULL, 'assesseur_1', NULL),
(2, 1, 3, NULL, 'assesseur_2', NULL),
(3, 1, NULL, 'AAAAA', 'jure_1', NULL),
(4, 1, NULL, 'BBBBBB', 'jure_2', NULL),
(5, 1, 7, NULL, 'procureur', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `libelle` varchar(200) NOT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `ordre` int(10) UNSIGNED DEFAULT 0,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `menus`
--

INSERT INTO `menus` (`id`, `code`, `libelle`, `icone`, `url`, `parent_id`, `ordre`, `actif`) VALUES
(1, 'dashboard', 'Tableau de bord', 'bi-speedometer2', '/dashboard', NULL, 1, 1),
(2, 'pv', 'Procès-Verbaux', 'bi-file-text', '/pv', NULL, 2, 1),
(3, 'dossiers', 'Dossiers', 'bi-folder2-open', '/dossiers', NULL, 3, 1),
(4, 'audiences', 'Audiences', 'bi-calendar-week', '/audiences', NULL, 4, 1),
(5, 'jugements', 'Jugements', 'bi-hammer', '/jugements', NULL, 5, 1),
(6, 'detenus', 'Population Carcérale', 'bi-person-lock', '/detenus', NULL, 6, 1),
(7, 'mandats', 'Mandats de Justice', 'bi-file-ruled', '/mandats', NULL, 7, 1),
(8, 'carte', 'Carte Antiterroriste', 'bi-map', '/carte', NULL, 8, 1),
(9, 'alertes', 'Alertes', 'bi-bell', '/alertes', NULL, 9, 1),
(10, 'users', 'Utilisateurs', 'bi-people', '/users', NULL, 10, 1),
(11, 'config', 'Configuration', 'bi-gear-fill', '/config', NULL, 11, 1);

-- --------------------------------------------------------

--
-- Structure de la table `mouvements_dossier`
--

CREATE TABLE `mouvements_dossier` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type_mouvement` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ancien_statut` varchar(60) DEFAULT NULL,
  `nouveau_statut` varchar(60) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mouvements_dossier`
--

INSERT INTO `mouvements_dossier` (`id`, `dossier_id`, `user_id`, `type_mouvement`, `description`, `ancien_statut`, `nouveau_statut`, `created_at`) VALUES
(1, 1, 1, 'creation', 'Dossier créé depuis PV RG N°001/2026/TGI-NY', NULL, 'en_instruction', '2026-04-17 16:09:56'),
(2, 1, 1, 'classement', 'Classé sans suite', 'en_instruction', 'classe', '2026-04-17 16:33:46'),
(3, 1, 1, 'declassement', 'Déclassé : ssss', 'classe', 'parquet', '2026-04-17 16:34:00'),
(4, 1, 1, 'affectation_instruction', 'Affecté au cabinet d\'instruction', 'parquet', 'en_instruction', '2026-04-17 20:02:34'),
(5, 2, 1, 'creation', 'Dossier créé depuis PV RG N°003/2026/TGI-NY — Mode de poursuite : Réquisitoire Introductif', NULL, 'en_instruction', '2026-04-17 20:53:43'),
(6, 3, 1, 'creation', 'Dossier créé depuis PV RG N°005/2026/TGI-NY — Mode de poursuite : Flagrant Délit', NULL, 'en_instruction', '2026-04-17 21:01:24'),
(7, 1, 1, 'ordonnance', 'Ordonnance ORD-2026-0001 créée', NULL, NULL, '2026-04-18 06:02:15');

-- --------------------------------------------------------

--
-- Structure de la table `ordonnances`
--

CREATE TABLE `ordonnances` (
  `id` int(11) NOT NULL,
  `numero_ordonnance` varchar(50) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `juge_id` int(11) DEFAULT NULL,
  `type_ordonnance` enum('renvoi','non_lieu','detention','liberation','saisie','perquisition','commission_rogatoire','autre') NOT NULL,
  `date_ordonnance` date NOT NULL,
  `contenu` text NOT NULL,
  `observations` text DEFAULT NULL,
  `statut` enum('projet','signee','notifiee','executee') NOT NULL DEFAULT 'projet',
  `date_signature` datetime DEFAULT NULL,
  `date_notification` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ordonnances`
--

INSERT INTO `ordonnances` (`id`, `numero_ordonnance`, `dossier_id`, `juge_id`, `type_ordonnance`, `date_ordonnance`, `contenu`, `observations`, `statut`, `date_signature`, `date_notification`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'ORD-2026-0001', 1, 8, 'detention', '2026-04-18', 'jughhhhhhh', 'chghgffghhjfgfgtyj', 'signee', '2026-04-18 07:02:27', NULL, 1, '2026-04-18 06:02:15', '2026-04-18 06:02:27');

-- --------------------------------------------------------

--
-- Structure de la table `parametres_tribunal`
--

CREATE TABLE `parametres_tribunal` (
  `id` int(11) NOT NULL,
  `cle` varchar(100) NOT NULL,
  `valeur` text DEFAULT NULL,
  `groupe` varchar(50) NOT NULL DEFAULT 'general',
  `libelle` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type_champ` enum('text','textarea','number','boolean','email','tel','url','color','select') NOT NULL DEFAULT 'text',
  `options_json` text DEFAULT NULL COMMENT 'JSON pour les selects',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `parametres_tribunal`
--

INSERT INTO `parametres_tribunal` (`id`, `cle`, `valeur`, `groupe`, `libelle`, `description`, `type_champ`, `options_json`, `updated_at`, `updated_by`) VALUES
(1, 'tribunal_nom_court', 'TGI-NY', 'identite', 'Nom court (sigle)', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(2, 'tribunal_nom_complet', 'Tribunal de Grande Instance Hors Classe de Niamey', 'identite', 'Nom complet du tribunal', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(3, 'tribunal_ville', 'Niamey', 'identite', 'Ville', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(4, 'tribunal_pays', 'République du Niger', 'identite', 'Pays', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(5, 'tribunal_adresse', 'Avenue de la Mairie — B.P. 466 — Niamey, République du Niger', 'identite', 'Adresse postale', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(6, 'tribunal_telephone', '+227 20 73 20 00', 'identite', 'Téléphone', NULL, 'tel', NULL, '2026-04-17 17:08:05', 1),
(7, 'tribunal_email', 'contact@tgi-niamey.ne', 'identite', 'Email institutionnel', NULL, 'email', NULL, '2026-04-17 17:08:05', 1),
(8, 'tribunal_website', '', 'identite', 'Site web', NULL, 'url', NULL, '2026-04-17 17:08:05', 1),
(9, 'tribunal_devise', 'Fraternité — Travail — Progrès', 'identite', 'Devise nationale', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(10, 'doc_entete_ligne1', 'REPUBLIQUE DU NIGER', 'documents', 'En-tête ligne 1', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(11, 'doc_entete_ligne2', 'MINISTÈRE DE LA JUSTICE', 'documents', 'En-tête ligne 2', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(12, 'doc_entete_ligne3', 'Tribunal de Grande Instance Hors Classe de Niamey', 'documents', 'En-tête ligne 3', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(13, 'doc_pied_page', 'Document officiel — TGI-NY — Niamey — République du Niger', 'documents', 'Pied de page par défaut', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(14, 'doc_qr_code_actif', '1', 'documents', 'Activer le QR code sur les mandats', NULL, 'boolean', NULL, '2026-04-17 17:08:05', 1),
(15, 'doc_qr_code_base_url', 'tjrtjrjrj', 'documents', 'URL de base pour les QR codes', NULL, 'url', NULL, '2026-04-17 17:08:05', 1),
(16, 'delai_pv_jours', '30', 'delais', 'Délai traitement PV (jours)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(17, 'delai_instruction_mois', '6', 'delais', 'Délai instruction (mois)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(18, 'delai_alerte_audience_jours', '3', 'delais', 'Alerte avant audience (jours)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(19, 'delai_appel_jours', '30', 'delais', 'Délai appel (jours)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(20, 'delai_detention_prov_mois', '6', 'delais', 'Délai max détention provisoire (mois)', NULL, 'number', NULL, '2026-04-17 17:08:05', 1),
(21, 'num_prefix_rg', 'RG N°', 'numerotation', 'Préfixe numéro RG', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(22, 'num_prefix_rp', 'RP N°', 'numerotation', 'Préfixe numéro RP', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(23, 'num_prefix_ri', 'RI N°', 'numerotation', 'Préfixe numéro RI', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(24, 'num_suffix_rg', 'TGI-NY', 'numerotation', 'Suffixe numéro RG', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(25, 'num_suffix_rp', 'PARQUET', 'numerotation', 'Suffixe numéro RP', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(26, 'num_suffix_ri', 'INSTR', 'numerotation', 'Suffixe numéro RI', NULL, 'text', NULL, '2026-04-17 17:08:05', 1),
(27, 'theme_couleur_primaire', '#0a2342', 'affichage', 'Couleur primaire', NULL, 'color', NULL, '2026-04-17 17:08:05', 1),
(28, 'items_par_page', '20', 'affichage', 'Éléments par page', NULL, 'number', NULL, '2026-04-17 17:08:05', 1);

-- --------------------------------------------------------

--
-- Structure de la table `parties`
--

CREATE TABLE `parties` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `type_partie` enum('prevenu','victime','partie_civile','temoin','expert','autre') NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `profession` varchar(150) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `parties`
--

INSERT INTO `parties` (`id`, `dossier_id`, `type_partie`, `nom`, `prenom`, `date_naissance`, `nationalite`, `profession`, `adresse`, `telephone`, `notes`, `created_at`) VALUES
(1, 3, '', 'SCP', 'ARZIKA', NULL, 'Nigérienne', '', '', 'KIMBA', NULL, '2026-04-18 05:41:55');

-- --------------------------------------------------------

--
-- Structure de la table `primo_intervenants`
--

CREATE TABLE `primo_intervenants` (
  `id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `primo_intervenants`
--

INSERT INTO `primo_intervenants` (`id`, `nom`, `type`, `description`, `actif`) VALUES
(1, 'Unité Spéciale de la Police', 'Police', 'DGPN — Unité Spéciale', 1),
(2, 'Forces Armées Nigériennes', 'Armée', 'Forces Armées du Niger', 1),
(3, 'Opération Damissa', 'Inter-forces', 'Opération sécuritaire inter-forces', 1),
(4, 'Garde Nationale du Niger', 'Gendarmerie', 'Garde Nationale — missions sécuritaires', 1),
(5, 'Gendarmerie Nationale', 'Gendarmerie', 'Gendarmerie Nationale du Niger', 1),
(6, 'Direction de la Surveillance du Territoire', 'Renseignement', 'DST — services de renseignement', 1),
(7, 'Police Judiciaire', 'Police', 'Brigade de Police Judiciaire', 1);

-- --------------------------------------------------------

--
-- Structure de la table `pv`
--

CREATE TABLE `pv` (
  `id` int(11) NOT NULL,
  `numero_pv` varchar(100) NOT NULL,
  `numero_rg` varchar(60) NOT NULL,
  `unite_enquete_id` int(11) DEFAULT NULL,
  `date_pv` date NOT NULL,
  `date_reception` date NOT NULL,
  `type_affaire` enum('civile','penale','commerciale') NOT NULL DEFAULT 'penale',
  `infraction_id` int(11) DEFAULT NULL,
  `est_antiterroriste` tinyint(1) DEFAULT 0,
  `region_id` int(11) DEFAULT NULL,
  `departement_id` int(11) DEFAULT NULL,
  `commune_id` int(11) DEFAULT NULL,
  `description_faits` text DEFAULT NULL,
  `statut` enum('nouveau','recu','en_traitement','classe','transfere','transfere_instruction','transfere_jugement_direct') NOT NULL DEFAULT 'recu',
  `motif_classement` text DEFAULT NULL,
  `date_classement` date DEFAULT NULL,
  `motif_declassement` text DEFAULT NULL,
  `date_declassement` date DEFAULT NULL,
  `substitut_id` int(11) DEFAULT NULL,
  `date_affectation_substitut` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pv`
--

INSERT INTO `pv` (`id`, `numero_pv`, `numero_rg`, `unite_enquete_id`, `date_pv`, `date_reception`, `type_affaire`, `infraction_id`, `est_antiterroriste`, `region_id`, `departement_id`, `commune_id`, `description_faits`, `statut`, `motif_classement`, `date_classement`, `motif_declassement`, `date_declassement`, `substitut_id`, `date_affectation_substitut`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '234/2026', 'RG N°001/2026/TGI-NY', 4, '2026-04-17', '2026-04-17', 'penale', NULL, 1, 6, 37, 14, '', 'transfere_instruction', NULL, NULL, NULL, NULL, 5, '2026-04-17', 1, '2026-04-17 16:05:28', '2026-04-17 16:09:56'),
(2, '239/2026', 'RG N°003/2026/TGI-NY', 5, '2026-04-17', '2026-04-17', 'penale', NULL, 1, 6, 46, 17, 'test', 'transfere_instruction', NULL, NULL, NULL, NULL, 6, '2026-04-17', 1, '2026-04-17 17:02:43', '2026-04-17 20:53:43'),
(3, '247/2026', 'RG N°005/2026/TGI-NY', 6, '2026-04-17', '2026-04-17', 'penale', 30, 0, NULL, NULL, NULL, '', 'transfere_instruction', NULL, NULL, NULL, NULL, 7, '2026-04-17', 1, '2026-04-17 21:00:34', '2026-04-17 21:01:24');

-- --------------------------------------------------------

--
-- Structure de la table `pv_primo_intervenants`
--

CREATE TABLE `pv_primo_intervenants` (
  `pv_id` int(11) NOT NULL,
  `primo_intervenant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pv_primo_intervenants`
--

INSERT INTO `pv_primo_intervenants` (`pv_id`, `primo_intervenant_id`) VALUES
(1, 2);

-- --------------------------------------------------------

--
-- Structure de la table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `regions`
--

INSERT INTO `regions` (`id`, `nom`, `code`) VALUES
(1, 'Agadez', 'AGZ'),
(2, 'Diffa', 'DIF'),
(3, 'Dosso', 'DOS'),
(4, 'Maradi', 'MAR'),
(5, 'Tahoua', 'TAH'),
(6, 'Tillabéri', 'TIL'),
(7, 'Zinder', 'ZND'),
(8, 'Niamey', 'NIA');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `libelle` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `code`, `libelle`) VALUES
(1, 'admin', 'Administrateur Système'),
(2, 'president', 'Président du Tribunal'),
(3, 'vice_president', 'Vice-Président'),
(4, 'procureur', 'Procureur de la République'),
(5, 'substitut_procureur', 'Substitut du Procureur'),
(6, 'juge_instruction', 'Juge d\'Instruction'),
(7, 'juge_siege', 'Juge du Siège'),
(8, 'greffier', 'Greffier'),
(9, 'avocat', 'Avocat');

-- --------------------------------------------------------

--
-- Structure de la table `salles_audience`
--

CREATE TABLE `salles_audience` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `capacite` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `salles_audience`
--

INSERT INTO `salles_audience` (`id`, `nom`, `capacite`, `description`, `actif`) VALUES
(1, 'Grande Salle d\'Assises', 150, 'Salle principale pour les affaires criminelles', 1),
(2, 'Salle Correctionnelle N°1', 80, 'Affaires correctionnelles', 1),
(3, 'Salle Correctionnelle N°2', 80, 'Affaires correctionnelles', 1),
(4, 'Salle Civile', 60, 'Affaires civiles et commerciales', 1),
(5, 'Chambre du Conseil', 20, 'Audiences à huis clos — instruction', 1);

-- --------------------------------------------------------

--
-- Structure de la table `scelles`
--

CREATE TABLE `scelles` (
  `id` int(11) NOT NULL,
  `numero_scelle` varchar(50) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `categorie` enum('arme','drogue','document','argent','electronique','vehicule','autre') NOT NULL,
  `description` text NOT NULL,
  `date_depot` date NOT NULL,
  `lieu_conservation` varchar(200) DEFAULT 'Greffe du TGI-NY',
  `observations` text DEFAULT NULL,
  `statut` enum('depose','inventorie','restitue','detruit','confisque') NOT NULL DEFAULT 'depose',
  `date_restitution` date DEFAULT NULL,
  `beneficiaire_restitution` varchar(200) DEFAULT NULL,
  `date_destruction` date DEFAULT NULL,
  `motif_destruction` text DEFAULT NULL,
  `pv_destruction` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `scelles`
--

INSERT INTO `scelles` (`id`, `numero_scelle`, `dossier_id`, `categorie`, `description`, `date_depot`, `lieu_conservation`, `observations`, `statut`, `date_restitution`, `beneficiaire_restitution`, `date_destruction`, `motif_destruction`, `pv_destruction`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'SCL-2026-0001', 2, 'drogue', 'uuuuuuu', '2026-04-18', '', '', 'depose', NULL, NULL, NULL, NULL, NULL, 1, '2026-04-17 23:07:23', '2026-04-17 23:07:23'),
(3, 'SCL-2026-0002', 2, 'drogue', 'ggggg', '2026-04-19', '', '', 'detruit', NULL, NULL, '2026-04-19', 'dddd', NULL, 1, '2026-04-19 06:21:35', '2026-04-19 06:22:32');

-- --------------------------------------------------------

--
-- Structure de la table `unites_enquete`
--

CREATE TABLE `unites_enquete` (
  `id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `type` enum('commissariat','brigade_police','gendarmerie','unite_speciale','autre') NOT NULL,
  `commune_id` int(11) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `unites_enquete`
--

INSERT INTO `unites_enquete` (`id`, `nom`, `type`, `commune_id`, `telephone`, `actif`) VALUES
(1, 'Commissariat Central de Niamey', 'commissariat', NULL, '+227 20 73 20 00', 1),
(2, 'Commissariat du 1er Arrondissement', 'commissariat', NULL, '+227 20 73 21 00', 1),
(3, 'Commissariat du 2ème Arrondissement', 'commissariat', NULL, '+227 20 73 22 00', 1),
(4, 'Brigade de Gendarmerie de Niamey', 'gendarmerie', NULL, '+227 20 73 30 00', 1),
(5, 'Brigade Territoriale de Say', 'gendarmerie', NULL, '+227 20 73 31 00', 1),
(6, 'Brigade de Kollo', 'gendarmerie', NULL, '+227 20 73 32 00', 1),
(7, 'Police Judiciaire Niamey', 'brigade_police', NULL, '+227 20 73 25 00', 1),
(8, 'Unité Spéciale Anti-Terrorisme', 'unite_speciale', NULL, '+227 20 73 40 00', 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `fonction_parquet_id` int(11) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `matricule` varchar(50) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `role_id`, `fonction_parquet_id`, `nom`, `prenom`, `email`, `password`, `telephone`, `matricule`, `actif`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'SYSTÈME', 'Admin', 'admin@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'SYS-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(2, 2, NULL, 'MAÏGA', 'Ousmane', 'president@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'PRES-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(3, 3, NULL, 'HASSANE', 'Aminatou', 'vice.president@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'VP-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(4, 4, NULL, 'MOUSSA', 'Ibrahim', 'procureur@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'PROC-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(5, 5, NULL, 'ADAMOU', 'Fatouma', 'substitut1@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'SUB-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(6, 5, NULL, 'CHAIBOU', 'Moustapha', 'substitut2@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'SUB-002', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(7, 5, NULL, 'MAHAMADOU', 'Salissou', 'substitut3@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'SUB-003', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(8, 6, NULL, 'SAIDOU', 'Aïssatou', 'juge.instr1@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'JI-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(9, 6, NULL, 'HAMIDOU', 'Mariama', 'juge.instr2@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'JI-002', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(10, 7, NULL, 'YACOUBA', 'Hassane', 'juge.siege@tgi-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'JS-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24'),
(11, 8, NULL, 'ISSA', 'Rahila', 'greffier@tgi-niamey.ne', '$2y$12$rZsYuSsaEp5vuBXThvHv2e5Y47YFcMo0Lvhm7yRb6BmfmH2UXXtSe', '', 'GRF-001', 1, '2026-04-17 16:04:24', '2026-04-19 06:27:01'),
(12, 9, NULL, 'MAHAMANE', 'Alio', 'avocat@barreau-niamey.ne', '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym', NULL, 'AVO-001', 1, '2026-04-17 16:04:24', '2026-04-17 16:04:24');

-- --------------------------------------------------------

--
-- Structure de la table `voies_recours`
--

CREATE TABLE `voies_recours` (
  `id` int(11) NOT NULL,
  `dossier_id` int(11) NOT NULL,
  `jugement_id` int(11) DEFAULT NULL,
  `type_recours` enum('appel','cassation','opposition','revision') NOT NULL,
  `demandeur_nom` varchar(200) NOT NULL,
  `demandeur_qualite` enum('prevenu','partie_civile','ministere_public','avocat') DEFAULT NULL,
  `date_declaration` date NOT NULL,
  `juridiction_saisie` varchar(200) DEFAULT NULL,
  `motifs` text DEFAULT NULL,
  `decision_rendue` text DEFAULT NULL,
  `date_decision` date DEFAULT NULL,
  `statut` enum('declare','instruit','juge','irrecevable','desiste') NOT NULL DEFAULT 'declare',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `voies_recours`
--

INSERT INTO `voies_recours` (`id`, `dossier_id`, `jugement_id`, `type_recours`, `demandeur_nom`, `demandeur_qualite`, `date_declaration`, `juridiction_saisie`, `motifs`, `decision_rendue`, `date_decision`, `statut`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 'opposition', 'SANI MOUSSA', 'prevenu', '2026-04-18', 'APPEL NY', '', '', '2026-04-18', 'irrecevable', 1, '2026-04-18 05:46:00', '2026-04-18 05:46:25');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `alertes`
--
ALTER TABLE `alertes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_id` (`dossier_id`),
  ADD KEY `pv_id` (`pv_id`),
  ADD KEY `destinataire_id` (`destinataire_id`),
  ADD KEY `idx_alertes_user` (`user_id`),
  ADD KEY `idx_alertes_est_lue` (`est_lue`);

--
-- Index pour la table `audiences`
--
ALTER TABLE `audiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salle_id` (`salle_id`),
  ADD KEY `president_id` (`president_id`),
  ADD KEY `greffier_id` (`greffier_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_audiences_dossier` (`dossier_id`),
  ADD KEY `idx_audiences_date` (`date_audience`);

--
-- Index pour la table `avocats`
--
ALTER TABLE `avocats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_avocat_matricule` (`matricule`);

--
-- Index pour la table `avocat_dossier`
--
ALTER TABLE `avocat_dossier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_avocat_dossier` (`avocat_id`,`dossier_id`),
  ADD KEY `fk_avdoss_dossier` (`dossier_id`);

--
-- Index pour la table `cabinets_instruction`
--
ALTER TABLE `cabinets_instruction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `juge_id` (`juge_id`);

--
-- Index pour la table `casier_judiciaire_condamnations`
--
ALTER TABLE `casier_judiciaire_condamnations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cjc_personne` (`personne_id`),
  ADD KEY `fk_cjc_dossier` (`dossier_id`),
  ADD KEY `fk_cjc_jugement` (`jugement_id`),
  ADD KEY `fk_cjc_created_by` (`created_by`);

--
-- Index pour la table `casier_judiciaire_personnes`
--
ALTER TABLE `casier_judiciaire_personnes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nin` (`nin`),
  ADD KEY `idx_nom` (`nom`);

--
-- Index pour la table `commissions_rogatoires`
--
ALTER TABLE `commissions_rogatoires`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_numero_cr` (`numero_cr`),
  ADD KEY `fk_cr_dossier` (`dossier_id`),
  ADD KEY `fk_cr_created_by` (`created_by`);

--
-- Index pour la table `communes`
--
ALTER TABLE `communes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `departement_id` (`departement_id`);

--
-- Index pour la table `communes_geo`
--
ALTER TABLE `communes_geo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nom_dept` (`nom`,`departement_nom`);

--
-- Index pour la table `controles_judiciaires`
--
ALTER TABLE `controles_judiciaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cj_dossier` (`dossier_id`),
  ADD KEY `fk_cj_ordonnance` (`ordonnance_id`),
  ADD KEY `fk_cj_created_by` (`created_by`);

--
-- Index pour la table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_id` (`region_id`);

--
-- Index pour la table `detenus`
--
ALTER TABLE `detenus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_ecrou` (`numero_ecrou`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_detenus_dossier` (`dossier_id`),
  ADD KEY `idx_detenus_statut` (`statut`),
  ADD KEY `idx_detenus_maison` (`maison_arret_id`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pv_id` (`pv_id`),
  ADD KEY `audience_id` (`audience_id`),
  ADD KEY `jugement_id` (`jugement_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_documents_dossier` (`dossier_id`);

--
-- Index pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_rg` (`numero_rg`),
  ADD UNIQUE KEY `numero_rp` (`numero_rp`),
  ADD UNIQUE KEY `numero_ri` (`numero_ri`),
  ADD KEY `declasse_par` (`declasse_par`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `departement_id` (`departement_id`),
  ADD KEY `commune_id` (`commune_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `juge_siege_id` (`juge_siege_id`),
  ADD KEY `idx_dossiers_statut` (`statut`),
  ADD KEY `idx_dossiers_cabinet` (`cabinet_id`),
  ADD KEY `idx_dossiers_substitut` (`substitut_id`),
  ADD KEY `idx_dossiers_pv` (`pv_id`),
  ADD KEY `idx_type_affaire` (`type_affaire`);

--
-- Index pour la table `droits_utilisateurs`
--
ALTER TABLE `droits_utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_droit_user_menu` (`user_id`,`menu_id`),
  ADD UNIQUE KEY `uk_droit_user_fonc` (`user_id`,`fonctionnalite_id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `fonctionnalite_id` (`fonctionnalite_id`),
  ADD KEY `accorde_par` (`accorde_par`);

--
-- Index pour la table `expertises_judiciaires`
--
ALTER TABLE `expertises_judiciaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exp_dossier` (`dossier_id`),
  ADD KEY `fk_exp_ordonnance` (`ordonnance_id`),
  ADD KEY `fk_exp_created_by` (`created_by`);

--
-- Index pour la table `fonctionnalites`
--
ALTER TABLE `fonctionnalites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Index pour la table `fonctions_parquet`
--
ALTER TABLE `fonctions_parquet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `infractions`
--
ALTER TABLE `infractions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `jugements`
--
ALTER TABLE `jugements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_jugement` (`numero_jugement`),
  ADD KEY `audience_id` (`audience_id`),
  ADD KEY `greffier_id` (`greffier_id`),
  ADD KEY `redige_par` (`redige_par`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_jugements_dossier` (`dossier_id`);

--
-- Index pour la table `maisons_arret`
--
ALTER TABLE `maisons_arret`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `commune_id` (`commune_id`);

--
-- Index pour la table `mandats`
--
ALTER TABLE `mandats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `detenu_id` (`detenu_id`),
  ADD KEY `partie_id` (`partie_id`),
  ADD KEY `emetteur_id` (`emetteur_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_mandats_dossier` (`dossier_id`),
  ADD KEY `idx_mandats_statut` (`statut`);

--
-- Index pour la table `membres_audience`
--
ALTER TABLE `membres_audience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audience_id` (`audience_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Index pour la table `mouvements_dossier`
--
ALTER TABLE `mouvements_dossier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_id` (`dossier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_numero_ordonnance` (`numero_ordonnance`),
  ADD KEY `fk_ord_dossier` (`dossier_id`),
  ADD KEY `fk_ord_juge` (`juge_id`),
  ADD KEY `fk_ord_created_by` (`created_by`);

--
-- Index pour la table `parametres_tribunal`
--
ALTER TABLE `parametres_tribunal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cle` (`cle`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Index pour la table `parties`
--
ALTER TABLE `parties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dossier_id` (`dossier_id`);

--
-- Index pour la table `primo_intervenants`
--
ALTER TABLE `primo_intervenants`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `pv`
--
ALTER TABLE `pv`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_rg` (`numero_rg`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `departement_id` (`departement_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `substitut_id` (`substitut_id`),
  ADD KEY `idx_pv_statut` (`statut`),
  ADD KEY `idx_pv_antiterro` (`est_antiterroriste`),
  ADD KEY `idx_pv_commune` (`commune_id`),
  ADD KEY `idx_pv_unite` (`unite_enquete_id`),
  ADD KEY `fk_pv_infraction` (`infraction_id`);

--
-- Index pour la table `pv_primo_intervenants`
--
ALTER TABLE `pv_primo_intervenants`
  ADD PRIMARY KEY (`pv_id`,`primo_intervenant_id`),
  ADD KEY `primo_intervenant_id` (`primo_intervenant_id`);

--
-- Index pour la table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `salles_audience`
--
ALTER TABLE `salles_audience`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `scelles`
--
ALTER TABLE `scelles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_numero_scelle` (`numero_scelle`),
  ADD KEY `fk_sc_dossier` (`dossier_id`),
  ADD KEY `fk_sc_created_by` (`created_by`);

--
-- Index pour la table `unites_enquete`
--
ALTER TABLE `unites_enquete`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commune_id` (`commune_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `fonction_parquet_id` (`fonction_parquet_id`);

--
-- Index pour la table `voies_recours`
--
ALTER TABLE `voies_recours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vr_dossier` (`dossier_id`),
  ADD KEY `fk_vr_jugement` (`jugement_id`),
  ADD KEY `fk_vr_created_by` (`created_by`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `alertes`
--
ALTER TABLE `alertes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `audiences`
--
ALTER TABLE `audiences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `avocats`
--
ALTER TABLE `avocats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `avocat_dossier`
--
ALTER TABLE `avocat_dossier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cabinets_instruction`
--
ALTER TABLE `cabinets_instruction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `casier_judiciaire_condamnations`
--
ALTER TABLE `casier_judiciaire_condamnations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `casier_judiciaire_personnes`
--
ALTER TABLE `casier_judiciaire_personnes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commissions_rogatoires`
--
ALTER TABLE `commissions_rogatoires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `communes`
--
ALTER TABLE `communes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `communes_geo`
--
ALTER TABLE `communes_geo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;

--
-- AUTO_INCREMENT pour la table `controles_judiciaires`
--
ALTER TABLE `controles_judiciaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `departements`
--
ALTER TABLE `departements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT pour la table `detenus`
--
ALTER TABLE `detenus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `dossiers`
--
ALTER TABLE `dossiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `droits_utilisateurs`
--
ALTER TABLE `droits_utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `expertises_judiciaires`
--
ALTER TABLE `expertises_judiciaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `fonctionnalites`
--
ALTER TABLE `fonctionnalites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `fonctions_parquet`
--
ALTER TABLE `fonctions_parquet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `infractions`
--
ALTER TABLE `infractions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `jugements`
--
ALTER TABLE `jugements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `maisons_arret`
--
ALTER TABLE `maisons_arret`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `mandats`
--
ALTER TABLE `mandats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `membres_audience`
--
ALTER TABLE `membres_audience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `mouvements_dossier`
--
ALTER TABLE `mouvements_dossier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `parametres_tribunal`
--
ALTER TABLE `parametres_tribunal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `parties`
--
ALTER TABLE `parties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `primo_intervenants`
--
ALTER TABLE `primo_intervenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `pv`
--
ALTER TABLE `pv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `salles_audience`
--
ALTER TABLE `salles_audience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `scelles`
--
ALTER TABLE `scelles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `unites_enquete`
--
ALTER TABLE `unites_enquete`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `voies_recours`
--
ALTER TABLE `voies_recours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `alertes`
--
ALTER TABLE `alertes`
  ADD CONSTRAINT `alertes_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertes_ibfk_2` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertes_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `alertes_ibfk_4` FOREIGN KEY (`destinataire_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `audiences`
--
ALTER TABLE `audiences`
  ADD CONSTRAINT `audiences_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audiences_ibfk_2` FOREIGN KEY (`salle_id`) REFERENCES `salles_audience` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audiences_ibfk_3` FOREIGN KEY (`president_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audiences_ibfk_4` FOREIGN KEY (`greffier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audiences_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `avocat_dossier`
--
ALTER TABLE `avocat_dossier`
  ADD CONSTRAINT `fk_avdoss_avocat` FOREIGN KEY (`avocat_id`) REFERENCES `avocats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_avdoss_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cabinets_instruction`
--
ALTER TABLE `cabinets_instruction`
  ADD CONSTRAINT `cabinets_instruction_ibfk_1` FOREIGN KEY (`juge_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `casier_judiciaire_condamnations`
--
ALTER TABLE `casier_judiciaire_condamnations`
  ADD CONSTRAINT `fk_cjc_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cjc_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cjc_jugement` FOREIGN KEY (`jugement_id`) REFERENCES `jugements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cjc_personne` FOREIGN KEY (`personne_id`) REFERENCES `casier_judiciaire_personnes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commissions_rogatoires`
--
ALTER TABLE `commissions_rogatoires`
  ADD CONSTRAINT `fk_cr_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cr_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `communes`
--
ALTER TABLE `communes`
  ADD CONSTRAINT `communes_ibfk_1` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `controles_judiciaires`
--
ALTER TABLE `controles_judiciaires`
  ADD CONSTRAINT `fk_cj_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cj_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cj_ordonnance` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `departements`
--
ALTER TABLE `departements`
  ADD CONSTRAINT `departements_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `detenus`
--
ALTER TABLE `detenus`
  ADD CONSTRAINT `detenus_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detenus_ibfk_2` FOREIGN KEY (`maison_arret_id`) REFERENCES `maisons_arret` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `detenus_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_3` FOREIGN KEY (`audience_id`) REFERENCES `audiences` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_4` FOREIGN KEY (`jugement_id`) REFERENCES `jugements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_5` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `dossiers`
--
ALTER TABLE `dossiers`
  ADD CONSTRAINT `dossiers_ibfk_1` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_2` FOREIGN KEY (`substitut_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_3` FOREIGN KEY (`cabinet_id`) REFERENCES `cabinets_instruction` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_4` FOREIGN KEY (`declasse_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_5` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_6` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_7` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dossiers_ibfk_9` FOREIGN KEY (`juge_siege_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `droits_utilisateurs`
--
ALTER TABLE `droits_utilisateurs`
  ADD CONSTRAINT `droits_utilisateurs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `droits_utilisateurs_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `droits_utilisateurs_ibfk_3` FOREIGN KEY (`fonctionnalite_id`) REFERENCES `fonctionnalites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `droits_utilisateurs_ibfk_4` FOREIGN KEY (`accorde_par`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `expertises_judiciaires`
--
ALTER TABLE `expertises_judiciaires`
  ADD CONSTRAINT `fk_exp_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_exp_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exp_ordonnance` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `fonctionnalites`
--
ALTER TABLE `fonctionnalites`
  ADD CONSTRAINT `fonctionnalites_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `jugements`
--
ALTER TABLE `jugements`
  ADD CONSTRAINT `jugements_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jugements_ibfk_2` FOREIGN KEY (`audience_id`) REFERENCES `audiences` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jugements_ibfk_3` FOREIGN KEY (`greffier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jugements_ibfk_4` FOREIGN KEY (`redige_par`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jugements_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `maisons_arret`
--
ALTER TABLE `maisons_arret`
  ADD CONSTRAINT `maisons_arret_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maisons_arret_ibfk_2` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `mandats`
--
ALTER TABLE `mandats`
  ADD CONSTRAINT `mandats_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mandats_ibfk_2` FOREIGN KEY (`detenu_id`) REFERENCES `detenus` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mandats_ibfk_3` FOREIGN KEY (`partie_id`) REFERENCES `parties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mandats_ibfk_4` FOREIGN KEY (`emetteur_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `mandats_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `membres_audience`
--
ALTER TABLE `membres_audience`
  ADD CONSTRAINT `membres_audience_ibfk_1` FOREIGN KEY (`audience_id`) REFERENCES `audiences` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `membres_audience_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `mouvements_dossier`
--
ALTER TABLE `mouvements_dossier`
  ADD CONSTRAINT `mouvements_dossier_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mouvements_dossier_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  ADD CONSTRAINT `fk_ord_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ord_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ord_juge` FOREIGN KEY (`juge_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `parametres_tribunal`
--
ALTER TABLE `parametres_tribunal`
  ADD CONSTRAINT `parametres_tribunal_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `parties`
--
ALTER TABLE `parties`
  ADD CONSTRAINT `parties_ibfk_1` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pv`
--
ALTER TABLE `pv`
  ADD CONSTRAINT `fk_pv_infraction` FOREIGN KEY (`infraction_id`) REFERENCES `infractions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_1` FOREIGN KEY (`unite_enquete_id`) REFERENCES `unites_enquete` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_2` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_3` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_4` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pv_ibfk_6` FOREIGN KEY (`substitut_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `pv_primo_intervenants`
--
ALTER TABLE `pv_primo_intervenants`
  ADD CONSTRAINT `pv_primo_intervenants_ibfk_1` FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pv_primo_intervenants_ibfk_2` FOREIGN KEY (`primo_intervenant_id`) REFERENCES `primo_intervenants` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `scelles`
--
ALTER TABLE `scelles`
  ADD CONSTRAINT `fk_sc_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sc_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`);

--
-- Contraintes pour la table `unites_enquete`
--
ALTER TABLE `unites_enquete`
  ADD CONSTRAINT `unites_enquete_ibfk_1` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`fonction_parquet_id`) REFERENCES `fonctions_parquet` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `voies_recours`
--
ALTER TABLE `voies_recours`
  ADD CONSTRAINT `fk_vr_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_vr_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vr_jugement` FOREIGN KEY (`jugement_id`) REFERENCES `jugements` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
