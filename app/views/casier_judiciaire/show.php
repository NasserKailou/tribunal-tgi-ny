<?php $pageTitle = 'Casier judiciaire — '.htmlspecialchars(($personne['nom']??'').' '.($personne['prenom']??'')); ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/casier-judiciaire" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Casier judiciaire</h4>
</div>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Identité</div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-sm-5 text-muted">NIN</dt><dd class="col-sm-7 font-monospace"><?=htmlspecialchars($personne['nin']??'—')?></dd>
                    <dt class="col-sm-5 text-muted">Nom</dt><dd class="col-sm-7"><strong><?=htmlspecialchars($personne['nom']??'—')?></strong></dd>
                    <dt class="col-sm-5 text-muted">Prénom</dt><dd class="col-sm-7"><?=htmlspecialchars($personne['prenom']??'—')?></dd>
                    <dt class="col-sm-5 text-muted">Date naissance</dt><dd class="col-sm-7"><?=htmlspecialchars($personne['date_naissance']??'—')?></dd>
                    <dt class="col-sm-5 text-muted">Lieu naissance</dt><dd class="col-sm-7"><?=htmlspecialchars($personne['lieu_naissance']??'—')?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Antécédents judiciaires</span>
                <?php if(empty($condamnations)): ?>
                <span class="badge bg-success">Casier vierge</span>
                <?php else: ?>
                <span class="badge bg-danger"><?=count($condamnations)?> condamnation(s)</span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if(empty($condamnations)): ?>
                <div class="text-center text-muted py-5"><i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>Aucune condamnation enregistrée</div>
                <?php else: ?>
                <div class="table-responsive"><table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Juridiction</th><th>Infraction</th><th>Peine</th><th>Dossier</th></tr></thead>
                    <tbody>
                    <?php foreach($condamnations as $c): ?>
                    <tr>
                        <td class="small"><?=htmlspecialchars($c['date_condamnation']??'—')?></td>
                        <td class="small"><?=htmlspecialchars($c['juridiction']??'TGI-NY')?></td>
                        <td class="small"><?=htmlspecialchars($c['infraction']??'—')?></td>
                        <td class="small fw-semibold"><?=htmlspecialchars($c['peine']??'—')?></td>
                        <td class="small"><?php if(!empty($c['dossier_id'])): ?><a href="<?=BASE_URL?>/dossiers/show/<?=$c['dossier_id']?>"><?=htmlspecialchars($c['numero_rg']??'—')?></a><?php else: ?>—<?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
