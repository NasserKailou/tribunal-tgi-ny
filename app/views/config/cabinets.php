<?php
/** @var array $cabinets */
/** @var array $juges */
/** @var int   $page */
/** @var int   $totalPages */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-door-open text-primary me-2"></i>Cabinets d'instruction</h2>
        <small class="text-muted"><a href="<?= BASE_URL ?>/config" class="text-decoration-none">Configuration</a> &rsaquo; Cabinets</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAjout">
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
                        <th>Numéro</th>
                        <th>Libellé</th>
                        <th>Juge assigné</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($cabinets)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun cabinet enregistré.</td></tr>
                <?php else: foreach ($cabinets as $i => $c): ?>
                    <tr>
                        <td><?= (($page - 1) * 20) + $i + 1 ?></td>
                        <td><span class="badge bg-primary"><?= htmlspecialchars($c['numero']) ?></span></td>
                        <td><?= htmlspecialchars($c['libelle'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($c['juge_nom'] ?? '—') ?></td>
                        <td>
                            <?php if ($c['actif']): ?>
                                <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-warning me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEdit<?= $c['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="<?= BASE_URL ?>/config/cabinets/delete/<?= $c['id'] ?>" class="d-inline"
                                onsubmit="return confirm('Supprimer ce cabinet ?')">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Édition -->
                    <div class="modal fade" id="modalEdit<?= $c['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?= BASE_URL ?>/config/cabinets/update/<?= $c['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier le cabinet</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Numéro <span class="text-danger">*</span></label>
                                            <input type="text" name="numero" class="form-control" required value="<?= htmlspecialchars($c['numero']) ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Libellé</label>
                                            <input type="text" name="libelle" class="form-control" value="<?= htmlspecialchars($c['libelle'] ?? '') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Juge assigné</label>
                                            <select name="juge_id" class="form-select">
                                                <option value="">— Aucun —</option>
                                                <?php foreach ($juges as $j): ?>
                                                    <option value="<?= $j['id'] ?>" <?= (int)$c['juge_id'] === (int)$j['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($j['nom']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
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
            <form method="POST" action="<?= BASE_URL ?>/config/cabinets/store">
                <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouveau cabinet</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Numéro <span class="text-danger">*</span></label>
                        <input type="text" name="numero" class="form-control" required placeholder="Ex: CAB-01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Libellé</label>
                        <input type="text" name="libelle" class="form-control" placeholder="Ex: 1er Cabinet d'instruction">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Juge assigné</label>
                        <select name="juge_id" class="form-select">
                            <option value="">— Aucun —</option>
                            <?php foreach ($juges as $j): ?>
                                <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
