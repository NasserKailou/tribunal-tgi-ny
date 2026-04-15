<?php $pageTitle = 'Alertes système'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-bell me-2 text-warning"></i>Alertes système</h4>
    <?php if (!empty($alertes)): ?>
    <form method="POST" action="<?= BASE_URL ?>/alertes/lire-tout">
        <?= CSRF::field() ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-check-all me-1"></i>Tout marquer lu</button>
    </form>
    <?php endif; ?>
</div>

<!-- Filtre niveau -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form class="d-flex gap-2 align-items-center" method="GET" action="<?= BASE_URL ?>/alertes">
            <select name="niveau" class="form-select form-select-sm w-auto">
                <option value="">Tous niveaux</option>
                <option value="info" <?= $niveau==='info'?'selected':'' ?>>Info</option>
                <option value="warning" <?= $niveau==='warning'?'selected':'' ?>>Avertissement</option>
                <option value="danger" <?= $niveau==='danger'?'selected':'' ?>>Danger</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Filtrer</button>
            <a href="<?= BASE_URL ?>/alertes" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($alertes)): ?>
        <div class="text-center text-muted py-5"><i class="bi bi-bell-slash fs-1 d-block mb-2"></i>Aucune alerte</div>
        <?php else: ?>
        <?php foreach ($alertes as $a): ?>
        <div class="d-flex gap-3 p-3 border-bottom <?= !$a['est_lue'] ? 'bg-light-subtle' : '' ?>">
            <div class="flex-shrink-0 mt-1">
                <?php $icons=['info'=>['info-circle','text-info'],'warning'=>['exclamation-triangle','text-warning'],'danger'=>['exclamation-circle','text-danger']]; [$ic,$tc]=$icons[$a['niveau']]??['bell','text-secondary']; ?>
                <i class="bi bi-<?= $ic ?> fs-5 <?= $tc ?>"></i>
            </div>
            <div class="flex-fill">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <?php if (!$a['est_lue']): ?><span class="badge bg-danger me-1">Nouveau</span><?php endif; ?>
                        <span class="badge <?= $a['niveau']==='danger'?'bg-danger':($a['niveau']==='warning'?'bg-warning text-dark':'bg-info text-dark') ?> me-1">
                            <?= ['info'=>'Info','warning'=>'Attention','danger'=>'Urgent'][$a['niveau']] ?? $a['niveau'] ?>
                        </span>
                        <span class="badge bg-secondary"><?= str_replace('_', ' ', $a['type_alerte']) ?></span>
                    </div>
                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></small>
                </div>
                <p class="mb-1 mt-1"><?= htmlspecialchars($a['message']) ?></p>
                <div class="d-flex gap-2 align-items-center small text-muted">
                    <?php if ($a['numero_rg']): ?>
                    <a href="<?= BASE_URL ?>/dossiers/show/<?= $a['dossier_id'] ?>" class="text-decoration-none"><i class="bi bi-folder2 me-1"></i><?= htmlspecialchars($a['numero_rg']) ?></a>
                    <?php endif; ?>
                    <?php if ($a['dest_nom']): ?><span>→ <?= htmlspecialchars($a['dest_nom']) ?></span><?php endif; ?>
                    <?php if (!$a['est_lue']): ?>
                    <form method="POST" action="<?= BASE_URL ?>/alertes/lire/<?= $a['id'] ?>" class="ms-2">
                        <?= CSRF::field() ?>
                        <button class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2">Marquer lu</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center py-3">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?=$i?>&niveau=<?=$niveau?>" class="btn btn-sm <?=$i===$page?'btn-primary':'btn-outline-secondary'?> mx-1"><?=$i?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
