<?php
/** @var array $stats */
/** @var array $flash */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-gear-fill text-warning me-2"></i>Configuration système</h2>
        <small class="text-muted">Administration des paramètres du TGI-NY</small>
    </div>
</div>

<div class="row g-4">

    <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/config/cabinets" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-door-open fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Cabinets d'instruction</h5>
                        <p class="text-muted mb-0 small">Numéro, libellé, juge assigné</p>
                        <span class="badge bg-primary mt-1"><?= $stats['cabinets'] ?> enregistré(s)</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/config/primo-intervenants" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-person-badge fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Primo intervenants</h5>
                        <p class="text-muted mb-0 small">Nom, type, description</p>
                        <span class="badge bg-success mt-1"><?= $stats['primo_intervenants'] ?> enregistré(s)</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/config/unites-enquete" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Unités d'enquête</h5>
                        <p class="text-muted mb-0 small">Police, gendarmerie, douane…</p>
                        <span class="badge bg-warning text-dark mt-1"><?= $stats['unites_enquete'] ?> enregistrée(s)</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/config/substituts" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-person-lines-fill fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Substituts du procureur</h5>
                        <p class="text-muted mb-0 small">Comptes utilisateurs substituts</p>
                        <span class="badge bg-info text-dark mt-1"><?= $stats['substituts'] ?> actif(s)</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/config/infractions" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Infractions</h5>
                        <p class="text-muted mb-0 small">Code, libellé, catégorie, peines</p>
                        <span class="badge bg-danger mt-1"><?= $stats['infractions'] ?> référencée(s)</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/config/maisons-arret" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-building-lock fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Maisons d'arrêt</h5>
                        <p class="text-muted mb-0 small">Établissements de détention</p>
                        <span class="badge bg-secondary mt-1"><?= $stats['maisons_arret'] ?> établissement(s)</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/config/salles-audience" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon bg-primary bg-opacity-10 text-primary" style="background:rgba(var(--bs-warning-rgb),.12)!important;color:var(--bs-warning)!important;">
                        <i class="bi bi-columns-gap fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Salles d'audience</h5>
                        <p class="text-muted mb-0 small">Salles, capacité, équipements</p>
                        <span class="badge mt-1" style="background:#b8860b;color:#fff"><?= $stats['salles_audience'] ?> salle(s)</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

     <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/config/membres-audience" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Membres d'audience</h5>
                        <p class="text-muted mb-0 small">Rôles, siège, greffiers, parquet</p>
                        <span class="badge bg-primary mt-1"><?= $stats['membres_audience'] ?? '—' ?> rôle(s) actifs</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <?php if (Auth::hasRole(['admin'])): ?>
    <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/config/parametres" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card" style="border-left:4px solid #0a2342!important">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon text-white" style="background:#0a2342">
                        <i class="bi bi-sliders fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Paramètres du tribunal</h5>
                        <p class="text-muted mb-0 small">Identité, documents, délais, numérotation</p>
                        <span class="badge mt-1" style="background:#0a2342">Configuration avancée</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-xl-4">
        <a href="<?= BASE_URL ?>/admin/droits" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 config-card" style="border-left:4px solid #dc3545!important">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="config-icon text-white" style="background:#dc3545">
                        <i class="bi bi-shield-lock fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Gestion des droits</h5>
                        <p class="text-muted mb-0 small">Menus et fonctionnalités par utilisateur</p>
                        <span class="badge bg-danger mt-1">Administration</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>

</div>


   

<style>
.config-card { transition: transform .15s, box-shadow .15s; cursor: pointer; }
.config-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12) !important; }
.config-icon { width: 64px; height: 64px; border-radius: 16px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
</style>
