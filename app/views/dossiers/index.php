<?php $pageTitle = 'Dossiers'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-folder2-open me-2 text-primary"></i>Dossiers judiciaires</h4>
    <?php if (Auth::hasRole(['admin','greffier','procureur','substitut_procureur'])): ?>
    <a href="<?= BASE_URL ?>/dossiers/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau dossier</a>
    <?php endif; ?>
</div>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET" action="<?= BASE_URL ?>/dossiers">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control" placeholder="N° RG, RP, RI, objet..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach(['enregistre'=>'Enregistré','parquet'=>'Parquet','instruction'=>'Instruction','en_audience'=>'En audience','juge'=>'Jugé','classe'=>'Classé','appel'=>'Appel'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=$statut===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">Tous types</option>
                    <option value="penale" <?=$type==='penale'?'selected':''?>>Pénale</option>
                    <option value="civile" <?=$type==='civile'?'selected':''?>>Civile</option>
                    <option value="commerciale" <?=$type==='commerciale'?'selected':''?>>Commerciale</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?= BASE_URL ?>/dossiers" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom"><span class="text-muted small"><?=$total?> dossier<?=$total>1?'s':''?> trouvé<?=$total>1?'s':''?></span></div>
    <div class="card-body p-0">
        <?php if(empty($dossiers)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-folder-x fs-1 d-block mb-2"></i>Aucun dossier trouvé</div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>N° RG</th><th>N° RP</th><th>N° RI</th><th>Type</th><th>Objet</th><th>Substitut</th><th>Cabinet</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($dossiers as $d): ?>
            <tr>
                <td><a href="<?=BASE_URL?>/dossiers/show/<?=$d['id']?>" class="fw-semibold text-decoration-none"><?=htmlspecialchars($d['numero_rg'])?></a></td>
                <td class="text-muted small"><?=htmlspecialchars($d['numero_rp']??'—')?></td>
                <td class="text-muted small"><?=htmlspecialchars($d['numero_ri']??'—')?></td>
                <td><span class="badge <?=$d['type_affaire']==='penale'?'bg-danger':($d['type_affaire']==='civile'?'bg-primary':'bg-success')?>"><?=ucfirst($d['type_affaire'])?></span></td>
                <td class="small" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?=htmlspecialchars($d['objet'])?>"><?=htmlspecialchars($d['objet'])?></td>
                <td class="small"><?=htmlspecialchars(($d['substitut_prenom']??'').($d['substitut_nom']?' '.$d['substitut_nom']:'—'))?></td>
                <td class="small"><?=htmlspecialchars($d['cabinet_num']??'—')?></td>
                <td>
                    <?php
                    $sm=['enregistre'=>['secondary','Enregistré'],'parquet'=>['warning','Parquet'],'instruction'=>['info','Instruction'],'en_audience'=>['primary','Audience'],'juge'=>['success','Jugé'],'classe'=>['dark','Classé'],'appel'=>['danger','Appel']];
                    [$sc,$sl]=$sm[$d['statut']]??['secondary',$d['statut']];
                    echo "<span class=\"badge bg-{$sc}\">{$sl}</span>";
                    if($d['nb_audiences']>0) echo " <span class=\"badge bg-light text-dark border\"><i class=\"bi bi-calendar\"></i> {$d['nb_audiences']}</span>";
                    ?>
                </td>
                <td class="text-end">
                    <a href="<?=BASE_URL?>/dossiers/show/<?=$d['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if($totalPages>1): ?>
        <div class="d-flex justify-content-center py-3">
            <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a href="?page=<?=$i?>&q=<?=urlencode($search)?>&statut=<?=$statut?>&type=<?=$type?>" class="btn btn-sm <?=$i===$page?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
