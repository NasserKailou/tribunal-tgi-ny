<?php $pageTitle = 'Carte Antiterroriste — Niger'; ?>
<style>
/* ── Carte principale ── */
#mapContainer {
    height: 720px;
    width: 100%;
    border: 2px solid #4a5568;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,.1), 0 2px 4px rgba(0,0,0,.06);
    background: #f0f4f8;
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
    box-shadow: 0 4px 6px rgba(0,0,0,.1);
    line-height: 2.1;
    border: 2px solid #e2e8f0;
    font-weight: 500;
    font-size: 12px;
    min-width: 210px;
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
    opacity: .9;
    border: 1px solid rgba(0,0,0,.2);
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

/* Loader overlay */
#mapLoader {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,.75);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 0 8px 8px;
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
            <label class="form-label small fw-semibold mb-1"><i class="bi bi-funnel me-1"></i>Région</label>
            <select id="filterRegion" class="form-select form-select-sm">
                <option value="">Toutes les régions</option>
                <option value="Agadez">Agadez</option>
                <option value="Diffa">Diffa</option>
                <option value="Dosso">Dosso</option>
                <option value="Maradi">Maradi</option>
                <option value="Tahoua">Tahoua</option>
                <option value="Tillabéri">Tillabéri</option>
                <option value="Zinder">Zinder</option>
                <option value="Niamey">Niamey</option>
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
                <i class="bi bi-search me-1"></i>Appliquer
            </button>
            <button id="btnResetFilter" class="btn btn-outline-secondary btn-sm px-3" title="Réinitialiser">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        </div>
    </div>
</div>

<!-- Statistiques rapides -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card danger">
            <div class="stat-number" id="totalPV">—</div>
            <div class="stat-label"><i class="bi bi-file-earmark-text me-1"></i>Total PV Antiterrorisme</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card success">
            <div class="stat-number green" id="totalCommunes">—</div>
            <div class="stat-label"><i class="bi bi-pin-map me-1"></i>Communes concernées</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card warning">
            <div class="stat-number orange" id="totalRegions">—</div>
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

<!-- Alerte top commune -->
<div id="topCommuneBadge" class="alert alert-danger py-2 mb-3 d-none no-print">
    <i class="bi bi-geo-alt-fill me-2"></i><span id="topCommuneText"></span>
</div>

<!-- Carte interactive -->
<div class="mb-4 position-relative">
    <div class="carte-header">
        <span>
            <i class="bi bi-globe-africa me-2"></i>
            <strong>Niger — 266 communes | Carte des PV Antiterrorisme</strong>
        </span>
        <div class="d-flex gap-2">
            <button id="btnResetZoom" class="btn btn-sm btn-light no-print">
                <i class="bi bi-arrows-fullscreen me-1"></i>Zoom Niger
            </button>
            <span class="badge bg-danger align-self-center" id="statTotal"><i class="bi bi-file-earmark-text me-1"></i>0 PV</span>
        </div>
    </div>
    <div id="mapContainer">
        <div id="mapLoader">
            <div class="text-center">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <div class="text-muted small">Chargement de la carte…</div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau détails -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header fw-semibold bg-dark text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Détails par commune</span>
        <span class="badge bg-secondary" id="statCommunes">0 communes</span>
    </div>
    <div class="card-body p-0">
        <div class="table-details-wrapper">
            <table class="table table-bordered table-hover table-sm mb-0 align-middle">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Commune</th>
                        <th>Région</th>
                        <th>Département</th>
                        <th class="text-center">Nb PV</th>
                        <th class="text-center no-print">Action</th>
                    </tr>
                </thead>
                <tbody id="tableDetailsBody">
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            <span class="spinner-border spinner-border-sm me-2"></span>Chargement des données…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Graphiques -->
<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="chart-container">
            <h5 class="fw-semibold mb-3">
                <i class="bi bi-bar-chart-fill me-2 text-danger"></i>Top 10 communes — PV Antiterrorisme
            </h5>
            <div id="chartTopCommunes"></div>
        </div>
    </div>
    <div class="col-md-5">
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

