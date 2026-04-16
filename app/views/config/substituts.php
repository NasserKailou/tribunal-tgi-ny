<?php
/** @var array $substituts */
/** @var array $roleSubstitut */
/** @var int   $page */
/** @var int   $totalPages */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-person-lines-fill text-info me-2"></i>Substituts du procureur</h2>
        <small class="text-muted"><a href="<?= BASE_URL ?>/config" class="text-decoration-none">Configuration</a> &rsaquo; Substituts</small>
    </div>
    <button class="btn btn-info text-dark" data-bs-toggle="modal" data-bs-target="#modalAjout">
        <i class="bi bi-person-plus me-1"></i> Ajouter
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Matricule</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($substituts)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun substitut enregistré.</td></tr>
                <?php else: foreach ($substituts as $i => $s): ?>
                    <tr>
                        <td><?= (($page - 1) * 20) + $i + 1 ?></td>
                        <td><code><?= htmlspecialchars($s['matricule'] ?? '—') ?></code></td>
                        <td class="fw-semibold"><?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars($s['telephone'] ?? '—') ?></td>
                        <td><?= $s['actif'] ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-info me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEdit<?= $s['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="<?= BASE_URL ?>/config/substituts/delete/<?= $s['id'] ?>" class="d-inline"
                                onsubmit="return confirm('Supprimer ce substitut ? Cette action est irréversible.')">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $s['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?= BASE_URL ?>/config/substituts/update/<?= $s['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier le substitut</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                                <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($s['nom']) ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                                <input type="text" name="prenom" class="form-control" required value="<?= htmlspecialchars($s['prenom']) ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Téléphone</label>
                                                <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($s['telephone'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Matricule</label>
                                                <input type="text" name="matricule" class="form-control" value="<?= htmlspecialchars($s['matricule'] ?? '') ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Nouveau mot de passe <small class="text-muted">(laisser vide pour ne pas changer)</small></label>
                                                <input type="password" name="password" class="form-control" autocomplete="new-password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-info text-dark">Enregistrer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-end">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- Modal Ajout -->
<div class="modal fade" id="modalAjout" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/config/substituts/store">
                <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nouveau substitut du procureur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" placeholder="+227 XX XX XX XX">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Matricule</label>
                            <input type="text" name="matricule" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info text-dark"><i class="bi bi-check-lg me-1"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
