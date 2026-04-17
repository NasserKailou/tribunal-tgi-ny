<?php $pageTitle = 'Détail PV — ' . $pv['numero_rg']; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/pv">PV</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($pv['numero_rg']) ?></li>
    </ol></nav>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-file-text me-2 text-primary"></i><?= htmlspecialchars($pv['numero_rg']) ?></h4>
            <p class="text-muted mb-0"><?= htmlspecialchars($pv['numero_pv']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if (Auth::hasRole(['admin','greffier','procureur'])): ?>
            <a href="<?= BASE_URL ?>/pv/edit/<?= $pv['id'] ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/export/pv/<?= $pv['id'] ?>" target="_blank" class="btn btn-outline-danger"><i class="bi bi-file-pdf me-1"></i>Imprimer / PDF</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Infos principales -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Informations générales</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><small class="text-muted d-block">Date du PV</small><strong><?= date('d/m/Y', strtotime($pv['date_pv'])) ?></strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Date de réception</small><strong><?= date('d/m/Y', strtotime($pv['date_reception'])) ?></strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Type d'affaire</small>
                        <span class="badge <?= $pv['type_affaire']==='penale'?'bg-danger':($pv['type_affaire']==='civile'?'bg-primary':'bg-success') ?> fs-6"><?= ucfirst($pv['type_affaire']) ?></span>
                        <?php if ($pv['est_antiterroriste']): ?><span class="badge bg-dark ms-1"><i class="bi bi-shield-exclamation"></i> Anti-terroriste</span><?php endif; ?>
                    </div>
                    <div class="col-md-6"><small class="text-muted d-block">Unité d'enquête</small><strong><?= htmlspecialchars($pv['unite_nom'] ?? '—') ?></strong></div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Type d'infraction</small>
                        <?php if (!empty($pv['infraction_libelle'])): ?>
                        <strong><?= htmlspecialchars($pv['infraction_libelle']) ?></strong>
                        <?php $catColors = ['criminelle'=>'danger','correctionnelle'=>'warning','contraventionnelle'=>'secondary']; ?>
                        <span class="badge bg-<?= $catColors[$pv['infraction_categorie']] ?? 'secondary' ?> ms-1"><?= ucfirst($pv['infraction_categorie'] ?? '') ?></span>
                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                    </div>
                    <div class="col-12"><small class="text-muted d-block">Description des faits</small><p class="mb-0"><?= nl2br(htmlspecialchars($pv['description_faits'] ?? '—')) ?></p></div>
                </div>
            </div>
        </div>

        <?php if ($pv['est_antiterroriste']): ?>
        <!-- Section antiterroriste -->
        <div class="card border-danger border-0 shadow-sm mb-4" style="border-left: 4px solid #dc3545 !important">
            <div class="card-header text-white fw-semibold" style="background:#dc3545"><i class="bi bi-shield-exclamation me-2"></i>Informations antiterroristes</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted d-block">Région</small><strong><?= htmlspecialchars($pv['region_nom'] ?? '—') ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Département</small><strong><?= htmlspecialchars($pv['dept_nom'] ?? '—') ?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Commune</small><strong><?= htmlspecialchars($pv['commune_nom'] ?? '—') ?></strong></div>
                    <div class="col-12">
                        <small class="text-muted d-block mb-2">Primo intervenants</small>
                        <?php if (!empty($pv['primo_intervenants'])): ?>
                        <?php foreach ($pv['primo_intervenants'] as $pi): ?>
                        <span class="badge bg-dark me-1 mb-1"><?= htmlspecialchars($pi['nom']) ?></span>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <span class="text-muted">Aucun renseigné</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($dossier): ?>
        <!-- Dossier lié -->
        <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #198754 !important">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-folder2 me-2 text-success"></i>Dossier lié</div>
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <strong><?= htmlspecialchars($dossier['numero_rg']) ?></strong>
                    <?php if ($dossier['numero_rp']): ?><span class="badge bg-secondary ms-1"><?= htmlspecialchars($dossier['numero_rp']) ?></span><?php endif; ?>
                    <?php if ($dossier['numero_ri']): ?><span class="badge bg-info text-dark ms-1"><?= htmlspecialchars($dossier['numero_ri']) ?></span><?php endif; ?>
                    <div class="text-muted small mt-1"><?= htmlspecialchars($dossier['objet']) ?></div>
                </div>
                <a href="<?= BASE_URL ?>/dossiers/show/<?= $dossier['id'] ?>" class="btn btn-outline-success btn-sm">Voir le dossier <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Colonne droite : workflow -->
    <div class="col-lg-4">
        <!-- Statut -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-diagram-3 me-2"></i>Statut & Workflow</div>
            <div class="card-body">
                <?php
                $statutMap=['recu'=>['secondary','Reçu','Nouveau PV, en attente d\'affectation'],'en_traitement'=>['warning','En traitement','Affecté à un substitut'],'classe'=>['dark','Classé','Classé sans suite'],'transfere_instruction'=>['info','→ Instruction','Transféré en cabinet d\'instruction'],'transfere_jugement_direct'=>['success','→ Audience directe','Envoyé directement en audience']];
                [$cls,$lbl,$desc]=$statutMap[$pv['statut']]??['secondary',$pv['statut'],''];
                ?>
                <div class="text-center mb-3">
                    <span class="badge bg-<?= $cls ?> p-3 fs-6"><?= $lbl ?></span>
                    <p class="text-muted small mt-2"><?= $desc ?></p>
                </div>

                <?php if ($pv['substitut_id']): ?>
                <div class="mb-2"><small class="text-muted">Substitut assigné</small><br>
                    <strong><?= htmlspecialchars($pv['substitut_prenom'].' '.$pv['substitut_nom']) ?></strong><br>
                    <?php if ($pv['date_affectation_substitut']): ?>
                    <small class="text-muted">Depuis le <?= date('d/m/Y', strtotime($pv['date_affectation_substitut'])) ?></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($pv['statut'] === 'classe' && $pv['motif_classement']): ?>
                <div class="alert alert-secondary small mt-2 py-2"><?= htmlspecialchars($pv['motif_classement']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($pv['statut'] === 'classe' && Auth::hasRole(['admin','procureur'])): ?>
        <div class="card border-0 shadow-sm mb-3 border-warning">
            <div class="card-header bg-warning fw-semibold">
                <i class="bi bi-arrow-counterclockwise me-2"></i>PV classé sans suite
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    <strong>Classé le :</strong> <?= $pv['date_classement'] ? date('d/m/Y', strtotime($pv['date_classement'])) : '—' ?>
                </p>
                <button class="btn btn-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalDeclasser">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Déclasser ce PV
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <?php if (in_array($pv['statut'], ['recu','en_traitement'])): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-play-circle me-2 text-primary"></i>Actions</div>
            <div class="card-body d-grid gap-2">

                <?php if (Auth::hasRole(['admin','procureur','president']) && $pv['statut']==='recu'): ?>
                <!-- Affecter substitut -->
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalAffecter">
                    <i class="bi bi-person-check me-2"></i>Affecter un substitut
                </button>
                <?php endif; ?>

                <?php if (Auth::hasRole(['admin','procureur','substitut_procureur']) && $pv['statut']==='en_traitement'): ?>
                <!-- Classer -->
                <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#modalClasser">
                    <i class="bi bi-archive me-2"></i>Classer sans suite
                </button>
                <!-- Transférer -->
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTransferer">
                    <i class="bi bi-send me-2"></i>Transférer
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Affecter -->
<div class="modal fade" id="modalAffecter" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Affecter un substitut</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="<?= BASE_URL ?>/pv/affecter/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <label class="form-label">Substitut du procureur</label>
                    <select name="substitut_id" class="form-select" required id="selectSubstitut">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($substituts as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="substitutChargeInfo" class="small text-muted mt-1"></div>
                    <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="suggererSubstitut()">
                        <i class="bi bi-magic me-1"></i>Suggérer le moins chargé
                    </button>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-warning">Affecter</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Classer -->
<div class="modal fade" id="modalClasser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Classer sans suite</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="<?= BASE_URL ?>/pv/classer/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <label class="form-label">Motif de classement <span class="text-danger">*</span></label>
                    <textarea name="motif_classement" class="form-control" rows="4" required placeholder="Indiquer le motif de classement..."></textarea>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-dark">Classer</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transférer -->
<div class="modal fade" id="modalTransferer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Transférer le PV</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="<?= BASE_URL ?>/pv/transferer/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Destination <span class="text-danger">*</span></label>
                        <div class="d-grid gap-2">
                            <div class="form-check border rounded p-3">
                                <input class="form-check-input" type="radio" name="destination" value="instruction" id="destInstr" required onchange="toggleCabinet(true)">
                                <label class="form-check-label" for="destInstr"><strong>Cabinet d'instruction</strong> — Ouvre une instruction judiciaire (génère RP + RI)</label>
                            </div>
                            <div class="form-check border rounded p-3">
                                <input class="form-check-input" type="radio" name="destination" value="audience_directe" id="destAud" onchange="toggleCabinet(false)">
                                <label class="form-check-label" for="destAud"><strong>Audience directe</strong> — Renvoie directement en audience de jugement (génère RP)</label>
                            </div>
                        </div>
                    </div>
                    <div id="cabinetBlock" style="display:none" class="mb-3">
                        <label class="form-label">Cabinet d'instruction</label>
                        <select name="cabinet_id" class="form-select" id="selectCabinet">
                            <option value="">— Sélectionner —</option>
                            <?php foreach ($cabinets as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['numero'] . ' — ' . $c['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="cabinetChargeInfo" class="mt-1 small text-muted"></div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-1" onclick="suggererCabinet()">
                            <i class="bi bi-magic me-1"></i>Suggérer le moins chargé
                        </button>
                    </div>
                    <!-- Mode de poursuite (visible uniquement si instruction) -->
                    <div id="modePoursuiteBlock" style="display:none" class="mb-3">
                        <label class="form-label fw-semibold">Mode de poursuite <span class="text-danger">*</span></label>
                        <select name="mode_poursuite" class="form-select" id="selectModePoursuite">
                            <option value="aucun">— AUCUN —</option>
                            <option value="CD">CD — Citation Directe</option>
                            <option value="FD">FD — Flagrant Délit</option>
                            <option value="CRCP">CRCP — Comparution sur Reconnaissance Préalable de Culpabilité</option>
                            <option value="RI">RI — Réquisitoire Introductif</option>
                        </select>
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Choisissez le mode de poursuite pour le dossier d'instruction.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Objet du dossier <span class="text-danger">*</span></label>
                        <textarea name="objet" class="form-control" rows="3" required><?= htmlspecialchars($pv['description_faits']??'') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-success">Transférer</button></div>
            </form>
        </div>
    </div>
</div>
<script>
function toggleCabinet(show){
    document.getElementById('cabinetBlock').style.display=show?'block':'none';
    document.getElementById('modePoursuiteBlock').style.display=show?'block':'none';
}
function suggererCabinet(){
    fetch('<?= BASE_URL ?>/api/cabinets/charge')
    .then(r=>r.json())
    .then(data=>{
        if(data.success && data.data.length){
            var best = data.data[0];
            var sel  = document.getElementById('selectCabinet');
            if(sel){
                sel.value = best.id;
                document.getElementById('cabinetChargeInfo').innerHTML =
                    '<i class="bi bi-info-circle text-success me-1"></i>Suggéré : <strong>' +
                    best.numero + ' — ' + best.libelle + '</strong> (' +
                    best.nb_dossiers + ' dossier(s) actif(s))';
            }
        }
    }).catch(()=>{});
}
function suggererSubstitut(){
    fetch('<?= BASE_URL ?>/api/substituts/charge')
    .then(r=>r.json())
    .then(data=>{
        if(data.success && data.data.length){
            var best = data.data[0];
            var sel  = document.getElementById('selectSubstitut');
            if(sel){
                sel.value = best.id;
                document.getElementById('substitutChargeInfo').innerHTML =
                    '<i class="bi bi-info-circle text-success me-1"></i>Suggéré : <strong>' +
                    best.prenom + ' ' + best.nom + '</strong> (' +
                    best.nb_pvs + ' PV(s) en cours)';
            }
        }
    }).catch(()=>{});
}
</script>

<!-- Modal Déclasser PV -->
<div class="modal fade" id="modalDeclasser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Déclasser le PV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/pv/declasser/<?= $pv['id'] ?>">
                <?= CSRF::field() ?>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Le PV sera remis au statut <strong>En traitement</strong> pour reprise du dossier.
                    </div>
                    <?php if ($pv['motif_classement']): ?>
                    <div class="mb-3">
                        <small class="text-muted">Motif du classement initial :</small>
                        <p class="fst-italic small"><?= htmlspecialchars($pv['motif_classement']) ?></p>
                    </div>
                    <?php endif; ?>
                    <label class="form-label fw-bold">Motif du déclassement <span class="text-danger">*</span></label>
                    <textarea name="motif_declassement" class="form-control" rows="4" required
                              placeholder="Exposez les raisons du déclassement (nouveaux éléments, erreur, …)"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Confirmer le déclassement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
