<?php $pageTitle = 'Détenu — ' . $detenu['numero_ecrou']; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=BASE_URL?>/detenus">Population Carcérale</a></li>
        <li class="breadcrumb-item active"><?=htmlspecialchars($detenu['numero_ecrou'])?></li>
    </ol></nav>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-person-lock me-2 text-danger"></i><?=htmlspecialchars($detenu['nom'].' '.$detenu['prenom'])?>
            </h4>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php
                $ds=['incarcere'=>['danger','Incarcéré'],'libere'=>['success','Libéré'],'transfere'=>['info','Transféré'],'evade'=>['dark','Évadé'],'decede'=>['secondary','Décédé']];
                [$dc,$dl]=$ds[$detenu['statut']]??['secondary',$detenu['statut']];
                ?>
                <span class="badge bg-<?=$dc?> fs-6"><?=$dl?></span>
                <!-- Badge sexe -->
                <span class="badge <?=($detenu['sexe']??'M')==='F'?'bg-pink text-dark':'bg-primary'?>">
                    <?=($detenu['sexe']??'M')==='F'?'♀ Femme':'♂ Homme'?>
                </span>
                <?php if(!empty($detenu['surnom_alias'])): ?>
                <span class="badge bg-warning text-dark">
                    <i class="bi bi-quote me-1"></i><?=htmlspecialchars($detenu['surnom_alias'])?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php if(Auth::hasRole(['admin','greffier','procureur','president'])): ?>
        <a href="<?=BASE_URL?>/detenus/edit/<?=$detenu['id']?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i>Modifier
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">

<!-- ─── Colonne principale ─── -->
<div class="col-lg-8">

    <!-- Photo + Informations personnelles -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Informations personnelles</div>
        <div class="card-body">
            <div class="row g-3">
                <?php if(!empty($detenu['photo_identite'])): ?>
                <!-- Photo en haut à droite -->
                <div class="col-12 d-flex justify-content-end mb-2">
                    <a href="<?=BASE_URL?>/<?=htmlspecialchars($detenu['photo_identite'])?>"
                       data-bs-toggle="modal" data-bs-target="#modalPhoto"
                       title="Voir la photo en grand">
                        <img src="<?=BASE_URL?>/<?=htmlspecialchars($detenu['photo_identite'])?>"
                             class="rounded border shadow-sm"
                             style="width:90px;height:110px;object-fit:cover;cursor:zoom-in;"
                             alt="Photo de <?=htmlspecialchars($detenu['nom'])?>">
                    </a>
                </div>
                <?php endif; ?>

                <div class="col-md-6">
                    <small class="text-muted">N° Écrou</small>
                    <br><strong class="font-monospace"><?=htmlspecialchars($detenu['numero_ecrou'])?></strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Type de détention</small>
                    <br><span class="badge bg-secondary fs-6"><?=str_replace('_',' ',$detenu['type_detention'])?></span>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Date de naissance</small>
                    <br><?=$detenu['date_naissance']?date('d/m/Y',strtotime($detenu['date_naissance'])):'—'?>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Lieu de naissance</small>
                    <br><?=htmlspecialchars($detenu['lieu_naissance']??'—')?>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Nationalité</small>
                    <br><?=htmlspecialchars($detenu['nationalite']??'—')?>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Profession</small>
                    <br><?=htmlspecialchars($detenu['profession']??'—')?>
                </div>

                <?php if(!empty($detenu['nom_mere'])): ?>
                <div class="col-md-6">
                    <small class="text-muted">Nom de la mère</small>
                    <br><?=htmlspecialchars($detenu['nom_mere'])?>
                </div>
                <?php endif; ?>

                <!-- Statut matrimonial -->
                <div class="col-md-6">
                    <small class="text-muted">Statut matrimonial</small>
                    <br>
                    <?php
                    $smLabels = ['celibataire'=>'Célibataire','marie'=>'Marié(e)','divorce'=>'Divorcé(e)','veuf'=>'Veuf/Veuve'];
                    $sm = $detenu['statut_matrimonial'] ?? 'celibataire';
                    echo htmlspecialchars($smLabels[$sm] ?? $sm);
                    if ($sm === 'marie' && ($detenu['nombre_enfants'] ?? 0) > 0):
                    ?>
                        &nbsp;<span class="badge bg-light text-dark border">
                            <?=(int)$detenu['nombre_enfants']?> enfant<?=(int)$detenu['nombre_enfants']>1?'s':''?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Détention -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Incarcération</div>
        <div class="card-body"><div class="row g-3">
            <div class="col-md-6">
                <small class="text-muted">Date d'incarcération</small>
                <br><?=date('d/m/Y',strtotime($detenu['date_incarceration']))?>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Libération prévue</small>
                <br><?=$detenu['date_liberation_prevue']?date('d/m/Y',strtotime($detenu['date_liberation_prevue'])):'—'?>
            </div>
            <?php if($detenu['date_liberation_effective']): ?>
            <div class="col-md-6">
                <small class="text-muted">Libéré le</small>
                <br><strong class="text-success"><?=date('d/m/Y',strtotime($detenu['date_liberation_effective']))?></strong>
            </div>
            <?php endif; ?>
            <div class="col-md-6">
                <small class="text-muted">Cellule</small>
                <br><?=htmlspecialchars($detenu['cellule']??'—')?>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Maison d'arrêt</small>
                <br>
                <?php if(!empty($detenu['maison_arret_nom'])): ?>
                    <a href="<?=BASE_URL?>/detenus?maison_arret=<?=(int)$detenu['maison_arret_id']?>"
                       class="text-decoration-none fw-semibold">
                        <?=htmlspecialchars($detenu['maison_arret_nom'])?>
                    </a>
                <?php elseif(!empty($detenu['etablissement'])): ?>
                    <?=htmlspecialchars($detenu['etablissement'])?>
                <?php else: ?>
                    —
                <?php endif; ?>
            </div>
            <?php if($detenu['notes']): ?>
            <div class="col-12">
                <small class="text-muted">Notes</small>
                <br><?=nl2br(htmlspecialchars($detenu['notes']))?>
            </div>
            <?php endif; ?>
        </div></div>
    </div>

    <!-- Dossier lié -->
    <?php if($detenu['numero_rg']): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <small class="text-muted">Dossier lié</small>
                <br><strong><?=htmlspecialchars($detenu['numero_rg'])?></strong>
            </div>
            <a href="<?=BASE_URL?>/dossiers/show/<?=$detenu['dossier_id']?>" class="btn btn-outline-primary btn-sm">
                Voir le dossier
            </a>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /col-lg-8 -->

