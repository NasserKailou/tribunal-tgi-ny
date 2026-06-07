<?php $pageTitle = 'Commission rogatoire'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/commissions-rogatoires" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-send me-2 text-primary"></i>Commission rogatoire <?=htmlspecialchars($commission['numero_cr']??'')?></h4>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Informations</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Dossier</dt><dd class="col-sm-8"><a href="<?=BASE_URL?>/dossiers/show/<?=$commission['dossier_id']?>"><?=htmlspecialchars($commission['numero_rg']??'—')?></a></dd>
                    <dt class="col-sm-4 text-muted">Autorité</dt><dd class="col-sm-8"><?=htmlspecialchars($commission['autorite_destinataire']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Type</dt><dd class="col-sm-8"><span class="badge bg-info text-dark"><?=ucfirst($commission['type_cr']??'—')?></span></dd>
                    <dt class="col-sm-4 text-muted">Date envoi</dt><dd class="col-sm-8"><?=htmlspecialchars($commission['date_envoi']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Date retour</dt><dd class="col-sm-8"><?=htmlspecialchars($commission['date_retour']??'—')?></dd>
                </dl>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Objet / Diligences</div>
            <div class="card-body"><p><?=nl2br(htmlspecialchars($commission['objet']??''))?></p></div>
        </div>
        <?php if(!empty($commission['resultats'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Résultats / Compte-rendu</div>
            <div class="card-body"><p><?=nl2br(htmlspecialchars($commission['resultats']))?></p></div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <?php if(Auth::hasRole(['admin','juge_instruction','greffier'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                <a href="<?=BASE_URL?>/commissions-rogatoires/edit/<?=$commission['id']?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Modifier</a>
                <?php if($commission['statut']==='envoyee'||$commission['statut']==='executee'): ?>
                <form method="POST" action="<?=BASE_URL?>/commissions-rogatoires/retour/<?=$commission['id']?>">
                    <?=CSRF::field()?>
                    <textarea name="resultats" class="form-control form-control-sm mb-2" rows="3" placeholder="Résultats reçus…"></textarea>
                    <button class="btn btn-success btn-sm w-100"><i class="bi bi-check-circle me-1"></i>Enregistrer retour</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
