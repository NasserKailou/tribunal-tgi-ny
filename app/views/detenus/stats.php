<?php $pageTitle = 'Statistiques — Population Carcérale'; ?>
<div class="mb-4 mt-2">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?=BASE_URL?>/detenus">Population Carcérale</a></li><li class="breadcrumb-item active">Statistiques</li></ol></nav>
    <h4 class="fw-bold"><i class="bi bi-bar-chart me-2 text-primary"></i>Statistiques carcérales</h4>
</div>
<div class="row g-3 mb-4">
    <?php $total=array_sum(array_column($byType,'nb')); ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Par type de détention</div>
        <div class="card-body"><canvas id="typeChart"></canvas></div></div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Évolution des incarcérations (12 mois)</div>
        <div class="card-body"><canvas id="incarcerationChart" height="100"></canvas></div></div>
    </div>
</div>

<?php if(!empty($longDetention)): ?>
<div class="card border-0 shadow-sm border-danger">
    <div class="card-header text-white fw-semibold" style="background:#dc3545"><i class="bi bi-exclamation-triangle me-2"></i>Détenus provisoires au-delà de <?=DELAI_DETENTION_PROVISOIRE_MOIS?> mois (<?=count($longDetention)?>)</div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Écrou</th><th>Nom</th><th>Type</th><th>Dossier</th><th>Incarcéré le</th><th class="text-danger">Durée</th></tr></thead>
        <tbody>
        <?php foreach($longDetention as $d): ?>
        <tr class="table-warning">
            <td><?=htmlspecialchars($d['numero_ecrou'])?></td>
            <td><a href="<?=BASE_URL?>/detenus/show/<?=$d['id']?>" class="fw-semibold text-decoration-none"><?=htmlspecialchars($d['nom'].' '.$d['prenom'])?></a></td>
            <td><span class="badge bg-secondary"><?=str_replace('_',' ',$d['type_detention'])?></span></td>
            <td><?=$d['numero_rg']?htmlspecialchars($d['numero_rg']):'—'?></td>
            <td><?=date('d/m/Y',strtotime($d['date_incarceration']))?></td>
            <td class="text-danger fw-bold"><?=$d['duree_mois']?> mois</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const byTypeData = <?= json_encode(array_column($byType,'nb','type_detention')) ?>;
new Chart(document.getElementById('typeChart'),{type:'doughnut',data:{labels:Object.keys(byTypeData).map(k=>k.replace(/_/g,' ')),datasets:[{data:Object.values(byTypeData),backgroundColor:['#dc3545','#fd7e14','#198754','#0d6efd','#6f42c1','#6c757d']}]},options:{responsive:true,plugins:{legend:{position:'bottom'}}}});

const incarc = <?= json_encode(array_column($byMonthIncarc,'nb','mois')) ?>;
new Chart(document.getElementById('incarcerationChart'),{type:'bar',data:{labels:Object.keys(incarc),datasets:[{label:'Nouvelles incarcérations',data:Object.values(incarc),backgroundColor:'rgba(220,53,69,0.7)'}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
</script>
