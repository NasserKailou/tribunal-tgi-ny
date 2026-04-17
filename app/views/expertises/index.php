<?php $pageTitle = 'Expertises judiciaires'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-microscope me-2 text-primary"></i>Expertises judiciaires</h4>
    <?php if(Auth::hasRole(['admin','greffier','juge_instruction'])): ?>
    <a href="<?=BASE_URL?>/expertises/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle expertise</a>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Expert, dossier RG, objet…" value="<?=htmlspecialchars($search??'')?>"></div>
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach(['ordonnee'=>'Ordonnée','en_cours'=>'En cours','deposee'=>'Déposée','validee'=>'Validée','contestee'=>'Contestée'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=($statut??'')===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?=BASE_URL?>/expertises" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><span class="text-muted small"><?=$total??0?> expertise(s)</span></div>
    <div class="card-body p-0">
        <?php if(empty($expertises)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-microscope fs-1 d-block mb-2"></i>Aucune expertise enregistrée</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Dossier</th><th>Objet</th><th>Expert</th><th>Type</th><th>Date mission</th><th>Délai dépôt</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($expertises as $e): ?>
            <tr>
                <td><a href="<?=BASE_URL?>/dossiers/show/<?=$e['dossier_id']?>" class="text-decoration-none"><?=htmlspecialchars($e['numero_rg']??'—')?></a></td>
                <td class="small" style="max-width:150px"><?=htmlspecialchars(mb_substr($e['objet_expertise']??'',0,50))?></td>
                <td class="small"><strong><?=htmlspecialchars($e['expert_nom']??'—')?></strong></td>
                <td><span class="badge bg-secondary"><?=htmlspecialchars($e['type_expertise']??'—')?></span></td>
                <td class="small"><?=htmlspecialchars($e['date_mission']??'—')?></td>
                <td class="small"><?php
                    if(!empty($e['delai_depot'])){
                        $diff=round((strtotime($e['delai_depot'])-time())/86400);
                        $cls=$diff<0?'text-danger fw-bold':($diff<7?'text-warning fw-bold':'text-success');
                        echo "<span class=\"$cls\">".$e['delai_depot']."</span>";
                        if($diff<0) echo " <span class=\"badge bg-danger\">En retard</span>";
                    } else echo '—';
                ?></td>
                <td><?php
                    $sc=['ordonnee'=>'secondary','en_cours'=>'info','deposee'=>'primary','validee'=>'success','contestee'=>'danger'][$e['statut']]??'secondary';
                    echo "<span class=\"badge bg-$sc\">".ucfirst(str_replace('_',' ',$e['statut']))."</span>";
                ?></td>
                <td class="text-end">
                    <a href="<?=BASE_URL?>/expertises/show/<?=$e['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <?php if(Auth::hasRole(['admin','juge_instruction','greffier'])&&in_array($e['statut'],['ordonnee','en_cours'])): ?>
                    <a href="<?=BASE_URL?>/expertises/edit/<?=$e['id']?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php if(($totalPages??1)>1): ?><div class="d-flex justify-content-center py-3">
            <?php for($i=1;$i<=($totalPages??1);$i++): ?><a href="?page=<?=$i?>&q=<?=urlencode($search??'')?>&statut=<?=$statut??''?>" class="btn btn-sm <?=$i===($page??1)?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a><?php endfor; ?>
        </div><?php endif; ?>
        <?php endif; ?>
    </div>
</div>
