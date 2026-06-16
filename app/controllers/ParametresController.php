<?php
/**
 * ParametresController — Gestion des paramètres du tribunal
 * Accès : admin uniquement
 */
class ParametresController extends Controller
{
    private function requireAdmin(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin']);
    }

    // ─── GET /config/parametres ──────────────────────────────────────────────
    public function index(): void
    {
        $this->requireAdmin();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();

        $rows = $this->db->query(
            "SELECT * FROM parametres_tribunal ORDER BY groupe, id"
        )->fetchAll();

        // Regrouper par groupe
        $params = [];
        foreach ($rows as $r) {
            $params[$r['groupe']][] = $r;
        }

        $groupeLabels = [
            'identite'      => 'Identité du tribunal',
            'documents'     => 'Documents & En-têtes',
            'delais'        => 'Délais métier',
            'numerotation'  => 'Numérotation',
            'affichage'     => 'Affichage',
        ];

        $pageTitle = 'Paramètres du tribunal';
        $this->view('config/parametres', compact('params','groupeLabels','flash','user','pageTitle'));
    }

    // ─── POST /config/parametres/save ────────────────────────────────────────
    public function save(): void
    {
        $this->requireAdmin();
        CSRF::check();

        $stmt = $this->db->prepare(
            "UPDATE parametres_tribunal SET valeur=:v, updated_by=:by WHERE cle=:cle"
        );

        foreach ($_POST as $cle => $valeur) {
            if (in_array($cle, ['_csrf','csrf_token'], true)) continue;
            $stmt->execute([
                ':v'   => trim($valeur),
                ':cle' => $cle,
                ':by'  => Auth::userId(),
            ]);
        }

        // Si la clé app_base_url est présente, écrire dans app_config.php
        if (isset($_POST['app_base_url'])) {
            $appUrl  = trim($_POST['app_base_url']);
            $content  = "<?php\n";
            $content .= "// Généré automatiquement depuis la page Paramètres — " . date('Y-m-d H:i:s') . "\n";
            if (!empty($appUrl)) {
                $escaped = addslashes(rtrim($appUrl, '/'));
                $content .= "define('APP_BASE_URL', '{$escaped}');\n";
            } else {
                $content .= "// APP_BASE_URL non définie — auto-détection activée\n";
            }
            $configFile = ROOT_PATH . DIRECTORY_SEPARATOR . 'app_config.php';
            @file_put_contents($configFile, $content);
        }

        $this->flash('success', 'Paramètres enregistrés avec succès.');
        $this->redirect('/config/parametres');
    }

    // ─── Helper statique : lire un paramètre ─────────────────────────────────
    public static function get(string $cle, string $default = ''): string
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $db    = Database::getInstance()->getPDO();
                $rows  = $db->query("SELECT cle, valeur FROM parametres_tribunal")->fetchAll();
                $cache = array_column($rows, 'valeur', 'cle');
            } catch (\Exception $e) {
                $cache = [];
            }
        }
        return $cache[$cle] ?? $default;
    }
}
