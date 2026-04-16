<?php $pageTitle = 'Carte Antiterroriste'; ?>
<style>
#map {
    height: 600px;
    min-height: 480px;
    border-radius: 10px;
    border: 2px solid #4a5568;
    box-shadow: 0 4px 6px rgba(0,0,0,.1);
}
.stat-card { background:#fff; border-radius:8px; padding:15px; box-shadow:0 2px 4px rgba(0,0,0,.1); text-align:center; }
.stat-number { font-size:2rem; font-weight:bold; color:#dc3545; }
.map-legend { background:white; padding:12px 15px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,.1); line-height:2; border:2px solid #e2e8f0; font-weight:500; font-size:12px; }
.map-legend h6 { color:#2d3748; font-weight:600; border-bottom:2px solid #e2e8f0; padding-bottom:5px; margin-bottom:8px; margin-top:0; }
.map-legend i { width:20px; height:20px; float:left; margin-right:10px; opacity:.85; border:1px solid rgba(0,0,0,.1); border-radius:3px; display:inline-block; }
.chart-container { background:#fff; border-radius:8px; padding:20px; box-shadow:0 2px 4px rgba(0,0,0,.1); min-height:380px; }
#chartTopCommunes, #chartRegions { height:350px; width:100%; }
@media print { .no-print { display:none!important; } #map { height:60vh!important; page-break-after:always; } }
</style>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h4 class="fw-bold mb-0"><i class="bi bi-map me-2 text-danger"></i>Carte des incidents antiterroristes</h4>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm no-print"><i class="bi bi-printer me-1"></i>Imprimer</button>
</div>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">Date début</label>
                <input type="date" id="filterDateDebut" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Date fin</label>
                <input type="date" id="filterDateFin" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Commune</label>
                <select id="filterCommune" class="form-select form-select-sm">
                    <option value="">Toutes les communes</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button id="btnApplyFilter" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-search me-1"></i>Appliquer</button>
                <button id="btnResetFilter" class="btn btn-outline-secondary btn-sm px-2" title="Réinitialiser"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="col-auto ms-auto d-flex gap-2 flex-wrap">
                <span class="badge bg-danger fs-6" id="statTotal"><i class="bi bi-file-earmark-text me-1"></i>0 PV</span>
                <span class="badge bg-secondary fs-6" id="statCommunes"><i class="bi bi-pin-map me-1"></i>0 communes</span>
                <span class="badge bg-warning text-dark fs-6" id="statRegions"><i class="bi bi-map me-1"></i>0 régions</span>
            </div>
        </div>
    </div>
</div>

<!-- Commune la plus touchée -->
<div id="topCommuneBadge" class="alert alert-danger py-2 mb-3 d-none no-print"></div>

<!-- Carte -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg,#1a3c5e,#2d6a9f);color:white">
        <span><i class="bi bi-globe-africa me-2"></i><strong>Niger — Répartition géographique des PV antiterrorisme</strong></span>
        <button id="btnResetZoom" class="btn btn-sm btn-light"><i class="bi bi-arrows-fullscreen me-1"></i>Réinitialiser zoom</button>
    </div>
    <div class="card-body p-1">
        <div id="mapContainer" style="height:600px;border-radius:8px;"></div>
    </div>
</div>

<!-- Stats rapides -->
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card"><div class="stat-number" id="totalPV">0</div><div>Total PV antiterrorisme</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-number text-success" id="totalCommunes">0</div><div>Communes concernées</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-number text-warning" id="totalRegions">0</div><div>Régions concernées</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-number text-danger" style="font-size:1.2rem" id="communeMax">—</div><div>Commune la plus touchée</div></div></div>
</div>

<!-- Graphiques -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="chart-container">
            <h5><i class="bi bi-bar-chart me-2"></i>Top 10 communes</h5>
            <div id="chartTopCommunes"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-container">
            <h5><i class="bi bi-pie-chart me-2"></i>Répartition par région</h5>
            <div id="chartRegions"></div>
        </div>
    </div>
</div>

<!-- Tableau détails -->
<div class="card border-0 shadow-sm">
    <div class="card-header fw-semibold"><i class="bi bi-table me-2"></i>Détails par commune</div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:400px;overflow-y:auto;">
            <table class="table table-bordered table-striped table-sm mb-0">
                <thead class="table-dark sticky-top">
                    <tr><th>Commune</th><th>Région</th><th>Département</th><th class="text-center">Nb PV</th><th class="text-center">Action</th></tr>
                </thead>
                <tbody id="tableDetailsBody">
                    <tr><td colspan="5" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span>Chargement…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Leaflet + Highcharts -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<script>
(function() {
'use strict';
var BASE = '<?= BASE_URL ?>';
var map = null, geojsonLayer = null, communesGeoJSON = null;
var communesInfo = [];
var pvByCommune = {};
var apiData = null;
var chartTop = null, chartReg = null;
var nigerBounds = [[10.5, 0.0], [24.0, 16.5]];

// ── Couleur choroplèthe ──
function getColor(n) {
    if(n <= 0)  return '#e8f4f8';
    if(n === 1) return '#fbbf24';
    if(n <= 4)  return '#f59e0b';
    if(n <= 9)  return '#dc2626';
    return '#b91c1c';
}

// ── Init carte ──
function initMap() {
    map = L.map('mapContainer', {
        center: [16.5, 8.5], zoom: 6,
        minZoom: 5, maxZoom: 18,
        maxBounds: [[ 8.0, -2.0],[24.5, 17.5]],
        maxBoundsViscosity: 1.0
    });
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd', maxZoom: 18
    }).addTo(map);

    addLegend();

    // Charger GeoJSON + communes.json en parallèle
    Promise.all([
        fetch(BASE + '/assets/data/niger_communes.geojson').then(r => r.json()),
        fetch(BASE + '/assets/data/communes.json').then(r => r.json())
    ]).then(function([geo, info]) {
        communesGeoJSON = geo;
        communesInfo    = info;
        populateFilterCommune();
        loadAndRender();
    }).catch(err => console.error('Erreur chargement données:', err));
}

function addLegend() {
    var legend = L.control({position:'bottomright'});
    legend.onAdd = function() {
        var d = L.DomUtil.create('div','map-legend');
        d.innerHTML = '<h6><strong>PV Antiterrorisme</strong></h6>';
        [['#b91c1c','10 et plus'],['#dc2626','5 à 9'],['#f59e0b','2 à 4'],['#fbbf24','1'],['#e8f4f8','0 (aucun)']].forEach(function(x){
            d.innerHTML += '<i style="background:'+x[0]+';border-color:#718096"></i> '+x[1]+'<br>';
        });
        return d;
    };
    legend.addTo(map);
}

// ── Charger API + rendre ──
function loadAndRender() {
    var commune   = document.getElementById('filterCommune').value;
    var dateDebut = document.getElementById('filterDateDebut').value;
    var dateFin   = document.getElementById('filterDateFin').value;
    var params    = new URLSearchParams();
    if(dateDebut) params.append('date_debut', dateDebut);
    if(dateFin)   params.append('date_fin',   dateFin);

    fetch(BASE + '/api/carte-data' + (params.toString() ? '?'+params.toString() : ''), {credentials:'same-origin'})
        .then(r => r.json())
        .then(function(data) {
            apiData = data;
            // Recalculer pvByCommune depuis l'API (NOM_COM MAJUSCULES)
            pvByCommune = {};
            (data.communes || []).forEach(function(c) {
                if(c.pv_count > 0) {
                    pvByCommune[c.nom.toUpperCase()] = c.pv_count;
                }
            });

            // Filtrer par commune si sélectionnée
            var communeFilter = commune ? commune.toUpperCase() : '';

            updateStats(data);
            renderChoropleth(communeFilter);
            createCharts(data);
            updateTable(data.communes || [], communeFilter);
        })
        .catch(err => {
            console.error('API error:', err);
            renderChoropleth('');
        });
}

// ── Rendu choroplèthe ──
function renderChoropleth(communeFilter) {
    if(!communesGeoJSON) return;
    if(geojsonLayer) map.removeLayer(geojsonLayer);

    var features = communesGeoJSON.features;
    if(communeFilter) {
        features = features.filter(f => (f.properties.NOM_COM||'').toUpperCase() === communeFilter);
    }

    geojsonLayer = L.geoJSON({type:'FeatureCollection', features: features}, {
        style: function(f) {
            var nom   = (f.properties.NOM_COM || '').toUpperCase();
            var count = pvByCommune[nom] || 0;
            return {
                fillColor: getColor(count),
                weight: count > 0 ? 2 : 1.5,
                opacity: 0.9,
                color: count > 0 ? (count >= 5 ? '#991b1b' : '#d97706') : '#718096',
                fillOpacity: count > 0 ? 0.75 : 0.4
            };
        },
        onEachFeature: function(feature, layer) {
            var nom   = (feature.properties.NOM_COM || feature.properties.commune || 'Inconnue');
            var count = pvByCommune[nom.toUpperCase()] || 0;
            var info  = communesInfo.find(c => c.NOM_COM && c.NOM_COM.toUpperCase() === nom.toUpperCase());
            var region= info ? info.adm_01 : (feature.properties.region_off || '');
            var dept  = info ? info.adm_02 : (feature.properties.dept_off   || '');

            layer.bindPopup(
                '<div style="min-width:200px"><h6 class="fw-bold">'+nom+'</h6>'+
                '<small>Région : '+region+' | Dép. : '+dept+'</small><hr class="my-1">'+
                '<strong style="color:'+(count>0?'#b91c1c':'#198754')+'">'+count+' PV antiterroriste'+(count>1?'s':'')+'</strong>'+
                (count>0?'<br><a href="#tableDetailsBody" onclick="zoomTo(\''+nom.replace(/'/g,"\'")+'\')" style="font-size:11px">📍 Voir dans le tableau</a>':'')+
                '</div>'
            );
            layer.on({
                mouseover: function(e) { e.target.setStyle({weight:3.5, color:'#1a202c', fillOpacity:0.95}); e.target.bringToFront(); },
                mouseout:  function(e) { geojsonLayer.resetStyle(e.target); },
                click:     function(e) { map.fitBounds(e.target.getBounds(), {padding:[50,50], maxZoom:10}); }
            });
        }
    }).addTo(map);

    if(geojsonLayer.getLayers().length > 0) {
        map.fitBounds(geojsonLayer.getBounds(), {padding:[10,10], maxZoom: communeFilter ? 10 : 6.5});
    }
}

window.zoomTo = function(nom) {
    if(!geojsonLayer) return;
    geojsonLayer.eachLayer(function(l) {
        if(l.feature && (l.feature.properties.NOM_COM||'').toUpperCase() === nom.toUpperCase()) {
            map.fitBounds(l.getBounds(), {padding:[50,50], maxZoom:10});
            l.openPopup();
        }
    });
};

document.getElementById('btnResetZoom').addEventListener('click', function() {
    if(geojsonLayer && geojsonLayer.getLayers().length > 0)
        map.fitBounds(geojsonLayer.getBounds(), {padding:[10,10], maxZoom:6.5});
    else map.setView([16.5,8.5], 6);
});

// ── Remplir filtre commune ──
function populateFilterCommune() {
    var sel = document.getElementById('filterCommune');
    var sorted = communesInfo.slice().sort((a,b) => (a.NOM_COM||'').localeCompare(b.NOM_COM||''));
    sorted.forEach(function(c) {
        var o = document.createElement('option');
        o.value = c.NOM_COM; o.textContent = c.NOM_COM + ' (' + c.adm_01 + ')';
        sel.appendChild(o);
    });
}

// ── Stats ──
function updateStats(data) {
    var pvRegions = new Set();
    var maxNom = '—', maxCount = 0;
    (data.communes||[]).forEach(function(c) {
        if(c.pv_count > 0) { pvRegions.add(c.region_nom); if(c.pv_count > maxCount){ maxCount = c.pv_count; maxNom = c.nom; } }
    });
    document.getElementById('totalPV').textContent       = data.total_pv_anti || 0;
    document.getElementById('totalCommunes').textContent = data.communes_actives || 0;
    document.getElementById('totalRegions').textContent  = pvRegions.size;
    document.getElementById('communeMax').textContent    = maxNom + (maxCount > 0 ? ' ('+maxCount+')' : '');
    document.getElementById('statTotal').innerHTML       = '<i class="bi bi-file-earmark-text me-1"></i>'+(data.total_pv_anti||0)+' PV antiterrorisme';
    document.getElementById('statCommunes').innerHTML    = '<i class="bi bi-pin-map me-1"></i>'+(data.communes_actives||0)+' communes actives';
    document.getElementById('statRegions').innerHTML     = '<i class="bi bi-map me-1"></i>'+pvRegions.size+' régions';

    var badge = document.getElementById('topCommuneBadge');
    if(maxCount > 0) {
        badge.innerHTML = '<i class="bi bi-geo-alt-fill me-2"></i>Commune la plus touchée : <strong>'+maxNom+'</strong> — '+maxCount+' PV';
        badge.classList.remove('d-none');
    }
}

// ── Graphiques Highcharts ──
function createCharts(data) {
    // Top 10 communes
    var communes = (data.communes||[]).filter(c => c.pv_count > 0)
                                       .sort((a,b) => b.pv_count - a.pv_count).slice(0,10);
    if(chartTop) chartTop.destroy();
    if(communes.length > 0) {
        chartTop = Highcharts.chart('chartTopCommunes', {
            chart: {type:'column', height:320},
            title: {text:null},
            xAxis: {categories: communes.map(c => c.nom), labels:{rotation:-45, style:{fontSize:'10px'}}},
            yAxis: {min:0, title:{text:'Nb PV'}, allowDecimals:false},
            legend: {enabled:false}, credits:{enabled:false},
            tooltip: {pointFormat:'<b>{point.y}</b> PV'},
            plotOptions: {column:{dataLabels:{enabled:true}, color:'#dc3545'}},
            series: [{name:'PV', data: communes.map(c => c.pv_count)}],
            exporting:{enabled:true}
        });
    } else {
        document.getElementById('chartTopCommunes').innerHTML = '<p class="text-muted text-center py-5">Aucun PV enregistré</p>';
    }

    // Répartition par région
    var pvRegion = data.pvByRegion || {};
    var regData  = Object.keys(pvRegion).filter(k => pvRegion[k] > 0)
                          .map(k => ({name:k, y:pvRegion[k]}));
    if(chartReg) chartReg.destroy();
    if(regData.length > 0) {
        chartReg = Highcharts.chart('chartRegions', {
            chart: {type:'pie', height:320},
            title: {text:null}, credits:{enabled:false},
            tooltip: {pointFormat:'<b>{point.y}</b> PV ({point.percentage:.1f}%)'},
            plotOptions: {pie:{dataLabels:{enabled:true, format:'<b>{point.name}</b>: {point.y}'}, showInLegend:true}},
            series: [{name:'PV', colorByPoint:true, data:regData}],
            exporting:{enabled:true}
        });
    } else {
        document.getElementById('chartRegions').innerHTML = '<p class="text-muted text-center py-5">Aucune donnée</p>';
    }
}

// ── Tableau ──
function updateTable(communes, communeFilter) {
    var sorted = communes.filter(c => c.pv_count > 0 && (!communeFilter || c.nom.toUpperCase() === communeFilter))
                          .sort((a,b) => b.pv_count - a.pv_count);
    var tbody = document.getElementById('tableDetailsBody');
    if(sorted.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Aucun PV antiterroriste</td></tr>';
        return;
    }
    tbody.innerHTML = sorted.map(c =>
        '<tr><td class="fw-semibold">'+c.nom+'</td><td>'+(c.region_nom||'—')+'</td><td>'+(c.dept_nom||'—')+'</td>'+
        '<td class="text-center"><span class="badge" style="background:'+getColor(c.pv_count)+';color:'+(c.pv_count>=5?'#fff':'#333')+'">'+c.pv_count+'</span></td>'+
        '<td class="text-center"><button class="btn btn-sm btn-primary py-0" onclick="zoomTo(\''+c.nom.replace(/'/g,"\\'")+'\')">'+'<i class="bi bi-geo-alt"></i></button></td></tr>'
    ).join('');
}

// ── Boutons filtres ──
document.getElementById('btnApplyFilter').addEventListener('click', loadAndRender);
document.getElementById('btnResetFilter').addEventListener('click', function() {
    document.getElementById('filterCommune').value   = '';
    document.getElementById('filterDateDebut').value = '';
    document.getElementById('filterDateFin').value   = '';
    loadAndRender();
});

// ── Démarrage ──
document.addEventListener('DOMContentLoaded', function() {
    // Attendre Leaflet + Highcharts
    var tries = 0;
    function tryInit() {
        if(typeof L !== 'undefined' && typeof Highcharts !== 'undefined') {
            initMap();
        } else if(tries++ < 30) {
            setTimeout(tryInit, 200);
        }
    }
    tryInit();
});

})();
</script>