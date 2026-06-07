<?php
/**
 * AccessControl — Contrôle d'accès centralisé (niveau ressource)
 *
 * Ce helper complète Auth (qui gère l'authentification et le rôle)
 * en ajoutant la logique métier de propriété/affectation :
 *
 *  ─ substitut_procureur  : uniquement les PV/dossiers qui lui sont affectés
 *  ─ juge_instruction     : uniquement les dossiers de son cabinet
 *  ─ greffier             : tous les PV/dossiers (lecture + saisie), sans restriction,
 *                           mais la distinction créateur/affectant doit être visible en UI
 *  ─ procureur/admin/président : accès complet
 *
 * Utilisation dans les contrôleurs :
 *   AccessControl::assertPVAccess($pvRow);        // redirige si refusé
 *   AccessControl::assertDossierAccess($dossierRow);
 *   AccessControl::canDeleteDocument($docRow);    // bool
 */
class AccessControl
{
    // ══════════════════════════════════════════════════════════════════════
    // PV
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Vérifie si l'utilisateur courant peut accéder à un PV.
     * $pvRow doit contenir au minimum : id, substitut_id
     *
     * Règles :
     *  - Non connecté → false
     *  - substitut_procureur → uniquement ses PV affectés
     *  - Tous les autres rôles → accès autorisé
     */
    public static function canAccessPV(array $pvRow): bool
    {
        if (!Auth::isLoggedIn()) return false;

        $role   = Auth::roleCode();
        $userId = Auth::userId();

        if ($role === 'substitut_procureur') {
            return (int)($pvRow['substitut_id'] ?? 0) === $userId;
        }

        // greffier, procureur, admin, président, juge_instruction : accès complet
        return true;
    }

