<?php $pageTitle = 'Nouveau contrôle judiciaire'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/controles-judiciaires" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-plus me-2 text-primary"></i>Nouveau contrôle judiciaire</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/controles-judiciaires/store">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dossier <span class="text-danger">*</span></label>
                    <select name="dossier_id" class="form-select" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach($dossiers??[] as $d): ?>
                        <option value="<?=$d['id']?>"><?=htmlspecialchars($d['numero_rg'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                    <select name="type_controle" class="form-select" required>
                        <option value="controle_judiciaire">Contrôle judiciaire</option>
                        <option value="liberte_provisoire">Liberté provisoire</option>
                        <option value="liberte_sous_caution">Liberté sous caution</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom de la personne <span class="text-danger">*</span></label>
                    <input type="text" name="personne_nom" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prénom</label>
                    <input type="text" name="personne_prenom" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de début <span class="text-danger">*</span></label>
                    <input type="date" name="date_debut" class="form-control" required value="<?=date('Y-m-d')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de fin prévue</label>
                    <input type="date" name="date_fin" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Obligations <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        <?php $obligs=['pointage'=>'Pointage régulier','interdiction_territoire'=>"Interdiction de quitter le territoire",'remise_passeport'=>'Remise du passeport','interdiction_contacts'=>'Interdiction de contact','residence_fixe'=>'Résidence fixe','caution'=>'Versement de caution']; ?>
                        <?php foreach($obligs as $v=>$l): ?>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="obligations_check[]" value="<?=$v?>" id="ob_<?=$v?>">
                                <label class="form-check-label" for="ob_<?=$v?>"><?=$l?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <textarea name="obligations" class="form-control mt-2" rows="3" placeholder="Autres obligations spécifiques…"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observations</label>
                    <textarea name="observations" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="<?=BASE_URL?>/controles-judiciaires" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
