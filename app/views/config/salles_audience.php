<?php
/** @var array $salles */
/** @var int   $page */
/** @var int   $totalPages */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-columns-gap me-2" style="color:#b8860b"></i>Salles d'audience</h2>
        <small class="text-muted"><a href="<?= BASE_URL ?>/config" class="text-decoration-none">Configuration</a> &rsaquo; Salles d'audience</small>
    </div>
    <button class="btn" style="background:#b8860b;color:#fff" data-bs-toggle="modal" data-bs-target="#modalAjout">
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
                        <th>Nom de la salle</th>
                        <th>Capacité</th>
                        <th>Équipements</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($salles)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune salle d'audience enregistrée.</td></tr>
                <?php else: foreach ($salles as $i => $s): ?>
                    <tr>
                        <td><?= (($page - 1) * 20) + $i + 1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($s['nom']) ?></td>
                        <td><span class="badge bg-dark"><?= number_format($s['capacite']) ?> places</span></td>
                        <td class="text-muted small"><?= htmlspecialchars(mb_substr($s['description'] ?? '', 0, 80)) ?><?= strlen($s['description'] ?? '') > 80 ? '…' : '' ?></td>
                        <td><?= $s['actif'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-warning me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEdit<?= $s['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="<?= BASE_URL ?>/config/salles-audience/delete/<?= $s['id'] ?>" class="d-inline"
                                onsubmit="return confirm('Supprimer cette salle ?')">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $s['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?= BASE_URL ?>/config/salles-audience/update/<?= $s['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier la salle</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($s['nom']) ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Capacité (places) <span class="text-danger">*</span></label>
                                            <input type="number" name="capacite" class="form-control" required min="1" value="<?= $s['capacite'] ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Équipements / Description</label>
                                            <textarea name="equipements" class="form-control" rows="3"><?= htmlspecialchars($s['description'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn" style="background:#b8860b;color:#fff">Enregistrer</button>
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
            <form method="POST" action="<?= BASE_URL ?>/config/salles-audience/store">
                <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                <div class="modal-header text-white" style="background:#b8860b">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle salle d'audience</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom de la salle <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" required placeholder="Ex: Salle Correctionnelle 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacité (places) <span class="text-danger">*</span></label>
                        <input type="number" name="capacite" class="form-control" required min="1" value="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Équipements / Description</label>
                        <textarea name="equipements" class="form-control" rows="3" placeholder="Ex: Écran, vidéoprojecteur, sono…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn text-white" style="background:#b8860b"><i class="bi bi-check-lg me-1"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