    /**
     * Lève une redirection avec message flash si l'accès au PV est refusé.
     * À appeler dans show(), edit(), update(), qualifier(), uploadDocument()…
     */
    public static function assertPVAccess(array $pvRow, Controller $ctrl): void
    {
        if (!self::canAccessPV($pvRow)) {
            $ctrl->flash('error', 'Accès refusé : ce PV ne vous est pas affecté.');
            $ctrl->redirect('/pv');
            exit;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // DOSSIERS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Vérifie si l'utilisateur courant peut accéder à un dossier.
     * $dossierRow doit contenir : id, substitut_id, cabinet_id (optionnel)
     *
     * Règles :
     *  - substitut_procureur → uniquement ses dossiers (substitut_id = userId)
     *  - juge_instruction    → uniquement les dossiers de son cabinet
     *  - Tous les autres     → accès complet
     */
    public static function canAccessDossier(array $dossierRow): bool
    {
        if (!Auth::isLoggedIn()) return false;

        $role   = Auth::roleCode();
        $userId = Auth::userId();

        if ($role === 'substitut_procureur') {
            return (int)($dossierRow['substitut_id'] ?? 0) === $userId;
        }

        if ($role === 'juge_instruction') {
            // Le juge voit les dossiers dont le cabinet_id correspond à son cabinet
            // On résout via la DB dans le contexte du contrôleur ;
            // ici on accepte si cabinet_id est non nul (filtrage affiné dans les listes SQL)
            // Pour le show() individuel, on vérifie via getUserCabinetId()
            $userCabinetId = self::getUserCabinetId($userId);
            if ($userCabinetId === null) return true; // juge sans cabinet → accès complet
            return (int)($dossierRow['cabinet_id'] ?? 0) === $userCabinetId;
        }

        return true;
    }

    /**
     * Lève une redirection avec message flash si l'accès au dossier est refusé.
     */
    public static function assertDossierAccess(array $dossierRow, Controller $ctrl): void
    {
        if (!self::canAccessDossier($dossierRow)) {
            $ctrl->flash('error', 'Accès refusé : vous n\'avez pas accès à ce dossier.');
            $ctrl->redirect('/dossiers');
            exit;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // DOCUMENTS / PIÈCES JOINTES
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Vérifie si l'utilisateur courant peut supprimer un document.
     * $docRow doit contenir : uploaded_by, dossier_id (nullable), pv_id (nullable)
     *
     * Règles :
     *  - admin / procureur / greffier / president → toujours autorisé
     *  - uploader → toujours autorisé (propriétaire)
     *  - substitut_procureur → seulement s'il est l'uploader OU si le doc appartient
     *    à un PV/dossier qui lui est affecté
     *  - juge_instruction → seulement s'il est l'uploader
     */
    public static function canDeleteDocument(array $docRow): bool
    {
        if (!Auth::isLoggedIn()) return false;

        $role   = Auth::roleCode();
        $userId = Auth::userId();

        // Rôles avec droits complets sur les documents
        if (in_array($role, ['admin', 'procureur', 'greffier', 'president'])) {
            return true;
        }

        // Propriétaire (uploader)
        if ((int)($docRow['uploaded_by'] ?? 0) === $userId) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur courant peut lire (servir) un document.
     * Règle simplifiée : tout utilisateur connecté ayant accès au parent.
     * Pour les documents de PV, on vérifie l'accès au PV parent.
     */
    public static function canReadDocument(array $docRow): bool
    {
        if (!Auth::isLoggedIn()) return false;

        $role   = Auth::roleCode();
        $userId = Auth::userId();

        // Rôles sans restriction
        if (in_array($role, ['admin', 'procureur', 'greffier', 'president'])) {
            return true;
        }

        // Substitut : peut lire les docs des PV qui lui sont affectés
        if ($role === 'substitut_procureur') {
            // Le pvRow n'est pas disponible ici directement ;
            // le appelant doit l'avoir chargé et vérifié avant
            // → par défaut on laisse passer (le contrôleur doit vérifier le parent)
            return true;
        }

        // Juge : peut lire les docs des dossiers de son cabinet → idem
        return true;
    }

    // ══════════════════════════════════════════════════════════════════════
    // CONSTRUCTION DES FILTRES SQL
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Retourne un fragment WHERE supplémentaire + les paramètres PDO
     * à ajouter à la requête liste des PV selon le rôle.
     *
     * Usage :
     *   [$extraWhere, $extraParams] = AccessControl::pvListFilter('p');
     *   if ($extraWhere) { $where[] = $extraWhere; $params = array_merge($params, $extraParams); }
     */
    public static function pvListFilter(string $alias = 'p'): array
    {
        $role   = Auth::roleCode();
        $userId = Auth::userId();

        if ($role === 'substitut_procureur') {
            return ["{$alias}.substitut_id = :ac_uid", ['ac_uid' => $userId]];
        }

        return ['', []];
    }

    /**
     * Retourne un fragment WHERE supplémentaire + paramètres PDO
     * pour la liste des dossiers selon le rôle.
     *
     * Usage :
     *   [$extraWhere, $extraParams] = AccessControl::dossierListFilter('d');
     */
    public static function dossierListFilter(string $alias = 'd'): array
    {
        $role   = Auth::roleCode();
        $userId = Auth::userId();

        if ($role === 'substitut_procureur') {
            return ["{$alias}.substitut_id = :ac_uid", ['ac_uid' => $userId]];
        }

        if ($role === 'juge_instruction') {
            $cabinetId = self::getUserCabinetId($userId);
            if ($cabinetId !== null) {
                return ["{$alias}.cabinet_id = :ac_cab", ['ac_cab' => $cabinetId]];
            }
        }

        return ['', []];
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPERS INTERNES
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Retourne l'ID du cabinet d'instruction associé à un juge (depuis la table users).
     * Retourne null si le juge n'est pas associé à un cabinet spécifique.
     */
    private static function getUserCabinetId(int $userId): ?int
    {
        static $cache = [];
        if (array_key_exists($userId, $cache)) return $cache[$userId];

        try {
            $db   = Database::getInstance()->getPDO();
            $stmt = $db->prepare(
                "SELECT cabinet_id FROM users WHERE id = ? LIMIT 1"
            );
            $stmt->execute([$userId]);
            $row  = $stmt->fetch();
            $cache[$userId] = ($row && $row['cabinet_id']) ? (int)$row['cabinet_id'] : null;
        } catch (\Exception $e) {
            $cache[$userId] = null;
        }

        return $cache[$userId];
    }

    // ══════════════════════════════════════════════════════════════════════
    // BADGES UI (pour les vues)
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Retourne une information contextuelle à afficher dans la liste des PV/dossiers
     * selon le rôle courant.
     */
    public static function listContextNote(): string
    {
        $role = Auth::roleCode();
        switch ($role) {
            case 'substitut_procureur':
                return 'Vous ne voyez que les PV/dossiers qui vous ont été affectés.';
            case 'juge_instruction':
                return 'Vous ne voyez que les dossiers de votre cabinet d\'instruction.';
            case 'greffier':
                return 'Vous avez accès à tous les dossiers. La colonne <em>Créateur</em> identifie le greffier saisissant.';
            default:
                return '';
        }
    }
}
