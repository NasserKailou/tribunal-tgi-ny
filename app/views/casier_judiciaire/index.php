<?php $pageTitle = 'Casier judiciaire'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Casier judiciaire</h4>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="Nom, prénom, NIN…" value="<?=htmlspecialchars($search??'')?>"></div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Rechercher</button>
                <a href="<?=BASE_URL?>/casier-judiciaire" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><span class="text-muted small"><?=$total??0?> personne(s)</span></div>
    <div class="card-body p-0">
        <?php if(empty($personnes)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-person-badge fs-1 d-block mb-2"></i>Entrez un nom pour consulter le casier judiciaire</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>NIN</th><th>Nom & Prénom</th><th>Date naissance</th><th>Lieu naissance</th><th>Condamnations</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($personnes as $p): ?>
            <tr>
                <td class="font-monospace small"><?=htmlspecialchars($p['nin']??'—')?></td>
                <td><strong><?=htmlspecialchars($p['nom'].' '.($p['prenom']??''))?></strong></td>
                <td class="small"><?=htmlspecialchars($p['date_naissance']??'—')?></td>
                <td class="small"><?=htmlspecialchars($p['lieu_naissance']??'—')?></td>
                <td><span class="badge <?=$p['nb_condamnations']>0?'bg-danger':'bg-success'?>"><?=$p['nb_condamnations']?> condamnation(s)</span></td>
                <td class="text-end">
                    <a href="<?=BASE_URL?>/casier-judiciaire/show/<?=$p['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Consulter</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
