<?php $pageTitle = 'Recours — '.ucfirst($recours['type_recours']??''); ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/voies-recours" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-arrow-repeat me-2 text-warning"></i>
        <?=ucfirst(htmlspecialchars($recours['type_recours']??'Recours'))?>
    </h4>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Détails du recours</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Dossier RG</dt><dd class="col-sm-8"><a href="<?=BASE_URL?>/dossiers/show/<?=$recours['dossier_id']?>"><?=htmlspecialchars($recours['numero_rg']??'—')?></a></dd>
                    <dt class="col-sm-4 text-muted">Type</dt><dd class="col-sm-8"><span class="badge bg-warning text-dark"><?=ucfirst(htmlspecialchars($recours['type_recours']??''))?></span></dd>
                    <dt class="col-sm-4 text-muted">Date déclaration</dt><dd class="col-sm-8"><?=htmlspecialchars($recours['date_declaration']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Demandeur</dt><dd class="col-sm-8"><?=htmlspecialchars($recours['demandeur_nom']??'—')?> <span class="text-muted small">(<?=htmlspecialchars($recours['demandeur_qualite']??'—')?>)</span></dd>
                    <dt class="col-sm-4 text-muted">Juridiction saisie</dt><dd class="col-sm-8"><?=htmlspecialchars($recours['juridiction_saisie']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Décision rendue</dt><dd class="col-sm-8"><?=htmlspecialchars($recours['decision_rendue']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Date décision</dt><dd class="col-sm-8"><?=htmlspecialchars($recours['date_decision']??'—')?></dd>
                </dl>
            </div>
        </div>
        <?php if(!empty($recours['motifs'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Motifs</div>
            <div class="card-body"><p class="mb-0"><?=nl2br(htmlspecialchars($recours['motifs']))?></p></div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Statut</div>
            <div class="card-body text-center py-4">
                <?php $sc=['declare'=>'secondary','instruit'=>'info','juge'=>'success','irrecevable'=>'danger','desiste'=>'dark'][$recours['statut']]??'secondary'; ?>
                <span class="badge bg-<?=$sc?> fs-6 px-4 py-2"><?=ucfirst(htmlspecialchars($recours['statut']??''))?></span>
            </div>
        </div>
        <?php if(Auth::hasRole(['admin','greffier','procureur','president'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                <a href="<?=BASE_URL?>/voies-recours/edit/<?=$recours['id']?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Modifier</a>
                <?php if($recours['statut']!=='juge'&&$recours['statut']!=='irrecevable'&&$recours['statut']!=='desiste'): ?>
                <form method="POST" action="<?=BASE_URL?>/voies-recours/clore/<?=$recours['id']?>">
                    <?=CSRF::field()?>
                    <div class="input-group input-group-sm">
                        <select name="statut_final" class="form-select">
                            <option value="juge">Jugé</option>
                            <option value="irrecevable">Irrecevable</option>
                            <option value="desiste">Désisté</option>
                        </select>
                        <button class="btn btn-warning">Clore</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
