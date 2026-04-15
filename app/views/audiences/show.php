<?php $pageTitle = 'Audience — ' . ($audience['numero_audience'] ?? ''); ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/audiences">Audiences</a></li><li class="breadcrumb-item active"><?=htmlspecialchars($audience['numero_audience']??'Audience')?></li></ol></nav>
    <div class="d-flex justify-content-between">
        <h4 class="fw-bold"><i class="bi bi-calendar-event me-2 text-primary"></i><?=htmlspecialchars($audience['numero_audience']??'Audience')?></h4>
        <?php $as=['planifiee'=>['primary','Planifiée'],'tenue'=>['success','Tenue'],'renvoyee'=>['warning','Renvoyée'],'annulee'=>['danger','Annulée']];[$ac,$al]=$as[$audience['statut']]??['secondary',$audience['statut']]; ?>
        <span class="badge bg-<?=$ac?> fs-6"><?=$al?></span>
    </div>
</div>
<div class="row g-4">
<div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white fw-semibold">Détails de l'audience</div><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><small class="text-muted">Dossier</small><br><a href="<?=BASE_URL?>/dossiers/show/<?=$audience['dossier_id']?>" class="fw-semibold text-decoration-none"><?=htmlspecialchars($audience['numero_rg'])?></a></div>
        <div class="col-md-6"><small class="text-muted">Date</small><br><strong><?=date('d/m/Y H:i',strtotime($audience['date_audience']))?></strong></div>
        <div class="col-md-6"><small class="text-muted">Type</small><br><span class="badge bg-info text-dark"><?=$audience['type_audience']?></span></div>
        <div class="col-md-6"><small class="text-muted">Salle</small><br><strong><?=htmlspecialchars($audience['salle_nom']??'—')?></strong> <?=$audience['capacite']?'('.$audience['capacite'].' places)':''?></div>
        <div class="col-md-6"><small class="text-muted">Président</small><br><strong><?=htmlspecialchars(($audience['president_prenom']??'').($audience['president_nom']?' '.$audience['president_nom']:'—'))?></strong></div>
        <div class="col-md-6"><small class="text-muted">Greffier</small><br><strong><?=htmlspecialchars(($audience['greffier_prenom']??'').($audience['greffier_nom']?' '.$audience['greffier_nom']:'—'))?></strong></div>
        <?php if($audience['notes']): ?><div class="col-12"><small class="text-muted">Notes</small><br><?=nl2br(htmlspecialchars($audience['notes']))?></div><?php endif; ?>
        <?php if($audience['motif_renvoi']): ?><div class="col-12"><small class="text-muted">Motif de renvoi</small><br><span class="text-warning"><?=htmlspecialchars($audience['motif_renvoi'])?></span></div><?php endif; ?>
    </div></div></div>

    <?php if(!empty($membres)): ?>
    <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white fw-semibold">Membres de l'audience</div><div class="card-body p-0">
    <table class="table table-hover mb-0"><thead class="table-light"><tr><th>Rôle</th><th>Nom</th></tr></thead><tbody>
    <?php foreach($membres as $m): ?><tr><td><span class="badge bg-secondary"><?=str_replace('_',' ',$m['role_audience'])?></span></td><td><?=htmlspecialchars($m['nom']?$m['nom'].' '.$m['prenom']:$m['nom_externe']??'—')?></td></tr><?php endforeach; ?>
    </tbody></table>
    </div></div>
    <?php endif; ?>
</div>
<div class="col-lg-5">
    <?php if($audience['statut']==='planifiee' && Auth::hasRole(['admin','greffier','president','procureur'])): ?>
    <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-white fw-semibold">Mettre à jour le statut</div>
    <div class="card-body">
    <form method="POST" action="<?=BASE_URL?>/audiences/update-statut/<?=$audience['id']?>">
        <?=CSRF::field()?>
        <div class="mb-3"><label class="form-label">Nouveau statut</label>
            <select name="statut" class="form-select" id="statutSelect" onchange="toggleRenvoi()">
                <option value="tenue">Audience tenue</option>
                <option value="renvoyee">Renvoyée</option>
                <option value="annulee">Annulée</option>
            </select>
        </div>
        <div id="renvoiBlock" style="display:none">
            <div class="mb-3"><label class="form-label">Motif de renvoi</label><textarea name="motif_renvoi" class="form-control" rows="2"></textarea></div>
            <div class="mb-3"><label class="form-label">Nouvelle date</label><input type="date" name="date_renvoi" class="form-control"></div>
        </div>
        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?=htmlspecialchars($audience['notes']??'')?></textarea></div>
        <button type="submit" class="btn btn-primary w-100">Mettre à jour</button>
    </form>
    </div></div>
    <script>function toggleRenvoi(){const s=document.getElementById('statutSelect').value;document.getElementById('renvoiBlock').style.display=s==='renvoyee'?'block':'none';}</script>
    <?php endif; ?>

    <?php if($audience['statut']==='tenue'): ?>
    <div class="d-grid gap-2">
        <a href="<?=BASE_URL?>/jugements/create/<?=$audience['dossier_id']?>" class="btn btn-success"><i class="bi bi-hammer me-2"></i>Saisir le jugement</a>
    </div>
    <?php endif; ?>
</div>
</div>
