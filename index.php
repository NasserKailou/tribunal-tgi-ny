<?php
/**
 * Point d'entrée racine — Redirige automatiquement vers /public/
 * Pour XAMPP : http://localhost/tribunal-tgi-ny/  => redirige vers /tribunal-tgi-ny/public/
 */
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script    = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$baseDir   = rtrim(dirname($script), '/');

// Redirect to /public/login
header('Location: ' . $protocol . '://' . $host . $baseDir . '/public/login');
exit;
