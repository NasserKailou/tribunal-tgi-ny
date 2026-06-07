<?php $pageTitle = 'Modifier recours'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/voies-recours/show/<?=$recours['id']?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Modifier le recours</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/voies-recours/update/<?=$recours['id']?>">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type de recours</label>
                    <select name="type_recours" class="form-select" required>
                        <?php foreach(['appel'=>'Appel','cassation'=>'Pourvoi en cassation','opposition'=>'Opposition','revision'=>'Révision'] as $v=>$l): ?>
                        <option value="<?=$v?>" <?=$recours['type_recours']===$v?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de déclaration</label>
                    <input type="date" name="date_declaration" class="form-control" value="<?=htmlspecialchars($recours['date_declaration']??'')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Demandeur</label>
                    <input type="text" name="demandeur_nom" class="form-control" value="<?=htmlspecialchars($recours['demandeur_nom']??'')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Juridiction saisie</label>
                    <input type="text" name="juridiction_saisie" class="form-control" value="<?=htmlspecialchars($recours['juridiction_saisie']??'')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Décision rendue</label>
                    <input type="text" name="decision_rendue" class="form-control" value="<?=htmlspecialchars($recours['decision_rendue']??'')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date décision</label>
                    <input type="date" name="date_decision" class="form-control" value="<?=htmlspecialchars($recours['date_decision']??'')?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Motifs</label>
                    <textarea name="motifs" class="form-control" rows="4"><?=htmlspecialchars($recours['motifs']??'')?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach(['declare'=>'Déclaré','instruit'=>'En instruction','juge'=>'Jugé','irrecevable'=>'Irrecevable','desiste'=>'Désisté'] as $v=>$l): ?>
                        <option value="<?=$v?>" <?=$recours['statut']===$v?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
                <a href="<?=BASE_URL?>/voies-recours/show/<?=$recours['id']?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
