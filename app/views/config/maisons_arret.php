<?php
/** @var array $maisonsArret */
/** @var array $regions */
/** @var int   $page */
/** @var int   $totalPages */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-building-lock text-secondary me-2"></i>Maisons d'arrêt</h2>
        <small class="text-muted"><a href="<?= BASE_URL ?>/config" class="text-decoration-none">Configuration</a> &rsaquo; Maisons d'arrêt</small>
    </div>
    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalAjout">
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
                        <th>Ville / Région</th>
                        <th>Capacité</th>
                        <th>Population</th>
                        <th>Directeur</th>
                        <th>Téléphone</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($maisonsArret)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucune maison d'arrêt enregistrée.</td></tr>
                <?php else: foreach ($maisonsArret as $i => $m): ?>
                    <?php
                        $taux = $m['capacite'] > 0 ? round($m['population_actuelle'] / $m['capacite'] * 100) : 0;
                        $tauxClass = $taux > 90 ? 'danger' : ($taux > 70 ? 'warning' : 'success');
                    ?>
                    <tr>
                        <td><?= (($page - 1) * 20) + $i + 1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($m['nom']) ?></td>
                        <td>
                            <?= htmlspecialchars($m['ville']) ?>
                            <?php if ($m['region_nom']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($m['region_nom']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($m['capacite']) ?></td>
                        <td>
                            <?= number_format($m['population_actuelle']) ?>
                            <div class="progress mt-1" style="height:4px;width:60px">
                                <div class="progress-bar bg-<?= $tauxClass ?>" style="width:<?= min($taux,100) ?>%"></div>
                            </div>
                            <small class="text-<?= $tauxClass ?>"><?= $taux ?>%</small>
                        </td>
                        <td><?= htmlspecialchars($m['directeur'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($m['telephone'] ?? '—') ?></td>
                        <td class="text-end">
                            <a href="<?= BASE_URL ?>/config/maisons-arret/stats/<?= $m['id'] ?>"
                               class="btn btn-sm btn-outline-info me-1" title="Stats population par sexe">
                                <i class="bi bi-bar-chart-line"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-warning me-1"
                                data-bs-toggle="modal" data-bs-target="#modalEdit<?= $m['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="<?= BASE_URL ?>/config/maisons-arret/delete/<?= $m['id'] ?>" class="d-inline"
                                onsubmit="return confirm('Supprimer cette maison d\'arrêt ?')">
                                <?= CSRF::field() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit<?= $m['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" action="<?= BASE_URL ?>/config/maisons-arret/update/<?= $m['id'] ?>">
                                    <?= CSRF::field() ?>
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier la maison d'arrêt</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                                <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($m['nom']) ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Ville <span class="text-danger">*</span></label>
                                                <input type="text" name="ville" class="form-control" required value="<?= htmlspecialchars($m['ville']) ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Région</label>
                                                <select name="region_id" class="form-select">
                                                    <option value="">— Aucune —</option>
                                                    <?php foreach ($regions as $r): ?>
                                                        <option value="<?= $r['id'] ?>" <?= (int)$m['region_id'] === (int)$r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['nom']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Capacité</label>
                                                <input type="number" name="capacite" class="form-control" min="0" value="<?= $m['capacite'] ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Population actuelle</label>
                                                <input type="number" name="population_actuelle" class="form-control" min="0" value="<?= $m['population_actuelle'] ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Directeur</label>
                                                <input type="text" name="directeur" class="form-control" value="<?= htmlspecialchars($m['directeur'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Téléphone</label>
                                                <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($m['telephone'] ?? '') ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Adresse</label>
                                                <textarea name="adresse" class="form-control" rows="2"><?= htmlspecialchars($m['adresse'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-secondary">Enregistrer</button>
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
            <form method="POST" action="<?= BASE_URL ?>/config/maisons-arret/store">
                <?= CSRF::field() ?>
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouvelle maison d'arrêt</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" required placeholder="Maison d'Arrêt de …">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ville <span class="text-danger">*</span></label>
                            <input type="text" name="ville" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Région</label>
                            <select name="region_id" class="form-select">
                                <option value="">— Aucune —</option>
                                <?php foreach ($regions as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Capacité</label>
                            <input type="number" name="capacite" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Population actuelle</label>
                            <input type="number" name="population_actuelle" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Directeur</label>
                            <input type="text" name="directeur" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" placeholder="+227 XX XX XX XX">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <textarea name="adresse" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-secondary"><i class="bi bi-check-lg me-1"></i>Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
