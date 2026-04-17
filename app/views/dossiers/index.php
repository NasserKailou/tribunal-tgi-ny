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
    <div class="card-header bg-white border-bottom"><span class="text-muted small"><?=$total?> dossier<?=$total>1?'s':''?> trouvé<?=$total>1?'s':''?></span></div>
    <div class="card-body p-0">
        <?php if(empty($dossiers)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-folder-x fs-1 d-block mb-2"></i>Aucun dossier trouvé</div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>N° RG</th><th>N° RP</th><th>N° RI</th><th>Type</th><th>Objet</th><th>Substitut</th><th>Cabinet</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach($dossiers as $d): ?>
            <tr>
                <td><a href="<?=BASE_URL?>/dossiers/show/<?=$d['id']?>" class="fw-semibold text-decoration-none"><?=htmlspecialchars($d['numero_rg'])?></a></td>
                <td class="text-muted small"><?=htmlspecialchars($d['numero_rp']??'—')?></td>
                <td class="text-muted small"><?=htmlspecialchars($d['numero_ri']??'—')?></td>
                <td><span class="badge <?=$d['type_affaire']==='penale'?'bg-danger':($d['type_affaire']==='civile'?'bg-primary':'bg-success')?>"><?=ucfirst($d['type_affaire'])?></span></td>
                <td class="small" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?=htmlspecialchars($d['objet'])?>"><?=htmlspecialchars($d['objet'])?></td>
                <td class="small"><?=htmlspecialchars(($d['substitut_prenom']??'').($d['substitut_nom']?' '.$d['substitut_nom']:'—'))?></td>
                <td class="small"><?=htmlspecialchars($d['cabinet_num']??'—')?></td>
                <td>
                    <?php
                    $sm=['enregistre'=>['secondary','Enregistré'],'parquet'=>['warning','Parquet'],'instruction'=>['info','Instruction'],'en_instruction'=>['info','En instruction'],'en_audience'=>['primary','Audience'],'juge'=>['success','Jugé'],'classe'=>['dark','Classé'],'appel'=>['danger','Appel']];
                    [$sc,$sl]=$sm[$d['statut']]??['secondary',$d['statut']];
                    echo "<span class=\"badge bg-{$sc}\">{$sl}</span>";
                    if($d['nb_audiences']>0) echo " <span class=\"badge bg-light text-dark border\"><i class=\"bi bi-calendar\"></i> {$d['nb_audiences']}</span>";
                    ?>
                </td>
                <td class="text-end">
                    <!-- Aperçu rapide (popup) -->
                    <button type="button"
                            class="btn btn-sm btn-outline-info me-1"
                            title="Aperçu rapide"
                            onclick="ouvrirApercuDossier(<?=$d['id']?>, '<?=htmlspecialchars($d['numero_rg'],ENT_QUOTES)?>')">
                        <i class="bi bi-eye"></i>
                    </button>
                    <!-- Ouvrir la fiche complète -->
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
            <a href="?page=<?=$i?>&q=<?=urlencode($search)?>&statut=<?=$statut?>&type=<?=$type?>" class="btn btn-sm <?=$i===$page?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     Modal Aperçu Rapide Dossier
============================================================ -->
<div class="modal fade" id="modalApercuDossier" tabindex="-1" aria-labelledby="modalApercuLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title" id="modalApercuLabel">
                    <i class="bi bi-folder2-open me-2"></i><span id="apercuTitle">Chargement…</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="apercuBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Chargement en cours…</div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
                <a id="apercuLienComplet" href="#" class="btn btn-primary btn-sm">
                    <i class="bi bi-folder2-open me-1"></i>Ouvrir la fiche complète
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var BASE_URL = '<?= BASE_URL ?>';
    var modal    = null;

    /* ---- Libellés statut ---- */
    var STATUTS = {
        enregistre   : ['secondary', 'Enregistré'],
        parquet      : ['warning',   'Parquet'],
        instruction  : ['info',      'Instruction'],
        en_instruction: ['info',     'En instruction'],
        en_audience  : ['primary',   'En audience'],
        juge         : ['success',   'Jugé'],
        classe       : ['dark',      'Classé'],
        appel        : ['danger',    'Appel']
    };

    var MODES_POURSUITE = {
        aucun : '—',
        CD    : 'Citation Directe (CD)',
        FD    : 'Flagrant Délit (FD)',
        CRCP  : 'CRCP',
        RI    : 'Réquisitoire Introductif (RI)'
    };

    window.ouvrirApercuDossier = function (id, rg) {
        /* Initialiser le modal Bootstrap une seule fois */
        if (!modal) {
            modal = new bootstrap.Modal(document.getElementById('modalApercuDossier'));
        }

        /* Titre provisoire + spinner */
        document.getElementById('apercuTitle').textContent = rg;
        document.getElementById('apercuBody').innerHTML =
            '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div>' +
            '<div class="mt-2 text-muted">Chargement en cours…</div></div>';
        document.getElementById('apercuLienComplet').href = BASE_URL + '/dossiers/show/' + id;

        modal.show();

        /* Charger les données */
        fetch(BASE_URL + '/api/dossiers/preview/' + id, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success) {
                    document.getElementById('apercuBody').innerHTML =
                        '<div class="alert alert-danger m-3">' + escHtml(res.message || 'Erreur inconnue.') + '</div>';
                    return;
                }
                renderApercu(res.data);
            })
            .catch(function () {
                document.getElementById('apercuBody').innerHTML =
                    '<div class="alert alert-danger m-3">Erreur réseau — impossible de charger l\'aperçu.</div>';
            });
    };

    function renderApercu(d) {
        var statutInfo = STATUTS[d.statut] || ['secondary', d.statut];
        var badgeType  = d.type_affaire === 'penale' ? 'danger' : (d.type_affaire === 'civile' ? 'primary' : 'success');

        /* Indicateur date limite */
        var dlHtml = '—';
        if (d.date_limite_traitement) {
            var dlParts = d.date_limite_traitement.split('/');
            var dlDate  = new Date(dlParts[2], dlParts[1] - 1, dlParts[0]);
            var now     = new Date();
            var diff    = (dlDate - now) / 86400000;
            var couleur = diff < 0 ? 'text-danger fw-bold' : (diff < 7 ? 'text-warning fw-bold' : 'text-success');
            var retardBadge = diff < 0 ? ' <span class="badge bg-danger">En retard</span>' : (diff < 7 ? ' <span class="badge bg-warning text-dark">Bientôt</span>' : '');
            dlHtml = '<span class="' + couleur + '">' + escHtml(d.date_limite_traitement) + '</span>' + retardBadge;
        }

        /* Mode de poursuite */
        var mpHtml = '—';
        if (d.mode_poursuite && d.mode_poursuite !== 'aucun') {
            mpHtml = '<span class="badge bg-info text-dark">' + escHtml(d.mode_poursuite) + '</span>' +
                     ' <small class="text-muted">' + escHtml(MODES_POURSUITE[d.mode_poursuite] || d.mode_poursuite) + '</small>';
        }

        /* Dernier mouvement */
        var mvtHtml = '<span class="text-muted small">Aucun mouvement enregistré</span>';
        if (d.dernier_mouvement) {
            var m = d.dernier_mouvement;
            mvtHtml = '<span class="badge bg-secondary me-1">' + escHtml(m.type) + '</span>' +
                      '<small class="text-muted">' + escHtml(m.description || '') + ' — ' +
                      escHtml(m.user) + ' (' + escHtml(m.date) + ')</small>';
        }

        var html = [
            '<!-- En-tête identité -->',
            '<div class="d-flex align-items-center gap-3 mb-3 p-3 bg-light rounded">',
            '  <div>',
            '    <h5 class="fw-bold mb-1">' + escHtml(d.numero_rg) + '</h5>',
            '    <div class="d-flex gap-1 flex-wrap">',
            d.numero_rp ? '<span class="badge bg-warning text-dark">RP : ' + escHtml(d.numero_rp) + '</span>' : '',
            d.numero_ri ? '<span class="badge bg-info text-dark">RI : ' + escHtml(d.numero_ri) + '</span>' : '',
            '      <span class="badge bg-' + badgeType + '">' + escHtml(d.type_affaire) + '</span>',
            '      <span class="badge bg-' + statutInfo[0] + '">' + escHtml(statutInfo[1]) + '</span>',
            '    </div>',
            '  </div>',
            '</div>',

            '<!-- Informations principales -->',
            '<div class="row g-3 mb-3">',
            '  <div class="col-sm-6"><small class="text-muted d-block">Objet</small><p class="mb-0 small">' + escHtml(d.objet) + '</p></div>',
            '  <div class="col-sm-6"><small class="text-muted d-block">Date d\'enregistrement</small><strong>' + escHtml(d.date_enregistrement) + '</strong></div>',
            '  <div class="col-sm-6"><small class="text-muted d-block">Substitut</small><strong>' + escHtml(d.substitut) + '</strong></div>',
            '  <div class="col-sm-6"><small class="text-muted d-block">Cabinet d\'instruction</small><strong>' + escHtml(d.cabinet) + '</strong></div>',
            d.juge_instruction && d.juge_instruction !== '—'
                ? '<div class="col-sm-6"><small class="text-muted d-block">Juge d\'instruction</small><strong>' + escHtml(d.juge_instruction) + '</strong></div>'
                : '',
            '  <div class="col-sm-6"><small class="text-muted d-block">Date limite traitement</small>' + dlHtml + '</div>',
            d.mode_poursuite && d.mode_poursuite !== 'aucun'
                ? '<div class="col-sm-6"><small class="text-muted d-block">Mode de poursuite</small>' + mpHtml + '</div>'
                : '',
            '</div>',

            '<!-- Compteurs -->',
            '<div class="row g-2 mb-3">',
            '  <div class="col-6 col-md-3">',
            '    <div class="card border-0 bg-light text-center py-2">',
            '      <div class="fw-bold fs-4 text-primary">' + d.nb_parties + '</div>',
            '      <small class="text-muted">Partie(s)</small>',
            '    </div>',
            '  </div>',
            '  <div class="col-6 col-md-3">',
            '    <div class="card border-0 bg-light text-center py-2">',
            '      <div class="fw-bold fs-4 text-info">' + d.nb_audiences + '</div>',
            '      <small class="text-muted">Audience(s)</small>',
            '    </div>',
            '  </div>',
            '  <div class="col-6 col-md-3">',
            '    <div class="card border-0 bg-light text-center py-2">',
            '      <div class="fw-bold fs-4 text-success">' + d.nb_jugements + '</div>',
            '      <small class="text-muted">Jugement(s)</small>',
            '    </div>',
            '  </div>',
            '  <div class="col-6 col-md-3">',
            '    <div class="card border-0 bg-light text-center py-2">',
            '      <div class="fw-bold fs-4 text-danger">' + d.nb_detenus + '</div>',
            '      <small class="text-muted">Détenu(s)</small>',
            '    </div>',
            '  </div>',
            '</div>',

            '<!-- Dernier mouvement -->',
            '<div class="border-top pt-3">',
            '  <small class="text-muted d-block mb-1"><i class="bi bi-clock-history me-1"></i>Dernier mouvement</small>',
            '  ' + mvtHtml,
            '</div>',
        ].join('\n');

        document.getElementById('apercuTitle').textContent = d.numero_rg;
        document.getElementById('apercuBody').innerHTML = html;
    }

    function escHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
})();
</script>
