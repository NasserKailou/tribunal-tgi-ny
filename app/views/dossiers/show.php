<?php $pageTitle = 'Dossier — ' . $dossier['numero_rg']; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/dossiers">Dossiers</a></li><li class="breadcrumb-item active"><?=htmlspecialchars($dossier['numero_rg'])?></li></ol></nav>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-folder2-open me-2 text-primary"></i><?=htmlspecialchars($dossier['numero_rg'])?></h4>
            <div class="d-flex gap-2 mt-1">
                <?php if($dossier['numero_rp']): ?><span class="badge bg-warning text-dark">RP: <?=htmlspecialchars($dossier['numero_rp'])?></span><?php endif; ?>
                <?php if($dossier['numero_ri']): ?><span class="badge bg-info text-dark">RI: <?=htmlspecialchars($dossier['numero_ri'])?></span><?php endif; ?>
                <?php $sm=['enregistre'=>['secondary','Enregistré'],'parquet'=>['warning','Parquet'],'instruction'=>['info','Instruction'],'en_audience'=>['primary','Audience'],'juge'=>['success','Jugé'],'classe'=>['dark','Classé'],'appel'=>['danger','Appel']];[$sc,$sl]=$sm[$dossier['statut']]??['secondary',$dossier['statut']]; ?>
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
                <?php if(in_array($dossier['statut'],['parquet','instruction'])): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-play-circle me-2 text-primary"></i>Actions</div>
                    <div class="card-body d-grid gap-2">
                        <?php if($dossier['statut']==='parquet' && Auth::hasRole(['admin','procureur','substitut_procureur','president'])): ?>
                        <button class="btn btn-info text-dark" data-bs-toggle="modal" data-bs-target="#modalInstruction"><i class="bi bi-send me-2"></i>Envoyer en instruction</button>
                        <?php endif; ?>
                        <?php if(in_array($dossier['statut'],['parquet','instruction']) && Auth::hasRole(['admin','procureur','juge_instruction','president'])): ?>
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
        <button class="btn btn-outline-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalAddPartie"><i class="bi bi-person-plus me-1"></i>Ajouter une partie</button>
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
                <select name="cabinet_id" class="form-select" required>
                    <option value="">— Sélectionner —</option>
                    <?php foreach($cabinets as $c): ?>
                    <option value="<?=$c['id']?>"><?=htmlspecialchars($c['numero'].' — '.$c['libelle'])?></option>
                    <?php endforeach; ?>
                </select>
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
                    <select name="type_partie" class="form-select" required><option value="plaignant">Plaignant</option><option value="defendeur">Défendeur</option><option value="prevenu">Prévenu</option><option value="victime">Victime</option><option value="avocat">Avocat</option><option value="temoin">Témoin</option><option value="mis_en_cause">Mis en cause</option></select>
                </div>
                <div class="col-md-6"><label class="form-label">Nom <span class="text-danger">*</span></label><input type="text" name="nom" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Prénom</label><input type="text" name="prenom" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="telephone" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Nationalité</label><input type="text" name="nationalite" class="form-control" value="Nigérienne"></div>
                <div class="col-md-6"><label class="form-label">Profession</label><input type="text" name="profession" class="form-control"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary" type="submit">Ajouter</button></div>
        </form>
    </div></div>
</div>
