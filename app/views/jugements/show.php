<?php $pageTitle = 'Jugement — ' . $jugement['numero_jugement']; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/jugements">Jugements</a></li><li class="breadcrumb-item active"><?=htmlspecialchars($jugement['numero_jugement'])?></li></ol></nav>
    <h4 class="fw-bold"><i class="bi bi-hammer me-2 text-primary"></i><?=htmlspecialchars($jugement['numero_jugement'])?></h4>
</div>
<div class="row g-4">
<div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white fw-semibold">Détails du jugement</div><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><small class="text-muted">Dossier</small><br><a href="<?=BASE_URL?>/dossiers/show/<?=$jugement['dossier_id']?>" class="fw-semibold text-decoration-none"><?=htmlspecialchars($jugement['numero_rg'])?></a></div>
        <div class="col-md-6"><small class="text-muted">Date</small><br><strong><?=date('d/m/Y',strtotime($jugement['date_jugement']))?></strong></div>
        <div class="col-md-6"><small class="text-muted">Type</small><br><span class="badge <?=$jugement['type_jugement']==='condamnation'?'bg-danger':($jugement['type_jugement']==='acquittement'||$jugement['type_jugement']==='relaxe'?'bg-success':'bg-secondary')?> fs-6"><?=$jugement['type_jugement']?></span></div>
        <div class="col-md-6"><small class="text-muted">Greffier</small><br><strong><?=htmlspecialchars(($jugement['greffier_prenom']??'').($jugement['greffier_nom']?' '.$jugement['greffier_nom']:'—'))?></strong></div>
        <div class="col-12"><small class="text-muted">Dispositif</small><div class="border rounded p-3 bg-light mt-1"><?=nl2br(htmlspecialchars($jugement['dispositif']))?></div></div>
        <?php if($jugement['peine_principale']): ?>
        <div class="col-12"><small class="text-muted">Peine</small><br><strong><?=htmlspecialchars($jugement['peine_principale'])?></strong>
            <?php if($jugement['duree_peine_mois']): ?> — <?=$jugement['duree_peine_mois']?> mois<?php endif; ?>
            <?php if($jugement['montant_amende']): ?> — <?=number_format($jugement['montant_amende'],0,',',' ')?> FCFA<?php endif; ?>
            <?php if($jugement['sursis']): ?><span class="badge bg-info text-dark ms-1">Avec sursis <?=$jugement['duree_sursis_mois']?mois?>)</span><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if($jugement['notes']): ?><div class="col-12"><small class="text-muted">Notes</small><br><?=nl2br(htmlspecialchars($jugement['notes']))?></div><?php endif; ?>
    </div></div></div>
</div>
<div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-semibold">Appel</div><div class="card-body">
        <?php if($jugement['appel_interjecte']): ?>
        <div class="text-center text-danger"><i class="bi bi-exclamation-circle fs-2 d-block mb-2"></i><strong>Appel interjeté</strong><br><small><?=$jugement['date_appel']?date('d/m/Y',strtotime($jugement['date_appel'])):'—'?></small></div>
        <?php elseif($jugement['appel_possible']): ?>
        <?php $diff=(strtotime($jugement['date_limite_appel'])-time())/86400; ?>
        <div class="text-center">
            <span class="badge <?=$diff<0?'bg-dark':($diff<5?'bg-danger':'bg-warning text-dark')?> p-2 fs-6 d-block mb-2">
                <?=$diff<0?'Délai expiré':'Délai : '.round(max(0,$diff)).' jour(s)'?>
            </span>
            <small class="text-muted">Limite: <?=date('d/m/Y',strtotime($jugement['date_limite_appel']))?></small>
        </div>
        <?php if($diff>0 && Auth::hasRole(['admin','greffier','procureur'])): ?>
        <form method="POST" action="<?=BASE_URL?>/jugements/appel/<?=$jugement['id']?>" class="mt-3" onsubmit="return confirm('Enregistrer l\'appel ?')">
            <?=CSRF::field()?>
            <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-flag me-1"></i>Enregistrer appel</button>
        </form>
        <?php endif; ?>
        <?php else: ?><div class="text-center text-muted">Appel non applicable</div>
        <?php endif; ?>
    </div></div>
</div>
</div>
