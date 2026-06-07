<?php $pageTitle = 'Enregistrer un scellé'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/scelles" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-archive me-2 text-primary"></i>Enregistrer un scellé</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/scelles/store">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dossier <span class="text-danger">*</span></label>
                    <select name="dossier_id" class="form-select" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach($dossiers??[] as $d): ?><option value="<?=$d['id']?>"><?=htmlspecialchars($d['numero_rg'])?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                    <select name="categorie" class="form-select" required>
                        <option value="">—</option>
                        <option value="arme">Arme</option>
                        <option value="drogue">Drogue / Stupéfiant</option>
                        <option value="document">Document</option>
                        <option value="argent">Argent / Valeur</option>
                        <option value="electronique">Appareil électronique</option>
                        <option value="vehicule">Véhicule</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de dépôt <span class="text-danger">*</span></label>
                    <input type="date" name="date_depot" class="form-control" required value="<?=date('Y-m-d')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lieu de conservation</label>
                    <input type="text" name="lieu_conservation" class="form-control" placeholder="Ex: Greffe du TGI, Chambre forte…">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" required placeholder="Description précise du scellé (nature, quantité, état…)"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observations</label>
                    <textarea name="observations" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="<?=BASE_URL?>/scelles" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
