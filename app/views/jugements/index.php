<?php $pageTitle = 'Jugements'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-hammer me-2 text-primary"></i>Jugements rendus</h4>
</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body py-2">
    <form class="row g-2" method="GET"><div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="N° jugement, N° dossier..." value="<?=htmlspecialchars($search)?>"></div>
    <div class="col-md-3"><select name="type" class="form-select"><option value="">Tous types</option><?php foreach(['condamnation'=>'Condamnation','acquittement'=>'Acquittement','non_lieu'=>'Non-lieu','relaxe'=>'Relaxe','renvoi'=>'Renvoi','avant_droit'=>'Avant-droit','autre'=>'Autre'] as $v=>$l): ?><option value="<?=$v?>" <?=$type===$v?'selected':''?>><?=$l?></option><?php endforeach; ?></select></div>
    <div class="col-auto d-flex gap-1"><button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button><a href="<?=BASE_URL?>/jugements" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a></div>
    </form>
</div></div>
<div class="card border-0 shadow-sm"><div class="card-body p-0">
    <?php if(empty($jugements)): ?><div class="text-center text-muted py-5"><i class="bi bi-hammer fs-1 d-block mb-2"></i>Aucun jugement</div>
    <?php else: ?><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>N° Jugement</th><th>Dossier</th><th>Date</th><th>Type</th><th>Greffier</th><th>Appel</th><th></th></tr></thead><tbody>
    <?php foreach($jugements as $j): ?>
    <tr>
        <td class="fw-semibold small"><?=htmlspecialchars($j['numero_jugement'])?></td>
        <td><a href="<?=BASE_URL?>/dossiers/show/<?=$j['dossier_id']?>" class="text-decoration-none"><?=htmlspecialchars($j['numero_rg'])?></a></td>
        <td><?=date('d/m/Y',strtotime($j['date_jugement']))?></td>
        <td><span class="badge <?=$j['type_jugement']==='condamnation'?'bg-danger':($j['type_jugement']==='acquittement'||$j['type_jugement']==='relaxe'?'bg-success':'bg-secondary')?>"><?=$j['type_jugement']?></span></td>
        <td class="small"><?=htmlspecialchars($j['greffier_nom']??'—')?></td>
        <td>
            <?php if($j['appel_interjecte']): ?><span class="badge bg-danger">Interjeté</span>
            <?php elseif($j['appel_possible']&&$j['date_limite_appel']): ?>
                <?php $diff=(strtotime($j['date_limite_appel'])-time())/86400; ?>
                <span class="badge <?=$diff<0?'bg-dark':($diff<5?'bg-warning text-dark':'bg-secondary')?>">
                    <?=$diff<0?'Expiré':date('d/m/Y',strtotime($j['date_limite_appel']))?>
                </span>
            <?php else: ?><span class="badge bg-dark">Non</span>
            <?php endif; ?>
        </td>
        <td><a href="<?=BASE_URL?>/jugements/show/<?=$j['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table></div>
    <?php if($totalPages>1): ?><div class="d-flex justify-content-center py-3"><?php for($i=1;$i<=$totalPages;$i++): ?><a href="?page=<?=$i?>&q=<?=urlencode($search)?>&type=<?=$type?>" class="btn btn-sm <?=$i===$page?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a><?php endfor; ?></div><?php endif; ?>
    <?php endif; ?>
</div></div>