<!-- ─── Colonne latérale ─── -->
<div class="col-lg-4">

    <?php
    $dureeJ    = (time() - strtotime($detenu['date_incarceration'])) / 86400;
    $dureeMois = floor($dureeJ / 30.4);
    $alerte    = ($detenu['statut']==='incarcere'
               && in_array($detenu['type_detention'],['prevenu','detenu_provisoire','inculpe'])
               && $dureeMois >= DELAI_DETENTION_PROVISOIRE_MOIS);
    ?>
    <div class="card border-0 shadow-sm mb-3 <?=$alerte?'border-danger':''?>">
        <div class="card-header bg-white fw-semibold">Durée de détention</div>
        <div class="card-body text-center">
            <div class="display-5 fw-bold <?=$alerte?'text-danger':($dureeMois>=3?'text-warning':'text-success')?>">
                <?=floor($dureeJ)?>j
            </div>
            <div class="text-muted"><?=$dureeMois?> mois</div>
            <?php if($alerte): ?>
            <div class="alert alert-danger py-2 small mt-2 mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i>Durée de détention provisoire dépassée !
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Placeholder photo si pas de photo -->
    <?php if(empty($detenu['photo_identite'])): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body text-center text-muted py-4">
            <i class="bi bi-person-square fs-1 text-secondary"></i>
            <div class="small mt-2">Pas de photo d'identité</div>
            <?php if(Auth::hasRole(['admin','greffier'])): ?>
            <a href="<?=BASE_URL?>/detenus/edit/<?=$detenu['id']?>"
               class="btn btn-outline-secondary btn-sm mt-2">
                <i class="bi bi-camera me-1"></i>Ajouter une photo
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if($detenu['statut']==='incarcere' && Auth::hasRole(['admin','greffier','president','procureur'])): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Libérer</div>
        <div class="card-body">
            <form method="POST" action="<?=BASE_URL?>/detenus/liberer/<?=$detenu['id']?>"
                  onsubmit="return confirm('Confirmer la libération ?')">
                <?=CSRF::field()?>
                <div class="mb-3">
                    <label class="form-label">Date de libération <span class="text-danger">*</span></label>
                    <input type="date" name="date_liberation_effective" class="form-control"
                           value="<?=date('Y-m-d')?>" required>
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-door-open me-1"></i>Enregistrer la libération
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /col-lg-4 -->
</div><!-- /row -->

<!-- ─── Modal Lightbox Photo ─── -->
<?php if(!empty($detenu['photo_identite'])): ?>
<div class="modal fade" id="modalPhoto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body text-center p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2 z-3"
                        data-bs-dismiss="modal" style="background-color:rgba(0,0,0,.5);border-radius:50%;padding:.4rem;"></button>
                <img src="<?=BASE_URL?>/<?=htmlspecialchars($detenu['photo_identite'])?>"
                     class="img-fluid rounded shadow"
                     style="max-height:80vh;"
                     alt="Photo d'identité de <?=htmlspecialchars($detenu['nom'].' '.$detenu['prenom'])?>">
                <div class="text-white text-center mt-2 small">
                    <?=htmlspecialchars($detenu['nom'].' '.$detenu['prenom'])?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
