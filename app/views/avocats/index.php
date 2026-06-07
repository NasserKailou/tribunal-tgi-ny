<?php $pageTitle = 'Avocats'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Barreau — Avocats</h4>
    <?php if(Auth::hasRole(['admin','greffier','procureur','president'])): ?>
    <a href="<?=BASE_URL?>/avocats/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvel avocat</a>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Nom, prénom, N° ordre…" value="<?=htmlspecialchars($search)?>"></div>
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach(['actif'=>'Actif','suspendu'=>'Suspendu','radié'=>'Radié','honoraire'=>'Honoraire'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=$statut===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?=BASE_URL?>/avocats" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><span class="text-muted small"><?=$total?> avocat(s)</span></div>
    <div class="card-body p-0">
        <?php if(empty($avocats)): ?><div class="text-center text-muted py-5"><i class="bi bi-person-badge fs-1 d-block mb-2"></i>Aucun avocat trouvé</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Matricule</th><th>Nom & Prénom</th><th>Barreau</th><th>N° Ordre</th><th>Téléphone</th><th>Dossiers</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($avocats as $a): ?>
            <tr>
                <td class="font-monospace small"><?=htmlspecialchars($a['matricule'])?></td>
                <td><strong><?=htmlspecialchars($a['nom'].' '.$a['prenom'])?></strong></td>
                <td class="small"><?=htmlspecialchars($a['barreau'])?></td>
                <td class="small"><?=htmlspecialchars($a['numero_ordre']??'—')?></td>
                <td class="small"><?=htmlspecialchars($a['telephone']??'—')?></td>
                <td><span class="badge bg-secondary"><?=$a['nb_dossiers']?></span></td>
                <td>
                    <?php $sc=['actif'=>'success','suspendu'=>'warning','radié'=>'danger','honoraire'=>'info'][$a['statut']]??'secondary'; ?>
                    <span class="badge bg-<?=$sc?>"><?=ucfirst($a['statut'])?></span>
                </td>
                <td class="text-end">
                    <a href="<?=BASE_URL?>/avocats/show/<?=$a['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <?php if(Auth::hasRole(['admin','greffier','procureur','president'])): ?>
                    <a href="<?=BASE_URL?>/avocats/edit/<?=$a['id']?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php if($totalPages>1): ?><div class="d-flex justify-content-center py-3">
            <?php for($i=1;$i<=$totalPages;$i++): ?><a href="?page=<?=$i?>&q=<?=urlencode($search)?>&statut=<?=$statut?>" class="btn btn-sm <?=$i===$page?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a><?php endfor; ?>
        </div><?php endif; ?>
        <?php endif; ?>
    </div>
</div>
