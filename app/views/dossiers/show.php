<?php $pageTitle = 'Dossier — ' . $dossier['numero_rg']; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/dossiers">Dossiers</a></li><li class="breadcrumb-item active"><?=htmlspecialchars($dossier['numero_rg'])?></li></ol></nav>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-folder2-open me-2 text-primary"></i><?=htmlspecialchars($dossier['numero_rg'])?></h4>
            <div class="d-flex gap-2 mt-1">
                <?php if($dossier['numero_rp']): ?><span class="badge bg-warning text-dark">RP: <?=htmlspecialchars($dossier['numero_rp'])?></span><?php endif; ?>
                <?php if($dossier['numero_ri']): ?><span class="badge bg-info text-dark">RI: <?=htmlspecialchars($dossier['numero_ri'])?></span><?php endif; ?>
                <?php $sm=['enregistre'=>['secondary','Enregistré'],'parquet'=>['warning','Parquet'],'instruction'=>['info','Instruction'],'en_instruction'=>['info','En instruction'],'en_audience'=>['primary','Audience'],'juge'=>['success','Jugé'],'classe'=>['dark','Classé'],'appel'=>['danger','Appel']];[$sc,$sl]=$sm[$dossier['statut']]??['secondary',$dossier['statut']]; ?>
                <span class="badge bg-<?=$sc?> fs-6"><?=$sl?></span>
            </div>
        </div>
        <?php if(Auth::hasRole(['admin','greffier','procureur'])): ?>
        <a href="<?=BASE_URL?>/dossiers/edit/<?=$dossier['id']?>" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>
        <?php endif; ?>
        <a href="<?=BASE_URL?>/export/dossier/<?=$dossier['id']?>" target="_blank" class="btn btn-outline-danger"><i class="bi bi-file-pdf me-1"></i>Imprimer dossier</a>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="dossierTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabInfos">Informations</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabParties">Parties (<?=count($parties)?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAudiences">Audiences (<?=count($audiences)?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabJugements">Jugements (<?=count($jugements)?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabDetenus">Détenus (<?=count($detenus)?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPieces"><i class="bi bi-paperclip"></i> Pièces jointes</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabHistorique">Historique</a></li>
</ul>

<div class="tab-content">
    <!-- Informations -->
    <div class="tab-pane fade show active" id="tabInfos">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold">Informations générales</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><small class="text-muted">Date d'enregistrement</small><br><strong><?=date('d/m/Y',strtotime($dossier['date_enregistrement']))?></strong></div>
                            <div class="col-md-6"><small class="text-muted">Type</small><br><span class="badge <?=$dossier['type_affaire']==='penale'?'bg-danger':($dossier['type_affaire']==='civile'?'bg-primary':'bg-success')?> fs-6"><?=ucfirst($dossier['type_affaire'])?></span></div>
                            <div class="col-md-6"><small class="text-muted">Substitut</small><br><strong><?=htmlspecialchars(($dossier['substitut_prenom']??'').($dossier['substitut_nom']?' '.$dossier['substitut_nom']:'—'))?></strong></div>
                            <div class="col-md-6"><small class="text-muted">Cabinet d'instruction</small><br><strong><?=htmlspecialchars($dossier['cabinet_num']?($dossier['cabinet_num'].' — '.$dossier['cabinet_lib']):'—')?></strong></div>
                            <?php if($dossier['date_instruction_debut']): ?>
                            <div class="col-md-6"><small class="text-muted">Début instruction</small><br><strong><?=date('d/m/Y',strtotime($dossier['date_instruction_debut']))?></strong></div>
                            <div class="col-md-6"><small class="text-muted">Juge d'instruction</small><br><strong><?=htmlspecialchars($dossier['juge_instr_prenom']?$dossier['juge_instr_prenom'].' '.$dossier['juge_instr_nom']:'—')?></strong></div>
                            <?php endif; ?>
                            <?php if($dossier['date_limite_traitement']): ?>
                            <div class="col-md-6"><small class="text-muted">Date limite traitement</small><br>
                                <?php $dl=strtotime($dossier['date_limite_traitement']);$now=time();$diff=($dl-$now)/86400; ?>
                                <strong class="<?=$diff<0?'text-danger':($diff<7?'text-warning':'text-success')?>"><?=date('d/m/Y',$dl)?></strong>
                                <?php if($diff<0): ?><span class="badge bg-danger ms-1">En retard</span><?php elseif($diff<7): ?><span class="badge bg-warning text-dark ms-1">Bientôt</span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <div class="col-12"><small class="text-muted">Objet</small><br><p class="mb-0"><?=nl2br(htmlspecialchars($dossier['objet']))?></p></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- Actions workflow -->
                <?php if(in_array($dossier['statut'],['parquet','instruction','en_instruction'])): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-play-circle me-2 text-primary"></i>Actions</div>
                    <div class="card-body d-grid gap-2">
                        <?php if($dossier['statut']==='parquet' && Auth::hasRole(['admin','procureur','substitut_procureur','president'])): ?>
                        <button class="btn btn-info text-dark" data-bs-toggle="modal" data-bs-target="#modalInstruction"><i class="bi bi-send me-2"></i>Envoyer en instruction</button>
                        <?php endif; ?>
                        <?php if(in_array($dossier['statut'],['parquet','instruction','en_instruction']) && Auth::hasRole(['admin','procureur','juge_instruction','president'])): ?>
                        <form method="POST" action="<?=BASE_URL?>/dossiers/envoyer-audience/<?=$dossier['id']?>" onsubmit="return confirm('Envoyer en audience ?')">
                            <?=CSRF::field()?>
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-calendar-plus me-2"></i>Envoyer en audience</button>
                        </form>
                        <?php endif; ?>
                        <?php if($dossier['statut']==='en_audience' && Auth::hasRole(['admin','greffier','president'])): ?>
                        <a href="<?=BASE_URL?>/jugements/create/<?=$dossier['id']?>" class="btn btn-success"><i class="bi bi-hammer me-2"></i>Saisir le jugement</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if(in_array($dossier['statut'],['parquet','instruction','en_instruction','en_audience']) && Auth::hasRole(['admin','procureur','substitut_procureur'])): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-archive text-secondary me-2"></i>Classement</div>
                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalClasser">
                            <i class="bi bi-archive me-2"></i>Classer sans suite
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                <?php if($dossier['statut']==='classe' && Auth::hasRole(['admin','procureur'])): ?>
                <div class="card border-0 shadow-sm mb-3 border-warning">
                    <div class="card-header bg-warning fw-semibold"><i class="bi bi-arrow-counterclockwise me-2"></i>Dossier classé</div>
                    <div class="card-body">
                        <?php if($dossier['motif_classement']): ?><p class="small text-muted mb-2"><strong>Motif :</strong> <?=htmlspecialchars($dossier['motif_classement'])?></p><?php endif; ?>
                        <button class="btn btn-warning w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#modalDeclasser">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Déclasser ce dossier
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                <?php if($dossier['statut']==='en_audience'): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body d-grid gap-2">
                        <a href="<?=BASE_URL?>/audiences/create?dossier_id=<?=$dossier['id']?>" class="btn btn-outline-primary"><i class="bi bi-calendar-plus me-2"></i>Planifier audience</a>
                        <a href="<?=BASE_URL?>/jugements/create/<?=$dossier['id']?>" class="btn btn-success"><i class="bi bi-hammer me-2"></i>Saisir jugement</a>
                        <a href="<?=BASE_URL?>/detenus/create?dossier_id=<?=$dossier['id']?>" class="btn btn-outline-danger"><i class="bi bi-person-lock me-2"></i>Enregistrer détenu</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Parties -->
    <div class="tab-pane fade" id="tabParties">
        <?php if(Auth::canEditDossier()): ?>
        <div class="d-flex gap-2 mb-3">
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddPartie">
                <i class="bi bi-person-plus me-1"></i>Ajouter une partie
            </button>
            <button class="btn btn-outline-danger btn-sm" id="btnImportDetenu"
                    data-bs-toggle="modal" data-bs-target="#modalImportDetenu">
                <i class="bi bi-person-lock me-1"></i>Importer depuis les détenus
            </button>
        </div>
        <?php endif; ?>
        <?php if(empty($parties)): ?><div class="text-center text-muted py-4">Aucune partie enregistrée</div>
        <?php else: ?>
        <div class="row g-3">
        <?php foreach($parties as $p): ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="badge bg-secondary mb-1"><?=str_replace('_',' ',$p['type_partie'])?></span>
                            <h6 class="mb-0"><?=htmlspecialchars($p['nom'].' '.$p['prenom'])?></h6>
                            <?php if($p['profession']): ?><small class="text-muted"><?=htmlspecialchars($p['profession'])?></small><?php endif; ?>
                            <?php if($p['telephone']): ?><div class="small text-muted"><i class="bi bi-telephone"></i> <?=htmlspecialchars($p['telephone'])?></div><?php endif; ?>
                        </div>
                        <?php if(Auth::canEditDossier()): ?>
                        <form method="POST" action="<?=BASE_URL?>/dossiers/partie/delete/<?=$p['id']?>" onsubmit="return confirm('Supprimer cette partie ?')">
                            <?=CSRF::field()?><input type="hidden" name="dossier_id" value="<?=$dossier['id']?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Audiences -->
    <div class="tab-pane fade" id="tabAudiences">
        <a href="<?=BASE_URL?>/audiences/create?dossier_id=<?=$dossier['id']?>" class="btn btn-outline-primary btn-sm mb-3"><i class="bi bi-calendar-plus me-1"></i>Planifier audience</a>
        <?php if(empty($audiences)): ?><div class="text-center text-muted py-4">Aucune audience planifiée</div>
        <?php else: ?><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>N° Audience</th><th>Date</th><th>Type</th><th>Salle</th><th>Président</th><th>Statut</th><th></th></tr></thead><tbody>
        <?php foreach($audiences as $a): ?>
        <tr><td><?=htmlspecialchars($a['numero_audience']??'—')?></td><td><?=date('d/m/Y H:i',strtotime($a['date_audience']))?></td><td><span class="badge bg-info text-dark"><?=$a['type_audience']?></span></td><td><?=htmlspecialchars($a['salle_nom']??'—')?></td><td><?=htmlspecialchars(($a['president_prenom']??'').' '.($a['president_nom']??'—'))?></td>
        <td><?php $as=['planifiee'=>['primary','Planifiée'],'tenue'=>['success','Tenue'],'renvoyee'=>['warning','Renvoyée'],'annulee'=>['danger','Annulée']];[$ac,$al]=$as[$a['statut']]??['secondary',$a['statut']];echo "<span class=\"badge bg-{$ac}\">{$al}</span>";?></td>
        <td><a href="<?=BASE_URL?>/audiences/show/<?=$a['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </div>

    <!-- Jugements -->
    <div class="tab-pane fade" id="tabJugements">
        <?php if(in_array($dossier['statut'],['en_audience','juge']) && Auth::hasRole(['admin','greffier','president','juge_siege'])): ?>
        <a href="<?=BASE_URL?>/jugements/create/<?=$dossier['id']?>" class="btn btn-outline-success btn-sm mb-3"><i class="bi bi-hammer me-1"></i>Saisir jugement</a>
        <?php endif; ?>
        <?php if(empty($jugements)): ?><div class="text-center text-muted py-4">Aucun jugement rendu</div>
        <?php else: ?><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>N° Jugement</th><th>Date</th><th>Type</th><th>Peine</th><th>Appel</th><th></th></tr></thead><tbody>
        <?php foreach($jugements as $j): ?>
        <tr><td class="fw-semibold"><?=htmlspecialchars($j['numero_jugement'])?></td><td><?=date('d/m/Y',strtotime($j['date_jugement']))?></td><td><span class="badge bg-secondary"><?=$j['type_jugement']?></span></td>
        <td class="small"><?=htmlspecialchars($j['peine_principale']??'—')?></td>
        <td><?php if($j['appel_possible']&&!$j['appel_interjecte']): ?><span class="badge bg-warning text-dark">Possible (<?=date('d/m/Y',strtotime($j['date_limite_appel']))?>)</span><?php elseif($j['appel_interjecte']): ?><span class="badge bg-danger">Interjeté</span><?php else: ?><span class="badge bg-dark">Non</span><?php endif; ?></td>
        <td><a href="<?=BASE_URL?>/jugements/show/<?=$j['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </div>

    <!-- Détenus -->
    <div class="tab-pane fade" id="tabDetenus">
        <a href="<?=BASE_URL?>/detenus/create?dossier_id=<?=$dossier['id']?>" class="btn btn-outline-danger btn-sm mb-3"><i class="bi bi-person-lock me-1"></i>Enregistrer détenu</a>
        <?php if(empty($detenus)): ?><div class="text-center text-muted py-4">Aucun détenu lié à ce dossier</div>
        <?php else: ?><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Écrou</th><th>Nom</th><th>Type détention</th><th>Incarcéré le</th><th>Libération prévue</th><th>Statut</th><th></th></tr></thead><tbody>
        <?php foreach($detenus as $d): ?>
        <tr><td><?=htmlspecialchars($d['numero_ecrou'])?></td><td><?=htmlspecialchars($d['nom'].' '.$d['prenom'])?></td><td><span class="badge bg-secondary"><?=str_replace('_',' ',$d['type_detention'])?></span></td><td><?=date('d/m/Y',strtotime($d['date_incarceration']))?></td><td><?=$d['date_liberation_prevue']?date('d/m/Y',strtotime($d['date_liberation_prevue'])):'—'?></td>
        <td><?php $ds=['incarcere'=>['danger','Incarcéré'],'libere'=>['success','Libéré'],'transfere'=>['info','Transféré'],'evade'=>['dark','Évadé'],'decede'=>['secondary','Décédé']];[$dc,$dl]=$ds[$d['statut']]??['secondary',$d['statut']];echo "<span class=\"badge bg-{$dc}\">{$dl}</span>";?></td>
        <td><a href="<?=BASE_URL?>/detenus/show/<?=$d['id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </div>

    <!-- Pièces jointes -->
    <div class="tab-pane fade" id="tabPieces">
        <!-- Section pièces jointes -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-paperclip me-2"></i>Pièces jointes</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalUpload">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                </button>
            </div>
            <div class="card-body" id="piecesList">
                <div class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Chargement…
                </div>
            </div>
        </div>
    </div>

    <!-- Historique -->
    <div class="tab-pane fade" id="tabHistorique">
        <?php if(empty($mouvements)): ?><div class="text-center text-muted py-4">Aucun mouvement enregistré</div>
        <?php else: ?><div class="timeline mt-2">
        <?php foreach($mouvements as $m): ?>
        <div class="d-flex gap-3 mb-3">
            <div class="text-center" style="min-width:40px"><div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width:32px;height:32px"><i class="bi bi-arrow-right small"></i></div></div>
            <div class="flex-fill border-start ps-3 pb-3">
                <strong><?=htmlspecialchars($m['type_mouvement'])?></strong>
                <?php if($m['nouveau_statut']): ?><span class="badge bg-secondary ms-1"><?=htmlspecialchars($m['nouveau_statut'])?></span><?php endif; ?>
                <div class="small text-muted"><?=htmlspecialchars($m['description']??'')?></div>
                <div class="small text-muted"><?=htmlspecialchars(($m['prenom']??'').($m['nom']?' '.$m['nom']:'Système'))?> — <?=date('d/m/Y H:i',strtotime($m['created_at']))?></div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal instruction -->
<div class="modal fade" id="modalInstruction" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Envoyer en instruction</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?=BASE_URL?>/dossiers/affecter-instruction/<?=$dossier['id']?>">
            <?=CSRF::field()?>
            <div class="modal-body">
                <label class="form-label">Cabinet d'instruction <span class="text-danger">*</span></label>
                <select name="cabinet_id" class="form-select" required id="selectCabinetInstr">
                    <option value="">— Sélectionner —</option>
                    <?php foreach($cabinets as $c): ?>
                    <option value="<?=$c['id']?>"><?=htmlspecialchars($c['numero'].' — '.$c['libelle'])?></option>
                    <?php endforeach; ?>
                </select>
                <div id="cabinetInstrInfo" class="small text-muted mt-1"></div>
                <button type="button" class="btn btn-outline-success btn-sm mt-1" onclick="suggererCabinetInstr()">
                    <i class="bi bi-magic me-1"></i>Suggérer le moins chargé
                </button>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Annuler</button><button class="btn btn-info text-dark" type="submit">Envoyer en instruction</button></div>
        </form>
    </div></div>
</div>

<!-- Modal Partie -->
<div class="modal fade" id="modalAddPartie" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Ajouter une partie</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?=BASE_URL?>/dossiers/partie/add/<?=$dossier['id']?>">
            <?=CSRF::field()?>
            <div class="modal-body row g-3">
                <div class="col-md-6"><label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type_partie" id="selectTypePartie" class="form-select" required>
                        <option value="plaignant">Plaignant</option>
                        <option value="defendeur">Défendeur</option>
                        <option value="prevenu">Prévenu</option>
                        <option value="victime">Victime</option>
                        <option value="avocat">Avocat</option>
                        <option value="temoin">Témoin</option>
                        <option value="mis_en_cause">Mis en cause</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" id="partieNom" name="nom" class="form-control" required>
                </div>
                <div class="col-md-6"><label class="form-label">Prénom</label>
                    <input type="text" id="partiePrenom" name="prenom" class="form-control">
                </div>
                <div class="col-md-6"><label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control">
                </div>
                <div class="col-md-6"><label class="form-label">Nationalité</label>
                    <input type="text" id="partieNationalite" name="nationalite" class="form-control" value="Nigérienne">
                </div>
                <div class="col-md-6"><label class="form-label">Profession</label>
                    <input type="text" name="profession" class="form-control">
                </div>
                <div class="col-12"><label class="form-label">Adresse</label>
                    <textarea id="partieAdresse" name="adresse" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary" type="submit">Ajouter</button></div>
        </form>
    </div></div>
</div>

<!-- Modal Import depuis Détenus -->
<div class="modal fade" id="modalImportDetenu" tabindex="-1" aria-labelledby="modalImportDetenuLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalImportDetenuLabel">
                    <i class="bi bi-person-lock me-2"></i>Importer depuis les détenus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info alert-sm py-2 small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Les avocats ne peuvent pas être importés depuis les détenus.
                    Sélectionnez un détenu pour pré-remplir le formulaire d'ajout de partie.
                </div>
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchDetenusInput"
                               class="form-control"
                               placeholder="Rechercher par nom, prénom ou n° écrou..."
                               autocomplete="off">
                    </div>
                </div>
                <div id="searchDetenusResults">
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-search me-1"></i>Tapez au moins 2 caractères pour rechercher
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     Modal Upload Pièces jointes
================================================================ -->
<div class="modal fade" id="modalUpload" tabindex="-1" aria-labelledby="modalUploadLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUploadLabel">
                    <i class="bi bi-upload me-2"></i>Ajouter une pièce jointe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="uploadFichier">
                        Fichier <span class="text-danger">*</span>
                        <small class="text-muted">(PDF, DOC, DOCX, JPG, PNG, XLSX, XLS, ODT — 10 Mo max)</small>
                    </label>
                    <input type="file" class="form-control" id="uploadFichier"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls,.odt">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="uploadDesc">Description (optionnelle)</label>
                    <input type="text" class="form-control" id="uploadDesc" maxlength="255"
                           placeholder="Ex : Acte de naissance, Rapport d’expertise…">
                </div>
                <!-- Barre de progression -->
                <div id="uploadProgressWrap" class="d-none">
                    <div class="progress" style="height:20px">
                        <div id="uploadProgressBar"
                             class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             role="progressbar" style="width:0%">0&nbsp;%</div>
                    </div>
                    <div id="uploadProgressMsg" class="small text-muted mt-1">Upload en cours…</div>
                </div>
                <!-- Message résultat -->
                <div id="uploadResult" class="d-none mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnUploadSubmit">
                    <i class="bi bi-upload me-1"></i>Envoyer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     Modal Visualisation (iframe pour PDF/images, download pour le reste)
================================================================ -->
<div class="modal fade" id="modalView" tabindex="-1" aria-labelledby="modalViewLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="height:90vh;display:flex;flex-direction:column">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0" id="modalViewLabel">
                    <i class="bi bi-eye me-2 text-primary"></i><span id="modalViewTitle">Document</span>
                </h6>
                <div class="d-flex gap-2 align-items-center">
                    <a id="modalViewDownload" href="#" download
                       class="btn btn-sm btn-outline-secondary" title="Télécharger">
                        <i class="bi bi-download"></i>
                    </a>
                    <a id="modalViewOpen" href="#" target="_blank"
                       class="btn btn-sm btn-outline-primary" title="Ouvrir dans un nouvel onglet">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0 flex-fill" style="overflow:hidden">
                <iframe id="viewerFrame"
                        src="about:blank"
                        style="width:100%;height:100%;border:none;display:block"
                        allowfullscreen></iframe>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     JavaScript — Pièces jointes
================================================================ -->
<script>
(function () {
    'use strict';

    const DOSSIER_ID  = <?= (int)$dossier['id'] ?>;
    const BASE_URL    = '<?= BASE_URL ?>';
    const CSRF_TOKEN  = '<?= htmlspecialchars(CSRF::generate(), ENT_QUOTES) ?>';

    /* ----------------------------------------------------------
     * Icônes selon MIME
     * ---------------------------------------------------------- */
    function iconeType(mime) {
        if (!mime) return 'bi-file-earmark';
        if (mime === 'application/pdf')   return 'bi-file-earmark-pdf text-danger';
        if (mime.startsWith('image/'))    return 'bi-file-earmark-image text-success';
        if (mime.includes('word') || mime.includes('odt')) return 'bi-file-earmark-word text-primary';
        if (mime.includes('excel') || mime.includes('spreadsheet')) return 'bi-file-earmark-excel text-success';
        return 'bi-file-earmark text-secondary';
    }

    /* ----------------------------------------------------------
     * Rendu de la liste
     * ---------------------------------------------------------- */
    function renderListe(docs) {
        const el = document.getElementById('piecesList');
        if (!docs || docs.length === 0) {
            el.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-3"></i><br>Aucune pièce jointe</div>';
            return;
        }
        let html = '<div class="list-group list-group-flush">';
        docs.forEach(function (d) {
            const icone = iconeType(d.type);
            const inline = d.inline;
            html += `
                <div class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2" id="piece-${d.id}">
                    <i class="bi ${icone} fs-4"></i>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-semibold text-truncate" title="${d.nom}">${escHtml(d.nom)}</div>
                        <small class="text-muted">${d.taille} &bull; ${d.date}${d.uploaded_by ? ' &bull; ' + escHtml(d.uploaded_by) : ''}${d.description ? ' &bull; ' + escHtml(d.description) : ''}</small>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <button class="btn btn-sm btn-outline-primary"
                                onclick="voirDoc(${d.id}, ${JSON.stringify(d.nom)}, ${JSON.stringify(d.url)}, ${inline ? 'true' : 'false'})">
                            <i class="bi bi-eye"></i> Voir
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="supprimerDoc(${d.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>`;
        });
        html += '</div>';
        el.innerHTML = html;
    }

    /* ----------------------------------------------------------
     * Échappement HTML
     * ---------------------------------------------------------- */
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ----------------------------------------------------------
     * Chargement de la liste (AJAX)
     * ---------------------------------------------------------- */
    function chargerListe() {
        const el = document.getElementById('piecesList');
        el.innerHTML = '<div class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm"></span> Chargement…</div>';
        fetch(BASE_URL + '/documents/list/' + DOSSIER_ID, {
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                renderListe(data.data);
            } else {
                el.innerHTML = '<div class="alert alert-warning">' + escHtml(data.message) + '</div>';
            }
        })
        .catch(function () {
            el.innerHTML = '<div class="alert alert-danger">Erreur de chargement.</div>';
        });
    }

    /* ----------------------------------------------------------
     * Voir un document
     * ---------------------------------------------------------- */
    window.voirDoc = function (id, nom, url, inline) {
        if (inline) {
            // Ouvre le modal avec iframe
            document.getElementById('modalViewTitle').textContent = nom;
            document.getElementById('viewerFrame').src = url;
            // Liens téléchargement / onglet
            var dlLink   = document.getElementById('modalViewDownload');
            var openLink = document.getElementById('modalViewOpen');
            if (dlLink)   { dlLink.href = url;   dlLink.download = nom; }
            if (openLink) { openLink.href = url; }
            var modal = new bootstrap.Modal(document.getElementById('modalView'));
            modal.show();
        } else {
            // Téléchargement direct pour docx, xlsx, odt…
            var a = document.createElement('a');
            a.href = url;
            a.download = nom;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    };

    // Vider l'iframe quand on ferme le modal (évite de garder le PDF en mémoire)
    document.getElementById('modalView').addEventListener('hide.bs.modal', function () {
        document.getElementById('viewerFrame').src = 'about:blank';
        var dlLink   = document.getElementById('modalViewDownload');
        var openLink = document.getElementById('modalViewOpen');
        if (dlLink)   dlLink.href = '#';
        if (openLink) openLink.href = '#';
    });

    /* ----------------------------------------------------------
     * Supprimer un document
     * ---------------------------------------------------------- */
    window.supprimerDoc = function (id) {
        if (!confirm('Confirmer la suppression de ce document ?')) return;
        fetch(BASE_URL + '/documents/delete/' + id, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf=' + encodeURIComponent(CSRF_TOKEN)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var el = document.getElementById('piece-' + id);
                if (el) el.remove();
                // Si plus aucun élément
                var list = document.getElementById('piecesList');
                if (!list.querySelector('[id^="piece-"]')) chargerListe();
            } else {
                alert('Erreur : ' + (data.message || 'Impossible de supprimer.'));
            }
        })
        .catch(function () { alert('Erreur réseau.'); });
    };

    /* ----------------------------------------------------------
     * Upload avec progression
     * ---------------------------------------------------------- */
    document.getElementById('btnUploadSubmit').addEventListener('click', function () {
        var fichier = document.getElementById('uploadFichier').files[0];
        if (!fichier) {
            alert('Veuillez sélectionner un fichier.');
            return;
        }

        var desc = document.getElementById('uploadDesc').value;
        var fd   = new FormData();
        fd.append('fichier', fichier);
        fd.append('description', desc);
        fd.append('_csrf', CSRF_TOKEN);

        var progressWrap = document.getElementById('uploadProgressWrap');
        var progressBar  = document.getElementById('uploadProgressBar');
        var progressMsg  = document.getElementById('uploadProgressMsg');
        var resultEl     = document.getElementById('uploadResult');
        var btn          = document.getElementById('btnUploadSubmit');

        progressWrap.classList.remove('d-none');
        resultEl.classList.add('d-none');
        resultEl.innerHTML = '';
        btn.disabled = true;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', BASE_URL + '/documents/upload/' + DOSSIER_ID);
        xhr.withCredentials = true;

        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                var pct = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = pct + '%';
                progressBar.textContent = pct + '\u00a0%';
                progressMsg.textContent = 'Upload en cours\u2026 ' + pct + ' %';
            }
        });

        xhr.addEventListener('load', function () {
            btn.disabled = false;
            progressWrap.classList.add('d-none');
            var rawText = xhr.responseText;
            // Tentative de parsing JSON — récupère aussi les erreurs serveur JSON
            try {
                // Chercher le JSON même s'il y a des notices PHP avant
                var jsonStart = rawText.indexOf('{');
                var jsonText  = jsonStart >= 0 ? rawText.substring(jsonStart) : rawText;
                var data = JSON.parse(jsonText);
                if (data.success) {
                    resultEl.className = 'alert alert-success mt-2';
                    resultEl.textContent = data.message;
                    resultEl.classList.remove('d-none');
                    // Reset form
                    document.getElementById('uploadFichier').value = '';
                    document.getElementById('uploadDesc').value    = '';
                    // Recharger la liste
                    chargerListe();
                    // Fermer le modal après 1,5 s
                    setTimeout(function () {
                        bootstrap.Modal.getInstance(document.getElementById('modalUpload')).hide();
                        resultEl.classList.add('d-none');
                    }, 1500);
                } else {
                    resultEl.className = 'alert alert-danger mt-2';
                    resultEl.textContent = data.message || 'Erreur inconnue.';
                    resultEl.classList.remove('d-none');
                }
            } catch (e) {
                // Réponse non-JSON : afficher le statut HTTP et le début de la réponse
                var status = xhr.status;
                var hint   = rawText.length > 0
                    ? ' (Réponse serveur: ' + rawText.substring(0, 120).replace(/<[^>]+>/g, '') + ')'
                    : '';
                resultEl.className = 'alert alert-danger mt-2';
                resultEl.textContent = 'Erreur serveur (HTTP ' + status + ').' + hint;
                resultEl.classList.remove('d-none');
            }
        });

        xhr.addEventListener('error', function () {
            btn.disabled = false;
            progressWrap.classList.add('d-none');
            resultEl.className = 'alert alert-danger mt-2';
            resultEl.textContent = 'Erreur réseau lors de l\'upload.';
            resultEl.classList.remove('d-none');
        });

        xhr.send(fd);
    });

    /* ----------------------------------------------------------
     * Chargement automatique quand l'onglet Pièces jointes est activé
     * ---------------------------------------------------------- */
    var tabPieces = document.querySelector('[href="#tabPieces"]');
    if (tabPieces) {
        tabPieces.addEventListener('shown.bs.tab', function () {
            chargerListe();
        });
    }

    // Si l'onglet est actif au chargement de la page (ex: retour URL avec #tabPieces)
    if (window.location.hash === '#tabPieces') {
        chargerListe();
    }
})();

