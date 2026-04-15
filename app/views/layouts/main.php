<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body>
<?php
$currentUser = Auth::currentUser();
$alerteHelper = new AlerteHelper(Database::getInstance()->getPDO());
$nbAlertes = $alerteHelper->countUnread($currentUser['id'] ?? null);
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($scriptDir && str_starts_with($currentPath, $scriptDir)) {
    $currentPath = substr($currentPath, strlen($scriptDir));
}
$currentPath = '/' . ltrim($currentPath, '/');
function isActive(string $prefix, string $currentPath): string {
    return str_starts_with($currentPath, $prefix) ? 'active' : '';
}
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-balance-scale me-2"></i>
        <div>
            <div class="fw-bold" style="font-size:0.85rem">TGI-NY</div>
            <div style="font-size:0.7rem;opacity:0.7">Tribunal de Grande Instance</div>
        </div>
    </div>
    <nav class="sidebar-nav mt-2">
        <a href="<?= BASE_URL ?>/dashboard" class="sidebar-link <?= isActive('/dashboard', $currentPath) ?: ($currentPath === '/' ? 'active' : '') ?>">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <div class="sidebar-section">Judiciaire</div>
        <a href="<?= BASE_URL ?>/pv" class="sidebar-link <?= isActive('/pv', $currentPath) ?>">
            <i class="bi bi-file-text"></i> Procès-Verbaux
        </a>
        <a href="<?= BASE_URL ?>/dossiers" class="sidebar-link <?= isActive('/dossiers', $currentPath) ?>">
            <i class="bi bi-folder2-open"></i> Dossiers
        </a>
        <a href="<?= BASE_URL ?>/audiences" class="sidebar-link <?= isActive('/audiences', $currentPath) ?>">
            <i class="bi bi-calendar-week"></i> Audiences
        </a>
        <a href="<?= BASE_URL ?>/jugements" class="sidebar-link <?= isActive('/jugements', $currentPath) ?>">
            <i class="bi bi-hammer"></i> Jugements
        </a>
        <div class="sidebar-section">Détention</div>
        <a href="<?= BASE_URL ?>/detenus" class="sidebar-link <?= isActive('/detenus', $currentPath) ?>">
            <i class="bi bi-person-lock"></i> Population Carcérale
        </a>
        <div class="sidebar-section">Sécurité</div>
        <a href="<?= BASE_URL ?>/carte" class="sidebar-link <?= isActive('/carte', $currentPath) ?>">
            <i class="bi bi-map"></i> Carte Antiterroriste
        </a>
        <div class="sidebar-section">Système</div>
        <a href="<?= BASE_URL ?>/alertes" class="sidebar-link <?= isActive('/alertes', $currentPath) ?> d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bell"></i> Alertes</span>
            <?php if ($nbAlertes > 0): ?>
            <span class="badge bg-danger"><?= $nbAlertes ?></span>
            <?php endif; ?>
        </a>
        <?php if (Auth::hasRole(['admin','president'])): ?>
        <a href="<?= BASE_URL ?>/users" class="sidebar-link <?= isActive('/users', $currentPath) ?>">
            <i class="bi bi-people"></i> Utilisateurs
        </a>
        <?php endif; ?>
    </nav>
</div>

<!-- Contenu principal -->
<div class="main-content" id="mainContent">
    <!-- Navbar -->
    <nav class="top-navbar">
        <button class="btn btn-sm btn-outline-secondary me-2" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <span class="navbar-brand-text d-none d-md-block">
            <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : APP_FULL_NAME ?>
        </span>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>/alertes" class="btn btn-sm btn-outline-warning position-relative">
                <i class="bi bi-bell"></i>
                <?php if ($nbAlertes > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $nbAlertes ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light dropdown-toggle user-btn" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($currentUser['role_lib']) ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash messages -->
    <div class="px-4 pt-3">
    <?php $flash = $flash ?? []; foreach (['success','error','warning','info'] as $ftype): ?>
        <?php if (!empty($flash[$ftype])): foreach ($flash[$ftype] as $msg): ?>
        <div class="alert alert-<?= $ftype === 'error' ? 'danger' : $ftype ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= $ftype === 'success' ? 'check-circle' : ($ftype === 'error' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endforeach; endif; ?>
    <?php endforeach; ?>
    </div>

    <!-- Page content -->
    <div class="page-content px-4 pb-4">
        <?= $content ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
