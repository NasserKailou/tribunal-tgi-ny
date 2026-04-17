<?php $pageTitle = 'Scellé '.(htmlspecialchars($scelle['numero_scelle']??'')); ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/scelles" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-archive me-2 text-primary"></i>Scellé <?=htmlspecialchars($scelle['numero_scelle']??'')?></h4>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Informations</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">N° Scellé</dt><dd class="col-sm-8 font-monospace"><?=htmlspecialchars($scelle['numero_scelle']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Dossier</dt><dd class="col-sm-8"><a href="<?=BASE_URL?>/dossiers/show/<?=$scelle['dossier_id']?>"><?=htmlspecialchars($scelle['numero_rg']??'—')?></a></dd>
                    <dt class="col-sm-4 text-muted">Catégorie</dt><dd class="col-sm-8"><span class="badge bg-secondary"><?=htmlspecialchars($scelle['categorie']??'—')?></span></dd>
                    <dt class="col-sm-4 text-muted">Date dépôt</dt><dd class="col-sm-8"><?=htmlspecialchars($scelle['date_depot']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Lieu conservation</dt><dd class="col-sm-8"><?=htmlspecialchars($scelle['lieu_conservation']??'—')?></dd>
                    <dt class="col-sm-4 text-muted">Statut</dt><dd class="col-sm-8"><?php
                        $sc=['depose'=>'warning','inventorie'=>'info','restitue'=>'success','detruit'=>'dark','confisque'=>'primary'][$scelle['statut']]??'secondary';
                        echo "<span class=\"badge bg-$sc\">".ucfirst($scelle['statut'])."</span>";
                    ?></dd>
                </dl>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Description</div>
            <div class="card-body"><p><?=nl2br(htmlspecialchars($scelle['description']??''))?></p></div>
        </div>
    </div>
    <div class="col-lg-4">
        <?php if(Auth::hasRole(['admin','greffier','juge_instruction'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                <?php if(in_array($scelle['statut'],['depose','inventorie'])): ?>
                <a href="<?=BASE_URL?>/scelles/edit/<?=$scelle['id']?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Modifier</a>
                <form method="POST" action="<?=BASE_URL?>/scelles/restituer/<?=$scelle['id']?>">
                    <?=CSRF::field()?><button class="btn btn-success btn-sm w-100"><i class="bi bi-box-arrow-up me-1"></i>Restituer</button>
                </form>
                <form method="POST" action="<?=BASE_URL?>/scelles/detruire/<?=$scelle['id']?>">
                    <?=CSRF::field()?>
                    <div class="input-group input-group-sm">
                        <input type="text" name="motif_destruction" class="form-control" placeholder="Motif destruction" required>
                        <button class="btn btn-danger">Détruire</button>
                    </div>
                </form>
                <?php endif; ?>
                <a href="<?=BASE_URL?>/scelles" class="btn btn-outline-secondary btn-sm">Retour liste</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
