<?php $pageTitle = 'Avocat — '.$avocat['nom'].' '.$avocat['prenom']; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/avocats">Avocats</a></li><li class="breadcrumb-item active"><?=htmlspecialchars($avocat['nom'].' '.$avocat['prenom'])?></li></ol></nav>
    <div class="d-flex justify-content-between">
        <h4 class="fw-bold"><i class="bi bi-person-badge me-2 text-primary"></i><?=htmlspecialchars($avocat['nom'].' '.$avocat['prenom'])?></h4>
        <?php if(Auth::hasRole(['admin','greffier','procureur','president'])): ?>
        <a href="<?=BASE_URL?>/avocats/edit/<?=$avocat['id']?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Modifier</a>
        <?php endif; ?>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Informations</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted d-block">Matricule</small><strong class="font-monospace"><?=htmlspecialchars($avocat['matricule'])?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Barreau</small><strong><?=htmlspecialchars($avocat['barreau'])?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">N° ordre</small><strong><?=htmlspecialchars($avocat['numero_ordre']??'—')?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Date inscription</small><strong><?=$avocat['date_inscription']?date('d/m/Y',strtotime($avocat['date_inscription'])):'—'?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Téléphone</small><strong><?=htmlspecialchars($avocat['telephone']??'—')?></strong></div>
                    <div class="col-md-4"><small class="text-muted d-block">Email</small><strong><?=htmlspecialchars($avocat['email']??'—')?></strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Spécialité</small><p class="mb-0"><?=htmlspecialchars($avocat['specialite']??'—')?></p></div>
                    <div class="col-md-6"><small class="text-muted d-block">Adresse</small><p class="mb-0"><?=nl2br(htmlspecialchars($avocat['adresse']??'—'))?></p></div>
                    <?php if($avocat['notes']): ?><div class="col-12"><small class="text-muted d-block">Notes</small><p class="mb-0 small fst-italic"><?=nl2br(htmlspecialchars($avocat['notes']))?></p></div><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-folder2 me-2"></i>Dossiers (<?=count($dossiers)?>)</div>
            <div class="card-body p-0">
                <?php if(empty($dossiers)): ?><div class="text-center text-muted py-4">Aucun dossier associé</div>
                <?php else: ?><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>N° RG</th><th>Rôle</th><th>Type</th><th>Statut</th><th></th></tr></thead><tbody>
                <?php foreach($dossiers as $d): ?>
                <tr><td><?=htmlspecialchars($d['numero_rg'])?></td><td><span class="badge bg-secondary"><?=str_replace('_',' ',$d['role_avocat'])?></span></td>
                <td><?=ucfirst($d['type_affaire'])?></td>
                <td><?=ucfirst(str_replace('_',' ',$d['dossier_statut']))?></td>
                <td><a href="<?=BASE_URL?>/dossiers/show/<?=$d['dossier_id']?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td></tr>
                <?php endforeach; ?></tbody></table></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width:80px;height:80px;font-size:2rem"><i class="bi bi-person-badge"></i></div>
                <h5 class="fw-bold mb-1"><?=htmlspecialchars($avocat['nom'].' '.$avocat['prenom'])?></h5>
                <?php $sc=['actif'=>'success','suspendu'=>'warning','radié'=>'danger','honoraire'=>'info'][$avocat['statut']]??'secondary'; ?>
                <span class="badge bg-<?=$sc?> mb-2"><?=ucfirst($avocat['statut'])?></span>
                <p class="text-muted small mb-0"><?=htmlspecialchars($avocat['barreau'])?></p>
            </div>
        </div>
    </div>
</div>
