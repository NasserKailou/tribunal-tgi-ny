<?php $pageTitle = 'Ordonnance — '.(htmlspecialchars($ordonnance['numero_ordonnance']??'')); ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/ordonnances" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>
        Ordonnance <?=htmlspecialchars($ordonnance['numero_ordonnance']??'')?>
    </h4>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Contenu de l'ordonnance</div>
            <div class="card-body">
                <div class="p-3 bg-light rounded" style="white-space:pre-wrap"><?=nl2br(htmlspecialchars($ordonnance['contenu']??''))?></div>
            </div>
        </div>
        <?php if(!empty($ordonnance['observations'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Observations</div>
            <div class="card-body"><p class="mb-0"><?=nl2br(htmlspecialchars($ordonnance['observations']))?></p></div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Informations</div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-sm-5 text-muted">N° ordonnance</dt><dd class="col-sm-7 font-monospace"><?=htmlspecialchars($ordonnance['numero_ordonnance']??'—')?></dd>
                    <dt class="col-sm-5 text-muted">Dossier RG</dt><dd class="col-sm-7"><a href="<?=BASE_URL?>/dossiers/show/<?=$ordonnance['dossier_id']?>"><?=htmlspecialchars($ordonnance['numero_rg']??'—')?></a></dd>
                    <dt class="col-sm-5 text-muted">Type</dt><dd class="col-sm-7"><span class="badge bg-info text-dark"><?=htmlspecialchars($ordonnance['type_ordonnance']??'—')?></span></dd>
                    <dt class="col-sm-5 text-muted">Date</dt><dd class="col-sm-7"><?=htmlspecialchars($ordonnance['date_ordonnance']??'—')?></dd>
                    <dt class="col-sm-5 text-muted">Juge</dt><dd class="col-sm-7"><?=htmlspecialchars(trim(($ordonnance['juge_prenom']??'').' '.($ordonnance['juge_nom']??'')))?></dd>
                    <dt class="col-sm-5 text-muted">Statut</dt><dd class="col-sm-7"><?php
                        $sc=['projet'=>'secondary','signee'=>'primary','notifiee'=>'info','executee'=>'success'][$ordonnance['statut']]??'secondary';
                        echo "<span class=\"badge bg-$sc\">".ucfirst($ordonnance['statut'])."</span>";
                    ?></dd>
                </dl>
            </div>
        </div>
        <?php if(Auth::hasRole(['admin','greffier','juge_instruction'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Actions</div>
            <div class="card-body d-grid gap-2">
                <?php if($ordonnance['statut']==='projet'): ?>
                <a href="<?=BASE_URL?>/ordonnances/edit/<?=$ordonnance['id']?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Modifier</a>
                <form method="POST" action="<?=BASE_URL?>/ordonnances/signer/<?=$ordonnance['id']?>"><?=CSRF::field()?><button class="btn btn-primary btn-sm w-100"><i class="bi bi-pen me-1"></i>Signer l'ordonnance</button></form>
                <?php elseif($ordonnance['statut']==='signee'): ?>
                <form method="POST" action="<?=BASE_URL?>/ordonnances/notifier/<?=$ordonnance['id']?>"><?=CSRF::field()?><button class="btn btn-info btn-sm w-100 text-white"><i class="bi bi-bell me-1"></i>Marquer notifiée</button></form>
                <?php endif; ?>
                <a href="<?=BASE_URL?>/ordonnances" class="btn btn-outline-secondary btn-sm">Retour liste</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
