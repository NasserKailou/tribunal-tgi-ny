<?php $pageTitle = 'Gestion des scellés'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-archive me-2 text-primary"></i>Gestion des scellés</h4>
    <?php if(Auth::hasRole(['admin','greffier','juge_instruction'])): ?>
    <a href="<?=BASE_URL?>/scelles/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Enregistrer un scellé</a>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="N° scellé, dossier RG, description…" value="<?=htmlspecialchars($search??'')?>"></div>
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach(['depose'=>'Déposé','inventorie'=>'Inventorié','restitue'=>'Restitué','detruit'=>'Détruit','confisque'=>'Confisqué'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=($statut??'')===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?=BASE_URL?>/scelles" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><span class="text-muted small"><?=$total??0?> scellé(s)</span></div>
    <div class="card-body p-0">
        <?php if(empty($scelles)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-archive fs-1 d-block mb-2"></i>Aucun scellé enregistré</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>N° Scellé</th><th>Dossier</th><th>Description</th><th>Catégorie</th><th>Date dépôt</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($scelles as $s): ?>
            <tr>
                <td class="font-monospace small fw-semibold"><?=htmlspecialchars($s['numero_scelle']??'—')?></td>
                <td><a href="<?=BASE_URL?>/dossiers/show/<?=$s['dossier_id']?>" class="text-decoration-none"><?=htmlspecialchars($s['numero_rg']??'—')?></a></td>
                <td class="small"><?=htmlspecialchars(mb_substr($s['description']??'',0,50))?><?=strlen($s['description']??'')>50?'…':''?></td>
                <td><span class="badge bg-secondary"><?=htmlspecialchars($s['categorie']??'—')?></span></td>
                <td class="small"><?=htmlspecialchars($s['date_depot']??'—')?></td>
                <td><?php
                    $sc=['depose'=>'warning','inventorie'=>'info','restitue'=>'success','detruit'=>'dark','confisque'=>'primary'][$s['statut']]??'secondary';
                    echo "<span class=\"badge bg-$sc\">".ucfirst($s['statut'])."</span>";
                ?></td>
                <td class="text-end">
                    <a href="<?=BASE_URL?>/scelles/show/<?=$s['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <?php if(Auth::hasRole(['admin','greffier','juge_instruction'])&&$s['statut']==='depose'): ?>
                    <a href="<?=BASE_URL?>/scelles/edit/<?=$s['id']?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
