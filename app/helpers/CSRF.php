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

    /**
     * Champ caché unifié : name="_csrf" (compatible AJAX et formulaires standards)
     */
    public static function field(): string {
        $token = htmlspecialchars(self::generate(), ENT_QUOTES);
        // Double champ pour compatibilité : _csrf ET csrf_token
        return '<input type="hidden" name="_csrf" value="' . $token . '">'
             . '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Vérification unifiée : accepte _csrf OU csrf_token
     */
    public static function check(): void {
        $token = $_POST['_csrf'] ?? $_POST['csrf_token'] ?? null;
        if (!self::verify($token)) {
            http_response_code(403);
            die('Token CSRF invalide. Veuillez actualiser et réessayer.');
        }
    }
}