/* ================================================================
   Import détenus → Formulaire partie
   ================================================================ */
(function() {
    var searchInput  = document.getElementById('searchDetenusInput');
    var resultsEl    = document.getElementById('searchDetenusResults');
    var importModal  = document.getElementById('modalImportDetenu');
    var partieModal  = document.getElementById('modalAddPartie');
    var debounceTimer;
    var BASE = '<?=BASE_URL?>';

    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        var q = this.value.trim();
        if (q.length < 2) {
            resultsEl.innerHTML = '<div class="text-center text-muted py-4 small"><i class="bi bi-search me-1"></i>Tapez au moins 2 caractères pour rechercher</div>';
            return;
        }
        debounceTimer = setTimeout(function() { searchDetenus(q); }, 350);
    });

    // Reset search quand le modal se ferme
    importModal.addEventListener('hidden.bs.modal', function() {
        searchInput.value = '';
        resultsEl.innerHTML = '<div class="text-center text-muted py-4 small"><i class="bi bi-search me-1"></i>Tapez au moins 2 caractères pour rechercher</div>';
    });

    function searchDetenus(q) {
        resultsEl.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-danger"></div> Recherche...</div>';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', BASE + '/api/detenus/search?q=' + encodeURIComponent(q), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
            if (xhr.status === 200) {
                renderResults(JSON.parse(xhr.responseText));
            } else {
                resultsEl.innerHTML = '<div class="alert alert-danger py-2 small">Erreur lors de la recherche</div>';
            }
        };
        xhr.onerror = function() {
            resultsEl.innerHTML = '<div class="alert alert-danger py-2 small">Erreur réseau</div>';
        };
        xhr.send();
    }

    function renderResults(detenus) {
        if (!detenus.length) {
            resultsEl.innerHTML = '<div class="text-center text-muted py-4 small">Aucun détenu trouvé</div>';
            return;
        }
        var html = '<div class="table-responsive"><table class="table table-hover table-sm align-middle mb-0">';
        html += '<thead class="table-light"><tr><th>N° Écrou</th><th>Nom</th><th>Prénom</th><th>Incarceration</th><th></th></tr></thead><tbody>';
        detenus.forEach(function(d) {
            var dateInc = d.date_incarceration ? new Date(d.date_incarceration).toLocaleDateString('fr-FR') : '—';
            html += '<tr>';
            html += '<td class="font-monospace small">' + escHtml(d.numero_ecrou) + '</td>';
            html += '<td>' + escHtml(d.nom) + '</td>';
            html += '<td>' + escHtml(d.prenom) + '</td>';
            html += '<td class="small">' + dateInc + '</td>';
            html += '<td><button type="button" class="btn btn-sm btn-outline-danger btn-import-det"'
                  + ' data-nom="' + escAttr(d.nom) + '"'
                  + ' data-prenom="' + escAttr(d.prenom) + '"'
                  + ' data-nationalite="' + escAttr(d.nationalite || 'Nigérienne') + '"'
                  + ' data-adresse="' + escAttr(d.adresse_detention || '') + '">'
                  + '<i class="bi bi-box-arrow-in-right me-1"></i>Importer</button></td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        resultsEl.innerHTML = html;

        // Attacher les événements sur les boutons Importer
        resultsEl.querySelectorAll('.btn-import-det').forEach(function(btn) {
            btn.addEventListener('click', function() {
                // Vérifier que le type_partie sélectionné n'est pas avocat
                var typeSelect = document.getElementById('selectTypePartie');
                if (typeSelect && typeSelect.value === 'avocat') {
                    alert('Les avocats ne peuvent pas être importés depuis les détenus. Veuillez changer le type de partie.');
                    return;
                }
                // Pré-remplir le formulaire
                var nomEl = document.getElementById('partieNom');
                var prenomEl = document.getElementById('partiePrenom');
                var natEl = document.getElementById('partieNationalite');
                var adrEl = document.getElementById('partieAdresse');
                if (nomEl) nomEl.value = btn.dataset.nom || '';
                if (prenomEl) prenomEl.value = btn.dataset.prenom || '';
                if (natEl) natEl.value = btn.dataset.nationalite || 'Nigérienne';
                if (adrEl) adrEl.value = btn.dataset.adresse || '';
                // Fermer modal import, ouvrir modal partie
                bootstrap.Modal.getInstance(importModal).hide();
                importModal.addEventListener('hidden.bs.modal', function openPartie() {
                    new bootstrap.Modal(partieModal).show();
                    importModal.removeEventListener('hidden.bs.modal', openPartie);
                }, { once: true });
            });
        });
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function escAttr(s) {
        return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }
})();
</script>

<!-- Modal Classer sans suite -->
<div class="modal fade" id="modalClasser" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-secondary text-white">
            <h5 class="modal-title"><i class="bi bi-archive me-2"></i>Classer sans suite</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="<?=BASE_URL?>/dossiers/classer/<?=$dossier['id']?>">
            <?=CSRF::field()?>
            <div class="modal-body">
                <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Cette action classe le dossier sans suite. Vous pourrez le déclasser ultérieurement.</div>
                <label class="form-label fw-bold">Motif du classement <span class="text-danger">*</span></label>
                <textarea name="motif_classement" class="form-control" rows="4" required placeholder="Exposez les raisons du classement sans suite…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-dark"><i class="bi bi-archive me-1"></i>Confirmer le classement</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Modal Déclasser -->
<div class="modal fade" id="modalDeclasser" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-warning">
            <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Déclasser le dossier</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="<?=BASE_URL?>/dossiers/declasser/<?=$dossier['id']?>">
            <?=CSRF::field()?>
            <div class="modal-body">
                <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Le dossier sera remis au stade <strong>Parquet</strong> pour reprise du traitement.</div>
                <?php if($dossier['motif_classement']): ?>
                <div class="mb-3"><small class="text-muted">Motif du classement initial :</small><p class="fst-italic small"><?=htmlspecialchars($dossier['motif_classement'])?></p></div>
                <?php endif; ?>
                <label class="form-label fw-bold">Motif du déclassement <span class="text-danger">*</span></label>
                <textarea name="motif_declassement" class="form-control" rows="4" required placeholder="Exposez les raisons du déclassement (nouveaux éléments, erreur de procédure…)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-warning"><i class="bi bi-arrow-counterclockwise me-1"></i>Confirmer le déclassement</button>
            </div>
        </form>
    </div></div>
</div>

<script>
function suggererCabinetInstr(){
    fetch('<?= BASE_URL ?>/api/cabinets/charge')
    .then(r=>r.json())
    .then(data=>{
        if(data.success && data.data.length){
            var best = data.data[0];
            var sel  = document.getElementById('selectCabinetInstr');
            if(sel){
                sel.value = best.id;
                document.getElementById('cabinetInstrInfo').innerHTML =
                    '<i class="bi bi-info-circle text-success me-1"></i>Suggéré : <strong>' +
                    best.numero + ' — ' + best.libelle + '</strong> (' +
                    best.nb_dossiers + ' dossier(s) actif(s))';
            }
        }
    }).catch(()=>{});
}
</script>
