<?php $pageTitle = 'Nouveau recours'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/voies-recours" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Déclarer une voie de recours</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/voies-recours/store">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type de recours <span class="text-danger">*</span></label>
                    <select name="type_recours" class="form-select" required>
                        <option value="">— Choisir —</option>
                        <option value="appel">Appel</option>
                        <option value="cassation">Pourvoi en cassation</option>
                        <option value="opposition">Opposition</option>
                        <option value="revision">Révision</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Dossier (jugement) <span class="text-danger">*</span></label>
                    <select name="dossier_id" class="form-select" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach($dossiers??[] as $d): ?>
                        <option value="<?=$d['id']?>"><?=htmlspecialchars($d['numero_rg'])?> — <?=htmlspecialchars(mb_substr($d['objet'],0,40))?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de déclaration <span class="text-danger">*</span></label>
                    <input type="date" name="date_declaration" class="form-control" required value="<?=date('Y-m-d')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Juridiction saisie</label>
                    <input type="text" name="juridiction_saisie" class="form-control" placeholder="Ex: Cour d'Appel de Niamey">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Demandeur (nom) <span class="text-danger">*</span></label>
                    <input type="text" name="demandeur_nom" class="form-control" required placeholder="Nom du requérant">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Qualité du demandeur</label>
                    <select name="demandeur_qualite" class="form-select">
                        <option value="">—</option>
                        <option value="prevenu">Prévenu/Accusé</option>
                        <option value="partie_civile">Partie civile</option>
                        <option value="ministere_public">Ministère Public</option>
                        <option value="avocat">Avocat</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Motifs</label>
                    <textarea name="motifs" class="form-control" rows="4" placeholder="Exposé des motifs du recours…"></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="<?=BASE_URL?>/voies-recours" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
