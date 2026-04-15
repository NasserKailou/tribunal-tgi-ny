<?php
class Auth {
    public static function login(array $user): void {
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'nom'      => $user['nom'],
            'prenom'   => $user['prenom'],
            'email'    => $user['email'],
            'role_id'  => $user['role_id'],
            'role_code'=> $user['role_code'],
            'role_lib' => $user['role_lib'],
            'matricule'=> $user['matricule'] ?? '',
        ];
        session_regenerate_id(true);
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user']['id']);
    }

    public static function currentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function userId(): ?int {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function roleCode(): string {
        return $_SESSION['user']['role_code'] ?? '';
    }

    public static function hasRole(string|array $roles): bool {
        if (!self::isLoggedIn()) return false;
        $roles = (array)$roles;
        return in_array(self::roleCode(), $roles);
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireRole(string|array $roles): void {
        self::requireLogin();
        if (!self::hasRole($roles)) {
            $_SESSION['flash']['error'][] = 'Accès refusé. Vous n\'avez pas les droits nécessaires.';
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    public static function isAdmin(): bool {
        return self::hasRole('admin');
    }

    public static function canEditDossier(): bool {
        return self::hasRole(['admin','greffier','substitut_procureur','procureur','juge_instruction','president']);
    }

    public static function canManageUsers(): bool {
        return self::hasRole(['admin','president']);
    }

    public static function canViewMap(): bool {
        return self::isLoggedIn();
    }
}
