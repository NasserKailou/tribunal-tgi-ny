<?php
/**
 * vue admin/droits.php — Liste des utilisateurs pour gérer les droits
 * @var array $users
 * @var array $menus
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-shield-lock text-primary me-2"></i>Gestion des droits</h2>
        <small class="text-muted">Administration &rsaquo; Droits utilisateurs</small>
    </div>
</div>

<div class="alert alert-info alert-sm py-2">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Note :</strong> Par défaut, les droits sont hérités du rôle de l'utilisateur.
    Les droits individuels permettent d'accorder ou de restreindre l'accès à des menus ou fonctionnalités spécifiques.
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-people me-2 text-primary"></i>
        <strong>Utilisateurs</strong>
        <span class="badge bg-secondary ms-2"><?= count($users) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th class="text-end">Droits</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></div>
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?= htmlspecialchars($u['role_lib']) ?></span>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <?= $u['actif']
                            ? '<span class="badge bg-success">Actif</span>'
                            : '<span class="badge bg-danger">Inactif</span>' ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>/admin/droits/user/<?= $u['id'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-shield-check me-1"></i>Gérer les droits
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
