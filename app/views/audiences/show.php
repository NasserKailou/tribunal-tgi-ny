<?php $pageTitle = 'Audience — ' . ($audience['numero_audience'] ?? ''); ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/audiences">Audiences</a></li>
        <li class="breadcrumb-item active"><?=htmlspecialchars($audience['numero_audience']??'Audience')?></li>
    </ol></nav>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-calendar-event me-2 text-primary"></i><?=htmlspecialchars($audience['numero_audience']??'Audience')?>
        </h4>
        <?php
        $as = ['planifiee'=>['primary','Planifiée'],'tenue'=>['success','Tenue'],
               'renvoyee'=>['warning','Renvoyée'],'annulee'=>['danger','Annulée']];
        [$ac,$al] = $as[$audience['statut']] ?? ['secondary', $audience['statut']];
        ?>
        <span class="badge bg-<?=$ac?> fs-6 px-3 py-2"><?=$al?></span>
    </div>
</div>

<div class="row g-4">
<!-- ── Colonne gauche ─────────────────────────────────────────────────────── -->
<div class="col-lg-7">

    <!-- Détails audience -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-semibold py-2">
            <i class="bi bi-info-circle me-2"></i>Détails de l'audience
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-folder2 text-primary fs-5"></i>
                        <div>
                            <div class="small text-muted">Dossier</div>
                            <a href="<?=BASE_URL?>/dossiers/show/<?=$audience['dossier_id']?>"
                               class="fw-semibold text-decoration-none">
                                <?=htmlspecialchars($audience['numero_rg'])?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-event text-primary fs-5"></i>
                        <div>
                            <div class="small text-muted">Date & Heure</div>
                            <strong><?=date('d/m/Y à H:i', strtotime($audience['date_audience']))?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-tag text-info fs-5"></i>
                        <div>
                            <div class="small text-muted">Type</div>
                            <span class="badge bg-info text-dark text-capitalize"><?=$audience['type_audience']?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-door-open text-secondary fs-5"></i>
                        <div>
                            <div class="small text-muted">Salle</div>
                            <strong><?=htmlspecialchars($audience['salle_nom']??'—')?></strong>
                            <?=$audience['capacite']?? '' ? '<span class="text-muted small">('.$audience['capacite'].' places)</span>' : ''?>
                        </div>
                    </div>
                </div>
                <?php if($audience['notes']): ?>
                <div class="col-12">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-journal-text text-secondary fs-5 mt-1"></i>
                        <div>
                            <div class="small text-muted">Notes</div>
                            <span><?=nl2br(htmlspecialchars($audience['notes']))?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if($audience['motif_renvoi']): ?>
                <div class="col-12">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-arrow-repeat text-warning fs-5 mt-1"></i>
                        <div>
                            <div class="small text-muted">Motif de renvoi</div>
                            <span class="text-warning fw-semibold"><?=htmlspecialchars($audience['motif_renvoi'])?></span>
                            <?php if($audience['date_renvoi']): ?>
                            <br><small>Nouvelle date : <?=date('d/m/Y', strtotime($audience['date_renvoi']))?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Composition du siège -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold py-2 border-bottom">
            <i class="bi bi-people-fill me-2 text-primary"></i>Composition de l'audience
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th width="35%">Fonction</th><th>Nom</th></tr>
                </thead>
                <tbody>
                <?php
                // Président et greffier depuis la table audiences
                $presidentNom = trim(($audience['president_prenom']??'').' '.($audience['president_nom']??''));
                $greffierNom  = trim(($audience['greffier_prenom']??'').' '.($audience['greffier_nom']??''));

                $icones = [
                    'president'  => ['bi-person-badge','primary',  'Président'],
                    'greffier'   => ['bi-person-lines-fill','secondary','Greffier'],
                    'assesseur_1'=> ['bi-person','info',      'Assesseur N°1'],
                    'assesseur_2'=> ['bi-person','info',      'Assesseur N°2'],
                    'jure_1'     => ['bi-person-check','warning','Juré N°1'],
                    'jure_2'     => ['bi-person-check','warning','Juré N°2'],
                    'procureur'  => ['bi-building','danger',   'Représentant Parquet'],
                    'substitut'  => ['bi-building','danger',   'Substitut'],
                    'avocat_defense'     => ['bi-briefcase','dark','Avocat défense'],
                    'avocat_partie_civile'=> ['bi-briefcase','secondary','Avocat partie civile'],
                    'autre'      => ['bi-person-fill','muted', 'Autre membre'],
                ];
                ?>
                <?php if($presidentNom): ?>
                <tr>
                    <td><i class="bi bi-person-badge text-primary me-2"></i><span class="fw-semibold">Président</span></td>
                    <td><?=htmlspecialchars($presidentNom)?></td>
                </tr>
                <?php endif; ?>
                <?php if($greffierNom): ?>
                <tr>
                    <td><i class="bi bi-person-lines-fill text-secondary me-2"></i><span class="fw-semibold">Greffier</span></td>
                    <td><?=htmlspecialchars($greffierNom)?></td>
                </tr>
                <?php endif; ?>
                <?php foreach($membres as $m):
                    $role = $m['role_audience'];
                    [$ico, $col, $lib] = $icones[$role] ?? ['bi-person-fill','secondary', ucfirst(str_replace('_',' ',$role))];
                    $nomAff = $m['nom'] ? trim(($m['prenom']??'').' '.$m['nom']) : ($m['nom_externe'] ?? '—');
                ?>
                <tr>
                    <td>
                        <i class="bi <?=$ico?> text-<?=$col?> me-2"></i>
                        <span class="fw-semibold"><?=$lib?></span>
                    </td>
                    <td><?=htmlspecialchars($nomAff)?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(!$presidentNom && !$greffierNom && empty($membres)): ?>
                <tr><td colspan="2" class="text-center text-muted py-3">Composition non renseignée</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ── Colonne droite ─────────────────────────────────────────────────────── -->
