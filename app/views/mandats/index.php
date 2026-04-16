<?php /* app/views/mandats/index.php */ ?>
<?php $pageTitle = 'Mandats de Justice'; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 fw-bold text-dark">
        <i class="bi bi-file-ruled text-danger me-2"></i>Mandats de Justice
    </h1>
    <?php if(Auth::hasRole(['admin','procureur','juge_instruction','president'])): ?>
    <a href="<?= BASE_URL ?>/mandats/create" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Émettre un mandat
    </a>
    <?php endif; ?>
</div>

<?php if($flash['success']??''): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($flash['success']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Rechercher (numéro, motif, nom…)">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <?php foreach(['arret'=>'Mandat d\'arrêt','depot'=>'Mandat de dépôt','amener'=>'Mandat d\'amener','comparution'=>'Mandat de comparution','perquisition'=>'Mandat de perquisition','liberation'=>'Mandat de libération'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= ($type===$k?'selected':'') ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach(['emis'=>'Émis','signifie'=>'Signifié','execute'=>'Exécuté','annule'=>'Annulé','expire'=>'Expiré'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= ($statut===$k?'selected':'') ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?= BASE_URL ?>/mandats" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>N° Mandat</th>
                        <th>Type</th>
                        <th>Cible</th>
                        <th>Dossier RG</th>
                        <th>Émetteur</th>
                        <th>Date émission</th>
                        <th>Expiration</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($mandats)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Aucun mandat trouvé</td></tr>
                <?php else: ?>
                    <?php
                    $typeLabels   = ['arret'=>['danger','Arrêt'],'depot'=>['dark','Dépôt'],'amener'=>['warning','Amener'],'comparution'=>['info','Comparution'],'perquisition'=>['secondary','Perquisition'],'liberation'=>['success','Libération']];
                    $statutLabels = ['emis'=>['primary','Émis'],'signifie'=>['info','Signifié'],'execute'=>['success','Exécuté'],'annule'=>['danger','Annulé'],'expire'=>['secondary','Expiré']];
                    foreach($mandats as $m):
                        [$tc,$tl] = $typeLabels[$m['type_mandat']] ?? ['secondary',$m['type_mandat']];
                        [$sc,$sl] = $statutLabels[$m['statut']]    ?? ['secondary',$m['statut']];
                        // Cible
                        if($m['detenu_label']) $cible = '👤 '.$m['detenu_label'].' (Détenu)';
                        elseif($m['partie_label']) $cible = '👤 '.$m['partie_label'].' (Partie)';
                        elseif($m['nouveau_nom']) $cible = '👤 '.$m['nouveau_prenom'].' '.$m['nouveau_nom'].' (Nouveau)';
                        else $cible = '—';
                        // Expiration colorée
                        $today = date('Y-m-d');
                        $expColor = '';
                        if($m['date_expiration']) {
                            if($m['date_expiration'] < $today) $expColor = 'text-danger fw-bold';
                            elseif($m['date_expiration'] <= date('Y-m-d', strtotime('+7 days'))) $expColor = 'text-warning fw-bold';
                        }
                    ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/mandats/show/<?= $m['id'] ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($m['numero']) ?></a></td>
                        <td><span class="badge bg-<?= $tc ?>"><?= $tl ?></span></td>
                        <td class="small"><?= htmlspecialchars($cible) ?></td>
                        <td><?= $m['numero_rg'] ? '<a href="'.BASE_URL.'/dossiers/show/'.($m['dossier_id']).'" class="small">'.htmlspecialchars($m['numero_rg']).'</a>' : '—' ?></td>
                        <td class="small"><?= htmlspecialchars($m['emetteur_nom']) ?></td>
                        <td class="small"><?= $m['date_emission'] ? date('d/m/Y', strtotime($m['date_emission'])) : '—' ?></td>
                        <td class="small <?= $expColor ?>"><?= $m['date_expiration'] ? date('d/m/Y', strtotime($m['date_expiration'])) : '—' ?></td>
                        <td><span class="badge bg-<?= $sc ?>"><?= $sl ?></span></td>
                        <td class="text-center">
                            <a href="<?= BASE_URL ?>/mandats/show/<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary" title="Détails"><i class="bi bi-eye"></i></a>
                            <a href="<?= BASE_URL ?>/mandats/print/<?= $m['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimer"><i class="bi bi-printer"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($totalPages > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted"><?= $total ?> mandat(s) — page <?= $page ?>/<?= $totalPages ?></small>
        <nav><ul class="pagination pagination-sm mb-0">
            <?php for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
            <li class="page-item <?= $i===$page?'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>