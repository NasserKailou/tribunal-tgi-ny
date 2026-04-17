<?php
/**
 * vue admin/droits_edit.php — Édition des droits d'un utilisateur
 * @var array  $targetUser
 * @var array  $menus
 * @var array  $fonctionnalites
 * @var array  $droitsMenusIds
 * @var array  $droitsFoncsIds
 */
// Regrouper fonctionnalités par menu
$foncsParMenu = [];
foreach ($fonctionnalites as $f) {
    $foncsParMenu[$f['menu_id']][] = $f;
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">
            <i class="bi bi-shield-lock text-primary me-2"></i>
            Droits — <?= htmlspecialchars($targetUser['prenom'] . ' ' . $targetUser['nom']) ?>
        </h2>
        <small class="text-muted">
            <a href="<?= BASE_URL ?>/admin/droits" class="text-decoration-none">Gestion des droits</a>
            &rsaquo; <?= htmlspecialchars($targetUser['prenom'] . ' ' . $targetUser['nom']) ?>
        </small>
    </div>
    <span class="badge bg-secondary fs-6"><?= htmlspecialchars($targetUser['role_lib']) ?></span>
</div>

<div class="alert alert-info py-2 small">
    <i class="bi bi-info-circle me-2"></i>
    Cochez les menus et fonctionnalités auxquels cet utilisateur doit avoir accès.
    Les éléments <strong>non cochés</strong> seront <strong>inaccessibles</strong>.
</div>

<form method="POST" action="<?= BASE_URL ?>/admin/droits/save/<?= $targetUser['id'] ?>" novalidate>
    <?= CSRF::field() ?>

    <div class="row g-4">
        <?php foreach ($menus as $m): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex align-items-center gap-2 py-2">
                    <div class="form-check mb-0">
                        <input type="checkbox"
                               class="form-check-input menu-check"
                               name="menus[]"
                               value="<?= $m['id'] ?>"
                               id="menu_<?= $m['id'] ?>"
                               data-menu="<?= $m['id'] ?>"
                               <?= in_array($m['id'], $droitsMenusIds) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="menu_<?= $m['id'] ?>">
                            <i class="bi <?= htmlspecialchars($m['icone'] ?? 'bi-grid') ?> me-1 text-primary"></i>
                            <?= htmlspecialchars($m['libelle']) ?>
                        </label>
                    </div>
                    <?php if (!empty($foncsParMenu[$m['id']])): ?>
                    <button type="button" class="btn btn-link btn-sm ms-auto p-0 text-muted toggle-all"
                            data-menu="<?= $m['id'] ?>">
                        Tout
                    </button>
                    <?php endif; ?>
                </div>

                <?php if (!empty($foncsParMenu[$m['id']])): ?>
                <div class="card-body py-2">
                    <?php foreach ($foncsParMenu[$m['id']] as $f): ?>
                    <div class="form-check ms-3 mb-1">
                        <input type="checkbox"
                               class="form-check-input fonc-check"
                               name="fonctionnalites[]"
                               value="<?= $f['id'] ?>"
                               id="fonc_<?= $f['id'] ?>"
                               data-menu="<?= $m['id'] ?>"
                               <?= in_array($f['id'], $droitsFoncsIds) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="fonc_<?= $f['id'] ?>">
                            <?= htmlspecialchars($f['libelle']) ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-between mt-4 gap-2">
        <div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnToutCocher">
                <i class="bi bi-check-all me-1"></i>Tout cocher
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm ms-1" id="btnToutDecocher">
                <i class="bi bi-square me-1"></i>Tout décocher
            </button>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/droits" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-floppy me-2"></i>Enregistrer les droits
            </button>
        </div>
    </div>
</form>

<script>
// Tout cocher / décocher
document.getElementById('btnToutCocher').addEventListener('click', function() {
    document.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = true; });
});
document.getElementById('btnToutDecocher').addEventListener('click', function() {
    document.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
});

// Toggle tout dans un menu
document.querySelectorAll('.toggle-all').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var menuId = this.dataset.menu;
        var foncs  = document.querySelectorAll('.fonc-check[data-menu="' + menuId + '"]');
        var allChecked = Array.from(foncs).every(function(cb) { return cb.checked; });
        foncs.forEach(function(cb) { cb.checked = !allChecked; });
    });
});

// Cocher le menu si une fonctionnalité est cochée
document.querySelectorAll('.fonc-check').forEach(function(cb) {
    cb.addEventListener('change', function() {
        if (this.checked) {
            var menuCb = document.querySelector('.menu-check[data-menu="' + this.dataset.menu + '"]');
            if (menuCb) menuCb.checked = true;
        }
    });
});
</script>
