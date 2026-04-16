<?php /* app/views/mandats/create.php */ ?>
<?php $pageTitle = 'Émettre un mandat'; ?>

<div class="d-flex align-items-center mb-4 gap-3">
    <a href="<?= BASE_URL ?>/mandats" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h3 mb-0 fw-bold"><i class="bi bi-file-ruled text-danger me-2"></i>Émettre un mandat de justice</h1>
</div>

<form method="post" action="<?= BASE_URL ?>/mandats/store" id="formMandat">
<?= CSRF::field() ?>

<div class="row g-4">
    <!-- Colonne gauche -->
    <div class="col-lg-7">

        <!-- Informations du mandat -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger text-white fw-semibold">
                <i class="bi bi-file-ruled me-2"></i>Informations du mandat
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Type de mandat <span class="text-danger">*</span></label>
                        <select name="type_mandat" class="form-select" required>
                            <option value="">— Choisir —</option>
                            <option value="arret">🔴 Mandat d'arrêt</option>
                            <option value="depot">⚫ Mandat de dépôt</option>
                            <option value="amener">🟡 Mandat d'amener</option>
                            <option value="comparution">🔵 Mandat de comparution</option>
                            <option value="perquisition">🟣 Mandat de perquisition</option>
                            <option value="liberation">🟢 Mandat de libération</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Dossier RG associé</label>
                        <select name="dossier_id" class="form-select" id="selDossier">
                            <option value="">— Sans dossier existant —</option>
                            <?php foreach($dossiers as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['numero_rg']) ?> — <?= htmlspecialchars(substr($d['objet'],0,50)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Un nouveau dossier sera créé si aucun n'est sélectionné</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date d'émission <span class="text-danger">*</span></label>
                        <input type="date" name="date_emission" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date d'expiration</label>
                        <input type="date" name="date_expiration" class="form-control">
                        <div class="form-text text-warning"><i class="bi bi-exclamation-triangle"></i> Laisser vide = pas d'expiration</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Motif du mandat <span class="text-danger">*</span></label>
                        <textarea name="motif" class="form-control" rows="4" required
                            placeholder="Exposez les faits et le motif justifiant l'émission du mandat…"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Infraction(s) retenue(s)</label>
                        <input type="text" name="infraction_libelle" class="form-control"
                               placeholder="Ex: Actes de terrorisme, art. 421-1 CP…">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lieu d'exécution prévu</label>
                        <input type="text" name="lieu_execution" class="form-control"
                               placeholder="Ex: Commune d'Agadez, domicile…">
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Colonne droite — Cible du mandat -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-person-fill me-2"></i>Personne ciblée par le mandat
            </div>
            <div class="card-body">

                <!-- Onglets : Détenu existant / Partie existante / Nouvelle personne -->
                <ul class="nav nav-pills nav-fill mb-3" id="cibleTabs">
                    <li class="nav-item">
                        <button class="nav-link active btn-sm" data-tab="nouveau" onclick="switchTab('nouveau',event)">
                            <i class="bi bi-person-plus me-1"></i>Nouvelle personne
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn-sm" data-tab="detenu" onclick="switchTab('detenu',event)">
                            <i class="bi bi-person-lock me-1"></i>Détenu existant
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn-sm" data-tab="partie" onclick="switchTab('partie',event)">
                            <i class="bi bi-people me-1"></i>Partie au dossier
                        </button>
                    </li>
                </ul>

                <!-- Détenu existant -->
                <div id="tab-detenu" class="cible-tab d-none">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rechercher un détenu</label>
                        <input type="text" id="searchDetenu" class="form-control" placeholder="Nom, prénom ou N° écrou…">
                        <div id="resultsDetenu" class="list-group mt-1"></div>
                    </div>
                    <input type="hidden" name="detenu_id" id="detenuId">
                    <div id="detenuSelected" class="alert alert-success d-none py-2 small"></div>
                </div>

                <!-- Partie existante -->
                <div id="tab-partie" class="cible-tab d-none">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rechercher une partie</label>
                        <input type="text" id="searchPartie" class="form-control" placeholder="Nom, prénom…">
                        <div id="resultsPartie" class="list-group mt-1"></div>
                    </div>
                    <input type="hidden" name="partie_id" id="partieId">
                    <div id="partieSelected" class="alert alert-success d-none py-2 small"></div>
                </div>

                <!-- Nouvelle personne -->
                <div id="tab-nouveau" class="cible-tab">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nouveau_nom" class="form-control" placeholder="NOM" id="nvNom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prénom</label>
                            <input type="text" name="nouveau_prenom" class="form-control" placeholder="Prénom">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date de naissance</label>
                            <input type="date" name="nouveau_ddn" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nationalité</label>
                            <input type="text" name="nouveau_nationalite" class="form-control" value="Nigérienne">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Profession</label>
                            <input type="text" name="nouveau_profession" class="form-control" placeholder="Profession">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Adresse</label>
                            <input type="text" name="nouveau_adresse" class="form-control" placeholder="Adresse connue">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Boutons -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="bi bi-file-ruled me-2"></i>Émettre le mandat
            </button>
            <a href="<?= BASE_URL ?>/mandats" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</div>

</form>

<script>
var BASE_URL = '<?= BASE_URL ?>';

function switchTab(tab, e) {
    if(e) e.preventDefault();
    document.querySelectorAll('.cible-tab').forEach(el => el.classList.add('d-none'));
    document.getElementById('tab-'+tab).classList.remove('d-none');
    document.querySelectorAll('#cibleTabs .nav-link').forEach(el => {
        el.classList.toggle('active', el.dataset.tab === tab);
    });
    // Reset hidden fields
    if(tab !== 'detenu') { document.getElementById('detenuId').value = ''; document.getElementById('detenuSelected').classList.add('d-none'); }
    if(tab !== 'partie') { document.getElementById('partieId').value = ''; document.getElementById('partieSelected').classList.add('d-none'); }
}

// Recherche détenu AJAX
let debounceDetenu;
document.getElementById('searchDetenu').addEventListener('input', function() {
    clearTimeout(debounceDetenu);
    const q = this.value.trim();
    if(q.length < 2) { document.getElementById('resultsDetenu').innerHTML = ''; return; }
    debounceDetenu = setTimeout(() => {
        fetch(BASE_URL + '/api/mandat-person-search?q=' + encodeURIComponent(q) + '&type=detenu', {credentials:'same-origin'})
            .then(r => r.json()).then(data => {
                const el = document.getElementById('resultsDetenu');
                el.innerHTML = data.map(d =>
                    `<button type="button" class="list-group-item list-group-item-action py-2 small"
                        onclick="selectDetenu(${d.id},'${d.label.replace(/'/g,"\\'")}')">
                        <i class="bi bi-person-lock me-1 text-danger"></i>${d.label}
                        <span class="badge bg-secondary ms-1">${d.statut||''}</span>
                    </button>`
                ).join('') || '<div class="list-group-item text-muted small">Aucun résultat</div>';
            });
    }, 300);
});

function selectDetenu(id, label) {
    document.getElementById('detenuId').value = id;
    document.getElementById('detenuSelected').textContent = '✓ Sélectionné : ' + label;
    document.getElementById('detenuSelected').classList.remove('d-none');
    document.getElementById('resultsDetenu').innerHTML = '';
    document.getElementById('searchDetenu').value = label;
}

// Recherche partie AJAX
let debouncePartie;
document.getElementById('searchPartie').addEventListener('input', function() {
    clearTimeout(debouncePartie);
    const q = this.value.trim();
    if(q.length < 2) { document.getElementById('resultsPartie').innerHTML = ''; return; }
    debouncePartie = setTimeout(() => {
        fetch(BASE_URL + '/api/mandat-person-search?q=' + encodeURIComponent(q) + '&type=partie', {credentials:'same-origin'})
            .then(r => r.json()).then(data => {
                const el = document.getElementById('resultsPartie');
                el.innerHTML = data.map(d =>
                    `<button type="button" class="list-group-item list-group-item-action py-2 small"
                        onclick="selectPartie(${d.id},'${d.label.replace(/'/g,"\\'")}')">
                        <i class="bi bi-person me-1 text-primary"></i>${d.label}
                    </button>`
                ).join('') || '<div class="list-group-item text-muted small">Aucun résultat</div>';
            });
    }, 300);
});

function selectPartie(id, label) {
    document.getElementById('partieId').value = id;
    document.getElementById('partieSelected').textContent = '✓ Sélectionné : ' + label;
    document.getElementById('partieSelected').classList.remove('d-none');
    document.getElementById('resultsPartie').innerHTML = '';
    document.getElementById('searchPartie').value = label;
}

// Validation
document.getElementById('formMandat').addEventListener('submit', function(e) {
    const tab = document.querySelector('#cibleTabs .nav-link.active').dataset.tab;
    if(tab === 'nouveau') {
        const nom = document.getElementById('nvNom').value.trim();
        if(!nom) { e.preventDefault(); alert('Veuillez saisir le nom de la personne ciblée.'); return; }
    }
    if(tab === 'detenu' && !document.getElementById('detenuId').value) {
        e.preventDefault(); alert('Veuillez sélectionner un détenu.'); return;
    }
    if(tab === 'partie' && !document.getElementById('partieId').value) {
        e.preventDefault(); alert('Veuillez sélectionner une partie.'); return;
    }
});
</script>