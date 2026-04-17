<?php $pageTitle = 'Voies de recours'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Voies de recours</h4>
    <?php if(Auth::hasRole(['admin','greffier','procureur','president','substitut_procureur'])): ?>
    <a href="<?=BASE_URL?>/voies-recours/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau recours</a>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Nom demandeur, dossier RG…" value="<?=htmlspecialchars($search??'')?>"></div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Tous types</option>
                    <?php foreach(['appel'=>'Appel','cassation'=>'Pourvoi en cassation','opposition'=>'Opposition','revision'=>'Révision'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=($type??'')===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach(['declare'=>'Déclaré','instruit'=>'En instruction','juge'=>'Jugé','irrecevable'=>'Irrecevable','desiste'=>'Désisté'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=($statut??'')===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?=BASE_URL?>/voies-recours" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><span class="text-muted small"><?=$total??0?> recours</span></div>
    <div class="card-body p-0">
        <?php if(empty($recours)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-arrow-repeat fs-1 d-block mb-2"></i>Aucune voie de recours enregistrée</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Type</th><th>Dossier</th><th>Demandeur</th><th>Date déclaration</th><th>Délai (j)</th><th>Juridiction</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($recours as $r): ?>
            <tr>
                <td><span class="badge bg-warning text-dark"><?=htmlspecialchars(ucfirst($r['type_recours']))?></span></td>
                <td><a href="<?=BASE_URL?>/dossiers/show/<?=$r['dossier_id']?>" class="text-decoration-none"><?=htmlspecialchars($r['numero_rg']??'—')?></a></td>
                <td class="small"><?=htmlspecialchars($r['demandeur_nom']??'—')?></td>
                <td class="small"><?=htmlspecialchars($r['date_declaration']??'—')?></td>
                <td class="small text-center"><?php
                    if($r['date_declaration']){
                        $diff=max(0,round((time()-strtotime($r['date_declaration']))/86400));
                        $limit=30;
                        $cls=$diff>$limit?'text-danger':($diff>$limit*0.8?'text-warning':'text-success');
                        echo "<span class=\"$cls fw-semibold\">$diff</span>";
                    } else echo '—';
                ?></td>
                <td class="small"><?=htmlspecialchars($r['juridiction_saisie']??'—')?></td>
                <td><?php
                    $sc=['declare'=>'secondary','instruit'=>'info','juge'=>'success','irrecevable'=>'danger','desiste'=>'dark'][$r['statut']]??'secondary';
                    echo "<span class=\"badge bg-$sc\">".ucfirst($r['statut'])."</span>";
                ?></td>
                <td class="text-end">
                    <a href="<?=BASE_URL?>/voies-recours/show/<?=$r['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <?php if(Auth::hasRole(['admin','greffier','procureur','president'])): ?>
                    <a href="<?=BASE_URL?>/voies-recours/edit/<?=$r['id']?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php if(($totalPages??1)>1): ?><div class="d-flex justify-content-center py-3">
            <?php for($i=1;$i<=($totalPages??1);$i++): ?><a href="?page=<?=$i?>&q=<?=urlencode($search??'')?>&type=<?=$type??''?>&statut=<?=$statut??''?>" class="btn btn-sm <?=$i===($page??1)?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a><?php endfor; ?>
        </div><?php endif; ?>
        <?php endif; ?>
    </div>
</div>
