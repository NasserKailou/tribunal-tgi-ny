<?php $pageTitle = 'Commissions rogatoires'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-send me-2 text-primary"></i>Commissions rogatoires</h4>
    <?php if(Auth::hasRole(['admin','greffier','juge_instruction'])): ?>
    <a href="<?=BASE_URL?>/commissions-rogatoires/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle commission</a>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-5"><input type="text" name="q" class="form-control" placeholder="Dossier RG, autorité destinataire…" value="<?=htmlspecialchars($search??'')?>"></div>
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach(['envoyee'=>'Envoyée','executee'=>'Exécutée','retour'=>'Retour reçu','classee'=>'Classée'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=($statut??'')===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?=BASE_URL?>/commissions-rogatoires" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><span class="text-muted small"><?=$total??0?> commission(s)</span></div>
    <div class="card-body p-0">
        <?php if(empty($commissions)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-send fs-1 d-block mb-2"></i>Aucune commission rogatoire enregistrée</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Numéro</th><th>Dossier</th><th>Autorité</th><th>Objet</th><th>Date envoi</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($commissions as $c): ?>
            <tr>
                <td class="font-monospace small"><?=htmlspecialchars($c['numero_cr']??'—')?></td>
                <td><a href="<?=BASE_URL?>/dossiers/show/<?=$c['dossier_id']?>" class="text-decoration-none"><?=htmlspecialchars($c['numero_rg']??'—')?></a></td>
                <td class="small"><?=htmlspecialchars($c['autorite_destinataire']??'—')?></td>
                <td class="small" style="max-width:150px"><?=htmlspecialchars(mb_substr($c['objet']??'',0,50))?></td>
                <td class="small"><?=htmlspecialchars($c['date_envoi']??'—')?></td>
                <td><?php
                    $sc=['envoyee'=>'info','executee'=>'primary','retour'=>'success','classee'=>'secondary'][$c['statut']]??'secondary';
                    echo "<span class=\"badge bg-$sc\">".ucfirst(str_replace('_',' ',$c['statut']))."</span>";
                ?></td>
                <td class="text-end">
                    <a href="<?=BASE_URL?>/commissions-rogatoires/show/<?=$c['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                    <?php if(Auth::hasRole(['admin','juge_instruction','greffier'])): ?>
                    <a href="<?=BASE_URL?>/commissions-rogatoires/edit/<?=$c['id']?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
