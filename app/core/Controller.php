<?php
abstract class Controller {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getPDO();
    }

    protected function view(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);
        $viewPath = ROOT_PATH . '/app/views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new RuntimeException("Vue introuvable : {$view}");
        }
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        require ROOT_PATH . '/app/views/layouts/' . $layout . '.php';
    }

    protected function redirect(string $url): void {
        if (!str_starts_with($url, 'http')) {
            $url = BASE_URL . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    protected function json(mixed $data, int $code = 200): void {
        // Vider tout buffer de sortie en cours (évite les notices PHP qui corrompent le JSON)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function flash(string $type, string $message): void {
        $_SESSION['flash'][$type][] = $message;
    }

    protected function getFlash(): array {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    protected function input(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function sanitize(string $value): string {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function paginate(string $table, int $page = 1, int $perPage = 20, string $where = '', array $params = [], string $order = 'id DESC'): array {
        $offset = ($page - 1) * $perPage;
        $whereClause = $where ? "WHERE {$where}" : '';
        $total = $this->db->prepare("SELECT COUNT(*) FROM {$table} {$whereClause}");
        $total->execute($params);
        $totalRows = (int)$total->fetchColumn();
        $stmt = $this->db->prepare("SELECT * FROM {$table} {$whereClause} ORDER BY {$order} LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        return [
            'data'       => $stmt->fetchAll(),
            'total'      => $totalRows,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => ceil($totalRows / $perPage),
        ];
    }
}
