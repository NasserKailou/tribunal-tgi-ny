<?php
// ROOT_PATH défini en premier, avant config.php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/app/config/config.php';
require_once ROOT_PATH . '/app/config/database.php';
require_once ROOT_PATH . '/app/core/Router.php';
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/helpers/Auth.php';
require_once ROOT_PATH . '/app/helpers/CSRF.php';
require_once ROOT_PATH . '/app/helpers/Numerotation.php';
require_once ROOT_PATH . '/app/helpers/Alerte.php';

// Autoloader
spl_autoload_register(function (string $class): void {
    $paths = [
        ROOT_PATH . '/app/controllers/' . $class . '.php',
        ROOT_PATH . '/app/models/'      . $class . '.php',
        ROOT_PATH . '/app/core/'        . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Vérifier les alertes à chaque requête (si connecté)
if (Auth::isLoggedIn()) {
    try {
        $alerteHelper = new AlerteHelper(Database::getInstance()->getPDO());
        $alerteHelper->verifierEtCreerAlertes();
    } catch (Exception $e) {
        // Silencieux en production
    }
}

$router = new Router();

// Auth
$router->get('/login',    'AuthController@loginForm');
$router->post('/login',   'AuthController@login');
$router->get('/logout',   'AuthController@logout');

// Dashboard
$router->get('/',          'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');
$router->get('/api/dashboard-stats', 'DashboardController@apiStats');

// PV
$router->get('/pv',                    'PVController@index');
$router->get('/pv/create',             'PVController@create');
$router->post('/pv/store',             'PVController@store');
$router->get('/pv/show/{id}',          'PVController@show');
$router->get('/pv/edit/{id}',          'PVController@edit');
$router->post('/pv/update/{id}',       'PVController@update');
$router->post('/pv/affecter/{id}',     'PVController@affecter');
$router->post('/pv/classer/{id}',      'PVController@classer');
$router->post('/pv/transferer/{id}',   'PVController@transferer');
$router->get('/api/departements/{region_id}', 'PVController@apiDepartements');
$router->get('/api/communes/{departement_id}', 'PVController@apiCommunes');

// Dossiers
$router->get('/dossiers',                        'DossierController@index');
$router->get('/dossiers/create',                 'DossierController@create');
$router->post('/dossiers/store',                 'DossierController@store');
$router->get('/dossiers/show/{id}',              'DossierController@show');
$router->get('/dossiers/edit/{id}',              'DossierController@edit');
$router->post('/dossiers/update/{id}',           'DossierController@update');
$router->post('/dossiers/affecter-instruction/{id}', 'DossierController@affecterInstruction');
$router->post('/dossiers/envoyer-audience/{id}', 'DossierController@envoyerAudience');
$router->post('/dossiers/partie/add/{id}',       'DossierController@addPartie');
$router->post('/dossiers/partie/delete/{id}',    'DossierController@deletePartie');

// Audiences
$router->get('/audiences',                    'AudienceController@index');
$router->get('/audiences/create',             'AudienceController@create');
$router->post('/audiences/store',             'AudienceController@store');
$router->get('/audiences/show/{id}',          'AudienceController@show');
$router->post('/audiences/update-statut/{id}','AudienceController@updateStatut');
$router->get('/audiences/calendrier',         'AudienceController@calendrier');
$router->get('/api/audiences-calendrier',     'AudienceController@apiCalendrier');

// Jugements
$router->get('/jugements',                    'JugementController@index');
$router->get('/jugements/create/{dossier_id}','JugementController@create');
$router->post('/jugements/store',             'JugementController@store');
$router->get('/jugements/show/{id}',          'JugementController@show');
$router->post('/jugements/appel/{id}',        'JugementController@enregistrerAppel');

// Détenus
$router->get('/detenus',              'DetenusController@index');
$router->get('/detenus/create',       'DetenusController@create');
$router->post('/detenus/store',       'DetenusController@store');
$router->get('/detenus/show/{id}',    'DetenusController@show');
$router->get('/detenus/edit/{id}',    'DetenusController@edit');
$router->post('/detenus/update/{id}', 'DetenusController@update');
$router->post('/detenus/liberer/{id}','DetenusController@liberer');
$router->get('/detenus/stats',        'DetenusController@stats');

// Carte antiterroriste
$router->get('/carte',          'CarteController@index');
$router->get('/api/carte-data', 'CarteController@apiData');

// Exports PDF/Impression
$router->get('/export/jugement/{id}', 'ExportController@jugement');
$router->get('/export/pv/{id}',       'ExportController@pv');
$router->get('/export/dossier/{id}',  'ExportController@dossier');

// Alertes
$router->get('/alertes',               'AlerteController@index');
$router->post('/alertes/lire/{id}',    'AlerteController@marquerLue');
$router->post('/alertes/lire-tout',    'AlerteController@marquerToutLu');
$router->get('/api/alertes-count',     'AlerteController@apiCount');

// Utilisateurs (admin)
$router->get('/users',               'UserController@index');
$router->get('/users/create',        'UserController@create');
$router->post('/users/store',        'UserController@store');
$router->get('/users/edit/{id}',     'UserController@edit');
$router->post('/users/update/{id}',  'UserController@update');
$router->post('/users/toggle/{id}',  'UserController@toggle');

$router->dispatch();