<script>
(function() {
'use strict';

/* ═══════════════════════════════════════════════════════
   CONFIGURATION
═══════════════════════════════════════════════════════ */
var BASE = '<?= BASE_URL ?>';

/* State */
var map             = null;
var geojsonLayer    = null;
var communesGeoJSON = null;   // FeatureCollection from niger_communes.geojson
var communesIndex   = {};     // NOM_COM (MAJUSCULES) → commune info
var pvByCommune     = {};     // NOM_COM (MAJUSCULES) → count (int)
var apiData         = null;
var chartTop        = null;
var chartReg        = null;

/* ═══════════════════════════════════════════════════════
   PALETTE CHOROPLÈTHE
═══════════════════════════════════════════════════════ */
function getColor(n) {
    if (!n || n <= 0) return '#d4e9f7';   // Bleu très clair — 0 PV
    if (n === 1)      return '#fde68a';   // Jaune clair — 1 PV
    if (n <= 3)       return '#fbbf24';   // Jaune — 2-3 PV
    if (n <= 6)       return '#f97316';   // Orange — 4-6 PV
    if (n <= 9)       return '#dc2626';   // Rouge — 7-9 PV
    return '#7f1d1d';                      // Rouge très foncé — 10+
}
function getBorderColor(n) {
    if (!n || n <= 0) return '#5b8db8';  // Bleu visible pour limites communales
    if (n === 1)      return '#d97706';
    if (n <= 3)       return '#b45309';
    if (n <= 6)       return '#c2410c';
    if (n <= 9)       return '#991b1b';
    return '#450a0a';
}
function getWeight(n) {
    // Toujours au moins 1.2 pour que les limites communales soient visibles
    return (n > 0) ? 2.0 : 1.2;
}
function getFillOpacity(n) {
    return (n > 0) ? 0.72 : 0.25;
}

/* ═══════════════════════════════════════════════════════
   INITIALISATION CARTE
═══════════════════════════════════════════════════════ */
function initMap() {
    map = L.map('mapContainer', {
        center: [17.0, 8.5],
        zoom: 6,
        minZoom: 5,
        maxZoom: 16,
        zoomControl: true,
        // Limiter la vue au Niger
        maxBounds: [[10.5, -1.0], [24.0, 16.5]],
        maxBoundsViscosity: 0.85
    });

    // Fond CartoDB Positron (léger, professionnel) — sans labels pour laisser le choroplèthe au premier plan
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        maxZoom: 18,
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> © <a href="https://carto.com/attributions">CARTO</a>'
    }).addTo(map);

    // Couche de labels par-dessus (villes, noms) — zIndex élevé
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        maxZoom: 18,
        zIndex: 650,
        attribution: ''
    }).addTo(map);

    addLegend();

    // Chargement parallèle GeoJSON communes + API données PV
    Promise.all([
        fetch(BASE + '/assets/data/niger_communes.geojson')
            .then(function(r) {
                if (!r.ok) throw new Error('GeoJSON HTTP ' + r.status);
                return r.json();
            }),
        fetchAPIData()
    ]).then(function(results) {
        communesGeoJSON = results[0];
        apiData         = results[1];

        // Construire l'index des communes
        buildCommunesIndex();

        // Construire le mapping PV
        buildPVMapping(apiData);

        // Remplir le filtre commune
        populateFilterCommune();

        // Rendu choroplèthe
        renderChoropleth('', '');

        // Statistiques
        updateStats(apiData);

        // Graphiques
        createCharts(apiData);

        // Tableau
        updateTable(apiData.communes || [], '', '');

        // Cacher loader
        hideLoader();

    }).catch(function(err) {
        console.error('Erreur chargement carte:', err);
        hideLoader();
        // Essayer quand même de charger l'API seule
        if (!communesGeoJSON) {
            fetch(BASE + '/assets/data/niger_communes.geojson')
                .then(function(r) { return r.json(); })
                .then(function(geo) {
                    communesGeoJSON = geo;
                    buildCommunesIndex();
                    renderChoropleth('', '');
                });
        }
    });
}

function hideLoader() {
    var loader = document.getElementById('mapLoader');
    if (loader) loader.style.display = 'none';
}

/* ═══════════════════════════════════════════════════════
   API DONNÉES
═══════════════════════════════════════════════════════ */
function fetchAPIData() {
    var dateDebut = document.getElementById('filterDateDebut').value;
    var dateFin   = document.getElementById('filterDateFin').value;
    var region    = document.getElementById('filterRegion').value;

    var params = new URLSearchParams();
    if (dateDebut) params.append('date_debut', dateDebut);
    if (dateFin)   params.append('date_fin',   dateFin);
    if (region)    params.append('region',     region);

    var url = BASE + '/api/carte-data' + (params.toString() ? '?' + params.toString() : '');
    return fetch(url, { credentials: 'same-origin' }).then(function(r) {
        if (!r.ok) throw new Error('API HTTP ' + r.status);
        return r.json();
    });
}

