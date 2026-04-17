<?php $pageTitle = 'Dossiers'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-folder2-open me-2 text-primary"></i>Dossiers judiciaires</h4>
    <?php if (Auth::hasRole(['admin','greffier','procureur','substitut_procureur'])): ?>
    <a href="<?= BASE_URL ?>/dossiers/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau dossier</a>
    <?php endif; ?>
</div>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="GET" action="<?= BASE_URL ?>/dossiers">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control" placeholder="N° RG, RP, RI, objet..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach(['enregistre'=>'Enregistré','parquet'=>'Parquet','instruction'=>'Instruction','en_instruction'=>'En instruction','en_audience'=>'En audience','juge'=>'Jugé','classe'=>'Classé','appel'=>'Appel'] as $v=>$l): ?>
                    <option value="<?=$v?>" <?=$statut===$v?'selected':''?>><?=$l?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">Tous types</option>
                    <option value="penale" <?=$type==='penale'?'selected':''?>>Pénale</option>
                    <option value="civile" <?=$type==='civile'?'selected':''?>>Civile</option>
                    <option value="commerciale" <?=$type==='commerciale'?'selected':''?>>Commerciale</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrer</button>
                <a href="<?= BASE_URL ?>/dossiers" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <span class="text-muted small"><?=$total?> dossier<?=$total>1?'s':''?> trouvé<?=$total>1?'s':''?></span>
    </div>
    <div class="card-body p-0">
        <?php if(empty($dossiers)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-folder-x fs-1 d-block mb-2"></i>Aucun dossier trouvé
        </div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>N° RG</th><th>N° RP</th><th>N° RI</th>
                    <th>Type</th><th>Objet</th><th>Substitut</th>
                    <th>Cabinet</th><th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($dossiers as $d): ?>
            <tr>
                <td>
                    <a href="<?=BASE_URL?>/dossiers/show/<?=$d['id']?>" class="fw-semibold text-decoration-none">
                        <?=htmlspecialchars($d['numero_rg'])?>
                    </a>
                </td>
                <td class="text-muted small"><?=htmlspecialchars($d['numero_rp']??'—')?></td>
                <td class="text-muted small"><?=htmlspecialchars($d['numero_ri']??'—')?></td>
                <td>
                    <span class="badge <?=$d['type_affaire']==='penale'?'bg-danger':($d['type_affaire']==='civile'?'bg-primary':'bg-success')?>">
                        <?=ucfirst($d['type_affaire'])?>
                    </span>
                </td>
                <td class="small" style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                    title="<?=htmlspecialchars($d['objet'])?>">
                    <?=htmlspecialchars($d['objet'])?>
                </td>
                <td class="small"><?=htmlspecialchars(trim(($d['substitut_prenom']??'').' '.($d['substitut_nom']??'')) ?: '—')?></td>
                <td class="small"><?=htmlspecialchars($d['cabinet_num']??'—')?></td>
                <td>
                    <?php
                    $sm=['enregistre'=>['secondary','Enregistré'],'parquet'=>['warning','Parquet'],
                         'instruction'=>['info','Instruction'],'en_instruction'=>['info','En instruction'],
                         'en_audience'=>['primary','Audience'],'juge'=>['success','Jugé'],
                         'classe'=>['dark','Classé'],'appel'=>['danger','Appel']];
                    [$sc,$sl]=$sm[$d['statut']]??['secondary',$d['statut']];
                    echo "<span class=\"badge bg-{$sc}\">{$sl}</span>";
                    if($d['nb_audiences']>0) echo " <span class=\"badge bg-light text-dark border\"><i class=\"bi bi-calendar\"></i> {$d['nb_audiences']}</span>";
                    ?>
                </td>
                <td class="text-end">
                    <!-- Bouton aperçu : data-bs-toggle ouvre le modal Bootstrap nativement -->
                    <button type="button"
                            class="btn btn-sm btn-outline-info me-1"
                            title="Aperçu rapide"
                            data-bs-toggle="modal"
                            data-bs-target="#modalApercuDossier"
                            data-dossier-id="<?= (int)$d['id'] ?>"
                            data-dossier-rg="<?= htmlspecialchars($d['numero_rg'], ENT_QUOTES) ?>">
                        <i class="bi bi-eye"></i>
                    </button>
                    <a href="<?=BASE_URL?>/dossiers/show/<?=$d['id']?>"
                       class="btn btn-sm btn-outline-primary"
                       title="Fiche complète">
                        <i class="bi bi-folder2-open"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if($totalPages>1): ?>
        <div class="d-flex justify-content-center py-3">
            <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a href="?page=<?=$i?>&q=<?=urlencode($search)?>&statut=<?=$statut?>&type=<?=$type?>"
               class="btn btn-sm <?=$i===$page?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ================================================================
     Modal Aperçu Rapide Dossier
     Le modal est déclenché par data-bs-toggle="modal" sur le bouton.
     L'événement show.bs.modal injecte les données avant l'animation.
================================================================ -->
<div class="modal fade" id="modalApercuDossier" tabindex="-1"
     aria-labelledby="modalApercuDossierLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#0a2342;color:#fff">
                <h5 class="modal-title" id="modalApercuDossierLabel">
                    <i class="bi bi-folder2-open me-2"></i>
                    <span id="apercu-title">…</span>
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-3" id="apercu-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted mb-0">Chargement…</p>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Fermer
                </button>
                <a id="apercu-lien-complet" href="#"
                   class="btn btn-sm" style="background:#0a2342;color:#fff;border-color:#0a2342">
                    <i class="bi bi-folder2-open me-1"></i>Fiche complète
                </a>
            </div>
        </div>
    </div>
</div>

<script>
/* ================================================================
   Aperçu rapide — Dossiers (corrigé v3.5)
   - Utilise l'événement show.bs.modal au lieu d'ouvrir manuellement
   - Garantit que Bootstrap est chargé avant d'utiliser ses APIs
================================================================ */
(function () {
    'use strict';

    var API_BASE = <?= json_encode(rtrim(BASE_URL, '/')) ?>;

    var STATUTS = {
        enregistre:    {cls: 'secondary', lbl: 'Enregistré'},
        parquet:       {cls: 'warning',   lbl: 'Parquet'},
        instruction:   {cls: 'info',      lbl: 'Instruction'},
        en_instruction:{cls: 'info',      lbl: 'En instruction'},
        en_audience:   {cls: 'primary',   lbl: 'En audience'},
        juge:          {cls: 'success',   lbl: 'Jugé'},
        classe:        {cls: 'dark',      lbl: 'Classé'},
        appel:         {cls: 'danger',    lbl: 'Appel'}
    };

    var MODES = {
        aucun: '—',
        CD:    'Citation Directe',
        FD:    'Flagrant Délit',
        CRCP:  'CRCP',
        RI:    'Réquisitoire Introductif'
    };

    function esc(s) {
        if (s == null || s === '') return '—';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(String(s)));
        return d.innerHTML;
    }

    function showSpinner() {
        document.getElementById('apercu-body').innerHTML =
            '<div class="text-center py-5">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="visually-hidden">Chargement…</span></div>' +
            '<p class="mt-2 text-muted mb-0">Chargement en cours…</p></div>';
    }

    function showError(msg) {
        document.getElementById('apercu-body').innerHTML =
            '<div class="alert alert-danger m-3"><i class="bi bi-exclamation-triangle me-2"></i>' +
            esc(msg) + '</div>';
    }

    function renderApercu(d) {
        var s    = STATUTS[d.statut] || {cls: 'secondary', lbl: d.statut};
        var tCls = d.type_affaire === 'penale' ? 'danger' :
                   (d.type_affaire === 'civile' ? 'primary' : 'success');

        /* Date limite colorée */
        var dlHtml = '<span class="text-muted">—</span>';
        if (d.date_limite_traitement) {
            var parts  = d.date_limite_traitement.split('/');
            var dlDate = new Date(parseInt(parts[2], 10), parseInt(parts[1], 10) - 1, parseInt(parts[0], 10));
            var diff   = (dlDate - new Date()) / 86400000;
            var col    = diff < 0   ? 'text-danger fw-bold' :
                         diff < 7   ? 'text-warning fw-bold' : 'text-success fw-bold';
            var badge  = diff < 0   ? ' <span class="badge bg-danger ms-1">En retard</span>' :
                         diff < 7   ? ' <span class="badge bg-warning text-dark ms-1">Bientôt</span>' : '';
            dlHtml = '<span class="' + col + '">' + esc(d.date_limite_traitement) + '</span>' + badge;
        }

        /* Mode de poursuite */
        var mpHtml = '';
        if (d.mode_poursuite && d.mode_poursuite !== 'aucun') {
            mpHtml = '<div class="col-sm-6">' +
                     '<small class="text-muted d-block">Mode de poursuite</small>' +
                     '<span class="badge bg-info text-dark">' + esc(d.mode_poursuite) + '</span> ' +
                     '<small class="text-muted">' + esc(MODES[d.mode_poursuite] || d.mode_poursuite) + '</small>' +
                     '</div>';
        }

        /* Dernier mouvement */
        var mvtHtml = '<p class="text-muted small mb-0"><i class="bi bi-clock me-1"></i>Aucun mouvement enregistré</p>';
        if (d.dernier_mouvement) {
            var m = d.dernier_mouvement;
            mvtHtml = '<span class="badge bg-secondary me-1">' + esc(m.type) + '</span>' +
                      '<small class="text-muted">' + esc(m.description || '') +
                      ' — <em>' + esc(m.user) + '</em> (' + esc(m.date) + ')</small>';
        }

        var html =
            /* Identité */
            '<div class="d-flex flex-wrap align-items-center gap-2 p-3 mb-3 rounded"' +
            ' style="background:#f0f4ff;border-left:4px solid #0a2342">' +
            '<h5 class="fw-bold mb-0 me-2">' + esc(d.numero_rg) + '</h5>' +
            (d.numero_rp ? '<span class="badge bg-warning text-dark">RP: ' + esc(d.numero_rp) + '</span>' : '') +
            (d.numero_ri ? '<span class="badge bg-info text-dark">RI: ' + esc(d.numero_ri) + '</span>' : '') +
            '<span class="badge bg-' + tCls + '">' + esc(d.type_affaire) + '</span>' +
            '<span class="badge bg-' + s.cls + '">' + esc(s.lbl) + '</span>' +
            '</div>' +

            /* Grille d'infos */
            '<div class="row g-3 mb-3">' +
            '<div class="col-12"><small class="text-muted d-block">Objet</small>' +
            '<p class="mb-0 small fw-semibold">' + esc(d.objet) + '</p></div>' +
            '<div class="col-sm-6"><small class="text-muted d-block">Date d\'enregistrement</small>' +
            '<strong>' + esc(d.date_enregistrement) + '</strong></div>' +
            '<div class="col-sm-6"><small class="text-muted d-block">Date limite traitement</small>' +
            dlHtml + '</div>' +
            '<div class="col-sm-6"><small class="text-muted d-block">Substitut</small>' +
            '<strong>' + esc(d.substitut) + '</strong></div>' +
            '<div class="col-sm-6"><small class="text-muted d-block">Cabinet d\'instruction</small>' +
            '<strong>' + esc(d.cabinet) + '</strong></div>' +
            (d.juge_instruction && d.juge_instruction !== '—' ?
                '<div class="col-sm-6"><small class="text-muted d-block">Juge d\'instruction</small>' +
                '<strong>' + esc(d.juge_instruction) + '</strong></div>' : '') +
            mpHtml +
            '</div>' +

            /* Compteurs */
            '<div class="row g-2 mb-3">' +
            '<div class="col-6 col-md-3"><div class="card border-0 bg-light text-center py-2 h-100">' +
            '<div class="fw-bold fs-3 text-primary">' + (d.nb_parties || 0) + '</div>' +
            '<small class="text-muted">Partie(s)</small></div></div>' +
            '<div class="col-6 col-md-3"><div class="card border-0 bg-light text-center py-2 h-100">' +
            '<div class="fw-bold fs-3 text-info">' + (d.nb_audiences || 0) + '</div>' +
            '<small class="text-muted">Audience(s)</small></div></div>' +
            '<div class="col-6 col-md-3"><div class="card border-0 bg-light text-center py-2 h-100">' +
            '<div class="fw-bold fs-3 text-success">' + (d.nb_jugements || 0) + '</div>' +
            '<small class="text-muted">Jugement(s)</small></div></div>' +
            '<div class="col-6 col-md-3"><div class="card border-0 bg-light text-center py-2 h-100">' +
            '<div class="fw-bold fs-3 text-danger">' + (d.nb_detenus || 0) + '</div>' +
            '<small class="text-muted">Détenu(s)</small></div></div>' +
            '</div>' +

            /* Dernier mouvement */
            '<div class="border-top pt-3">' +
            '<small class="text-muted d-block mb-1">' +
            '<i class="bi bi-clock-history me-1"></i>Dernier mouvement</small>' +
            mvtHtml + '</div>';

        document.getElementById('apercu-title').textContent = d.numero_rg || '—';
        document.getElementById('apercu-body').innerHTML    = html;
    }

    function chargerApercu(id) {
        showSpinner();
        fetch(API_BASE + '/api/dossiers/preview/' + encodeURIComponent(id), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (r) {
            if (!r.ok) { throw new Error('HTTP ' + r.status + ' — ' + r.statusText); }
            return r.json();
        })
        .then(function (res) {
            if (res && res.success) {
                renderApercu(res.data);
            } else {
                showError((res && res.message) ? res.message : 'Réponse inattendue du serveur.');
            }
        })
        .catch(function (err) {
            showError('Impossible de charger l\'aperçu : ' + err.message);
        });
    }

    /* ── Écoute sur l'événement Bootstrap show.bs.modal ──
       Cet événement se déclenche AVANT l'ouverture de l'animation,
       et le bouton déclencheur est disponible dans relatedTarget.
       Avantage : Bootstrap est forcément chargé à ce stade. */
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('modalApercuDossier');
        if (!modalEl) return;

        modalEl.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            if (!btn) return;
            var id = btn.getAttribute('data-dossier-id');
            var rg = btn.getAttribute('data-dossier-rg') || '…';

            document.getElementById('apercu-title').textContent     = rg;
            document.getElementById('apercu-lien-complet').href     = API_BASE + '/dossiers/show/' + id;

            chargerApercu(id);
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('apercu-title').textContent = '…';
            document.getElementById('apercu-body').innerHTML    =
                '<div class="text-center py-5">' +
                '<div class="spinner-border text-primary" role="status"></div>' +
                '<p class="mt-2 text-muted mb-0">Chargement…</p></div>';
            document.getElementById('apercu-lien-complet').href = '#';
        });
    });

})();
</script>
