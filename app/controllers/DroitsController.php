<?php
/**
 * DroitsController — Gestion des droits utilisateurs par menu et fonctionnalité
 * Accès : admin uniquement
 */
class DroitsController extends Controller
{
    private function requireAdmin(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin']);
    }

    // ─── GET /admin/droits ────────────────────────────────────────────────────
    public function index(): void
    {
        $this->requireAdmin();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();

        $users = $this->db->query(
            "SELECT u.id, u.nom, u.prenom, u.email, u.actif, r.libelle AS role_lib, r.code AS role_code
             FROM users u
             JOIN roles r ON u.role_id = r.id
             ORDER BY r.id, u.nom"
        )->fetchAll();

        $menus = $this->db->query(
            "SELECT * FROM menus ORDER BY ordre"
        )->fetchAll();

        $pageTitle = 'Gestion des droits';
        $this->view('admin/droits', compact('users','menus','flash','user','pageTitle'));
    }

    // ─── GET /admin/droits/user/{id} ─────────────────────────────────────────
    public function editUser(string $userId): void
    {
        $this->requireAdmin();
        $flash = $this->getFlash();
        $currentUser = Auth::currentUser();

        $userId = (int)$userId;

        $targetUser = $this->db->prepare(
            "SELECT u.*, r.libelle AS role_lib, r.code AS role_code
             FROM users u JOIN roles r ON u.role_id=r.id
             WHERE u.id=?"
        );
        $targetUser->execute([$userId]);
        $targetUser = $targetUser->fetch();

        if (!$targetUser) {
            $this->flash('error', 'Utilisateur introuvable.');
            $this->redirect('/admin/droits');
        }

        // Menus + fonctionnalités
        $menus = $this->db->query(
            "SELECT m.*, GROUP_CONCAT(f.id ORDER BY f.id SEPARATOR ',') AS fonc_ids
             FROM menus m
             LEFT JOIN fonctionnalites f ON f.menu_id = m.id AND f.actif=1
             WHERE m.actif=1
             GROUP BY m.id
             ORDER BY m.ordre"
        )->fetchAll();

        $fonctionnalites = $this->db->query(
            "SELECT f.*, m.libelle AS menu_libelle
             FROM fonctionnalites f
             JOIN menus m ON f.menu_id = m.id
             WHERE f.actif=1
             ORDER BY m.ordre, f.id"
        )->fetchAll();

        // Droits actuels
        $droitsMenus = $this->db->prepare(
            "SELECT menu_id FROM droits_utilisateurs
             WHERE user_id=? AND menu_id IS NOT NULL AND accorde=1"
        );
        $droitsMenus->execute([$userId]);
        $droitsMenusIds = array_column($droitsMenus->fetchAll(), 'menu_id');

        $droitsFoncs = $this->db->prepare(
            "SELECT fonctionnalite_id FROM droits_utilisateurs
             WHERE user_id=? AND fonctionnalite_id IS NOT NULL AND accorde=1"
        );
        $droitsFoncs->execute([$userId]);
        $droitsFoncsIds = array_column($droitsFoncs->fetchAll(), 'fonctionnalite_id');

        $pageTitle = 'Droits — ' . $targetUser['prenom'] . ' ' . $targetUser['nom'];
        $this->view('admin/droits_edit', compact(
            'targetUser','menus','fonctionnalites',
            'droitsMenusIds','droitsFoncsIds',
            'flash','currentUser','pageTitle'
        ));
    }

    // ─── POST /admin/droits/save/{userId} ────────────────────────────────────
    public function saveUser(string $userId): void
    {
        $this->requireAdmin();
        CSRF::check();

        $userId = (int)$userId;

        // Supprimer tous les droits existants pour cet utilisateur
        $this->db->prepare("DELETE FROM droits_utilisateurs WHERE user_id=?")
                 ->execute([$userId]);

        // Réinscrire les droits cochés
        $stmtMenu = $this->db->prepare(
            "INSERT INTO droits_utilisateurs (user_id, menu_id, accorde, accorde_par)
             VALUES (:uid, :mid, 1, :by)"
        );
        $stmtFonc = $this->db->prepare(
            "INSERT INTO droits_utilisateurs (user_id, fonctionnalite_id, accorde, accorde_par)
             VALUES (:uid, :fid, 1, :by)"
        );

        $menusCoches = $_POST['menus'] ?? [];
        foreach ($menusCoches as $menuId) {
            $stmtMenu->execute([':uid' => $userId, ':mid' => (int)$menuId, ':by' => Auth::userId()]);
        }

        $foncsCoches = $_POST['fonctionnalites'] ?? [];
        foreach ($foncsCoches as $foncId) {
            $stmtFonc->execute([':uid' => $userId, ':fid' => (int)$foncId, ':by' => Auth::userId()]);
        }

        $this->flash('success', 'Droits mis à jour avec succès.');
        $this->redirect('/admin/droits');
    }

    // ─── Méthodes statiques pour vérification des droits ──────────────────
    public static function hasMenuAccess(int $userId, string $menuCode): bool
    {
        static $cache = [];
        $key = $userId . '_' . $menuCode;
        if (!isset($cache[$key])) {
            try {
                $db   = Database::getInstance()->getPDO();
                $stmt = $db->prepare(
                    "SELECT d.accorde FROM droits_utilisateurs d
                     JOIN menus m ON m.id = d.menu_id
                     WHERE d.user_id=? AND m.code=? AND d.menu_id IS NOT NULL
                     LIMIT 1"
                );
                $stmt->execute([$userId, $menuCode]);
                $row = $stmt->fetch();
                // Si aucune ligne → pas de restriction spécifique → accès par défaut autorisé
                $cache[$key] = ($row === false) ? true : (bool)$row['accorde'];
            } catch (\Exception $e) {
                $cache[$key] = true;
            }
        }
        return $cache[$key];
    }

    public static function hasFuncAccess(int $userId, string $funcCode): bool
    {
        static $cache = [];
        $key = $userId . '_' . $funcCode;
        if (!isset($cache[$key])) {
            try {
                $db   = Database::getInstance()->getPDO();
                $stmt = $db->prepare(
                    "SELECT d.accorde FROM droits_utilisateurs d
                     JOIN fonctionnalites f ON f.id = d.fonctionnalite_id
                     WHERE d.user_id=? AND f.code=? AND d.fonctionnalite_id IS NOT NULL
                     LIMIT 1"
                );
                $stmt->execute([$userId, $funcCode]);
                $row = $stmt->fetch();
                $cache[$key] = ($row === false) ? true : (bool)$row['accorde'];
            } catch (\Exception $e) {
                $cache[$key] = true;
            }
        }
        return $cache[$key];
    }
}
