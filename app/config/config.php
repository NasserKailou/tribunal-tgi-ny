<?php
define('APP_NAME', 'TGI-NY');
define('APP_FULL_NAME', 'Tribunal de Grande Instance Hors Classe de Niamey');
define('APP_VERSION', '1.0.0');

// Détecter l'URL de base automatiquement
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base = rtrim(dirname($script), '/');
// Si on est dans /public, remonter d'un niveau pour la base
if (str_ends_with($base, '/public')) {
    $base = substr($base, 0, -7);
}
define('BASE_URL', $protocol . '://' . $host . $base . '/public');
define('ROOT_PATH', dirname(__DIR__, 2));
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Délais métier
define('DELAI_TRAITEMENT_PV_JOURS', 30);
define('DELAI_INSTRUCTION_MOIS', 6);
define('DELAI_ALERTE_AUDIENCE_JOURS', 3);
define('DELAI_APPEL_JOURS', 30);
define('DELAI_DETENTION_PROVISOIRE_MOIS', 6);

// Taille max upload
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
