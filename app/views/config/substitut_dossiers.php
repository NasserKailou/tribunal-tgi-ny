<?php
/**
 * vue config/substitut_dossiers.php — Dossiers et PVs assignés à un substitut
 */
$statutLabels = ['enregistre'=>'Enregistré','parquet'=>'Parquet','instruction'=>'Instruction','en_audience'=>'Audience','juge'=>'Jugé','classe'=>'Classé','appel'=>'Appel'];
$statutCls    = ['enregistre'=>'secondary','parquet'=>'warning','instruction'=>'info','en_audience'=>'primary','juge'=>'success','classe'=>'dark','appel'=>'danger'];
$pvStatutLabels = ['recu'=>'Reçu','en_traitement'=>'En traitement','classe'=>'Classé','transfere_instruction'=>'→ Instruction','transfere_jugement_direct'=>'→ Audience'];
?>
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/config">Configuration</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/config/substituts">Substituts</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($substitut['prenom'].' '.$substitut['nom']) ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-person-lines-fill text-info me-2"></i>
                <?= htmlspecialchars($substitut['prenom'].' '.$substitut['nom']) ?>
            </h3>
            <small class="text-muted">Substitut du Procureur</small>
        </div>
        <a href="<?= BASE_URL ?>/config/substituts" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-1 fw-bold text-primary"><?= count($dossiers) ?></div>
                <div class="text-muted small"><i class="bi bi-folder2-open me-1"></i>Dossiers assignés</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <?php $actifsDos = array_filter($dossiers, fn($d) => !in_array($d['statut'],['juge','classe'])); ?>
                <div class="fs-1 fw-bold text-warning"><?= count($actifsDos) ?></div>
                <div class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Dossiers actifs</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-1 fw-bold text-info"><?= count($pvs) ?></div>
                <div class="text-muted small"><i class="bi bi-file-text me-1"></i>PVs assignés</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <?php $pvActifs = array_filter($pvs, fn($p) => in_array($p['statut'],['recu','en_traitement'])); ?>
                <div class="fs-1 fw-bold text-danger"><?= count($pvActifs) ?></div>
                <div class="text-muted small"><i class="bi bi-hourglass me-1"></i>PVs en cours</div>
            </div>
        </div>
    </div>
</div>

<!-- Onglets -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabDossiers">Dossiers (<?= count($dossiers) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPvs">PVs (<?= count($pvs) ?>)</a></li>
</ul>

<div class="tab-content">
    <!-- Dossiers -->
    <div class="tab-pane fade show active" id="tabDossiers">
        <?php if (empty($dossiers)): ?>
            <div class="text-center text-muted py-5"><i class="bi bi-folder-x fs-1"></i><br>Aucun dossier assigné</div>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>N° RG</th><th>Objet</th><th>Cabinet</th><th>Statut</th><th>Date</th><th></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($dossiers as $d): ?>
                        <tr>
                            <td class="fw-semibold font-monospace small"><?= htmlspecialchars($d['numero_rg']) ?></td>
                            <td class="small"><?= htmlspecialchars(mb_substr($d['objet'],0,60)) ?></td>
                            <td class="small"><?= htmlspecialchars($d['cabinet_num'] ? $d['cabinet_num'].' — '.$d['cabinet_lib'] : '—') ?></td>
                            <td><span class="badge bg-<?= $statutCls[$d['statut']] ?? 'secondary' ?>"><?= $statutLabels[$d['statut']] ?? $d['statut'] ?></span></td>
                            <td class="small text-muted"><?= date('d/m/Y', strtotime($d['date_enregistrement'])) ?></td>
                            <td><a href="<?= BASE_URL ?>/dossiers/show/<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- PVs -->
    <div class="tab-pane fade" id="tabPvs">
        <?php if (empty($pvs)): ?>
            <div class="text-center text-muted py-5"><i class="bi bi-file-x fs-1"></i><br>Aucun PV assigné</div>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>N° RG</th><th>N° PV</th><th>Unité</th><th>Type</th><th>Statut</th><th>Date</th><th></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pvs as $p): ?>
                        <tr>
                            <td class="fw-semibold font-monospace small"><?= htmlspecialchars($p['numero_rg']) ?></td>
                            <td class="small"><?= htmlspecialchars($p['numero_pv']) ?></td>
                            <td class="small"><?= htmlspecialchars($p['unite_nom'] ?? '—') ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($p['type_affaire']) ?></span></td>
                            <td><span class="badge bg-info text-dark"><?= $pvStatutLabels[$p['statut']] ?? $p['statut'] ?></span></td>
                            <td class="small text-muted"><?= date('d/m/Y', strtotime($p['date_reception'])) ?></td>
                            <td><a href="<?= BASE_URL ?>/pv/show/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
