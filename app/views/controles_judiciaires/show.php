<?php $pageTitle = 'Contrôle judiciaire'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/controles-judiciaires" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Contrôle judiciaire</h4>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Informations</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Personne</dt><dd class="col-sm-8"><strong><?=htmlspecialchars(($controle['personne_prenom']??'').' '.($controle['personne_nom']??''))?></strong></dd>
                    <dt class="col-sm-4 text-muted">Dossier RG</dt><dd class="col-sm-8"><a href="<?=BASE_URL?>/dossiers/show/<?=$controle['dossier_id']?>"><?=htmlspecialchars($controle['numero_rg']??'—')?></a></dd>
                    <dt class="col-sm-4 text-muted">Type</dt><dd class="col-sm-8"><span class="badge bg-info text-dark"><?=htmlspecialchars(str_replace('_',' ',ucfirst($controle['type_controle']??'')))?></span></dd>
                    <dt class="col-sm-4 text-muted">Date début</dt><dd class="col-sm-8"><?=htmlspecialchars($controle['date_debut']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Date fin prévue</dt><dd class="col-sm-8"><?=htmlspecialchars($controle['date_fin']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Statut</dt><dd class="col-sm-8"><?php
                        $sc=['actif'=>'success','leve'=>'secondary','viole'=>'danger','expire'=>'warning'][$controle['statut']]??'secondary';
                        echo "<span class=\"badge bg-$sc\">".ucfirst($controle['statut'])."</span>";
                    ?></dd>
                </dl>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Obligations</div>
            <div class="card-body"><p class="mb-0"><?=nl2br(htmlspecialchars($controle['obligations']??'Aucune obligation spécifique'))?></p></div>
        </div>
    </div>
    <div class="col-lg-4">
        <?php if(Auth::hasRole(['admin','juge_instruction','greffier','procureur'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                <?php if($controle['statut']==='actif'): ?>
                <a href="<?=BASE_URL?>/controles-judiciaires/edit/<?=$controle['id']?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Modifier</a>
                <form method="POST" action="<?=BASE_URL?>/controles-judiciaires/lever/<?=$controle['id']?>">
                    <?=CSRF::field()?>
                    <button class="btn btn-success btn-sm w-100"><i class="bi bi-check-circle me-1"></i>Lever le contrôle</button>
                </form>
                <form method="POST" action="<?=BASE_URL?>/controles-judiciaires/violation/<?=$controle['id']?>">
                    <?=CSRF::field()?>
                    <button class="btn btn-danger btn-sm w-100"><i class="bi bi-exclamation-triangle me-1"></i>Signaler violation</button>
                </form>
                <?php endif; ?>
                <a href="<?=BASE_URL?>/controles-judiciaires" class="btn btn-outline-secondary btn-sm">Retour liste</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
