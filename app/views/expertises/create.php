<?php $pageTitle = 'Nouvelle expertise'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/expertises" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-microscope me-2 text-primary"></i>Ordonner une expertise</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/expertises/store">
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
                    <label class="form-label fw-semibold">Type d'expertise <span class="text-danger">*</span></label>
                    <select name="type_expertise" class="form-select" required>
                        <option value="">—</option>
                        <option value="medico_legale">Médico-légale</option>
                        <option value="psychiatrique">Psychiatrique</option>
                        <option value="comptable">Comptable / Financière</option>
                        <option value="technique">Technique</option>
                        <option value="balistique">Balistique</option>
                        <option value="graphologique">Graphologique</option>
                        <option value="informatique">Informatique / Numérique</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nom de l'expert <span class="text-danger">*</span></label>
                    <input type="text" name="expert_nom" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Qualification / Spécialité</label>
                    <input type="text" name="expert_qualification" class="form-control" placeholder="Ex: Médecin légiste">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de mission <span class="text-danger">*</span></label>
                    <input type="date" name="date_mission" class="form-control" required value="<?=date('Y-m-d')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Délai de dépôt du rapport</label>
                    <input type="date" name="delai_depot" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Objet de l'expertise <span class="text-danger">*</span></label>
                    <textarea name="objet_expertise" class="form-control" rows="4" required placeholder="Description de la mission confiée à l'expert…"></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Ordonner l'expertise</button>
                <a href="<?=BASE_URL?>/expertises" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
