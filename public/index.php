<?php
// Buffer de sortie global — protège les réponses JSON des notices PHP
ob_start();

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
$router->post('/dossiers/classer/{id}',         'DossierController@classerDossier');
$router->post('/dossiers/declasser/{id}',       'DossierController@declasserDossier');


// Mandats
$router->get('/mandats',                      'MandatController@index');
$router->get('/mandats/create',               'MandatController@create');
$router->post('/mandats/store',               'MandatController@store');
$router->get('/mandats/show/{id}',            'MandatController@show');
$router->get('/mandats/print/{id}',           'MandatController@printMandat');
$router->post('/mandats/update-statut/{id}',  'MandatController@updateStatut');
$router->get('/api/mandat-person-search',     'MandatController@apiSearch');

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
$router->get('/detenus/etat/{id}',     'DetenusController@etat');
$router->get('/detenus/show/{id}',    'DetenusController@show');
$router->get('/detenus/edit/{id}',    'DetenusController@edit');
$router->post('/detenus/update/{id}', 'DetenusController@update');
$router->post('/detenus/liberer/{id}','DetenusController@liberer');
$router->get('/detenus/stats',        'DetenusController@stats');
$router->get('/api/detenus/search',   'DetenusController@apiSearch');

// Carte antiterroriste
$router->get('/carte',          'CarteController@index');
$router->get('/api/carte-data', 'CarteController@apiData');
$router->get('/carte/data',     'CarteController@apiData');

// Exports PDF/Impression
$router->get('/export/jugement/{id}', 'ExportController@jugement');
$router->get('/export/pv/{id}',       'ExportController@pv');
$router->get('/export/dossier/{id}',  'ExportController@dossier');

// Alertes
$router->get('/alertes',               'AlerteController@index');
$router->post('/alertes/lire/{id}',    'AlerteController@marquerLue');
$router->post('/alertes/lire-tout',    'AlerteController@marquerToutLu');
$router->get('/api/alertes-count',     'AlerteController@apiCount');

// Documents / Pièces jointes
$router->post('/documents/upload/{dossierId}',  'DocumentController@upload');
$router->post('/documents/delete/{id}',          'DocumentController@delete');
$router->get('/documents/view/{id}',             'DocumentController@view');
$router->get('/documents/list/{dossierId}',      'DocumentController@list');

// Utilisateurs (admin)
$router->get('/users',               'UserController@index');
$router->get('/users/create',        'UserController@create');
$router->post('/users/store',        'UserController@store');
$router->get('/users/edit/{id}',     'UserController@edit');
$router->post('/users/update/{id}',  'UserController@update');
$router->post('/users/toggle/{id}',  'UserController@toggle');

// Configuration (admin / procureur)
$router->get('/config',                                  'ConfigController@index');

$router->get('/config/cabinets',                         'ConfigController@cabinets');
$router->post('/config/cabinets/store',                  'ConfigController@cabinetStore');
$router->post('/config/cabinets/update/{id}',            'ConfigController@cabinetUpdate');
$router->post('/config/cabinets/delete/{id}',            'ConfigController@cabinetDelete');
$router->get('/config/cabinets/dossiers/{id}',           'ConfigController@cabinetDossiers');

$router->get('/config/primo-intervenants',               'ConfigController@primoIntervenants');
$router->post('/config/primo-intervenants/store',        'ConfigController@primoIntervenantStore');
$router->post('/config/primo-intervenants/update/{id}',  'ConfigController@primoIntervenantUpdate');
$router->post('/config/primo-intervenants/delete/{id}',  'ConfigController@primoIntervenantDelete');

$router->get('/config/unites-enquete',                   'ConfigController@unitesEnquete');
$router->post('/config/unites-enquete/store',            'ConfigController@uniteEnqueteStore');
$router->post('/config/unites-enquete/update/{id}',      'ConfigController@uniteEnqueteUpdate');
$router->post('/config/unites-enquete/delete/{id}',      'ConfigController@uniteEnqueteDelete');

$router->get('/config/substituts',                       'ConfigController@substituts');
$router->post('/config/substituts/store',                'ConfigController@substitutStore');
$router->post('/config/substituts/update/{id}',          'ConfigController@substitutUpdate');
$router->post('/config/substituts/delete/{id}',          'ConfigController@substitutDelete');
$router->get('/config/substituts/dossiers/{id}',         'ConfigController@substitutDossiers');

$router->get('/config/infractions',                      'ConfigController@infractions');
$router->post('/config/infractions/store',               'ConfigController@infractionStore');
$router->post('/config/infractions/update/{id}',         'ConfigController@infractionUpdate');
$router->post('/config/infractions/delete/{id}',         'ConfigController@infractionDelete');

$router->get('/config/maisons-arret',                    'ConfigController@maisonsArret');
$router->post('/config/maisons-arret/store',             'ConfigController@maisonArretStore');
$router->post('/config/maisons-arret/update/{id}',       'ConfigController@maisonArretUpdate');
$router->post('/config/maisons-arret/delete/{id}',       'ConfigController@maisonArretDelete');
$router->get('/config/maisons-arret/stats/{id}',         'ConfigController@maisonArretStats');

$router->get('/config/membres-audience',                 'ConfigController@membresAudience');
$router->get('/config/salles-audience',                  'ConfigController@sallesAudience');
$router->post('/config/salles-audience/store',           'ConfigController@salleAudienceStore');
$router->post('/config/salles-audience/update/{id}',     'ConfigController@salleAudienceUpdate');
$router->post('/config/salles-audience/delete/{id}',     'ConfigController@salleAudienceDelete');

// Paramètres du tribunal
$router->get('/config/parametres',                       'ParametresController@index');
$router->post('/config/parametres/save',                 'ParametresController@save');

// Gestion des droits utilisateurs
$router->get('/admin/droits',                            'DroitsController@index');
$router->get('/admin/droits/user/{id}',                  'DroitsController@editUser');
$router->post('/admin/droits/save/{userId}',             'DroitsController@saveUser');

// API cabinets & substituts charge
$router->get('/api/cabinets/charge',                     'ConfigController@apiCabinetsCharge');
$router->get('/api/substituts/charge',                   'ConfigController@apiSubstitutsCharge');

// PV déclassement
$router->post('/pv/declasser/{id}',                      'PVController@declasser');

$router->dispatch();

// Envoyer la réponse HTML bufferisée (les réponses JSON ont déjà appelé exit)
if (ob_get_level() > 0) {
    ob_end_flush();
}
