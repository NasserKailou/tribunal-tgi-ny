<?php $pageTitle = 'Nouvelle commission rogatoire'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/commissions-rogatoires" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-send me-2 text-primary"></i>Nouvelle commission rogatoire</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/commissions-rogatoires/store">
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
                    <label class="form-label fw-semibold">Autorité destinataire <span class="text-danger">*</span></label>
                    <input type="text" name="autorite_destinataire" class="form-control" required placeholder="Ex: Police Judiciaire, Interpol…">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type</label>
                    <select name="type_cr" class="form-select">
                        <option value="nationale">Nationale</option>
                        <option value="internationale">Internationale</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date d'envoi <span class="text-danger">*</span></label>
                    <input type="date" name="date_envoi" class="form-control" required value="<?=date('Y-m-d')?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Objet / Diligences demandées <span class="text-danger">*</span></label>
                    <textarea name="objet" class="form-control" rows="5" required placeholder="Décrivez les diligences demandées…"></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                <a href="<?=BASE_URL?>/commissions-rogatoires" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
