<?php $pageTitle = 'Contrôles judiciaires'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Contrôles judiciaires &amp; Liberté provisoire</h4>
    <?php if(Auth::hasRole(['admin','greffier','juge_instruction','procureur'])): ?>
    <a href="<?=BASE_URL?>/controles-judiciaires/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau contrôle</a>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Nom personne, dossier RG…" value="<?=htmlspecialchars($search??'')?>"></div>
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach(['actif'=>'Actif','leve'=>'Levé','viole'=>'Violé','expire'=>'Expiré'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=($statut??'')===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?=BASE_URL?>/controles-judiciaires" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><span class="text-muted small"><?=$total??0?> contrôle(s)</span></div>
    <div class="card-body p-0">
        <?php if(empty($controles)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-shield-check fs-1 d-block mb-2"></i>Aucun contrôle judiciaire enregistré</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Personne</th><th>Dossier</th><th>Date début</th><th>Date fin</th><th>Obligations</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($controles as $c): ?>
            <tr>
                <td><strong><?=htmlspecialchars($c['personne_nom']??'—')?></strong><br><small class="text-muted"><?=htmlspecialchars($c['personne_prenom']??'')?></small></td>
                <td><a href="<?=BASE_URL?>/dossiers/show/<?=$c['dossier_id']?>" class="text-decoration-none"><?=htmlspecialchars($c['numero_rg']??'—')?></a></td>
                <td class="small"><?=htmlspecialchars($c['date_debut']??'—')?></td>
                <td class="small"><?php
                    if($c['date_fin']){
                        $diff=round((strtotime($c['date_fin'])-time())/86400);
                        $cls=$diff<0?'text-danger':($diff<7?'text-warning':'text-success');
                        echo "<span class=\"$cls fw-semibold\">".$c['date_fin']."</span>";
                    } else echo '—';
                ?></td>
                <td class="small" style="max-width:180px"><?=htmlspecialchars(mb_substr($c['obligations']??'',0,60))?><?=strlen($c['obligations']??'')>60?'…':''?></td>
                <td><?php
                    $sc=['actif'=>'success','leve'=>'secondary','viole'=>'danger','expire'=>'warning'][$c['statut']]??'secondary';
                    echo "<span class=\"badge bg-$sc\">".ucfirst($c['statut'])."</span>";
                ?></td>
                <td class="text-end">
                    <a href="<?=BASE_URL?>/controles-judiciaires/show/<?=$c['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <?php if(Auth::hasRole(['admin','juge_instruction','greffier'])): ?>
                    <a href="<?=BASE_URL?>/controles-judiciaires/edit/<?=$c['id']?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
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
