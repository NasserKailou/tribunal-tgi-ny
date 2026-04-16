<?php
define('APP_NAME', 'TGI-NY');
define('APP_FULL_NAME', 'Tribunal de Grande Instance Hors Classe de Niamey');
define('APP_VERSION', '1.0.0');

// ─── Détection automatique de BASE_URL (XAMPP sous-dossier OU vhost OU production) ───
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''); // Windows XAMPP fix

// SCRIPT_NAME = /tribunal-tgi-ny/public/index.php  =>  dirname = /tribunal-tgi-ny/public
$publicDir = rtrim(dirname($script), '/');

// Si on n'est pas dans un sous-dossier (vhost pointe direct sur /public)
// SCRIPT_NAME = /index.php => dirname = /
if ($publicDir === '' || $publicDir === '/') {
    define('BASE_URL', $protocol . '://' . $host);
} else {
    // Sous-dossier XAMPP: /tribunal-tgi-ny/public
    define('BASE_URL', $protocol . '://' . $host . $publicDir);
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
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
