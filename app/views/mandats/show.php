<?php $pageTitle = 'Mandat ' . htmlspecialchars($mandat['numero']); ?>

<?php
$typeLabels   = ['arret'=>['danger','Mandat d\'arrêt'],'depot'=>['dark','Mandat de dépôt'],'amener'=>['warning','Mandat d\'amener'],'comparution'=>['info','Mandat de comparution'],'perquisition'=>['secondary','Mandat de perquisition'],'liberation'=>['success','Mandat de libération']];
$statutLabels = ['emis'=>['primary','Émis'],'signifie'=>['info','Signifié'],'execute'=>['success','Exécuté'],'annule'=>['danger','Annulé'],'expire'=>['secondary','Expiré']];
[$tc,$tl] = $typeLabels[$mandat['type_mandat']] ?? ['secondary',$mandat['type_mandat']];
[$sc,$sl] = $statutLabels[$mandat['statut']]    ?? ['secondary',$mandat['statut']];
if(!empty($mandat['detenu_label']))      $cible = $mandat['detenu_label'].' (Détenu — '.($mandat['numero_ecrou']??'').')';
elseif(!empty($mandat['partie_label']))  $cible = $mandat['partie_label'].' (Partie — '.($mandat['type_partie']??'').')';
elseif(!empty($mandat['nouveau_nom']))   $cible = (($mandat['nouveau_prenom']??'').' '.$mandat['nouveau_nom']);
else                              $cible = 'Non précisé';
$today = date('Y-m-d');
$expired = $mandat['date_expiration'] && $mandat['date_expiration'] < $today;
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= BASE_URL ?>/mandats" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h3 mb-0 fw-bold"><i class="bi bi-file-ruled text-danger me-2"></i><?= htmlspecialchars($mandat['numero']) ?></h1>
        <span class="badge bg-<?= $tc ?> fs-6"><?= $tl ?></span>
        <span class="badge bg-<?= $sc ?>"><?= $sl ?></span>
        <?php if($expired): ?><span class="badge bg-danger">EXPIRÉ</span><?php endif; ?>
    </div>
    <a href="<?= BASE_URL ?>/mandats/print/<?= $mandat['id'] ?>" target="_blank" class="btn btn-outline-dark">
        <i class="bi bi-printer me-1"></i>Imprimer
    </a>
</div>

