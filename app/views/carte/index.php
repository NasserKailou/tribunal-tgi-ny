<?php $pageTitle = 'Carte Antiterroriste — Niger'; ?>
<style>
/* ── Carte principale ── */
#mapContainer {
    height: 700px;
    width: 100%;
    border: 2px solid #4a5568;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,.1), 0 2px 4px rgba(0,0,0,.06);
}

/* ── Stat cards ── */
.stat-card {
    background: #fff;
    border-radius: 8px;
    padding: 18px 15px;
    box-shadow: 0 2px 6px rgba(0,0,0,.08);
    text-align: center;
    border-left: 4px solid #dee2e6;
    transition: transform .15s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card.danger  { border-left-color: #dc3545; }
.stat-card.success { border-left-color: #198754; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.info    { border-left-color: #0dcaf0; }
.stat-number { font-size: 2rem; font-weight: 700; color: #dc3545; }
.stat-number.green  { color: #198754; }
.stat-number.orange { color: #fd7e14; }
.stat-number.blue   { color: #0d6efd; }
.stat-label { font-size: 0.8rem; color: #6c757d; margin-top: 4px; }

/* ── Légende carte ── */
.map-legend {
    background: white;
    padding: 12px 15px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,.1), 0 2px 4px rgba(0,0,0,.06);
    line-height: 2.1;
    border: 2px solid #e2e8f0;
    font-weight: 500;
    font-size: 12px;
    min-width: 195px;
}
.map-legend h6 {
    color: #2d3748;
    font-weight: 700;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 6px;
    margin-bottom: 8px;
    margin-top: 0;
    font-size: 13px;
}
.map-legend i {
    width: 20px;
    height: 20px;
    float: left;
    margin-right: 10px;
    opacity: .85;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 3px;
    display: inline-block;
}

/* ── Graphiques ── */
.chart-container {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,.08);
    min-height: 420px;
}
#chartTopCommunes { height: 380px; width: 100%; }
#chartRegions     { height: 380px; width: 100%; }

/* ── Filtre section ── */
.filter-section {
    background: #f8f9fa;
    padding: 16px 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

/* ── Table ── */
.table-details-wrapper {
    max-height: 420px;
    overflow-y: auto;
}

/* ── Header carte ── */
.carte-header {
    background: linear-gradient(135deg, #1a3c5e 0%, #2563a8 100%);
    color: #fff;
    padding: 12px 18px;
    border-radius: 8px 8px 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

@media print {
    .no-print { display: none !important; }
    #mapContainer { height: 60vh !important; page-break-after: always; }
}
@media (max-width: 768px) {
    #mapContainer { height: 400px; }
    .stat-number { font-size: 1.4rem; }
}
</style>

<!-- En-tête page -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-map text-danger me-2"></i>
        Carte du Niger — Répartition Géographique des PV Antiterrorisme
    </h4>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm no-print">
        <i class="bi bi-printer me-1"></i>Imprimer
    </button>
</div>

<!-- Filtres -->
<div class="filter-section mb-3 no-print">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1"><i class="bi bi-pin-map me-1"></i>Commune</label>
            <select id="filterCommune" class="form-select form-select-sm">
                <option value="">Toutes les communes</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1"><i class="bi bi-calendar me-1"></i>Date début</label>
            <input type="date" id="filterDateDebut" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1"><i class="bi bi-calendar me-1"></i>Date fin</label>
            <input type="date" id="filterDateFin" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-3 d-flex gap-2 align-items-end">
            <button id="btnApplyFilter" class="btn btn-primary btn-sm flex-grow-1">
                <i class="bi bi-search me-1"></i>Appliquer les filtres
            </button>
            <button id="btnResetFilter" class="btn btn-outline-secondary btn-sm px-3" title="Réinitialiser">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        </div>
        <div class="col-md-2 d-flex align-items-end justify-content-end gap-1 flex-wrap">
            <span class="badge bg-danger fs-6" id="statTotal"><i class="bi bi-file-earmark-text me-1"></i>0 PV</span>
            <span class="badge bg-secondary fs-6" id="statCommunes"><i class="bi bi-pin-map me-1"></i>0</span>
        </div>
    </div>
</div>

<!-- Statistiques rapides -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card danger">
            <div class="stat-number" id="totalPV">0</div>
            <div class="stat-label"><i class="bi bi-file-earmark-text me-1"></i>Total PV Antiterrorisme</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card success">
            <div class="stat-number green" id="totalCommunes">0</div>
            <div class="stat-label"><i class="bi bi-pin-map me-1"></i>Communes concernées</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card warning">
            <div class="stat-number orange" id="totalRegions">0</div>
            <div class="stat-label"><i class="bi bi-map me-1"></i>Régions concernées</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card info">
            <div class="stat-number blue" id="communeMax" style="font-size:1.1rem;line-height:1.3">—</div>
            <div class="stat-label"><i class="bi bi-geo-alt-fill me-1"></i>Commune la plus touchée</div>
        </div>
    </div>
</div>

<!-- Commune badge alerte -->
<div id="topCommuneBadge" class="alert alert-danger py-2 mb-3 d-none no-print">
    <i class="bi bi-geo-alt-fill me-2"></i><span id="topCommuneText"></span>
</div>

<!-- Carte interactive -->
<div class="mb-4">
    <div class="carte-header">
        <span>
            <i class="bi bi-globe-africa me-2"></i>
            <strong>Carte du Niger — Répartition Géographique des PV Antiterrorisme</strong>
        </span>
        <button id="btnResetZoom" class="btn btn-sm btn-light no-print">
            <i class="bi bi-arrows-fullscreen me-1"></i>Réinitialiser le zoom
        </button>
    </div>
    <div id="mapContainer"></div>
</div>

<!-- Tableau détails -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header fw-semibold bg-dark text-white">
        <i class="bi bi-table me-2"></i>Détails par commune
    </div>
    <div class="card-body p-0">
        <div class="table-details-wrapper">
            <table class="table table-bordered table-striped table-sm mb-0 align-middle">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>Commune</th>
                        <th>Région</th>
                        <th>Département</th>
                        <th class="text-center">Nb PV</th>
                        <th class="text-center no-print">Action</th>
                    </tr>
                </thead>
                <tbody id="tableDetailsBody">
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">
                            <span class="spinner-border spinner-border-sm me-2"></span>Chargement des données…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Graphiques Highcharts -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="fw-semibold mb-3">
                <i class="bi bi-bar-chart-fill me-2 text-danger"></i>Top 10 communes
            </h5>
            <div id="chartTopCommunes"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="fw-semibold mb-3">
                <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Répartition par région
            </h5>
            <div id="chartRegions"></div>
        </div>
    </div>
</div>

<!-- Leaflet + Highcharts -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>

<script>
(function() {
'use strict';

var BASE            = '<?= BASE_URL ?>';
var map             = null;
var geojsonLayer    = null;
var communesGeoJSON = null;
var communesInfo    = [];
var pvByCommune     = {};   // { 'NOM_MAJUSCULES': count }
var apiData         = null;
var chartTop        = null;
var chartReg        = null;
var nigerBounds     = [[10.0, -1.0], [23.5, 16.5]];

// ── Palette choroplèthe ──────────────────────────────────────
function getColor(n) {
    if (n <= 0)  return '#e8f4f8';   // Bleu très clair — 0 PV
    if (n === 1) return '#fbbf24';   // Jaune — 1 PV
    if (n <= 4)  return '#f59e0b';   // Orange/Ambre — 2-4 PV
    if (n <= 9)  return '#dc2626';   // Rouge — 5-9 PV
    return '#b91c1c';                // Rouge foncé — 10+
}
function getBorderColor(n) {
    if (n <= 0)  return '#94a3b8';
    if (n === 1) return '#f59e0b';
    if (n <= 4)  return '#d97706';
    if (n <= 9)  return '#991b1b';
    return '#7f1d1d';
}

// ── Initialisation de la carte ───────────────────────────────
function initMap() {
    map = L.map('mapContainer', {
        center: [17.0, 8.5],
        zoom: 6,
        minZoom: 5,
        maxZoom: 18,
        maxBounds: [[ 8.0, -3.0], [24.5, 17.0]],
        maxBoundsViscosity: 0.9,
        zoomControl: true
    });

    // Fond CartoDB Positron (léger, professionnel)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
    }).addTo(map);

    addLegend();

    // Chargement parallèle GeoJSON + communes.json
    Promise.all([
        fetch(BASE + '/assets/data/niger_communes.geojson').then(function(r) {
            if (!r.ok) throw new Error('GeoJSON HTTP ' + r.status);
            return r.json();
        }),
        fetch(BASE + '/assets/data/communes.json').then(function(r) {
            if (!r.ok) throw new Error('communes.json HTTP ' + r.status);
            return r.json();
        })
    ]).then(function(results) {
        communesGeoJSON = results[0];
        communesInfo    = results[1];
        populateFilterCommune();
        loadAPIAndRender();
    }).catch(function(err) {
        console.error('Erreur chargement données géographiques:', err);
        // Essayer quand même de charger l'API
        loadAPIAndRender();
    });
}

// ── Légende ──────────────────────────────────────────────────
function addLegend() {
    var legend = L.control({ position: 'bottomright' });
    legend.onAdd = function() {
        var d = L.DomUtil.create('div', 'map-legend');
        d.innerHTML = '<h6><strong>Nombre de PV Antiterrorisme</strong></h6>';
        var items = [
            ['#b91c1c', '#7f1d1d', '10 et plus'],
            ['#dc2626', '#991b1b', '5 à 9'],
            ['#f59e0b', '#d97706', '2 à 4'],
            ['#fbbf24', '#f59e0b', '1'],
            ['#e8f4f8', '#94a3b8', '0']
        ];
        items.forEach(function(item) {
            d.innerHTML += '<i style="background:' + item[0] + ';border-color:' + item[1] + ';"></i>' +
                           '<span>' + item[2] + '</span><br>';
        });
        return d;
    };
    legend.addTo(map);
}

// ── Appel API + rendu ─────────────────────────────────────────
function loadAPIAndRender() {
    var commune   = document.getElementById('filterCommune').value;
    var dateDebut = document.getElementById('filterDateDebut').value;
    var dateFin   = document.getElementById('filterDateFin').value;
    var params    = new URLSearchParams();
    if (dateDebut) params.append('date_debut', dateDebut);
    if (dateFin)   params.append('date_fin',   dateFin);

    var url = BASE + '/api/carte-data' + (params.toString() ? '?' + params.toString() : '');

    fetch(url, { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            apiData = data;
            pvByCommune = {};
            (data.communes || []).forEach(function(c) {
                if (c.pv_count > 0) {
                    pvByCommune[c.nom.toUpperCase()] = parseInt(c.pv_count, 10);
                }
            });
            updateStats(data);
            renderChoropleth(commune ? commune.toUpperCase() : '');
            createCharts(data);
            updateTable(data.communes || [], commune ? commune.toUpperCase() : '');
        })
        .catch(function(err) {
            console.error('Erreur API carte:', err);
            renderChoropleth('');
            updateTableEmpty();
        });
}

// ── Rendu choroplèthe ─────────────────────────────────────────
function renderChoropleth(communeFilter) {
    if (!communesGeoJSON) return;
    if (geojsonLayer) map.removeLayer(geojsonLayer);

    var features = communesGeoJSON.features;
    if (communeFilter) {
        features = features.filter(function(f) {
            return (f.properties.NOM_COM || '').toUpperCase() === communeFilter;
        });
    }

    geojsonLayer = L.geoJSON(
        { type: 'FeatureCollection', features: features },
        {
            style: function(f) {
                var nom   = (f.properties.NOM_COM || '').toUpperCase();
                var count = pvByCommune[nom] || 0;
                return {
                    fillColor:   getColor(count),
                    weight:      count > 0 ? 1.8 : 1.2,
                    opacity:     1,
                    color:       getBorderColor(count),
                    fillOpacity: count > 0 ? 0.75 : 0.35
                };
            },
            onEachFeature: function(feature, layer) {
                var nom   = (feature.properties.NOM_COM || feature.properties.commune || 'Inconnue');
                var count = pvByCommune[nom.toUpperCase()] || 0;
                var info  = communesInfo.find(function(c) {
                    return (c.NOM_COM || c.nom || '').toUpperCase() === nom.toUpperCase();
                });
                var region = info ? (info.adm_01 || info.region || '') :
                             (feature.properties.region_off || '');
                var dept   = info ? (info.adm_02 || info.departement || '') :
                             (feature.properties.dept_off   || '');

                layer.bindPopup(
                    '<div style="min-width:200px">' +
                    '<h6 class="fw-bold mb-1">' + nom + '</h6>' +
                    '<small class="text-muted">Région : ' + (region || 'N/A') + ' | Dép. : ' + (dept || 'N/A') + '</small>' +
                    '<hr class="my-2">' +
                    '<strong style="color:' + (count > 0 ? '#b91c1c' : '#198754') + ';font-size:1rem;">' +
                    count + ' PV antiterroriste' + (count > 1 ? 's' : '') +
                    '</strong>' +
                    (count > 0 ? '<br><a href="#tableDetailsBody" onclick="window.zoomTo(\'' +
                    nom.replace(/'/g, "\\'") + '\')" style="font-size:11px">📍 Voir dans le tableau</a>' : '') +
                    '</div>'
                );

                layer.on({
                    mouseover: function(e) {
                        e.target.setStyle({ weight: 3.5, color: '#1a202c', fillOpacity: 0.95 });
                        e.target.bringToFront();
                    },
                    mouseout:  function(e) { geojsonLayer.resetStyle(e.target); },
                    click:     function(e) {
                        map.fitBounds(e.target.getBounds(), { padding: [50, 50], maxZoom: 10 });
                    }
                });
            }
        }
    ).addTo(map);

    if (geojsonLayer.getLayers().length > 0) {
        map.fitBounds(geojsonLayer.getBounds(), {
            padding: [10, 10],
            maxZoom: communeFilter ? 10 : 6.5
        });
    }
}

// Zoom sur une commune depuis le tableau
window.zoomTo = function(nom) {
    if (!geojsonLayer) return;
    geojsonLayer.eachLayer(function(l) {
        if (l.feature && (l.feature.properties.NOM_COM || '').toUpperCase() === nom.toUpperCase()) {
            map.fitBounds(l.getBounds(), { padding: [50, 50], maxZoom: 10 });
            l.openPopup();
        }
    });
};

// ── Filtre commune ────────────────────────────────────────────
function populateFilterCommune() {
    var sel = document.getElementById('filterCommune');
    var sorted = communesInfo.slice().sort(function(a, b) {
        return (a.NOM_COM || a.nom || '').localeCompare(b.NOM_COM || b.nom || '');
    });
    sorted.forEach(function(c) {
        var name = c.NOM_COM || c.nom || '';
        var reg  = c.adm_01  || c.region || '';
        var o = document.createElement('option');
        o.value = name;
        o.textContent = name + (reg ? ' (' + reg + ')' : '');
        sel.appendChild(o);
    });
}

// ── Statistiques ──────────────────────────────────────────────
function updateStats(data) {
    var pvRegions = new Set();
    var maxNom = '—', maxCount = 0;
    (data.communes || []).forEach(function(c) {
        if (c.pv_count > 0) {
            if (c.region_nom) pvRegions.add(c.region_nom);
            if (c.pv_count > maxCount) { maxCount = c.pv_count; maxNom = c.nom; }
        }
    });
    var total    = data.total_pv_anti || 0;
    var actives  = data.communes_actives || 0;
    document.getElementById('totalPV').textContent       = total;
    document.getElementById('totalCommunes').textContent = actives;
    document.getElementById('totalRegions').textContent  = pvRegions.size;
    document.getElementById('communeMax').textContent    = maxNom + (maxCount > 0 ? ' (' + maxCount + ')' : '');
    document.getElementById('statTotal').innerHTML       = '<i class="bi bi-file-earmark-text me-1"></i>' + total + ' PV antiterrorisme';
    document.getElementById('statCommunes').innerHTML    = '<i class="bi bi-pin-map me-1"></i>' + actives + ' communes';

    var badge = document.getElementById('topCommuneBadge');
    var txt   = document.getElementById('topCommuneText');
    if (maxCount > 0) {
        txt.textContent = 'Commune la plus touchée : ' + maxNom + ' — ' + maxCount + ' PV';
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

// ── Graphiques ───────────────────────────────────────────────
function createCharts(data) {
    // Top 10 communes
    var communes = (data.communes || [])
        .filter(function(c) { return c.pv_count > 0; })
        .sort(function(a, b) { return b.pv_count - a.pv_count; })
        .slice(0, 10);

    if (chartTop) chartTop.destroy();
    if (communes.length > 0) {
        chartTop = Highcharts.chart('chartTopCommunes', {
            chart: { type: 'column', height: 360 },
            title: { text: null },
            xAxis: {
                categories: communes.map(function(c) { return c.nom; }),
                labels: { rotation: -45, style: { fontSize: '10px' } }
            },
            yAxis: { min: 0, title: { text: 'Nb PV' }, allowDecimals: false },
            legend: { enabled: false },
            credits: { enabled: false },
            tooltip: { pointFormat: '<b>{point.y}</b> PV antiterroriste(s)' },
            plotOptions: {
                column: {
                    dataLabels: { enabled: true, format: '{point.y}' },
                    color: '#dc3545',
                    borderRadius: 3
                }
            },
            series: [{ name: 'PV', data: communes.map(function(c) { return c.pv_count; }) }],
            exporting: { enabled: true }
        });
    } else {
        document.getElementById('chartTopCommunes').innerHTML =
            '<p class="text-muted text-center py-5"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Aucun PV antiterroriste enregistré</p>';
    }

    // Répartition par région
    var pvRegion = data.pvByRegion || {};
    var regData  = Object.keys(pvRegion)
        .filter(function(k) { return pvRegion[k] > 0; })
        .map(function(k) { return { name: k, y: pvRegion[k] }; });

    if (chartReg) chartReg.destroy();
    if (regData.length > 0) {
        chartReg = Highcharts.chart('chartRegions', {
            chart: { type: 'pie', height: 360 },
            title: { text: null },
            credits: { enabled: false },
            tooltip: { pointFormat: '<b>{point.y}</b> PV ({point.percentage:.1f}%)' },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.y}',
                        style: { fontSize: '11px' }
                    },
                    showInLegend: true
                }
            },
            series: [{ name: 'PV', colorByPoint: true, data: regData }],
            exporting: { enabled: true }
        });
    } else {
        document.getElementById('chartRegions').innerHTML =
            '<p class="text-muted text-center py-5"><i class="bi bi-pie-chart fs-2 d-block mb-2"></i>Aucune donnée par région</p>';
    }
}

// ── Tableau ──────────────────────────────────────────────────
function updateTable(communes, communeFilter) {
    var sorted = communes
        .filter(function(c) {
            return c.pv_count > 0 && (!communeFilter || c.nom.toUpperCase() === communeFilter);
        })
        .sort(function(a, b) { return b.pv_count - a.pv_count; });

    var tbody = document.getElementById('tableDetailsBody');
    if (sorted.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">' +
                          '<i class="bi bi-inbox me-2"></i>Aucun PV antiterroriste dans les données</td></tr>';
        return;
    }
    tbody.innerHTML = sorted.map(function(c) {
        var color = getColor(c.pv_count);
        var textC = c.pv_count >= 5 ? '#fff' : '#333';
        return '<tr>' +
            '<td class="fw-semibold">' + c.nom + '</td>' +
            '<td>' + (c.region_nom || '—') + '</td>' +
            '<td>' + (c.dept_nom   || '—') + '</td>' +
            '<td class="text-center"><span class="badge rounded-pill" style="background:' + color + ';color:' + textC + ';font-size:.85rem;padding:5px 10px;">' + c.pv_count + '</span></td>' +
            '<td class="text-center no-print">' +
            '<button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="window.zoomTo(\'' + c.nom.replace(/'/g, "\\'") + '\')" title="Localiser sur la carte">' +
            '<i class="bi bi-geo-alt"></i></button></td></tr>';
    }).join('');
}

function updateTableEmpty() {
    document.getElementById('tableDetailsBody').innerHTML =
        '<tr><td colspan="5" class="text-center text-muted py-3">' +
        '<i class="bi bi-wifi-off me-2"></i>Impossible de charger les données</td></tr>';
}

// ── Boutons ──────────────────────────────────────────────────
document.getElementById('btnApplyFilter').addEventListener('click', loadAPIAndRender);
document.getElementById('btnResetFilter').addEventListener('click', function() {
    document.getElementById('filterCommune').value   = '';
    document.getElementById('filterDateDebut').value = '';
    document.getElementById('filterDateFin').value   = '';
    loadAPIAndRender();
});
document.getElementById('btnResetZoom').addEventListener('click', function() {
    if (geojsonLayer && geojsonLayer.getLayers().length > 0) {
        map.fitBounds(geojsonLayer.getBounds(), { padding: [10, 10], maxZoom: 6.5 });
    } else {
        map.setView([17.0, 8.5], 6);
    }
});

// ── Démarrage ────────────────────────────────────────────────
(function tryInit() {
    if (typeof L !== 'undefined' && typeof Highcharts !== 'undefined') {
        initMap();
    } else {
        setTimeout(tryInit, 200);
    }
})();

})();
</script>