function buildPVMapping(data) {
    pvByCommune = {};
    (data.communes || []).forEach(function(c) {
        if (c.pv_count > 0) {
            // Store both cases for matching
            pvByCommune[c.nom.toUpperCase()]    = parseInt(c.pv_count, 10);
            pvByCommune[c.nom]                  = parseInt(c.pv_count, 10);
        }
    });
}

function getPVCount(nomCom) {
    // Try exact uppercase match (how NOM_COM is stored in GeoJSON)
    var key = (nomCom || '').toUpperCase();
    if (pvByCommune[key] !== undefined) return pvByCommune[key];
    // Try original
    if (pvByCommune[nomCom] !== undefined) return pvByCommune[nomCom];
    return 0;
}

/* ═══════════════════════════════════════════════════════
   INDEX COMMUNES (pour popup enrichi)
═══════════════════════════════════════════════════════ */
function buildCommunesIndex() {
    if (!communesGeoJSON) return;
    communesGeoJSON.features.forEach(function(feat) {
        var p = feat.properties;
        var key = (p.NOM_COM || '').toUpperCase();
        communesIndex[key] = {
            nom:      p.NOM_COM      || p.commune      || key,
            region:   p.region_off   || p.region       || '',
            dept:     p.dept_off     || p.departement  || '',
            lon_c:    p.lon_c        || 0,
            lat_c:    p.lat_c        || 0
        };
    });
}

/* ═══════════════════════════════════════════════════════
   LÉGENDE
═══════════════════════════════════════════════════════ */
function addLegend() {
    var legend = L.control({ position: 'bottomright' });
    legend.onAdd = function() {
        var d = L.DomUtil.create('div', 'map-legend');
        d.innerHTML = '<h6><i class="bi bi-info-circle me-1"></i><strong>PV Antiterroristes</strong></h6>';
        var items = [
            ['#7f1d1d', '#450a0a', '10 et plus'],
            ['#dc2626', '#b91c1c', '7 à 9'],
            ['#f97316', '#ea580c', '4 à 6'],
            ['#fbbf24', '#d97706', '2 à 3'],
            ['#fde68a', '#f59e0b', '1'],
            ['#d4e9f7', '#93c5fd', '0 (aucun PV)']
        ];
        items.forEach(function(item) {
            d.innerHTML += '<i style="background:' + item[0] + ';border-color:' + item[1] + ';"></i>' +
                           '<span>' + item[2] + '</span><br>';
        });
        return d;
    };
    legend.addTo(map);
}

