<?php
/** @var array $infractions */
/** @var int   $page */
/** @var int   $totalPages */
$catLabels = [
    'criminelle'        => ['label' => 'Criminelle',         'class' => 'danger'],
    'correctionnelle'   => ['label' => 'Correctionnelle',    'class' => 'warning'],
    'contraventionnelle'=> ['label' => 'Contraventionnelle', 'class' => 'info'],
];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Infractions</h2>
        <small class="text-muted"><a href="<?= BASE_URL ?>/config" class="text-decoration-none">Configuration</a> &rsaquo; Infractions</small>
    </div>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalAjout">
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
                        <th>Code</th>
                        <th>Libellé</th>
                        <th>Catégorie</th>
                        <th>Peine min</th>
                        <th>Peine max</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($infractions)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucune infraction référencée.</td></tr>
                <?php else: foreach ($infractions as $i => $inf): ?>
                    <?php $cat = $catLabels[$inf['categorie']] ?? ['label'=>$inf['categorie'],'class'=>'secondary']; ?>
                    <tr>
                        <td><?= (($page - 1) * 20) + $i + 1 ?></td>
                        <td><code><?= htmlspecialchars($inf['code']) ?></code></td>
                        <td class="fw-semibold"><?= htmlspecialchars($inf['libelle']) ?></td>
                        <td><span class="badge bg-<?= $cat['class'] ?>"><?= $cat['label'] ?></span></td>
                        <td><?= $inf['peine_min_mois'] !== null ? $inf['peine_min_mois'] . ' mois' : '—' ?></td>
                        <td><?= $inf['peine_max_mois'] !== null ? ($inf['peine_max_mois'] >= 999 ? '∞ (perpétuité)' : $inf['peine_max_mois'] . ' mois') : '—' ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-warning me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEdit<?= $inf['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="<?= BASE_URL ?>/config/infractions/delete/<?= $inf['id'] ?>" class="d-inline"
                                onsubmit="return confirm('Supprimer cette infraction ?')">
                                <?= CSRF::field() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $inf['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" action="<?= BASE_URL ?>/config/infractions/update/<?= $inf['id'] ?>">
                                    <?= CSRF::field() ?>
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier l'infraction</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                                <input type="text" name="code" class="form-control text-uppercase" required value="<?= htmlspecialchars($inf['code']) ?>">
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                                                <input type="text" name="libelle" class="form-control" required value="<?= htmlspecialchars($inf['libelle']) ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                                <select name="categorie" class="form-select" required>
                                                    <?php foreach ($catLabels as $k => $v): ?>
                                                        <option value="<?= $k ?>" <?= $inf['categorie'] === $k ? 'selected' : '' ?>><?= $v['label'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Peine min (mois)</label>
                                                <input type="number" name="peine_min_mois" class="form-control" min="0" value="<?= htmlspecialchars($inf['peine_min_mois'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Peine max (mois)</label>
                                                <input type="number" name="peine_max_mois" class="form-control" min="0" value="<?= htmlspecialchars($inf['peine_max_mois'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-danger">Enregistrer</button>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/config/infractions/store">
                <?= CSRF::field() ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle infraction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" required placeholder="INF-XXX">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Libellé <span class="text-danger">*</span></label>
                            <input type="text" name="libelle" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                            <select name="categorie" class="form-select" required>
                                <option value="">— Sélectionner —</option>
                                <?php foreach ($catLabels as $k => $v): ?>
                                    <option value="<?= $k ?>"><?= $v['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Peine min (mois)</label>
                            <input type="number" name="peine_min_mois" class="form-control" min="0" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Peine max (mois)</label>
                            <input type="number" name="peine_max_mois" class="form-control" min="0" placeholder="999 = perpétuité">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-check-lg me-1"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
