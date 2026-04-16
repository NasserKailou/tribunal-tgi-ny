<?php $pageTitle = 'Configuration — Membres d\'audience'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?=BASE_URL?>/config">Configuration</a></li>
                <li class="breadcrumb-item active">Membres d'audience</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-people-fill me-2 text-primary"></i>Gestion des membres d'audience
        </h4>
        <small class="text-muted">Rôles disponibles et membres habituels des audiences du TGI-NY</small>
    </div>
</div>

<?php if(!empty($flash['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i><?=htmlspecialchars($flash['success'])?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if(!empty($flash['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i><?=htmlspecialchars($flash['error'])?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- ── Rôles disponibles ──────────────────────────────────────────────── -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white py-2 fw-semibold">
                <i class="bi bi-list-check me-2"></i>Rôles d'audience disponibles
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th class="text-center">Utilisations</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $icons = [
                        'president'            => ['bi-person-badge','primary'],
                        'greffier'             => ['bi-person-lines-fill','secondary'],
                        'assesseur_1'          => ['bi-person','info'],
                        'assesseur_2'          => ['bi-person','info'],
                        'jure_1'               => ['bi-person-check','warning'],
                        'jure_2'               => ['bi-person-check','warning'],
                        'procureur'            => ['bi-building','danger'],
                        'substitut'            => ['bi-building','danger'],
                        'juge_assesseur'       => ['bi-person-workspace','dark'],
                        'avocat_defense'       => ['bi-briefcase','dark'],
                        'avocat_partie_civile' => ['bi-briefcase-fill','secondary'],
                        'greffier_adjoint'     => ['bi-person-lines-fill','success'],
                        'autre'                => ['bi-person-fill','muted'],
                    ];
                    $statsByRole = [];
                    foreach ($stats as $s) { $statsByRole[$s['role_audience']] = $s['nb']; }
                    ?>
                    <?php foreach($roles as $code => $libelle):
                        [$ico, $col] = $icons[$code] ?? ['bi-person','secondary'];
                        $nb = $statsByRole[$code] ?? 0;
                    ?>
                    <tr>
                        <td>
                            <i class="bi <?=$ico?> text-<?=$col?> me-1"></i>
                            <code class="small"><?=$code?></code>
                        </td>
                        <td class="fw-semibold"><?=$libelle?></td>
                        <td class="text-center">
                            <?php if($nb > 0): ?>
                            <span class="badge bg-primary rounded-pill"><?=$nb?></span>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Membres habituels ──────────────────────────────────────────────── -->
    <div class="col-lg-7">

        <!-- Présidence & Siège -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-2 fw-semibold">
                <i class="bi bi-person-badge text-primary me-2"></i>Composition du siège
                <small class="text-muted fw-normal ms-2">(Présidents, assesseurs)</small>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Nom</th><th>Rôle</th><th class="text-center">Actif</th></tr>
                    </thead>
                    <tbody>
                    <?php if(empty($juges)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-2">Aucun juge de siège configuré</td></tr>
                    <?php else: ?>
                    <?php foreach($juges as $j): ?>
                    <tr>
                        <td class="fw-semibold"><?=htmlspecialchars($j['prenom'].' '.$j['nom'])?></td>
                        <td><span class="badge bg-primary bg-opacity-75"><?=htmlspecialchars($j['role_code'] ?? 'juge')?></span></td>
                        <td class="text-center">
                            <?=$j['actif'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>'?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?=BASE_URL?>/users" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>Gérer les utilisateurs
                </a>
            </div>
        </div>

        <!-- Greffiers -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-2 fw-semibold">
                <i class="bi bi-person-lines-fill text-secondary me-2"></i>Greffiers
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Nom</th><th>Email</th><th class="text-center">Actif</th></tr>
                    </thead>
                    <tbody>
                    <?php if(empty($greffiers)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-2">Aucun greffier configuré</td></tr>
                    <?php else: ?>
                    <?php foreach($greffiers as $g): ?>
                    <tr>
                        <td class="fw-semibold"><?=htmlspecialchars($g['prenom'].' '.$g['nom'])?></td>
                        <td class="small text-muted"><?=htmlspecialchars($g['email'])?></td>
                        <td class="text-center">
                            <?=$g['actif'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>'?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Parquet -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-2 fw-semibold">
                <i class="bi bi-building text-danger me-2"></i>Parquet
                <span class="badge bg-danger ms-2"><?=count($parquet)?> membres</span>
            </div>
            <div class="card-body p-0" style="max-height:280px;overflow-y:auto;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                        <tr><th>Nom / Titre</th><th>Fonction</th></tr>
                    </thead>
                    <tbody>
                    <?php if(empty($parquet)): ?>
                    <tr><td colspan="2" class="text-center text-muted py-2">Aucun membre du parquet</td></tr>
                    <?php else: ?>
                    <?php foreach($parquet as $p): ?>
                    <tr>
                        <td class="fw-semibold"><?=htmlspecialchars(trim($p['prenom'].' '.$p['nom']))?></td>
                        <td>
                            <span class="badge <?=$p['role_code']==='procureur'?'bg-danger':'bg-secondary'?>">
                                <?=$p['role_code']==='procureur'?'Procureur':'Substitut'?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?=BASE_URL?>/config/substituts" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-pencil me-1"></i>Gérer les substituts
                </a>
            </div>
        </div>

    </div>
</div>
