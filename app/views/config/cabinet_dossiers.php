<?php
/**
 * vue config/cabinet_dossiers.php — Dossiers et PVs assignés à un cabinet
 */
$statutLabels = ['enregistre'=>'Enregistré','parquet'=>'Parquet','instruction'=>'Instruction','en_audience'=>'Audience','juge'=>'Jugé','classe'=>'Classé','appel'=>'Appel'];
$statutCls    = ['enregistre'=>'secondary','parquet'=>'warning','instruction'=>'info','en_audience'=>'primary','juge'=>'success','classe'=>'dark','appel'=>'danger'];
?>
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/config">Configuration</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/config/cabinets">Cabinets</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($cabinet['numero']) ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-door-open text-primary me-2"></i>
                <?= htmlspecialchars($cabinet['numero']) ?> — <?= htmlspecialchars($cabinet['libelle']) ?>
            </h3>
            <small class="text-muted">
                Juge : <strong><?= htmlspecialchars($cabinet['juge_nom'] ?? '—') ?></strong>
            </small>
        </div>
        <a href="<?= BASE_URL ?>/config/cabinets" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-1 fw-bold text-primary"><?= count($dossiers) ?></div>
                <div class="text-muted small"><i class="bi bi-folder2-open me-1"></i>Dossiers assignés</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <?php $actifs = array_filter($dossiers, fn($d) => !in_array($d['statut'],['juge','classe'])); ?>
                <div class="fs-1 fw-bold text-warning"><?= count($actifs) ?></div>
                <div class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Dossiers actifs</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-1 fw-bold text-info"><?= count($pvs) ?></div>
                <div class="text-muted small"><i class="bi bi-file-text me-1"></i>PVs liés</div>
            </div>
        </div>
    </div>
</div>

<!-- Filtre statut -->
<form method="GET" class="mb-3">
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <label class="form-label mb-0 fw-semibold">Filtrer par statut :</label>
        <a href="?statut=" class="btn btn-sm <?= !$statut ? 'btn-primary' : 'btn-outline-secondary' ?>">Tous</a>
        <?php foreach ($statutLabels as $k => $v): ?>
        <a href="?statut=<?= $k ?>" class="btn btn-sm <?= $statut===$k ? 'btn-'.$statutCls[$k] : 'btn-outline-secondary' ?>"><?= $v ?></a>
        <?php endforeach; ?>
    </div>
</form>

<!-- Onglets -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabDossiers">Dossiers (<?= count($dossiers) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPvs">PVs liés (<?= count($pvs) ?>)</a></li>
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
                            <tr><th>N° RG</th><th>N° RP</th><th>N° RI</th><th>Objet</th><th>Substitut</th><th>Statut</th><th>Date</th><th></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($dossiers as $d): ?>
                        <tr>
                            <td class="fw-semibold font-monospace small"><?= htmlspecialchars($d['numero_rg']) ?></td>
                            <td class="font-monospace small"><?= htmlspecialchars($d['numero_rp'] ?? '—') ?></td>
                            <td class="font-monospace small"><?= htmlspecialchars($d['numero_ri'] ?? '—') ?></td>
                            <td class="small"><?= htmlspecialchars(mb_substr($d['objet'],0,60)) ?><?= strlen($d['objet'])>60?'…':'' ?></td>
                            <td class="small"><?= htmlspecialchars(($d['sub_prenom']??'').($d['sub_nom']?' '.$d['sub_nom']:'—')) ?></td>
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
            <div class="text-center text-muted py-5"><i class="bi bi-file-x fs-1"></i><br>Aucun PV lié</div>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>N° RG</th><th>N° PV</th><th>Unité</th><th>Type</th><th>Statut</th><th>Date réception</th><th></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pvs as $p): ?>
                        <tr>
                            <td class="fw-semibold font-monospace small"><?= htmlspecialchars($p['numero_rg']) ?></td>
                            <td class="small"><?= htmlspecialchars($p['numero_pv']) ?></td>
                            <td class="small"><?= htmlspecialchars($p['unite_nom'] ?? '—') ?></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($p['type_affaire']) ?></span></td>
                            <td><span class="badge bg-info text-dark"><?= $p['statut'] ?></span></td>
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