/* ═══════════════════════════════════════════════════════
   RENDU CHOROPLÈTHE
═══════════════════════════════════════════════════════ */
function renderChoropleth(communeFilter, regionFilter) {
    if (!communesGeoJSON) return;
    if (geojsonLayer) map.removeLayer(geojsonLayer);

    var features = communesGeoJSON.features;

    // Filtre région
    if (regionFilter) {
        var rf = regionFilter.toLowerCase();
        features = features.filter(function(f) {
            var r = (f.properties.region_off || f.properties.region || '').toLowerCase();
            return r === rf || r.indexOf(rf) >= 0;
        });
    }

    // Filtre commune
    if (communeFilter) {
        var cf = communeFilter.toUpperCase();
        features = features.filter(function(f) {
            return (f.properties.NOM_COM || '').toUpperCase() === cf;
        });
    }

    geojsonLayer = L.geoJSON(
        { type: 'FeatureCollection', features: features },
        {
            style: function(f) {
                var nom   = f.properties.NOM_COM || f.properties.commune || '';
                var count = getPVCount(nom);
                return {
                    fillColor:   getColor(count),
                    weight:      getWeight(count),
                    opacity:     1,
                    color:       getBorderColor(count),
                    fillOpacity: getFillOpacity(count),
                    dashArray:   count > 0 ? null : null  // limites continues pour toutes communes
                };
            },
            onEachFeature: function(feature, layer) {
                var nom    = feature.properties.NOM_COM || feature.properties.commune || 'Inconnue';
                var count  = getPVCount(nom);
                var info   = communesIndex[nom.toUpperCase()] || {};
                var region = info.region || feature.properties.region_off || feature.properties.region || '—';
                var dept   = info.dept   || feature.properties.dept_off   || feature.properties.departement || '—';
                var total  = (apiData && apiData.total_pv_anti) ? apiData.total_pv_anti : 0;
                var pct    = (total > 0 && count > 0) ? ((count / total) * 100).toFixed(1) + '%' : '';

                // Couleur selon niveau
                var colorNum = count > 0 ? getColor(count) : '#198754';
                var textNum  = count > 0
                    ? '<span style="color:' + colorNum + ';font-size:1.15rem;font-weight:800">' + count + ' PV</span>' +
                      (pct ? ' <small class="text-muted">(' + pct + ')</small>' : '')
                    : '<span style="color:#198754;font-weight:600">Aucun PV antiterroriste</span>';

                var html = '<div style="min-width:220px;font-family:inherit">' +
                    '<div style="background:#1a3c5e;color:#fff;padding:6px 10px;margin:-1px -1px 8px;border-radius:4px 4px 0 0">' +
                    '<strong style="font-size:.95rem">' + nom + '</strong></div>' +
                    '<div style="padding:0 4px">' +
                    '<div style="font-size:12px;color:#555;margin-bottom:6px">' +
                    '<i class="bi bi-geo-alt-fill text-secondary me-1"></i>' +
                    '<strong>Région :</strong> ' + region + '<br>' +
                    '<i class="bi bi-building text-secondary me-1"></i>' +
                    '<strong>Département :</strong> ' + dept + '</div>' +
                    '<div style="background:#f8f9fa;border-radius:4px;padding:6px 8px;text-align:center">' +
                    textNum +
                    '</div>' +
                    (count > 0 ? '<div style="margin-top:6px;text-align:center">' +
                    '<button onclick="window.zoomTo(\'' + nom.replace(/'/g, "\\'") + '\')" ' +
                    'style="font-size:11px;padding:2px 8px;border:1px solid #0d6efd;border-radius:3px;background:#fff;color:#0d6efd;cursor:pointer">' +
                    '📍 Voir dans le tableau</button></div>' : '') +
                    '</div></div>';

                layer.bindPopup(html, { maxWidth: 260 });

                layer.on({
                    mouseover: function(e) {
                        e.target.setStyle({
                            weight:      count > 0 ? 3.5 : 2.0,
                            color:       '#1a202c',
                            fillOpacity: count > 0 ? 0.92 : 0.55
                        });
                        e.target.bringToFront();
                    },
                    mouseout: function(e) {
                        if (geojsonLayer) geojsonLayer.resetStyle(e.target);
                    },
                    click: function(e) {
                        map.fitBounds(e.target.getBounds(), { padding: [40, 40], maxZoom: 11 });
                    }
                });
            }
        }
    ).addTo(map);

    if (geojsonLayer.getLayers().length > 0) {
        var maxZoom = communeFilter ? 11 : (regionFilter ? 9 : 7);
        // Utiliser les vraies limites du GeoJSON pour cadrer correctement le Niger
        try {
            var bounds = geojsonLayer.getBounds();
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [20, 20], maxZoom: maxZoom, animate: true });
            } else {
                map.setView([17.0, 8.5], 6);
            }
        } catch(e) {
            map.setView([17.0, 8.5], 6);
        }
    }
}

/* Zoom depuis tableau */
window.zoomTo = function(nom) {
    if (!geojsonLayer) return;
    geojsonLayer.eachLayer(function(l) {
        if (l.feature) {
            var fn = (l.feature.properties.NOM_COM || '').toUpperCase();
            if (fn === nom.toUpperCase()) {
                map.fitBounds(l.getBounds(), { padding: [50, 50], maxZoom: 11 });
                l.openPopup();
            }
        }
    });
};

