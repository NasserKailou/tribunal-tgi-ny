-- ============================================================
-- Migration 016 : Ajout cabinet_id sur la table users
-- Nécessaire pour le filtrage juge_instruction dans AccessControl
-- ============================================================
-- Idempotent : ne fait rien si la colonne existe déjà
-- ============================================================

-- Ajouter cabinet_id sur users (référence cabinets_instruction)
-- Un juge d'instruction est rattaché à UN cabinet.
-- NULL = pas de restriction par cabinet (juge sans affectation fixe).
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS cabinet_id INT DEFAULT NULL
        COMMENT 'Cabinet d''instruction auquel le juge est rattaché (juge_instruction uniquement)';

-- Clé étrangère optionnelle (souple : ON DELETE SET NULL pour éviter les erreurs si cabinet supprimé)
-- On ne l'ajoute que si elle n'existe pas encore
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND COLUMN_NAME  = 'cabinet_id'
      AND REFERENCED_TABLE_NAME IS NOT NULL
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE users ADD CONSTRAINT fk_users_cabinet
     FOREIGN KEY (cabinet_id) REFERENCES cabinets_instruction(id) ON DELETE SET NULL',
    'SELECT 1 -- FK already exists'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index pour accélérer les jointures / lookups AccessControl::getUserCabinetId()
CREATE INDEX IF NOT EXISTS idx_users_cabinet_id ON users (cabinet_id);