<div class="col-lg-5">
    <?php if($audience['statut']==='planifiee' && Auth::hasRole(['admin','greffier','president','procureur'])): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold py-2 border-bottom">
            <i class="bi bi-pencil-square me-2 text-primary"></i>Mettre à jour le statut
        </div>
        <div class="card-body">
            <form method="POST" action="<?=BASE_URL?>/audiences/update-statut/<?=$audience['id']?>">
                <?=CSRF::field()?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nouveau statut</label>
                    <select name="statut" class="form-select" id="statutSelect" onchange="toggleRenvoi()">
                        <option value="tenue">✅ Audience tenue</option>
                        <option value="renvoyee">🔄 Renvoyée</option>
                        <option value="annulee">❌ Annulée</option>
                    </select>
                </div>
                <div id="renvoiBlock" style="display:none">
                    <div class="mb-3">
                        <label class="form-label">Motif de renvoi</label>
                        <textarea name="motif_renvoi" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouvelle date</label>
                        <input type="date" name="date_renvoi" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"><?=htmlspecialchars($audience['notes']??'')?></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check2-circle me-1"></i>Mettre à jour
                </button>
            </form>
        </div>
    </div>
    <script>
    function toggleRenvoi() {
        const s = document.getElementById('statutSelect').value;
        document.getElementById('renvoiBlock').style.display = s === 'renvoyee' ? 'block' : 'none';
    }
    </script>
    <?php endif; ?>

    <?php if($audience['statut']==='tenue'): ?>
    <div class="d-grid gap-2">
        <a href="<?=BASE_URL?>/jugements/create/<?=$audience['dossier_id']?>"
           class="btn btn-success btn-lg">
            <i class="bi bi-hammer me-2"></i>Saisir le jugement
        </a>
    </div>
    <?php endif; ?>

    <!-- Dossier associé -->
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <div class="small text-muted mb-1">Affaire</div>
            <div class="fw-semibold"><?=htmlspecialchars($audience['objet']??'—')?></div>
            <div class="mt-2">
                <span class="badge bg-light text-dark border"><?=ucfirst($audience['type_affaire']??'')?></span>
            </div>
            <div class="mt-3">
                <a href="<?=BASE_URL?>/dossiers/show/<?=$audience['dossier_id']?>"
                   class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-folder2-open me-1"></i>Ouvrir le dossier
                </a>
            </div>
        </div>
    </div>

</div>
</div>
