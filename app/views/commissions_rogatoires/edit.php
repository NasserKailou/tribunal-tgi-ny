<?php $pageTitle = 'Modifier commission rogatoire'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/commissions-rogatoires/show/<?=$commission['id']?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Modifier la commission rogatoire</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/commissions-rogatoires/update/<?=$commission['id']?>">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Autorité destinataire</label>
                    <input type="text" name="autorite_destinataire" class="form-control" value="<?=htmlspecialchars($commission['autorite_destinataire']??'')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach(['envoyee'=>'Envoyée','executee'=>'Exécutée','retour'=>'Retour reçu','classee'=>'Classée'] as $v=>$l): ?>
                        <option value="<?=$v?>" <?=$commission['statut']===$v?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Objet</label>
                    <textarea name="objet" class="form-control" rows="4"><?=htmlspecialchars($commission['objet']??'')?></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
                <a href="<?=BASE_URL?>/commissions-rogatoires/show/<?=$commission['id']?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