/* ═══════════════════════════════════════════════════════
   FILTRE COMMUNE
═══════════════════════════════════════════════════════ */
function populateFilterCommune() {
    var sel = document.getElementById('filterCommune');
    if (!communesGeoJSON) return;

    var names = [];
    communesGeoJSON.features.forEach(function(f) {
        var n = f.properties.NOM_COM || f.properties.commune;
        if (n) names.push({
            nom:    n,
            region: f.properties.region_off || f.properties.region || ''
        });
    });
    names.sort(function(a, b) { return a.nom.localeCompare(b.nom); });
    names.forEach(function(c) {
        var o = document.createElement('option');
        o.value = c.nom;
        o.textContent = c.nom + (c.region ? ' (' + c.region + ')' : '');
        sel.appendChild(o);
    });
}

/* ═══════════════════════════════════════════════════════
   STATISTIQUES
═══════════════════════════════════════════════════════ */
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
    document.getElementById('communeMax').textContent    = maxCount > 0
        ? maxNom + ' (' + maxCount + ')'
        : '—';
    document.getElementById('statTotal').innerHTML =
        '<i class="bi bi-file-earmark-text me-1"></i>' + total + ' PV antiterrorisme';
    document.getElementById('statCommunes').innerHTML =
        actives + ' commune' + (actives !== 1 ? 's' : '');

    var badge = document.getElementById('topCommuneBadge');
    var txt   = document.getElementById('topCommuneText');
    if (maxCount > 0) {
        txt.textContent = 'Commune la plus touchée : ' + maxNom + ' — ' + maxCount + ' PV antiterroriste(s)';
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

/* ═══════════════════════════════════════════════════════
   GRAPHIQUES HIGHCHARTS
═══════════════════════════════════════════════════════ */
function createCharts(data) {
    var communes = (data.communes || [])
        .filter(function(c) { return c.pv_count > 0; })
        .sort(function(a, b) { return b.pv_count - a.pv_count; })
        .slice(0, 10);

    if (chartTop) { try { chartTop.destroy(); } catch(e){} }
    if (communes.length > 0) {
        chartTop = Highcharts.chart('chartTopCommunes', {
            chart: { type: 'bar', height: 360, backgroundColor: 'transparent' },
            title: { text: null },
            xAxis: {
                categories: communes.map(function(c) { return c.nom; }),
                labels: { style: { fontSize: '11px', fontWeight: '600' } }
            },
            yAxis: { min: 0, title: { text: 'Nombre de PV' }, allowDecimals: false,
                     gridLineColor: '#f0f0f0' },
            legend: { enabled: false },
            credits: { enabled: false },
            tooltip: {
                pointFormat: '<b>{point.y}</b> PV antiterroriste(s)<br>' +
                             '<small>({point.percentage:.1f}% du total)</small>',
                percentageDecimals: 1
            },
            plotOptions: {
                bar: {
                    dataLabels: { enabled: true, format: '{point.y}', style: { fontSize: '10px' } },
                    colorByPoint: true,
                    colors: communes.map(function(c) { return getColor(c.pv_count); }),
                    borderRadius: 3
                }
            },
            series: [{
                name: 'PV',
                data: communes.map(function(c) { return c.pv_count; })
            }],
            exporting: { enabled: true }
        });
    } else {
        document.getElementById('chartTopCommunes').innerHTML =
            '<div class="text-muted text-center py-5">' +
            '<i class="bi bi-inbox fs-2 d-block mb-2"></i>' +
            'Aucun PV antiterroriste enregistré</div>';
    }

    // Répartition par région
    var pvRegion = data.pvByRegion || {};
    var regData  = Object.keys(pvRegion)
        .filter(function(k) { return pvRegion[k] > 0; })
        .map(function(k) { return { name: k, y: pvRegion[k] }; });

    if (chartReg) { try { chartReg.destroy(); } catch(e){} }
    if (regData.length > 0) {
        var colors8 = ['#b91c1c','#dc2626','#f97316','#fbbf24','#1a3c5e','#2563a8','#059669','#7c3aed'];
        chartReg = Highcharts.chart('chartRegions', {
            chart: { type: 'pie', height: 360, backgroundColor: 'transparent' },
            title: { text: null },
            credits: { enabled: false },
            tooltip: { pointFormat: '<b>{point.y}</b> PV ({point.percentage:.1f}%)' },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    colors: colors8,
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.y}',
                        style: { fontSize: '11px' }
                    },
                    showInLegend: true,
                    innerSize: '35%'
                }
            },
            series: [{ name: 'PV', colorByPoint: true, data: regData }],
            exporting: { enabled: true }
        });
    } else {
        document.getElementById('chartRegions').innerHTML =
            '<div class="text-muted text-center py-5">' +
            '<i class="bi bi-pie-chart fs-2 d-block mb-2"></i>' +
            'Aucune donnée régionale</div>';
    }
}

