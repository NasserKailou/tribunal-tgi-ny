<?php $pageTitle = 'Planifier une audience'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/audiences">Audiences</a></li><li class="breadcrumb-item active">Planifier</li></ol></nav>
    <h4 class="fw-bold"><i class="bi bi-calendar-plus me-2 text-primary"></i>Planifier une audience</h4>
</div>
<div class="row justify-content-center"><div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?=BASE_URL?>/audiences/store" novalidate>
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-12"><label class="form-label fw-semibold">Dossier <span class="text-danger">*</span></label>
                    <select name="dossier_id" class="form-select" required>
                        <option value="">— Sélectionner un dossier —</option>
                        <?php foreach($dossiers as $d): ?><option value="<?=$d['id']?>" <?=($dossierPreselect===$d['id']||($_POST['dossier_id']??'')==$d['id'])?'selected':''?>><?=htmlspecialchars($d['numero_rg'].' — '.$d['objet'])?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Date et heure <span class="text-danger">*</span></label><input type="datetime-local" name="date_audience" class="form-control" required value="<?=$_POST['date_audience']??''?>"></div>
                <div class="col-md-6"><label class="form-label">Type d'audience <span class="text-danger">*</span></label>
                    <select name="type_audience" class="form-select" required>
                        <?php foreach(['correctionnelle'=>'Correctionnelle','criminelle'=>'Criminelle','civile'=>'Civile','commerciale'=>'Commerciale','instruction'=>'Instruction'] as $v=>$l): ?><option value="<?=$v?>"><?=$l?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Salle</label>
                    <select name="salle_id" class="form-select">
                        <option value="">— Salle —</option>
                        <?php foreach($salles as $s): ?><option value="<?=$s['id']?>"><?=htmlspecialchars($s['nom'])?> (<?=$s['capacite']?> places)</option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Président</label>
                    <select name="president_id" class="form-select">
                        <option value="">— Président —</option>
                        <?php foreach($juges as $j): ?><option value="<?=$j['id']?>"><?=htmlspecialchars($j['prenom'].' '.$j['nom'])?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Greffier</label>
                    <select name="greffier_id" class="form-select">
                        <option value="">— Greffier —</option>
                        <?php foreach($greffiers as $g): ?><option value="<?=$g['id']?>"><?=htmlspecialchars($g['prenom'].' '.$g['nom'])?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3"><?=$_POST['notes']??''?></textarea></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-calendar-check me-1"></i>Planifier</button>
                <a href="<?=BASE_URL?>/audiences" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div></div>
