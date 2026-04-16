-- Migration 006 — Parquet réel + Cabinets d'instruction officiels + communes_geo
-- TGI-NY v3.0

-- ── 1. Table communes_geo (266 communes Niger pour choroplèthe) ───────────────
CREATE TABLE IF NOT EXISTS communes_geo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    departement_nom VARCHAR(150),
    region_nom VARCHAR(100),
    longitude DECIMAL(10,6),
    latitude DECIMAL(10,6),
    code_commune VARCHAR(20),
    UNIQUE KEY uk_nom_dept (nom, departement_nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. membres_audience — enrichir l'enum ────────────────────────────────────
ALTER TABLE membres_audience
  MODIFY COLUMN role_audience ENUM(
    'president','greffier','assesseur_1','assesseur_2','jure_1','jure_2',
    'procureur','substitut','juge_assesseur','avocat_defense',
    'avocat_partie_civile','greffier_adjoint','autre'
  ) NOT NULL DEFAULT 'autre';

-- ── 3. Cabinets d'instruction officiels ──────────────────────────────────────
DELETE FROM cabinets_instruction;
INSERT INTO cabinets_instruction (numero, libelle, actif) VALUES
('CAB-01', 'Doyen des Juges d''Instruction',              1),
('CAB-02', 'Cabinet Droit Commun Mineur N°1',             1),
('CAB-03', 'Cabinet Droit Commun Mineur N°2',             1),
('CAB-04', 'Cabinet Droit Commun Majeur N°1',             1),
('CAB-05', 'Cabinet Droit Commun Majeur N°2',             1),
('CAB-06', 'Cabinet Pôle Économique et Financier N°1',    1),
('CAB-07', 'Cabinet Pôle Économique et Financier N°2',    1),
('CAB-08', 'Cabinet Pôle Antiterroriste',                 1);

-- ── 4. Membres du parquet (nommés selon organigramme) ────────────────────────
-- Note : à insérer uniquement si la table users ne contient pas ces rôles
-- Le script Python de seed gère les hash bcrypt, ce script pose juste la structure
