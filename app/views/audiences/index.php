<?php $pageTitle = 'Audiences'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-calendar-week me-2 text-primary"></i>Audiences</h4>
    <div class="d-flex gap-2">
        <a href="<?=BASE_URL?>/audiences/calendrier" class="btn btn-outline-info btn-sm"><i class="bi bi-calendar me-1"></i>Calendrier</a>
        <?php if(Auth::hasRole(['admin','greffier','president','procureur'])): ?>
        <a href="<?=BASE_URL?>/audiences/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Planifier audience</a>
        <?php endif; ?>
    </div>
</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body py-2">
    <form class="row g-2" method="GET"><div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="N° dossier..." value="<?=htmlspecialchars($search)?>"></div>
    <div class="col-md-2"><select name="statut" class="form-select"><option value="">Tous statuts</option><option value="planifiee" <?=$statut==='planifiee'?'selected':''?>>Planifiée</option><option value="tenue" <?=$statut==='tenue'?'selected':''?>>Tenue</option><option value="renvoyee" <?=$statut==='renvoyee'?'selected':''?>>Renvoyée</option><option value="annulee" <?=$statut==='annulee'?'selected':''?>>Annulée</option></select></div>
    <div class="col-md-2"><select name="type" class="form-select"><option value="">Tous types</option><?php foreach(['correctionnelle','criminelle','civile','commerciale','instruction'] as $t): ?><option value="<?=$t?>" <?=$type===$t?'selected':''?>><?=ucfirst($t)?></option><?php endforeach; ?></select></div>
    <div class="col-auto d-flex gap-1"><button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button><a href="<?=BASE_URL?>/audiences" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a></div>
    </form>
</div></div>
<div class="card border-0 shadow-sm"><div class="card-body p-0">
    <?php if(empty($audiences)): ?><div class="text-center text-muted py-5"><i class="bi bi-calendar-x fs-1 d-block mb-2"></i>Aucune audience</div>
    <?php else: ?><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>N° Audience</th><th>Dossier</th><th>Date</th><th>Type</th><th>Salle</th><th>Président</th><th>Statut</th><th class="text-end">Actions</th></tr></thead><tbody>
    <?php foreach($audiences as $a): ?>
    <tr>
        <td class="small"><?=htmlspecialchars($a['numero_audience']??'—')?></td>
        <td><a href="<?=BASE_URL?>/dossiers/show/<?=$a['dossier_id']?>" class="text-decoration-none fw-semibold"><?=htmlspecialchars($a['numero_rg'])?></a></td>
        <td><?=date('d/m/Y H:i',strtotime($a['date_audience']))?></td>
        <td><span class="badge bg-info text-dark"><?=$a['type_audience']?></span></td>
        <td class="small"><?=htmlspecialchars($a['salle_nom']??'—')?></td>
        <td class="small"><?=htmlspecialchars(($a['president_prenom']??'').($a['president_nom']?' '.$a['president_nom']:'—'))?></td>
        <td><?php $as=['planifiee'=>['primary','Planifiée'],'tenue'=>['success','Tenue'],'renvoyee'=>['warning','Renvoyée'],'annulee'=>['danger','Annulée']];[$ac,$al]=$as[$a['statut']]??['secondary',$a['statut']];echo "<span class=\"badge bg-{$ac}\">{$al}</span>";?></td>
        <td class="text-end"><a href="<?=BASE_URL?>/audiences/show/<?=$a['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table></div>
    <?php if($totalPages>1): ?><div class="d-flex justify-content-center py-3"><?php for($i=1;$i<=$totalPages;$i++): ?><a href="?page=<?=$i?>&q=<?=urlencode($search)?>&statut=<?=$statut?>&type=<?=$type?>" class="btn btn-sm <?=$i===$page?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a><?php endfor; ?></div><?php endif; ?>
    <?php endif; ?>
</div></div>
