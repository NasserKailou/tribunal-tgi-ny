<?php $pageTitle = 'Nouvelle ordonnance'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/ordonnances" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Nouvelle ordonnance</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/ordonnances/store">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dossier (RG) <span class="text-danger">*</span></label>
                    <select name="dossier_id" class="form-select" required>
                        <option value="">— Sélectionner un dossier —</option>
                        <?php foreach($dossiers??[] as $d): ?>
                        <option value="<?=$d['id']?>"><?=htmlspecialchars($d['numero_rg'])?> — <?=htmlspecialchars(mb_substr($d['objet'],0,50))?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type d'ordonnance <span class="text-danger">*</span></label>
                    <select name="type_ordonnance" class="form-select" required>
                        <option value="">— Choisir —</option>
                        <option value="renvoi">Renvoi en jugement</option>
                        <option value="non_lieu">Non-lieu</option>
                        <option value="detention">Détention provisoire</option>
                        <option value="liberation">Mise en liberté</option>
                        <option value="saisie">Saisie</option>
                        <option value="perquisition">Perquisition</option>
                        <option value="commission_rogatoire">Commission rogatoire</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de l'ordonnance <span class="text-danger">*</span></label>
                    <input type="date" name="date_ordonnance" class="form-control" required value="<?=date('Y-m-d')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Juge instructeur</label>
                    <select name="juge_id" class="form-select">
                        <option value="">— Auto (cabinet assigné) —</option>
                        <?php foreach($juges??[] as $j): ?>
                        <option value="<?=$j['id']?>"><?=htmlspecialchars($j['prenom'].' '.$j['nom'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Contenu / Motivations <span class="text-danger">*</span></label>
                    <textarea name="contenu" class="form-control" rows="6" required placeholder="Rédigez le contenu de l'ordonnance…"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observations</label>
                    <textarea name="observations" class="form-control" rows="3" placeholder="Observations complémentaires…"></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="<?=BASE_URL?>/ordonnances" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
