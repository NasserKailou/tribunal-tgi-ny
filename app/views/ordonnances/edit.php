<?php $pageTitle = 'Modifier ordonnance'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/ordonnances/show/<?=$ordonnance['id']?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Modifier l'ordonnance</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/ordonnances/update/<?=$ordonnance['id']?>">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type d'ordonnance</label>
                    <select name="type_ordonnance" class="form-select" required>
                        <?php foreach(['renvoi'=>'Renvoi en jugement','non_lieu'=>'Non-lieu','detention'=>'Détention provisoire','liberation'=>'Mise en liberté','saisie'=>'Saisie','perquisition'=>'Perquisition','commission_rogatoire'=>'Commission rogatoire','autre'=>'Autre'] as $v=>$l): ?>
                        <option value="<?=$v?>" <?=$ordonnance['type_ordonnance']===$v?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" name="date_ordonnance" class="form-control" value="<?=htmlspecialchars($ordonnance['date_ordonnance']??'')?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Contenu / Motivations</label>
                    <textarea name="contenu" class="form-control" rows="8" required><?=htmlspecialchars($ordonnance['contenu']??'')?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observations</label>
                    <textarea name="observations" class="form-control" rows="3"><?=htmlspecialchars($ordonnance['observations']??'')?></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
                <a href="<?=BASE_URL?>/ordonnances/show/<?=$ordonnance['id']?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
