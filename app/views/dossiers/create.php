<?php $pageTitle = 'Nouveau dossier'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/dossiers">Dossiers</a></li><li class="breadcrumb-item active">Nouveau</li></ol></nav>
    <h4 class="fw-bold"><i class="bi bi-folder-plus me-2 text-primary"></i>Créer un dossier</h4>
</div>
<div class="row justify-content-center"><div class="col-lg-9">
<form method="POST" action="<?=BASE_URL?>/dossiers/store" novalidate>
    <?=CSRF::field()?>
    <div class="card border-0 shadow-sm mb-4"><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Type d'affaire <span class="text-danger">*</span></label>
            <select name="type_affaire" class="form-select" required>
                <option value="penale">Pénale</option>
                <option value="civile">Civile</option>
                <option value="commerciale">Commerciale</option>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Date d'enregistrement</label><input type="date" name="date_enregistrement" class="form-control" value="<?=date('Y-m-d')?>"></div>
        <div class="col-12"><label class="form-label">Objet <span class="text-danger">*</span></label><textarea name="objet" class="form-control" rows="3" required placeholder="Décrire l'objet du dossier..."></textarea></div>
        <div class="col-md-6"><label class="form-label">PV lié</label>
            <select name="pv_id" class="form-select"><option value="">— Aucun —</option>
                <?php foreach($pvs as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['numero_rg'])?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Substitut</label>
            <select name="substitut_id" class="form-select"><option value="">— —</option>
                <?php foreach($substituts as $s): ?><option value="<?=$s['id']?>"><?=htmlspecialchars($s['prenom'].' '.$s['nom'])?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Cabinet d'instruction</label>
            <select name="cabinet_id" class="form-select"><option value="">— Aucun —</option>
                <?php foreach($cabinets as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['numero'].' — '.$c['libelle'])?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><small class="text-muted d-block mt-3">N° RG attribué automatiquement :<br><code><?=htmlspecialchars($suggestRG)?></code></small></div>
    </div></div></div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-save me-1"></i>Créer le dossier</button>
        <a href="<?=BASE_URL?>/dossiers" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
</div></div>
