<?php
/** @var array $primoIntervenants */
/** @var int   $page */
/** @var int   $totalPages */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-person-badge text-success me-2"></i>Primo intervenants</h2>
        <small class="text-muted"><a href="<?= BASE_URL ?>/config" class="text-decoration-none">Configuration</a> &rsaquo; Primo intervenants</small>
    </div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAjout">
        <i class="bi bi-plus-circle me-1"></i> Ajouter
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($primoIntervenants)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun primo intervenant enregistré.</td></tr>
                <?php else: foreach ($primoIntervenants as $i => $pi): ?>
                    <tr>
                        <td><?= (($page - 1) * 20) + $i + 1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($pi['nom']) ?></td>
                        <td><?= htmlspecialchars($pi['type'] ?? '—') ?></td>
                        <td class="text-muted small"><?= htmlspecialchars(mb_substr($pi['description'] ?? '', 0, 80)) ?><?= strlen($pi['description'] ?? '') > 80 ? '…' : '' ?></td>
                        <td><?= $pi['actif'] ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-warning me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEdit<?= $pi['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="<?= BASE_URL ?>/config/primo-intervenants/delete/<?= $pi['id'] ?>" class="d-inline"
                                onsubmit="return confirm('Supprimer ce primo intervenant ?')">
                                <?= CSRF::field() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $pi['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?= BASE_URL ?>/config/primo-intervenants/update/<?= $pi['id'] ?>">
                                    <?= CSRF::field() ?>
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($pi['nom']) ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Type</label>
                                            <input type="text" name="type" class="form-control" value="<?= htmlspecialchars($pi['type'] ?? '') ?>" placeholder="Ex: OPJ, Commissaire…">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($pi['description'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-success">Enregistrer</button>
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
            <form method="POST" action="<?= BASE_URL ?>/config/primo-intervenants/store">
                <?= CSRF::field() ?>
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouveau primo intervenant</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="type" class="form-control" placeholder="Ex: OPJ, Commissaire, Gendarme…">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