<?php if (!empty($flash['success'])): foreach ((array)$flash['success'] as $msg): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endforeach; endif; ?>
<?php if (!empty($flash['error'])): foreach ((array)$flash['error'] as $msg): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endforeach; endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Détails -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger text-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Détails du mandat</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="w-35 text-muted">Numéro</th><td class="fw-bold"><?= htmlspecialchars($mandat['numero']) ?></td></tr>
                    <tr><th class="text-muted">Type</th><td><span class="badge bg-<?= $tc ?>"><?= $tl ?></span></td></tr>
                    <tr><th class="text-muted">Cible</th><td class="fw-semibold"><i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($cible) ?></td></tr>
                    <?php if($mandat['numero_rg']): ?>
                    <tr><th class="text-muted">Dossier</th><td><a href="<?= BASE_URL ?>/dossiers/show/<?= $mandat['dossier_id'] ?>"><?= htmlspecialchars($mandat['numero_rg']) ?></a> — <?= htmlspecialchars($mandat['dossier_objet'] ?? '') ?></td></tr>
                    <?php endif; ?>
                    <tr><th class="text-muted">Infraction</th><td><?= htmlspecialchars($mandat['infraction_libelle'] ?? '—') ?></td></tr>
                    <tr><th class="text-muted">Lieu d'exécution</th><td><?= htmlspecialchars($mandat['lieu_execution'] ?? '—') ?></td></tr>
                    <tr><th class="text-muted">Date d'émission</th><td><?= $mandat['date_emission'] ? date('d/m/Y', strtotime($mandat['date_emission'])) : '—' ?></td></tr>
                    <tr><th class="text-muted">Date d'expiration</th><td class="<?= $expired ? 'text-danger fw-bold' : '' ?>"><?= $mandat['date_expiration'] ? date('d/m/Y', strtotime($mandat['date_expiration'])) : 'Illimité' ?></td></tr>
                    <tr><th class="text-muted">Émetteur</th><td><?= htmlspecialchars($mandat['emetteur_nom']) ?> <small class="text-muted">(<?= htmlspecialchars($mandat['emetteur_role'] ?? '') ?>)</small></td></tr>
                    <tr><th class="text-muted">Statut</th><td><span class="badge bg-<?= $sc ?>"><?= $sl ?></span></td></tr>
                    <?php if($mandat['date_execution']): ?>
                    <tr><th class="text-muted">Date exécution</th><td><?= date('d/m/Y', strtotime($mandat['date_execution'])) ?></td></tr>
                    <?php endif; ?>
                    <?php if($mandat['executant_nom']): ?>
                    <tr><th class="text-muted">Exécuté par</th><td><?= htmlspecialchars($mandat['executant_nom']) ?></td></tr>
                    <?php endif; ?>
                </table>
                <hr>
                <h6 class="fw-bold mb-2">Motif :</h6>
                <p class="mb-0"><?= nl2br(htmlspecialchars($mandat['motif'])) ?></p>
                <?php if($mandat['observations']): ?>
                <hr><h6 class="fw-bold mb-2">Observations :</h6>
                <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($mandat['observations'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Personne ciblée détails (si nouvelle) -->
        <?php if($mandat['nouveau_nom'] && !$mandat['detenu_id'] && !$mandat['partie_id']): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-semibold"><i class="bi bi-person-lines-fill me-2"></i>Identité de la personne ciblée</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6"><small class="text-muted">Nom complet</small><div class="fw-bold"><?= htmlspecialchars($mandat['nouveau_prenom'].' '.$mandat['nouveau_nom']) ?></div></div>
                    <?php if($mandat['nouveau_ddn']): ?><div class="col-md-6"><small class="text-muted">Date de naissance</small><div><?= date('d/m/Y', strtotime($mandat['nouveau_ddn'])) ?></div></div><?php endif; ?>
                    <?php if($mandat['nouveau_nationalite']): ?><div class="col-md-6"><small class="text-muted">Nationalité</small><div><?= htmlspecialchars($mandat['nouveau_nationalite']) ?></div></div><?php endif; ?>
                    <?php if($mandat['nouveau_profession']): ?><div class="col-md-6"><small class="text-muted">Profession</small><div><?= htmlspecialchars($mandat['nouveau_profession']) ?></div></div><?php endif; ?>
                    <?php if($mandat['nouveau_adresse']): ?><div class="col-12"><small class="text-muted">Adresse</small><div><?= htmlspecialchars($mandat['nouveau_adresse']) ?></div></div><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Colonne droite : changer statut -->
    <?php if(Auth::hasRole(['admin','procureur','greffier','juge_instruction'])): ?>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-semibold bg-secondary text-white"><i class="bi bi-arrow-repeat me-2"></i>Mettre à jour le statut</div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>/mandats/update-statut/<?= $mandat['id'] ?>">
                    <?= CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nouveau statut</label>
                        <select name="statut" class="form-select" required>
                            <?php foreach(['emis'=>'Émis','signifie'=>'Signifié','execute'=>'Exécuté','annule'=>'Annulé','expire'=>'Expiré'] as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= $mandat['statut']===$k?'selected':'' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date d'exécution</label>
                        <input type="date" name="date_execution" class="form-control" value="<?= $mandat['date_execution'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Exécuté par (OPJ/unité)</label>
                        <input type="text" name="executant_nom" class="form-control" value="<?= htmlspecialchars($mandat['executant_nom'] ?? '') ?>" placeholder="Nom OPJ ou unité">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Observations</label>
                        <textarea name="observations" class="form-control" rows="3" placeholder="Observations…"><?= htmlspecialchars($mandat['observations'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>