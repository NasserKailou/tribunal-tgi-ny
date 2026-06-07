<?php $pageTitle = 'Ordonnances du JI'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Ordonnances du Juge d'Instruction</h4>
    <?php if(Auth::hasRole(['admin','greffier','juge_instruction'])): ?>
    <a href="<?=BASE_URL?>/ordonnances/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle ordonnance</a>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="N° ordonnance, dossier RG…" value="<?=htmlspecialchars($search??'')?>"></div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Tous types</option>
                    <?php foreach(['renvoi'=>'Renvoi en jugement','non_lieu'=>'Non-lieu','detention'=>'Détention provisoire','liberation'=>'Mise en liberté','saisie'=>'Saisie','perquisition'=>'Perquisition','commission_rogatoire'=>'Commission rogatoire','autre'=>'Autre'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=($type??'')===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach(['projet'=>'Projet','signee'=>'Signée','notifiee'=>'Notifiée','executee'=>'Exécutée'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=($statut??'')===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?=BASE_URL?>/ordonnances" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><span class="text-muted small"><?=$total??0?> ordonnance(s)</span></div>
    <div class="card-body p-0">
        <?php if(empty($ordonnances)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>Aucune ordonnance trouvée</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Numéro</th><th>Dossier</th><th>Type</th><th>Date</th><th>Juge</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($ordonnances as $o): ?>
            <tr>
                <td class="font-monospace small fw-semibold"><?=htmlspecialchars($o['numero_ordonnance']??'—')?></td>
                <td><a href="<?=BASE_URL?>/dossiers/show/<?=$o['dossier_id']?>" class="text-decoration-none"><?=htmlspecialchars($o['numero_rg']??'—')?></a></td>
                <td><span class="badge bg-info text-dark"><?=htmlspecialchars($o['type_ordonnance']??'—')?></span></td>
                <td class="small"><?=htmlspecialchars($o['date_ordonnance']??'—')?></td>
                <td class="small"><?=htmlspecialchars(trim(($o['juge_prenom']??'').' '.($o['juge_nom']??'')))?></td>
                <td><?php
                    $sc=['projet'=>'secondary','signee'=>'primary','notifiee'=>'info','executee'=>'success'][$o['statut']]??'secondary';
                    echo "<span class=\"badge bg-$sc\">".ucfirst($o['statut'])."</span>";
                ?></td>
                <td class="text-end">
                    <a href="<?=BASE_URL?>/ordonnances/show/<?=$o['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <?php if(Auth::hasRole(['admin','greffier','juge_instruction']) && $o['statut']==='projet'): ?>
                    <a href="<?=BASE_URL?>/ordonnances/edit/<?=$o['id']?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
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