/* ═══════════════════════════════════════════════════════
   TABLEAU
═══════════════════════════════════════════════════════ */
function updateTable(communes, communeFilter, regionFilter) {
    var sorted = (communes || [])
        .filter(function(c) {
            if (c.pv_count <= 0) return false;
            if (communeFilter && c.nom.toUpperCase() !== communeFilter.toUpperCase()) return false;
            if (regionFilter && (c.region_nom || '').toLowerCase().indexOf(regionFilter.toLowerCase()) < 0) return false;
            return true;
        })
        .sort(function(a, b) { return b.pv_count - a.pv_count; });

    var tbody = document.getElementById('tableDetailsBody');
    if (sorted.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">' +
                          '<i class="bi bi-inbox me-2"></i>Aucun PV antiterroriste dans la sélection</td></tr>';
        return;
    }
    tbody.innerHTML = sorted.map(function(c, i) {
        var color = getColor(c.pv_count);
        var textC = c.pv_count >= 7 ? '#fff' : '#333';
        return '<tr>' +
            '<td class="text-muted small">' + (i + 1) + '</td>' +
            '<td class="fw-semibold">' + (c.nom || '—') + '</td>' +
            '<td>' + (c.region_nom || '—') + '</td>' +
            '<td>' + (c.dept_nom   || '—') + '</td>' +
            '<td class="text-center">' +
            '<span class="badge rounded-pill" style="background:' + color + ';color:' + textC + ';font-size:.85rem;padding:5px 10px;">' +
            c.pv_count + '</span></td>' +
            '<td class="text-center no-print">' +
            '<button class="btn btn-sm btn-outline-primary py-0 px-2" ' +
            'onclick="window.zoomTo(\'' + (c.nom || '').replace(/'/g, "\\'") + '\')" ' +
            'title="Localiser sur la carte"><i class="bi bi-geo-alt"></i></button></td></tr>';
    }).join('');
}

/* ═══════════════════════════════════════════════════════
   RECHARGEMENT AVEC FILTRES
═══════════════════════════════════════════════════════ */
function reloadWithFilters() {
    document.getElementById('mapLoader').style.display = 'flex';

    var communeFilter = document.getElementById('filterCommune').value;
    var regionFilter  = document.getElementById('filterRegion').value;

    fetchAPIData().then(function(data) {
        apiData = data;
        buildPVMapping(data);
        renderChoropleth(communeFilter, regionFilter);
        updateStats(data);
        createCharts(data);
        updateTable(data.communes || [], communeFilter, regionFilter);
        hideLoader();
    }).catch(function(err) {
        console.error('Erreur rechargement:', err);
        hideLoader();
        if (communesGeoJSON) renderChoropleth(communeFilter, regionFilter);
    });
}

/* ═══════════════════════════════════════════════════════
   BOUTONS
═══════════════════════════════════════════════════════ */
document.getElementById('btnApplyFilter').addEventListener('click', reloadWithFilters);

document.getElementById('btnResetFilter').addEventListener('click', function() {
    document.getElementById('filterCommune').value   = '';
    document.getElementById('filterRegion').value    = '';
    document.getElementById('filterDateDebut').value = '';
    document.getElementById('filterDateFin').value   = '';
    reloadWithFilters();
});

document.getElementById('btnResetZoom').addEventListener('click', function() {
    if (geojsonLayer && geojsonLayer.getLayers().length > 0) {
        map.fitBounds(geojsonLayer.getBounds(), { padding: [15, 15], maxZoom: 6.5 });
    } else {
        map.setView([17.0, 8.5], 6);
    }
    // Re-render all communes
    var communeFilter = document.getElementById('filterCommune').value;
    var regionFilter  = document.getElementById('filterRegion').value;
    renderChoropleth(communeFilter, regionFilter);
});

/* ═══════════════════════════════════════════════════════
   DÉMARRAGE — Attendre Leaflet + Highcharts
═══════════════════════════════════════════════════════ */
(function tryInit() {
    if (typeof L !== 'undefined' && typeof Highcharts !== 'undefined') {
        initMap();
    } else {
        setTimeout(tryInit, 150);
    }
})();

})();
</script>
