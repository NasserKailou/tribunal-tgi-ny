<?php
/**
 * vue config/parametres.php — Paramètres du tribunal (100% configurable)
 * @var array $params        Paramètres regroupés par groupe
 * @var array $groupeLabels  Libellés des groupes
 */
$groupeIcons = [
    'identite'      => 'bi-building',
    'documents'     => 'bi-file-earmark-text',
    'delais'        => 'bi-clock-history',
    'numerotation'  => 'bi-hash',
    'affichage'     => 'bi-palette',
];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-sliders text-primary me-2"></i>Paramètres du tribunal</h2>
        <small class="text-muted">
            <a href="<?= BASE_URL ?>/config" class="text-decoration-none">Configuration</a>
            &rsaquo; Paramètres
        </small>
    </div>
</div>

<?php if (!empty($flash['success'])): foreach ($flash['success'] as $msg): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endforeach; endif; ?>

<form method="POST" action="<?= BASE_URL ?>/config/parametres/save" novalidate>
    <?= CSRF::field() ?>

    <!-- Navigation onglets groupes -->
    <ul class="nav nav-pills mb-4 gap-1" id="paramTabs">
        <?php $first = true; foreach ($params as $groupe => $items): ?>
        <li class="nav-item">
            <button class="nav-link <?= $first ? 'active' : '' ?>"
                    type="button"
                    data-bs-toggle="pill"
                    data-bs-target="#tab-<?= $groupe ?>">
                <i class="bi <?= $groupeIcons[$groupe] ?? 'bi-gear' ?> me-1"></i>
                <?= htmlspecialchars($groupeLabels[$groupe] ?? ucfirst($groupe)) ?>
            </button>
        </li>
        <?php $first = false; endforeach; ?>
    </ul>

    <!-- Contenu onglets -->
    <div class="tab-content">
        <?php $first = true; foreach ($params as $groupe => $items): ?>
        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="tab-<?= $groupe ?>">
            <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi <?= $groupeIcons[$groupe] ?? 'bi-gear' ?> text-primary"></i>
                    <strong><?= htmlspecialchars($groupeLabels[$groupe] ?? ucfirst($groupe)) ?></strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($items as $p): ?>
                        <div class="col-md-<?= $p['type_champ'] === 'textarea' ? '12' : '6' ?>">
                            <label class="form-label fw-semibold">
                                <?= htmlspecialchars($p['libelle'] ?? $p['cle']) ?>
                            </label>
                            <?php if ($p['type_champ'] === 'textarea'): ?>
                                <textarea name="<?= htmlspecialchars($p['cle']) ?>"
                                          class="form-control"
                                          rows="3"><?= htmlspecialchars($p['valeur'] ?? '') ?></textarea>
                            <?php elseif ($p['type_champ'] === 'boolean'): ?>
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="<?= htmlspecialchars($p['cle']) ?>" value="0">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="<?= htmlspecialchars($p['cle']) ?>"
                                           value="1"
                                           <?= ($p['valeur'] ?? '0') === '1' ? 'checked' : '' ?>
                                           role="switch">
                                    <label class="form-check-label text-muted small">Activé</label>
                                </div>
                            <?php elseif ($p['type_champ'] === 'color'): ?>
                                <div class="input-group">
                                    <span class="input-group-text p-1">
                                        <input type="color"
                                               value="<?= htmlspecialchars($p['valeur'] ?? '#0a2342') ?>"
                                               style="width:30px;height:30px;border:none;padding:0;background:none"
                                               oninput="document.getElementById('txt_<?= htmlspecialchars($p['cle']) ?>').value=this.value">
                                    </span>
                                    <input type="text"
                                           id="txt_<?= htmlspecialchars($p['cle']) ?>"
                                           name="<?= htmlspecialchars($p['cle']) ?>"
                                           class="form-control font-monospace"
                                           value="<?= htmlspecialchars($p['valeur'] ?? '') ?>">
                                </div>
                            <?php else: ?>
                                <input type="<?= htmlspecialchars($p['type_champ']) ?>"
                                       name="<?= htmlspecialchars($p['cle']) ?>"
                                       class="form-control"
                                       value="<?= htmlspecialchars($p['valeur'] ?? '') ?>">
                            <?php endif; ?>
                            <?php if ($p['description']): ?>
                                <small class="text-muted"><?= htmlspecialchars($p['description']) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $first = false; endforeach; ?>
    </div>

    <div class="d-flex justify-content-end mt-4 gap-2">
        <a href="<?= BASE_URL ?>/config" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-floppy me-2"></i>Enregistrer tous les paramètres
        </button>
    </div>
</form>
