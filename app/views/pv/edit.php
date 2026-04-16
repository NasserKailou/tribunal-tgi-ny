<?php $pageTitle = 'Modifier le PV'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/pv">Procès-Verbaux</a></li>
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/pv/show/<?=$pv['id']?>"><?=htmlspecialchars($pv['numero_rg'])?></a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol></nav>
    <h4 class="fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Modifier le PV</h4>
</div>

<div class="row justify-content-center"><div class="col-lg-9">
<form method="POST" action="<?=BASE_URL?>/pv/update/<?=$pv['id']?>" novalidate>
    <?=CSRF::field()?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Informations générales</div>
        <div class="card-body"><div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">N° PV d'origine <span class="text-danger">*</span></label>
                <input type="text" name="numero_pv" class="form-control" required value="<?=htmlspecialchars($pv['numero_pv'])?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Type d'affaire</label>
                <select name="type_affaire" class="form-select">
                    <option value="penale"      <?=$pv['type_affaire']==='penale'     ?'selected':''?>>Pénale</option>
                    <option value="civile"      <?=$pv['type_affaire']==='civile'     ?'selected':''?>>Civile</option>
                    <option value="commerciale" <?=$pv['type_affaire']==='commerciale'?'selected':''?>>Commerciale</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Date du PV</label>
                <input type="date" name="date_pv" class="form-control" value="<?=htmlspecialchars($pv['date_pv'])?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Date de réception</label>
                <input type="date" name="date_reception" class="form-control" value="<?=htmlspecialchars($pv['date_reception'])?>">
            </div>
            <div class="col-12">
                <label class="form-label">Unité d'enquête</label>
                <select name="unite_enquete_id" class="form-select">
                    <option value="">— Sélectionner —</option>
                    <?php foreach($unites as $u): ?>
                    <option value="<?=$u['id']?>" <?=$pv['unite_enquete_id']==$u['id']?'selected':''?>>
                        <?=htmlspecialchars($u['nom'])?> (<?=htmlspecialchars($u['type'])?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description des faits</label>
                <textarea name="description_faits" class="form-control" rows="4"><?=htmlspecialchars($pv['description_faits']??'')?></textarea>
            </div>
        </div></div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex align-items-center gap-2">
            <div class="form-check mb-0">
                <input type="checkbox" class="form-check-input" id="chkAnti" name="est_antiterroriste" value="1"
                    <?=$pv['est_antiterroriste']?'checked':''?> onchange="toggleAnti(this.checked)">
                <label class="form-check-label fw-semibold text-danger" for="chkAnti">
                    🔴 Affaire antiterroriste
                </label>
            </div>
        </div>
        <div class="card-body" id="antiSection" style="<?=$pv['est_antiterroriste']?'':'display:none'?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Région</label>
                    <select name="region_id" class="form-select" id="selRegion" onchange="loadDepts(this.value)">
                        <option value="">— Sélectionner —</option>
                        <?php foreach($regions as $r): ?>
                        <option value="<?=$r['id']?>" <?=$pv['region_id']==$r['id']?'selected':''?>><?=htmlspecialchars($r['nom'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Département</label>
                    <select name="departement_id" class="form-select" id="selDept" onchange="loadCommunes(this.value)">
                        <option value="">— Sélectionner —</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Commune</label>
                    <select name="commune_id" class="form-select" id="selCommune">
                        <option value="">— Sélectionner —</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Primo intervenants</label>
                    <div class="row g-2">
                        <?php foreach($primos as $pi): ?>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="primo_intervenants[]"
                                    value="<?=$pi['id']?>"
                                    id="pi<?=$pi['id']?>"
                                    <?=in_array($pi['id'], $pv['primo_ids']??[])?'checked':''?>>
                                <label class="form-check-label" for="pi<?=$pi['id']?>"><?=htmlspecialchars($pi['nom'])?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-save me-1"></i>Enregistrer</button>
        <a href="<?=BASE_URL?>/pv/show/<?=$pv['id']?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
</div></div>

<script>
function toggleAnti(v) { document.getElementById('antiSection').style.display = v ? '' : 'none'; }
function loadDepts(regionId) {
    if (!regionId) return;
    fetch('<?=BASE_URL?>/api/departements/' + regionId)
        .then(r=>r.json()).then(d=>{
            const sel = document.getElementById('selDept');
            sel.innerHTML = '<option value="">— Sélectionner —</option>';
            d.forEach(item => sel.innerHTML += `<option value="${item.id}">${item.nom}</option>`);
        });
}
function loadCommunes(deptId) {
    if (!deptId) return;
    fetch('<?=BASE_URL?>/api/communes/' + deptId)
        .then(r=>r.json()).then(d=>{
            const sel = document.getElementById('selCommune');
            sel.innerHTML = '<option value="">— Sélectionner —</option>';
            d.forEach(item => sel.innerHTML += `<option value="${item.id}">${item.nom}</option>`);
        });
}
</script>
