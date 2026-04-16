<?php
/** @var array $unitesEnquete */
/** @var int   $page */
/** @var int   $totalPages */
$types = [
    'commissariat'  => 'Commissariat de police',
    'brigade_police'=> 'Brigade de police',
    'gendarmerie'   => 'Gendarmerie',
    'unite_speciale'=> 'Unité spéciale',
    'autre'         => 'Autre',
];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-shield-check text-warning me-2"></i>Unités d'enquête</h2>
        <small class="text-muted"><a href="<?= BASE_URL ?>/config" class="text-decoration-none">Configuration</a> &rsaquo; Unités d'enquête</small>
    </div>
    <button class="btn btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalAjout">
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
                        <th>Contact</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($unitesEnquete)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune unité d'enquête enregistrée.</td></tr>
                <?php else: foreach ($unitesEnquete as $i => $u): ?>
                    <tr>
                        <td><?= (($page - 1) * 20) + $i + 1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($u['nom']) ?></td>
                        <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($types[$u['type']] ?? $u['type']) ?></span></td>
                        <td><?= htmlspecialchars($u['telephone'] ?? '—') ?></td>
                        <td><?= $u['actif'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-warning me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEdit<?= $u['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="<?= BASE_URL ?>/config/unites-enquete/delete/<?= $u['id'] ?>" class="d-inline"
                                onsubmit="return confirm('Supprimer cette unité d\'enquête ?')">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $u['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?= BASE_URL ?>/config/unites-enquete/update/<?= $u['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier l'unité</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($u['nom']) ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Type <span class="text-danger">*</span></label>
                                            <select name="type" class="form-select" required>
                                                <?php foreach ($types as $k => $v): ?>
                                                    <option value="<?= $k ?>" <?= $u['type'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Contact / Téléphone</label>
                                            <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($u['telephone'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-warning text-dark">Enregistrer</button>
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
            <form method="POST" action="<?= BASE_URL ?>/config/unites-enquete/store">
                <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle unité d'enquête</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" required placeholder="Ex: Commissariat Central de Niamey">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">— Sélectionner —</option>
                            <?php foreach ($types as $k => $v): ?>
                                <option value="<?= $k ?>"><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact / Téléphone</label>
                        <input type="text" name="contact" class="form-control" placeholder="+227 20 XX XX XX">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-check-lg me-1"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
