<?php $pageTitle = 'Expertise judiciaire'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/expertises" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-microscope me-2 text-primary"></i>Expertise judiciaire</h4>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Détails</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Dossier</dt><dd class="col-sm-8"><a href="<?=BASE_URL?>/dossiers/show/<?=$expertise['dossier_id']?>"><?=htmlspecialchars($expertise['numero_rg']??'—')?></a></dd>
                    <dt class="col-sm-4 text-muted">Type</dt><dd class="col-sm-8"><span class="badge bg-secondary"><?=htmlspecialchars(str_replace('_',' ',ucfirst($expertise['type_expertise']??'')))?></span></dd>
                    <dt class="col-sm-4 text-muted">Expert</dt><dd class="col-sm-8"><strong><?=htmlspecialchars($expertise['expert_nom']??'—')?></strong></dd>
                    <dt class="col-sm-4 text-muted">Qualification</dt><dd class="col-sm-8"><?=htmlspecialchars($expertise['expert_qualification']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Date mission</dt><dd class="col-sm-8"><?=htmlspecialchars($expertise['date_mission']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Délai dépôt</dt><dd class="col-sm-8"><?=htmlspecialchars($expertise['delai_depot']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Date dépôt</dt><dd class="col-sm-8"><?=htmlspecialchars($expertise['date_depot_rapport']??'—')?></dd>
                </dl>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Objet de l'expertise</div>
            <div class="card-body"><p class="mb-0"><?=nl2br(htmlspecialchars($expertise['objet_expertise']??''))?></p></div>
        </div>
        <?php if(!empty($expertise['conclusions'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Conclusions du rapport</div>
            <div class="card-body"><div class="p-3 bg-light rounded"><?=nl2br(htmlspecialchars($expertise['conclusions']))?></div></div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Statut</div>
            <div class="card-body text-center py-4">
                <?php $sc=['ordonnee'=>'secondary','en_cours'=>'info','deposee'=>'primary','validee'=>'success','contestee'=>'danger'][$expertise['statut']]??'secondary'; ?>
                <span class="badge bg-<?=$sc?> fs-6 px-4 py-2"><?=ucfirst(str_replace('_',' ',$expertise['statut']??''))?></span>
            </div>
        </div>
        <?php if(Auth::hasRole(['admin','juge_instruction','greffier'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                <?php if(in_array($expertise['statut'],['ordonnee','en_cours'])): ?>
                <a href="<?=BASE_URL?>/expertises/edit/<?=$expertise['id']?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Modifier</a>
                <form method="POST" action="<?=BASE_URL?>/expertises/deposer/<?=$expertise['id']?>">
                    <?=CSRF::field()?>
                    <div class="mb-2"><textarea name="conclusions" class="form-control form-control-sm" rows="3" placeholder="Conclusions du rapport…"></textarea></div>
                    <button class="btn btn-primary btn-sm w-100"><i class="bi bi-upload me-1"></i>Enregistrer dépôt rapport</button>
                </form>
                <?php endif; ?>
                <a href="<?=BASE_URL?>/expertises" class="btn btn-outline-secondary btn-sm">Retour liste</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
