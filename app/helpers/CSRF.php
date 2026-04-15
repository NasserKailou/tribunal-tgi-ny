<?php
class CSRF {
    public static function generate(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verify(?string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) return false;
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function field(): string {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::generate(), ENT_QUOTES) . '">';
    }

    public static function check(): void {
        if (!self::verify($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            die('Token CSRF invalide. Veuillez actualiser et réessayer.');
        }
    }
}
