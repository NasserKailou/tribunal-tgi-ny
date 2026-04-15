<?php $pageTitle = 'Utilisateurs'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Gestion des utilisateurs</h4>
    <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Nouvel utilisateur</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Matricule</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td class="fw-semibold"><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($u['role_lib']) ?></span></td>
                <td class="small"><?= htmlspecialchars($u['matricule'] ?? '—') ?></td>
                <td><?= $u['actif'] ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-danger">Inactif</span>' ?></td>
                <td class="text-end d-flex gap-1 justify-content-end">
                    <a href="<?= BASE_URL ?>/users/edit/<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    <?php if ($u['id'] !== Auth::userId()): ?>
                    <form method="POST" action="<?= BASE_URL ?>/users/toggle/<?= $u['id'] ?>">
                        <?= CSRF::field() ?>
                        <button type="submit" class="btn btn-sm <?= $u['actif'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= $u['actif'] ? 'Désactiver' : 'Activer' ?>">
                            <i class="bi bi-<?= $u['actif'] ? 'person-dash' : 'person-check' ?>"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
