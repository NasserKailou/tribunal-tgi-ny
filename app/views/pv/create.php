<?php $pageTitle = 'Nouveau PV'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv">PV</a></li>
        <li class="breadcrumb-item active">Nouveau PV</li>
    </ol></nav>
    <h4 class="fw-bold"><i class="bi bi-file-plus me-2 text-primary"></i>Enregistrer un nouveau PV</h4>
</div>

<form method="POST" action="<?= BASE_URL ?>/pv/store" novalidate id="formNewPV">
    <?= CSRF::field() ?>
    <div class="row g-4">

        <!-- ══════════ Colonne gauche ══════════ -->
        <div class="col-lg-8">

            <!-- Identification -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-card-text me-2"></i>Identification du PV
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">N° de PV <span class="text-danger">*</span></label>
                            <input type="text" name="numero_pv" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['numero_pv'] ?? '') ?>"
                                   placeholder="ex: PV 456/2026/BCAN">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">N° RG (auto-généré)</label>
                            <input type="text" class="form-control bg-light"
                                   value="<?= htmlspecialchars($suggestRG) ?>" readonly>
                            <div class="form-text">Attribué automatiquement à l'enregistrement</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date du PV <span class="text-danger">*</span></label>
                            <input type="date" name="date_pv" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['date_pv'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de réception <span class="text-danger">*</span></label>
                            <input type="date" name="date_reception" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['date_reception'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type d'affaire <span class="text-danger">*</span></label>
                            <select name="type_affaire" class="form-select" required>
                                <option value="penale"      <?= ($_POST['type_affaire'] ?? 'penale') === 'penale'      ? 'selected' : '' ?>>Pénale</option>
                                <option value="civile"      <?= ($_POST['type_affaire'] ?? '') === 'civile'            ? 'selected' : '' ?>>Civile</option>
                                <option value="commerciale" <?= ($_POST['type_affaire'] ?? '') === 'commerciale'       ? 'selected' : '' ?>>Commerciale</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unité d'enquête</label>
                            <select name="unite_enquete_id" class="form-select">
                                <option value="">— Sélectionner —</option>
                                <?php foreach ($unites as $u): ?>
                                <option value="<?= $u['id'] ?>"
                                    <?= ($_POST['unite_enquete_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['nom']) ?> (<?= $u['type'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description des faits</label>
                            <textarea name="description_faits" class="form-control" rows="4"
                                      placeholder="Décrire brièvement les faits..."><?= htmlspecialchars($_POST['description_faits'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ INFRACTIONS UNITÉ D'ENQUÊTE ══════════════════════════ -->
            <div class="card border-0 shadow-sm mb-4" id="cardInfractions">
                <div class="card-header bg-white d-flex align-items-center justify-content-between fw-semibold">
                    <span>
                        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                        Types d'infractions retenues
                        <small class="text-muted fw-normal ms-1">(Infractions communiquées par l'unité d'enquête)</small>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            id="btnAddInfraction" title="Ajouter une nouvelle infraction"
                            data-bs-toggle="modal" data-bs-target="#modalNewInfraction">
                        <i class="bi bi-plus-lg me-1"></i>Nouvelle infraction
                    </button>
                </div>
                <div class="card-body">
                    <!-- Compteur sélection -->
                    <div class="mb-3">
                        <span id="countInfractions" class="badge bg-primary">0 sélectionnée(s)</span>
                        <small class="text-muted ms-2">Cochez toutes les infractions applicables (plusieurs choix possibles)</small>
                    </div>

                    <!-- Filtre rapide -->
                    <div class="mb-3">
                        <input type="text" id="filterInfractions" class="form-control form-control-sm"
                               placeholder="Filtrer les infractions...">
                    </div>

                    <!-- Groupement par catégorie -->
                    <?php
                    $infByCategorie = [];
                    foreach ($infractions as $inf) {
                        $infByCategorie[$inf['categorie']][] = $inf;
                    }
                    $selectedIds = array_map('intval', (array)($_POST['infraction_ids'] ?? []));
                    $catLabels = [
                        'criminelle'         => ['label' => 'Infractions criminelles',         'icon' => 'bi-exclamation-octagon-fill', 'color' => 'danger'],
                        'correctionnelle'    => ['label' => 'Infractions correctionnelles',    'icon' => 'bi-exclamation-triangle-fill','color' => 'warning'],
                        'contraventionnelle' => ['label' => 'Infractions contraventionnelles', 'icon' => 'bi-info-circle-fill',         'color' => 'secondary'],
                    ];
                    ?>
                    <div id="listInfractions">
                    <?php foreach ($catLabels as $catKey => $catInfo): ?>
                        <?php if (empty($infByCategorie[$catKey])) continue; ?>
                        <div class="mb-3 infraction-group" data-categorie="<?= $catKey ?>">
                            <h6 class="text-<?= $catInfo['color'] ?> fw-semibold mb-2 small text-uppercase">
                                <i class="bi <?= $catInfo['icon'] ?> me-1"></i><?= $catInfo['label'] ?>
                            </h6>
                            <div class="infraction-items">
                            <?php foreach ($infByCategorie[$catKey] as $inf): ?>
                                <?php $checked = in_array((int)$inf['id'], $selectedIds); ?>
                                <div class="infraction-row <?= $checked ? 'selected' : '' ?>"
                                     data-id="<?= $inf['id'] ?>"
                                     data-libelle="<?= strtolower(htmlspecialchars($inf['libelle'])) ?>">
                                    <label class="infraction-label w-100">
                                        <input type="checkbox"
                                               name="infraction_ids[]"
                                               value="<?= $inf['id'] ?>"
                                               class="infraction-checkbox"
                                               <?= $checked ? 'checked' : '' ?>>
                                        <span class="infraction-code"><?= htmlspecialchars($inf['code'] ?? '') ?></span>
                                        <span class="infraction-text"><?= htmlspecialchars($inf['libelle']) ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>

                    <?php if (empty($infractions)): ?>
                    <p class="text-muted text-center py-3">
                        <i class="bi bi-inbox me-2"></i>Aucune infraction configurée —
                        <a href="<?= BASE_URL ?>/config/infractions" target="_blank">Aller à la configuration</a>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section antiterroriste -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex align-items-center justify-content-between fw-semibold">
                    <span><i class="bi bi-shield-exclamation me-2 text-danger"></i>Affaire antiterroriste</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="est_antiterroriste"
                               id="switchAntiterro" value="1"
                               <?= isset($_POST['est_antiterroriste']) ? 'checked' : '' ?>
                               onchange="toggleAntiterro()">
                        <label class="form-check-label" for="switchAntiterro">Cocher si affaire antiterroriste</label>
                    </div>
                </div>
                <div class="card-body" id="antiterroSection"
                     style="display:<?= isset($_POST['est_antiterroriste']) ? 'block' : 'none' ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Région</label>
                            <select name="region_id" id="regionSelect" class="form-select"
                                    onchange="loadDepartements(this.value)">
                                <option value="">— Région —</option>
                                <?php foreach ($regions as $r): ?>
                                <option value="<?= $r['id'] ?>"
                                    <?= ($_POST['region_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Département</label>
                            <select name="departement_id" id="deptSelect" class="form-select"
                                    onchange="loadCommunes(this.value)">
                                <option value="">— Département —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Commune</label>
                            <select name="commune_id" id="communeSelect" class="form-select">
                                <option value="">— Commune —</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Primo intervenants</label>
                            <div class="row row-cols-2 row-cols-md-3 g-2">
                                <?php foreach ($primos as $pi): ?>
                                <div class="col">
                                    <div class="form-check border rounded p-2">
                                        <input class="form-check-input" type="checkbox"
                                               name="primo_intervenants[]" value="<?= $pi['id'] ?>"
                                               id="pi<?= $pi['id'] ?>"
                                               <?= in_array($pi['id'], (array)($_POST['primo_intervenants'] ?? [])) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="pi<?= $pi['id'] ?>">
                                            <?= htmlspecialchars($pi['nom']) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════ Colonne droite ══════════ -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-info-circle me-2"></i>Résumé
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Le numéro RG sera attribué automatiquement selon le format :<br>
                        <code>RG N°XXX/AAAA/TGI-NY</code>
                    </p>
                    <hr>
                    <div id="resumeInfractions" class="mb-3" style="display:none">
                        <small class="text-muted fw-semibold d-block mb-1">Infractions sélectionnées :</small>
                        <div id="resumeInfractionsList" class="small"></div>
                    </div>
                    <p class="text-muted small mb-0">
                        Une fois enregistré, le PV pourra être :<br>
                        • Affecté à un substitut<br>
                        • Classé sans suite<br>
                        • Transféré en instruction ou directement en audience
                    </p>
                </div>
                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-2"></i>Enregistrer le PV
                    </button>
                    <a href="<?= BASE_URL ?>/pv" class="btn btn-outline-secondary w-100 mt-2">Annuler</a>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- ══════════════════════════════════════════════════════════════════════
     MODAL — Ajouter une nouvelle infraction
══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalNewInfraction" tabindex="-1" aria-labelledby="modalNewInfractionLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalNewInfractionLabel">
                    <i class="bi bi-plus-circle me-2"></i>Nouvelle infraction
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="alertNewInfraction" class="alert d-none mb-3" role="alert"></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Code <small class="text-muted">(ex: CPV-001)</small></label>
                    <input type="text" id="newInfrCode" class="form-control text-uppercase"
                           placeholder="Optionnel — code court">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Libellé <span class="text-danger">*</span></label>
                    <input type="text" id="newInfrLibelle" class="form-control"
                           placeholder="Intitulé complet de l'infraction" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                    <select id="newInfrCategorie" class="form-select">
                        <option value="criminelle">Criminelle</option>
                        <option value="correctionnelle" selected>Correctionnelle</option>
                        <option value="contraventionnelle">Contraventionnelle</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnSaveNewInfraction">
                    <span class="spinner-border spinner-border-sm me-1 d-none" id="spinNewInfr"></span>
                    <i class="bi bi-save me-1" id="iconSaveInfr"></i>Enregistrer et sélectionner
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     CSS INFRACTIONS
══════════════════════════════════════════════════════════════════════ -->
<style>
.infraction-row {
    border: 1.5px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 5px;
    transition: all .15s ease;
    cursor: pointer;
    background: #fff;
}
.infraction-row:hover {
    border-color: #1a3c5e;
    background: #f0f4fa;
}
.infraction-row.selected {
    background: #1a3c5e !important;
    border-color: #1a3c5e !important;
    color: #fff !important;
}
.infraction-row.selected .infraction-text,
.infraction-row.selected .infraction-code {
    color: #fff !important;
}
.infraction-label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    cursor: pointer;
    margin: 0;
    user-select: none;
}
.infraction-checkbox {
    flex-shrink: 0;
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #fff;
}
.infraction-code {
    flex-shrink: 0;
    font-size: .72rem;
    font-weight: 700;
    background: rgba(0,0,0,.08);
    padding: 1px 5px;
    border-radius: 3px;
    letter-spacing: .3px;
    min-width: 36px;
    text-align: center;
}
.infraction-row.selected .infraction-code {
    background: rgba(255,255,255,.2);
}
.infraction-text {
    font-size: .88rem;
    flex: 1;
}
.infraction-row.hidden-filter { display: none; }
</style>

<!-- ══════════════════════════════════════════════════════════════════════
     JS INFRACTIONS + AJAX
══════════════════════════════════════════════════════════════════════ -->
<script>
(function() {
'use strict';

var CSRF_TOKEN = document.querySelector('[name="csrf_token"]').value;
var BASE       = '<?= BASE_URL ?>';

// ── Gestion des checkboxes colorées ───────────────────────────────
function updateRowStyle(row) {
    var cb = row.querySelector('.infraction-checkbox');
    if (cb.checked) {
        row.classList.add('selected');
    } else {
        row.classList.remove('selected');
    }
}

function updateCounter() {
    var checked = document.querySelectorAll('.infraction-checkbox:checked').length;
    var badge   = document.getElementById('countInfractions');
    badge.textContent = checked + ' sélectionnée(s)';
    badge.className   = 'badge ' + (checked > 0 ? 'bg-primary' : 'bg-secondary');

    // Résumé colonne droite
    var resumeDiv  = document.getElementById('resumeInfractions');
    var resumeList = document.getElementById('resumeInfractionsList');
    var selectedRows = document.querySelectorAll('.infraction-row.selected');
    if (selectedRows.length > 0) {
        resumeDiv.style.display = 'block';
        resumeList.innerHTML = Array.from(selectedRows).map(function(r) {
            return '<span class="badge bg-primary me-1 mb-1">' +
                r.querySelector('.infraction-text').textContent + '</span>';
        }).join('');
    } else {
        resumeDiv.style.display = 'none';
    }
}

// Délégation sur les lignes
document.getElementById('listInfractions').addEventListener('click', function(e) {
    var row = e.target.closest('.infraction-row');
    if (!row) return;

    // Si clic directement sur la checkbox, laisser le comportement natif se produire
    // puis mettre à jour le style. Sinon, toggler manuellement.
    if (e.target.type !== 'checkbox') {
        var cb = row.querySelector('.infraction-checkbox');
        cb.checked = !cb.checked;
    }
    setTimeout(function() {
        updateRowStyle(row);
        updateCounter();
    }, 0);
});

// Init au chargement (pour POST retry)
document.querySelectorAll('.infraction-row').forEach(function(row) {
    updateRowStyle(row);
});
updateCounter();

// ── Filtre texte ───────────────────────────────────────────────────
document.getElementById('filterInfractions').addEventListener('input', function() {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('.infraction-row').forEach(function(row) {
        var lib = row.dataset.libelle || '';
        if (!q || lib.indexOf(q) >= 0) {
            row.classList.remove('hidden-filter');
        } else {
            row.classList.add('hidden-filter');
        }
    });
    // Masquer les groupes sans résultat
    document.querySelectorAll('.infraction-group').forEach(function(grp) {
        var visible = grp.querySelectorAll('.infraction-row:not(.hidden-filter)').length;
        grp.style.display = visible > 0 ? '' : 'none';
    });
});

// ── Modal nouvelle infraction + AJAX ──────────────────────────────
document.getElementById('btnSaveNewInfraction').addEventListener('click', function() {
    var code     = document.getElementById('newInfrCode').value.trim().toUpperCase();
    var libelle  = document.getElementById('newInfrLibelle').value.trim();
    var categorie= document.getElementById('newInfrCategorie').value;
    var alertDiv = document.getElementById('alertNewInfraction');
    var spin     = document.getElementById('spinNewInfr');
    var icon     = document.getElementById('iconSaveInfr');

    alertDiv.className = 'alert d-none';

    if (!libelle) {
        alertDiv.className  = 'alert alert-warning';
        alertDiv.textContent = 'Le libellé est obligatoire.';
        return;
    }

    // Loader
    spin.classList.remove('d-none');
    icon.classList.add('d-none');
    document.getElementById('btnSaveNewInfraction').disabled = true;

    var formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('code', code);
    formData.append('libelle', libelle);
    formData.append('categorie', categorie);

    fetch(BASE + '/api/infractions/store', {
        method: 'POST',
        body:   formData,
        credentials: 'same-origin',
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        spin.classList.add('d-none');
        icon.classList.remove('d-none');
        document.getElementById('btnSaveNewInfraction').disabled = false;

        if (!data.success) {
            alertDiv.className   = 'alert alert-danger';
            alertDiv.textContent = data.message || 'Erreur inconnue.';
            return;
        }

        var inf = data.infraction;
        // Ajouter la ligne dans la liste
        addInfractionToList(inf);

        // Fermer le modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('modalNewInfraction'));
        if (modal) modal.hide();

        // Réinitialiser le formulaire modal
        document.getElementById('newInfrCode').value    = '';
        document.getElementById('newInfrLibelle').value = '';
        document.getElementById('newInfrCategorie').value = 'correctionnelle';
    })
    .catch(function(err) {
        spin.classList.add('d-none');
        icon.classList.remove('d-none');
        document.getElementById('btnSaveNewInfraction').disabled = false;
        alertDiv.className   = 'alert alert-danger';
        alertDiv.textContent = 'Erreur réseau : ' + err.message;
    });
});

function addInfractionToList(inf) {
    // Trouver le groupe correspondant à la catégorie
    var grp = document.querySelector('.infraction-group[data-categorie="' + inf.categorie + '"]');
    if (!grp) {
        // Créer le groupe s'il n'existe pas
        grp = document.createElement('div');
        grp.className = 'mb-3 infraction-group';
        grp.dataset.categorie = inf.categorie;
        var catIcons = {
            criminelle: 'bi-exclamation-octagon-fill text-danger',
            correctionnelle: 'bi-exclamation-triangle-fill text-warning',
            contraventionnelle: 'bi-info-circle-fill text-secondary'
        };
        var catLabels = {
            criminelle: 'Infractions criminelles',
            correctionnelle: 'Infractions correctionnelles',
            contraventionnelle: 'Infractions contraventionnelles'
        };
        grp.innerHTML = '<h6 class="fw-semibold mb-2 small text-uppercase"><i class="bi ' + (catIcons[inf.categorie] || 'bi-tag') + ' me-1"></i>' + (catLabels[inf.categorie] || inf.categorie) + '</h6><div class="infraction-items"></div>';
        document.getElementById('listInfractions').appendChild(grp);
    }

    var items = grp.querySelector('.infraction-items');
    var row   = document.createElement('div');
    row.className    = 'infraction-row selected';
    row.dataset.id   = inf.id;
    row.dataset.libelle = (inf.libelle || '').toLowerCase();

    row.innerHTML = '<label class="infraction-label w-100">' +
        '<input type="checkbox" name="infraction_ids[]" value="' + inf.id + '" class="infraction-checkbox" checked>' +
        '<span class="infraction-code">' + (inf.code || '') + '</span>' +
        '<span class="infraction-text">' + inf.libelle + '</span>' +
        '</label>';

    items.appendChild(row);
    updateCounter();

    // Scroll vers la ligne
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// ── Département / Commune (antiterroriste) ─────────────────────────
window.toggleAntiterro = function() {
    document.getElementById('antiterroSection').style.display =
        document.getElementById('switchAntiterro').checked ? 'block' : 'none';
};

window.loadDepartements = function(regionId) {
    if (!regionId) {
        document.getElementById('deptSelect').innerHTML    = '<option value="">— Département —</option>';
        document.getElementById('communeSelect').innerHTML = '<option value="">— Commune —</option>';
        return;
    }
    fetch(BASE + '/api/departements/' + regionId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var html = '<option value="">— Département —</option>';
            data.forEach(function(d) { html += '<option value="' + d.id + '">' + d.nom + '</option>'; });
            document.getElementById('deptSelect').innerHTML    = html;
            document.getElementById('communeSelect').innerHTML = '<option value="">— Commune —</option>';
        });
};

window.loadCommunes = function(deptId) {
    if (!deptId) {
        document.getElementById('communeSelect').innerHTML = '<option value="">— Commune —</option>';
        return;
    }
    fetch(BASE + '/api/communes/' + deptId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var html = '<option value="">— Commune —</option>';
            data.forEach(function(c) { html += '<option value="' + c.id + '">' + c.nom + '</option>'; });
            document.getElementById('communeSelect').innerHTML = html;
        });
};

})();
</script>
