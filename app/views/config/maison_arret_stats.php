<?php
/**
 * vue config/maison_arret_stats.php — Population carcérale par sexe
 * @var array  $maison
 * @var array  $statsParSexe   [{sexe, nb, types:[...]}]
 * @var array  $statsParType   [{type_detention, sexe, nb}]
 * @var int    $total
 */
?>
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/config">Configuration</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/config/maisons-arret">Maisons d'arrêt</a></li>
            <li class="breadcrumb-item active">Stats — <?= htmlspecialchars($maison['nom']) ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-bar-chart-line text-info me-2"></i>
                Population carcérale — <?= htmlspecialchars($maison['nom']) ?>
            </h3>
            <small class="text-muted">
                Ville : <?= htmlspecialchars($maison['ville']) ?>
                <?php if ($maison['directeur']): ?>
                | Directeur : <?= htmlspecialchars($maison['directeur']) ?>
                <?php endif; ?>
            </small>
        </div>
        <a href="<?= BASE_URL ?>/config/maisons-arret" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="fs-1 fw-bold text-primary"><?= $total ?></div>
                <div class="text-muted small">Total détenus</div>
            </div>
        </div>
    </div>
    <?php
    $hommes = 0; $femmes = 0;
    foreach ($statsParSexe as $s) {
        if ($s['sexe'] === 'M') $hommes = $s['nb'];
        if ($s['sexe'] === 'F') $femmes = $s['nb'];
    }
    $pctH = $total > 0 ? round($hommes/$total*100) : 0;
    $pctF = $total > 0 ? round($femmes/$total*100) : 0;
    $cap   = (int)$maison['capacite'];
    $taux  = $cap > 0 ? round($total/$cap*100) : 0;
    $tcls  = $taux > 100 ? 'danger' : ($taux > 80 ? 'warning' : 'success');
    ?>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center" style="border-top:4px solid #0d6efd!important">
            <div class="card-body py-3">
                <div class="fs-1 fw-bold text-primary"><?= $hommes ?></div>
                <div class="text-muted small"><i class="bi bi-gender-male me-1"></i>Hommes</div>
                <div class="progress mt-2" style="height:6px">
                    <div class="progress-bar bg-primary" style="width:<?= $pctH ?>%"></div>
                </div>
                <small class="text-muted"><?= $pctH ?>%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center" style="border-top:4px solid #e83e8c!important">
            <div class="card-body py-3">
                <div class="fs-1 fw-bold" style="color:#e83e8c"><?= $femmes ?></div>
                <div class="text-muted small"><i class="bi bi-gender-female me-1"></i>Femmes</div>
                <div class="progress mt-2" style="height:6px">
                    <div class="progress-bar" style="width:<?= $pctF ?>%;background:#e83e8c"></div>
                </div>
                <small class="text-muted"><?= $pctF ?>%</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center" style="border-top:4px solid var(--bs-<?= $tcls ?>)!important">
            <div class="card-body py-3">
                <div class="fs-1 fw-bold text-<?= $tcls ?>"><?= $taux ?>%</div>
                <div class="text-muted small"><i class="bi bi-percent me-1"></i>Taux d'occupation</div>
                <div class="progress mt-2" style="height:6px">
                    <div class="progress-bar bg-<?= $tcls ?>" style="width:<?= min($taux,100) ?>%"></div>
                </div>
                <small class="text-muted">Capacité : <?= $cap ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Tableau détaillé par type de détention et sexe -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <strong><i class="bi bi-table me-2"></i>Répartition par type de détention</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type de détention</th>
                                <th class="text-center"><i class="bi bi-gender-male text-primary"></i> Hommes</th>
                                <th class="text-center"><i class="bi bi-gender-female" style="color:#e83e8c"></i> Femmes</th>
                                <th class="text-center">Total</th>
                                <th>Proportion</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Restructurer par type
                        $byType = [];
                        foreach ($statsParType as $row) {
                            $t = $row['type_detention'];
                            if (!isset($byType[$t])) $byType[$t] = ['M'=>0,'F'=>0,'total'=>0];
                            $byType[$t][$row['sexe']] = (int)$row['nb'];
                            $byType[$t]['total'] += (int)$row['nb'];
                        }
                        $typeLabels = [
                            'prevenu'           => 'Prévenu',
                            'inculpe'           => 'Inculpé',
                            'condamne'          => 'Condamné',
                            'detenu_provisoire' => 'Détenu provisoire',
                            'mis_en_examen'     => 'Mis en examen',
                            'autre'             => 'Autre',
                        ];
                        arsort($byType);
                        foreach ($byType as $type => $data):
                            $pct = $total > 0 ? round($data['total']/$total*100) : 0;
                        ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($typeLabels[$type] ?? $type) ?></span></td>
                            <td class="text-center fw-semibold text-primary"><?= $data['M'] ?></td>
                            <td class="text-center fw-semibold" style="color:#e83e8c"><?= $data['F'] ?></td>
                            <td class="text-center fw-bold"><?= $data['total'] ?></td>
                            <td>
                                <div class="progress" style="height:8px;min-width:80px">
                                    <div class="progress-bar bg-info" style="width:<?= $pct ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $pct ?>%</small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Total</td>
                                <td class="text-center text-primary"><?= $hommes ?></td>
                                <td class="text-center" style="color:#e83e8c"><?= $femmes ?></td>
                                <td class="text-center"><?= $total ?></td>
                                <td>100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique camembert -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <strong><i class="bi bi-pie-chart me-2"></i>Répartition par sexe</strong>
            </div>
            <div class="card-body">
                <?php if ($total > 0): ?>
                <canvas id="chartSexe" height="220"></canvas>
                <?php else: ?>
                <div class="text-center text-muted py-4">Aucun détenu enregistré</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($total > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartSexe'), {
    type: 'doughnut',
    data: {
        labels: ['Hommes', 'Femmes'],
        datasets: [{
            data: [<?= $hommes ?>, <?= $femmes ?>],
            backgroundColor: ['#0d6efd', '#e83e8c'],
            borderWidth: 3,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        var total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                        var pct   = Math.round(ctx.parsed / total * 100);
                        return ctx.label + ' : ' + ctx.parsed + ' (' + pct + '%)';
                    }
                }
            }
        }
    }
});
</script>
<?php endif; ?>
