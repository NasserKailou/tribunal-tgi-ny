<?php $pageTitle = 'Modifier expertise'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/expertises/show/<?=$expertise['id']?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Modifier l'expertise</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/expertises/update/<?=$expertise['id']?>">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Expert</label>
                    <input type="text" name="expert_nom" class="form-control" value="<?=htmlspecialchars($expertise['expert_nom']??'')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Délai de dépôt</label>
                    <input type="date" name="delai_depot" class="form-control" value="<?=htmlspecialchars($expertise['delai_depot']??'')?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Objet</label>
                    <textarea name="objet_expertise" class="form-control" rows="4"><?=htmlspecialchars($expertise['objet_expertise']??'')?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach(['ordonnee'=>'Ordonnée','en_cours'=>'En cours','deposee'=>'Déposée','validee'=>'Validée','contestee'=>'Contestée'] as $v=>$l): ?>
                        <option value="<?=$v?>" <?=$expertise['statut']===$v?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
                <a href="<?=BASE_URL?>/expertises/show/<?=$expertise['id']?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
