<?php $pageTitle = 'Détenu — ' . $detenu['numero_ecrou']; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/detenus">Population Carcérale</a></li><li class="breadcrumb-item active"><?=htmlspecialchars($detenu['numero_ecrou'])?></li></ol></nav>
    <div class="d-flex justify-content-between align-items-start">
        <h4 class="fw-bold"><i class="bi bi-person-lock me-2 text-danger"></i><?=htmlspecialchars($detenu['nom'].' '.$detenu['prenom'])?></h4>
        <?php $ds=['incarcere'=>['danger','Incarcéré'],'libere'=>['success','Libéré'],'transfere'=>['info','Transféré'],'evade'=>['dark','Évadé'],'decede'=>['secondary','Décédé']];[$dc,$dl]=$ds[$detenu['statut']]??['secondary',$detenu['statut']]; ?>
        <span class="badge bg-<?=$dc?> fs-6"><?=$dl?></span>
    </div>
</div>
<div class="row g-4">
<div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white fw-semibold">Informations</div><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><small class="text-muted">N° Écrou</small><br><strong class="font-monospace"><?=htmlspecialchars($detenu['numero_ecrou'])?></strong></div>
        <div class="col-md-6"><small class="text-muted">Type de détention</small><br><span class="badge bg-secondary fs-6"><?=str_replace('_',' ',$detenu['type_detention'])?></span></div>
        <div class="col-md-6"><small class="text-muted">Date de naissance</small><br><?=$detenu['date_naissance']?date('d/m/Y',strtotime($detenu['date_naissance'])):'—'?></div>
        <div class="col-md-6"><small class="text-muted">Lieu de naissance</small><br><?=htmlspecialchars($detenu['lieu_naissance']??'—')?></div>
        <div class="col-md-6"><small class="text-muted">Nationalité</small><br><?=htmlspecialchars($detenu['nationalite']??'—')?></div>
        <div class="col-md-6"><small class="text-muted">Profession</small><br><?=htmlspecialchars($detenu['profession']??'—')?></div>
        <div class="col-md-6"><small class="text-muted">Date d'incarcération</small><br><?=date('d/m/Y',strtotime($detenu['date_incarceration']))?></div>
        <div class="col-md-6"><small class="text-muted">Libération prévue</small><br><?=$detenu['date_liberation_prevue']?date('d/m/Y',strtotime($detenu['date_liberation_prevue'])):'—'?></div>
        <?php if($detenu['date_liberation_effective']): ?><div class="col-md-6"><small class="text-muted">Libéré le</small><br><strong class="text-success"><?=date('d/m/Y',strtotime($detenu['date_liberation_effective']))?></strong></div><?php endif; ?>
        <div class="col-md-6"><small class="text-muted">Cellule</small><br><?=htmlspecialchars($detenu['cellule']??'—')?></div>
        <div class="col-md-6"><small class="text-muted">Établissement</small><br><?=htmlspecialchars($detenu['etablissement']??'—')?></div>
        <?php if($detenu['notes']): ?><div class="col-12"><small class="text-muted">Notes</small><br><?=nl2br(htmlspecialchars($detenu['notes']))?></div><?php endif; ?>
    </div></div></div>

    <?php if($detenu['numero_rg']): ?>
    <div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center justify-content-between">
        <div><small class="text-muted">Dossier lié</small><br><strong><?=htmlspecialchars($detenu['numero_rg'])?></strong></div>
        <a href="<?=BASE_URL?>/dossiers/show/<?=$detenu['dossier_id']?>" class="btn btn-outline-primary btn-sm">Voir le dossier</a>
    </div></div>
    <?php endif; ?>
</div>
<div class="col-lg-5">
    <?php
    $dureeJ = (time() - strtotime($detenu['date_incarceration'])) / 86400;
    $dureeMois = floor($dureeJ/30.4);
    $alerte = ($detenu['statut']==='incarcere' && in_array($detenu['type_detention'],['prevenu','detenu_provisoire','inculpe']) && $dureeMois >= DELAI_DETENTION_PROVISOIRE_MOIS);
    ?>
    <div class="card border-0 shadow-sm mb-3 <?=$alerte?'border-danger':''?>">
        <div class="card-header bg-white fw-semibold">Durée de détention</div>
        <div class="card-body text-center">
            <div class="display-5 fw-bold <?=$alerte?'text-danger':($dureeMois>=3?'text-warning':'text-success')?>"><?=floor($dureeJ)?>j</div>
            <div class="text-muted"><?=$dureeMois?> mois</div>
            <?php if($alerte): ?><div class="alert alert-danger py-2 small mt-2 mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Durée de détention provisoire dépassée !</div><?php endif; ?>
        </div>
    </div>

    <?php if($detenu['statut']==='incarcere' && Auth::hasRole(['admin','greffier','president','procureur'])): ?>
    <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-semibold">Libérer</div>
    <div class="card-body">
    <form method="POST" action="<?=BASE_URL?>/detenus/liberer/<?=$detenu['id']?>" onsubmit="return confirm('Confirmer la libération ?')">
        <?=CSRF::field()?>
        <div class="mb-3"><label class="form-label">Date de libération <span class="text-danger">*</span></label><input type="date" name="date_liberation_effective" class="form-control" value="<?=date('Y-m-d')?>" required></div>
        <button type="submit" class="btn btn-success w-100"><i class="bi bi-door-open me-1"></i>Enregistrer la libération</button>
    </form>
    </div></div>
    <?php endif; ?>
</div>
</div>
