<?php $pageTitle = 'Procès-Verbaux'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-text me-2 text-primary"></i>Procès-Verbaux</h4>
    <?php if (Auth::hasRole(['admin','greffier','procureur','substitut_procureur','president'])): ?>
    <a href="<?= BASE_URL ?>/pv/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau PV</a>
    <?php endif; ?>
</div>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET" action="<?= BASE_URL ?>/pv">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Rechercher N° RG, N° PV..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach (['recu'=>'Reçu','en_traitement'=>'En traitement','classe'=>'Classé','transfere_instruction'=>'→ Instruction','transfere_jugement_direct'=>'→ Jugement direct'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $statut===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">Tous types</option>
                    <option value="penale" <?= $type==='penale'?'selected':'' ?>>Pénale</option>
                    <option value="civile" <?= $type==='civile'?'selected':'' ?>>Civile</option>
                    <option value="commerciale" <?= $type==='commerciale'?'selected':'' ?>>Commerciale</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="antiterro" class="form-select">
                    <option value="">Tous PV</option>
                    <option value="1" <?= $antiterro==='1'?'selected':'' ?>>Anti-terroriste uniquement</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?= BASE_URL ?>/pv" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <span class="text-muted small"><?= $total ?> PV trouvé<?= $total > 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pvList)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>Aucun PV trouvé</div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>N° RG</th><th>N° PV</th><th>Date réception</th><th>Type</th>
                    <th>Unité d'enquête</th><th>Substitut</th><th>Statut</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pvList as $pv): ?>
            <tr>
                <td>
                    <a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>" class="fw-semibold text-decoration-none">
                        <?= htmlspecialchars($pv['numero_rg']) ?>
                    </a>
                </td>
                <td class="text-muted small"><?= htmlspecialchars($pv['numero_pv']) ?></td>
                <td><?= date('d/m/Y', strtotime($pv['date_reception'])) ?></td>
                <td>
                    <span class="badge <?= $pv['type_affaire']==='penale'?'bg-danger':($pv['type_affaire']==='civile'?'bg-primary':'bg-success') ?>">
                        <?= ucfirst($pv['type_affaire']) ?>
                    </span>
                    <?php if ($pv['est_antiterroriste']): ?><span class="badge bg-dark ms-1" title="Anti-terroriste"><i class="bi bi-shield-exclamation"></i></span><?php endif; ?>
                </td>
                <td class="text-muted small"><?= htmlspecialchars($pv['unite_nom'] ?? '—') ?></td>
                <td class="small"><?= htmlspecialchars(($pv['substitut_prenom'] ?? '') . ' ' . ($pv['substitut_nom'] ?? '—')) ?></td>
                <td>
                    <?php
                    $statutBadges=['recu'=>['bg-secondary','Reçu'],'en_traitement'=>['bg-warning text-dark','En traitement'],'classe'=>['bg-dark','Classé'],'transfere_instruction'=>['bg-info text-dark','Instruction'],'transfere_jugement_direct'=>['bg-success','Audience']];
                    [$cls,$lbl]=$statutBadges[$pv['statut']]??['bg-secondary',$pv['statut']];
                    echo "<span class=\"badge {$cls}\">{$lbl}</span>";
                    ?>
                </td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/pv/show/<?= $pv['id'] ?>" class="btn btn-sm btn-outline-primary" title="Voir"><i class="bi bi-eye"></i></a>
                    <?php if (Auth::hasRole(['admin','greffier','procureur'])): ?>
                    <a href="<?= BASE_URL ?>/pv/edit/<?= $pv['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Modifier"><i class="bi bi-pencil"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center py-3">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&statut=<?= $statut ?>&type=<?= $type ?>"
               class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-outline-secondary' ?> mx-1"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
