<?php $pageTitle = 'Modifier le PV'; ?>
<style>
/* ── Infractions multi-checkbox dans edit ── */
#listInfractionsEdit {
    max-height: 260px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: .375rem;
}
.infraction-row-edit {
    padding: 6px 10px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    background: #fff;
    color: #212529;
    transition: background .1s, color .1s;
}
.infraction-row-edit.selected {
    background: #1a3c5e !important;
    color: #fff !important;
}
.infraction-row-edit.selected code { color: #93c5fd !important; }
.infraction-row-edit:last-child   { border-bottom: none; }
</style>

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

    <!-- ── Informations générales ── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Informations générales</div>
        <div class="card-body"><div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">N° PV d'origine <span class="text-danger">*</span></label>
                <input type="text" name="numero_pv" class="form-control" required
                       value="<?=htmlspecialchars($pv['numero_pv'])?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Type d'affaire</label>
                <select name="type_affaire" class="form-select">
                    <option value="penale"       <?=$pv['type_affaire']==='penale'      ?'selected':''?>>Pénale</option>
                    <option value="civile"       <?=$pv['type_affaire']==='civile'      ?'selected':''?>>Civile</option>
                    <option value="commerciale"  <?=$pv['type_affaire']==='commerciale' ?'selected':''?>>Commerciale</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Date du PV</label>
                <input type="date" name="date_pv" class="form-control"
                       value="<?=htmlspecialchars($pv['date_pv'])?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Date de réception</label>
                <input type="date" name="date_reception" class="form-control"
                       value="<?=htmlspecialchars($pv['date_reception'])?>">
            </div>
            <div class="col-12">
                <label class="form-label">Unité d'enquête</label>
                <select name="unite_enquete_id" class="form-select">
                    <option value="">— Sélectionner —</option>
                    <?php foreach($unites as $u): ?>
                    <option value="<?=$u['id']?>"
                        <?=$pv['unite_enquete_id']==$u['id']?'selected':''?>>
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

    <!-- ── Types d'infractions (multi-checkbox, pré-cochés) ── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
            <span>
                <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                Types d'infractions retenues (plusieurs choix possibles)
            </span>
            <span class="badge bg-primary" id="cntInfEdit">0 sélectionnée(s)</span>
        </div>
        <div class="card-body pb-2">
            <input type="text" id="filterInfEdit" class="form-control form-control-sm mb-2"
                   placeholder="Filtrer les infractions…">

            <div id="listInfractionsEdit">
                <?php
                /* Grouper par catégorie */
                $editGroups     = [];
                foreach ($infractions as $inf) { $editGroups[$inf['categorie']][] = $inf; }
                $editPreChecked = $pv['infractions_enquete_ids'] ?? [];

                /* Fallback : si les nouvelles tables sont vides, on pré-coche l'ancienne infraction */
                if (empty($editPreChecked) && !empty($pv['infraction_id'])) {
                    $editPreChecked = [(int)$pv['infraction_id']];
                }

                foreach ($editGroups as $cat => $infList):
                ?>
                <div class="mb-0 inf-group-edit" data-categorie="<?=htmlspecialchars($cat)?>">
                    <div class="px-2 py-1 fw-semibold text-uppercase"
                         style="font-size:.7rem;background:#f8f9fa;border-bottom:1px solid #dee2e6;color:#6c757d;position:sticky;top:0;z-index:1;">
                        <?=htmlspecialchars($cat)?>
                    </div>
                    <div class="inf-items-edit">
                        <?php foreach ($infList as $inf):
                            $ck = in_array((int)$inf['id'], $editPreChecked);
                        ?>
                        <div class="infraction-row-edit <?=$ck?'selected':''?>"
                             data-id="<?=$inf['id']?>"
                             data-libelle="<?=htmlspecialchars(strtolower($inf['libelle']))?>">
                            <label class="d-flex align-items-center gap-2 mb-0 w-100"
                                   style="cursor:pointer;">
                                <input type="checkbox" name="infraction_ids[]"
                                       value="<?=$inf['id']?>"
                                       class="inf-checkbox-edit"
                                       style="flex-shrink:0;"
                                       <?=$ck?'checked':''?>>
                                <?php if (!empty($inf['code'])): ?>
                                <code style="font-size:.75rem;<?=$ck?'color:#93c5fd;':'color:#6c757d;'?>">
                                    <?=htmlspecialchars($inf['code'])?>
                                </code>
                                <?php endif; ?>
                                <span style="font-size:.85rem;"><?=htmlspecialchars($inf['libelle'])?></span>
                                <span class="ms-auto badge"
                                      style="font-size:.65rem;background:rgba(0,0,0,.08);color:inherit;">
                                    <?=ucfirst($inf['categorie'])?>
                                </span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Résumé sélection -->
            <div id="summaryInfEdit" class="mt-2 d-flex flex-wrap gap-1 min-h-24px"></div>
        </div>
    </div>

    <!-- ── Section antiterroriste ── -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex align-items-center gap-2">
            <div class="form-check mb-0">
                <input type="checkbox" class="form-check-input" id="chkAnti"
                       name="est_antiterroriste" value="1"
                       <?=$pv['est_antiterroriste']?'checked':''?>
                       onchange="toggleAnti(this.checked)">
                <label class="form-check-label fw-semibold text-danger" for="chkAnti">
                    🔴 Affaire antiterroriste
                </label>
            </div>
        </div>
        <div class="card-body" id="antiSection"
             style="<?=$pv['est_antiterroriste']?'':'display:none'?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Région</label>
                    <select name="region_id" class="form-select" id="selRegion"
                            onchange="loadDepts(this.value)">
                        <option value="">— Sélectionner —</option>
                        <?php foreach($regions as $r): ?>
                        <option value="<?=$r['id']?>"
                            <?=$pv['region_id']==$r['id']?'selected':''?>><?=htmlspecialchars($r['nom'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Département</label>
                    <select name="departement_id" class="form-select" id="selDept"
                            onchange="loadCommunes(this.value)">
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
                                <input class="form-check-input" type="checkbox"
                                       name="primo_intervenants[]" value="<?=$pi['id']?>"
                                       id="pi<?=$pi['id']?>"
                                       <?=in_array($pi['id'], $pv['primo_ids']??[])?'checked':''?>>
                                <label class="form-check-label" for="pi<?=$pi['id']?>">
                                    <?=htmlspecialchars($pi['nom'])?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">
            <i class="bi bi-save me-1"></i>Enregistrer
        </button>
        <a href="<?=BASE_URL?>/pv/show/<?=$pv['id']?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
</div></div>

<script>
(function () {
'use strict';

/* ══════════════════════════════════════
   MULTI-CHECKBOX INFRACTIONS
══════════════════════════════════════ */
var listEl = document.getElementById('listInfractionsEdit');

if (listEl) {
    /* Délégation clic sur les lignes */
    listEl.addEventListener('change', function (e) {
        if (!e.target.classList.contains('inf-checkbox-edit')) return;
        var row = e.target.closest('.infraction-row-edit');
        if (!row) return;
        if (e.target.checked) {
            row.classList.add('selected');
            row.style.background = '#1a3c5e';
            row.style.color      = '#fff';
            row.querySelectorAll('code').forEach(function(c){ c.style.color='#93c5fd'; });
        } else {
            row.classList.remove('selected');
            row.style.background = '#fff';
            row.style.color      = '#212529';
            row.querySelectorAll('code').forEach(function(c){ c.style.color='#6c757d'; });
        }
        updateSummaryEdit();
    });

    /* Filtre texte */
    var filterEl = document.getElementById('filterInfEdit');
    if (filterEl) {
        filterEl.addEventListener('input', function () {
            var q = this.value.toLowerCase();
            listEl.querySelectorAll('.infraction-row-edit').forEach(function (row) {
                row.style.display = (row.dataset.libelle || '').indexOf(q) >= 0 ? '' : 'none';
            });
        });
    }

    function updateSummaryEdit() {
        var checked = listEl.querySelectorAll('.inf-checkbox-edit:checked');
        var cnt = document.getElementById('cntInfEdit');
        if (cnt) cnt.textContent = checked.length + ' sélectionnée(s)';

        var summary = document.getElementById('summaryInfEdit');
        if (!summary) return;
        summary.innerHTML = '';
        checked.forEach(function (cb) {
            var row  = cb.closest('.infraction-row-edit');
            var span = document.createElement('span');
            span.className = 'badge rounded-pill text-white';
            span.style.background = '#1a3c5e';
            span.style.fontSize   = '.75rem';
            var libelle = row ? (row.querySelector('span:not(.badge)') || {}).textContent || '' : '';
            span.textContent = libelle.trim();
            summary.appendChild(span);
        });
    }

    /* Init au chargement (pour les pré-cochés) */
    updateSummaryEdit();
}

/* ══════════════════════════════════════
   SECTION ANTITERRORISTE
══════════════════════════════════════ */
function toggleAnti(v) {
    document.getElementById('antiSection').style.display = v ? '' : 'none';
}

function loadDepts(regionId) {
    if (!regionId) return;
    fetch('<?=BASE_URL?>/api/departements/' + regionId)
        .then(function(r){ return r.json(); })
        .then(function(d){
            var sel = document.getElementById('selDept');
            sel.innerHTML = '<option value="">— Sélectionner —</option>';
            d.forEach(function(item){
                sel.innerHTML += '<option value="' + item.id + '">' + item.nom + '</option>';
            });
        });
}

function loadCommunes(deptId) {
    if (!deptId) return;
    fetch('<?=BASE_URL?>/api/communes/' + deptId)
        .then(function(r){ return r.json(); })
        .then(function(d){
            var sel = document.getElementById('selCommune');
            sel.innerHTML = '<option value="">— Sélectionner —</option>';
            d.forEach(function(item){
                sel.innerHTML += '<option value="' + item.id + '">' + item.nom + '</option>';
            });
        });
}

/* ══════════════════════════════════════
   Pré-charger dept/communes si anti
══════════════════════════════════════ */
<?php if ($pv['est_antiterroriste'] && $pv['region_id']): ?>
(function() {
    var regionId = <?=(int)$pv['region_id']?>;
    var deptId   = <?=(int)($pv['departement_id'] ?? 0)?>;
    var commId   = <?=(int)($pv['commune_id']     ?? 0)?>;

    fetch('<?=BASE_URL?>/api/departements/' + regionId)
        .then(function(r){ return r.json(); })
        .then(function(d){
            var sel = document.getElementById('selDept');
            sel.innerHTML = '<option value="">— Sélectionner —</option>';
            d.forEach(function(item){
                sel.innerHTML += '<option value="' + item.id + '"' +
                    (item.id == deptId ? ' selected' : '') + '>' + item.nom + '</option>';
            });
            if (deptId) {
                return fetch('<?=BASE_URL?>/api/communes/' + deptId)
                    .then(function(r){ return r.json(); });
            }
        })
        .then(function(d){
            if (!d) return;
            var sel = document.getElementById('selCommune');
            sel.innerHTML = '<option value="">— Sélectionner —</option>';
            d.forEach(function(item){
                sel.innerHTML += '<option value="' + item.id + '"' +
                    (item.id == commId ? ' selected' : '') + '>' + item.nom + '</option>';
            });
        });
})();
<?php endif; ?>

})();
</script>
