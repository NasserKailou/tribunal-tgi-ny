<?php $pageTitle = 'Modifier contrôle judiciaire'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/controles-judiciaires/show/<?=$controle['id']?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Modifier le contrôle judiciaire</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/controles-judiciaires/update/<?=$controle['id']?>">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de fin</label>
                    <input type="date" name="date_fin" class="form-control" value="<?=htmlspecialchars($controle['date_fin']??'')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach(['actif'=>'Actif','leve'=>'Levé','viole'=>'Violé','expire'=>'Expiré'] as $v=>$l): ?>
                        <option value="<?=$v?>" <?=$controle['statut']===$v?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Obligations</label>
                    <textarea name="obligations" class="form-control" rows="4"><?=htmlspecialchars($controle['obligations']??'')?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observations</label>
                    <textarea name="observations" class="form-control" rows="2"><?=htmlspecialchars($controle['observations']??'')?></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
                <a href="<?=BASE_URL?>/controles-judiciaires/show/<?=$controle['id']?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
