<?php $pageTitle = 'Population Carcérale'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-lock me-2 text-danger"></i>Population Carcérale</h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/detenus/stats" class="btn btn-outline-info btn-sm"><i class="bi bi-bar-chart me-1"></i>Statistiques</a>
        <?php if (Auth::hasRole(['admin','greffier','procureur','president'])): ?>
        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalNewDetenu">
            <i class="bi bi-plus-lg me-1"></i>Enregistrer détenu
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats rapides -->
<div class="row g-3 mb-4">
    <div class="col"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-3 fw-bold text-danger"><?= $totalIncarceres ?></div><div class="text-muted small">Total incarcérés</div></div></div>
    <?php foreach (['prevenu'=>'Prévenus','inculpe'=>'Inculpés','condamne'=>'Condamnés','detenu_provisoire'=>'Prov.','mis_en_examen'=>'MEE','autre'=>'Autre'] as $t=>$l): ?>
    <div class="col"><div class="card border-0 shadow-sm text-center p-3"><div class="fs-3 fw-bold"><?= $statsPopulation[$t] ?? 0 ?></div><div class="text-muted small"><?= $l ?></div></div></div>
    <?php endforeach; ?>
</div>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Nom, prénom, écrou..." value="<?= htmlspecialchars($search) ?>"></div>
            <div class="col-md-2"><select name="type" class="form-select">
                <option value="">Tous types</option>
                <?php foreach (['prevenu'=>'Prévenu','inculpe'=>'Inculpé','condamne'=>'Condamné','detenu_provisoire'=>'Détenu provisoire','mis_en_examen'=>'Mis en examen','autre'=>'Autre'] as $v=>$l): ?>
                <option value="<?=$v?>" <?=$type===$v?'selected':''?>><?=$l?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="col-md-2"><select name="statut" class="form-select">
                <option value="incarcere" <?=$statut==='incarcere'?'selected':''?>>Incarcérés</option>
                <option value="" <?=$statut===''?'selected':''?>>Tous</option>
                <option value="libere" <?=$statut==='libere'?'selected':''?>>Libérés</option>
            </select></div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?= BASE_URL ?>/detenus" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($detenus)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-person-dash fs-1 d-block mb-2"></i>Aucun détenu trouvé</div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>N° Écrou</th><th>Nom</th><th>Type</th><th>Dossier</th><th>Incarcéré le</th><th>Libération prévue</th><th>Durée</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($detenus as $d): ?>
            <?php
                $dureeJ = (time() - strtotime($d['date_incarceration'])) / 86400;
                $dureeMois = floor($dureeJ / 30.4);
                $alerte = ($d['statut']==='incarcere' && in_array($d['type_detention'],['prevenu','detenu_provisoire','inculpe']) && $dureeMois >= DELAI_DETENTION_PROVISOIRE_MOIS);
            ?>
            <tr class="<?= $alerte ? 'table-warning' : '' ?>">
                <td class="fw-mono small"><?= htmlspecialchars($d['numero_ecrou']) ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/detenus/show/<?= $d['id'] ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($d['nom'] . ' ' . $d['prenom']) ?></a>
                    <?php if ($alerte): ?><span class="badge bg-danger ms-1" title="Durée dépassée"><i class="bi bi-exclamation-triangle"></i></span><?php endif; ?>
                </td>
                <td><span class="badge bg-secondary"><?= str_replace('_',' ',$d['type_detention']) ?></span></td>
                <td class="small"><?= $d['numero_rg'] ? '<a href="'.BASE_URL.'/dossiers/show/'.htmlspecialchars($d['dossier_id']).'" class="text-decoration-none">'.htmlspecialchars($d['numero_rg']).'</a>' : '—' ?></td>
                <td><?= date('d/m/Y', strtotime($d['date_incarceration'])) ?></td>
                <td><?= $d['date_liberation_prevue'] ? date('d/m/Y', strtotime($d['date_liberation_prevue'])) : '—' ?></td>
                <td class="small <?= $dureeMois >= 6 ? 'text-danger fw-bold' : '' ?>"><?= floor($dureeJ) ?>j (<?= $dureeMois ?>mois)</td>
                <td>
                    <?php $ds=['incarcere'=>['danger','Incarcéré'],'libere'=>['success','Libéré'],'transfere'=>['info','Transféré'],'evade'=>['dark','Évadé'],'decede'=>['secondary','Décédé']];[$dc,$dl]=$ds[$d['statut']]??['secondary',$d['statut']];echo "<span class=\"badge bg-{$dc}\">{$dl}</span>";?>
                </td>
                <td class="text-end">
                    <a href="<?= BASE_URL ?>/detenus/show/<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center py-3">
            <?php for ($i=1;$i<=$totalPages;$i++): ?>
            <a href="?page=<?=$i?>&q=<?=urlencode($search)?>&type=<?=$type?>&statut=<?=$statut?>" class="btn btn-sm <?=$i===$page?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if(Auth::hasRole(['admin','greffier','procureur','president'])): ?>
<?php include ROOT_PATH.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'detenus'.DIRECTORY_SEPARATOR.'create.php'; ?>
<?php endif; ?>

<?php if(!empty($_GET['open_modal'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('modalNewDetenu');
    if (modalEl) { new bootstrap.Modal(modalEl).show(); }
});
</script>
<?php endif; ?>
