<?php
define('APP_NAME', 'TGI-NY');
define('APP_FULL_NAME', 'Tribunal de Grande Instance Hors Classe de Niamey');
define('APP_VERSION', '1.0.1');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

// ─── Fichier de configuration locale (optionnel) ────────────────────────────
// Créer ROOT_PATH/app_config.php pour surcharger les paramètres locaux
// Exemple : <?php define('APP_BASE_URL', 'http://monserveur.com/tgi');
$localConfig = ROOT_PATH . DIRECTORY_SEPARATOR . 'app_config.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

// ─── Détection / surcharge de BASE_URL ──────────────────────────────────────
if (!defined('BASE_URL')) {
    if (defined('APP_BASE_URL') && !empty(APP_BASE_URL)) {
        // Priorité 1 : valeur définie manuellement dans app_config.php
        define('BASE_URL', rtrim(APP_BASE_URL, '/'));
    } else {
        // Priorité 2 : auto-détection depuis $_SERVER
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script   = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''); // Windows XAMPP fix

        // SCRIPT_NAME = /tribunal-tgi-ny/public/index.php => dirname = /tribunal-tgi-ny/public
        $publicDir = rtrim(dirname($script), '/');

        // Si on n'est pas dans un sous-dossier (vhost pointe direct sur /public)
        if ($publicDir === '' || $publicDir === '/') {
            define('BASE_URL', $protocol . '://' . $host);
        } else {
            // Sous-dossier XAMPP : /tribunal-tgi-ny/public
            define('BASE_URL', $protocol . '://' . $host . $publicDir);
        }
    }
}

define('UPLOAD_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOAD_URL',  BASE_URL . '/uploads/');

// Délais métier (jours / mois)
define('DELAI_TRAITEMENT_PV_JOURS',      30);
define('DELAI_INSTRUCTION_MOIS',          6);
define('DELAI_ALERTE_AUDIENCE_JOURS',     3);
define('DELAI_APPEL_JOURS',              30);
define('DELAI_DETENTION_PROVISOIRE_MOIS', 6);

// Upload
define('MAX_UPLOAD_SIZE',    10 * 1024 * 1024); // 10 MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/niger_geo.php';
