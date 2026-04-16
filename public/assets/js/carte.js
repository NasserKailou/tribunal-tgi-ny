// carte.js — TGI-NY v4.0 — Inspiré code exemple Scala/Play (CartoDB + fitBounds Niger)
'use strict';

var map, geojsonLayer, communesGeoJSON = null;
var pvCounts = {}, pvByRegion = {};
var currentRegionFilter = '';
var chartInstance = null;
var nigerBounds = [[10.5, 0.0], [24.0, 16.5]]; // limites Niger

// ── Palette choroplèthe (identique au code exemple) ──────────────────────────
function getPVColor(n) {
    if (n <= 0)  return '#e8f4f8';   // aucun PV — bleu très clair
    if (n === 1) return '#fbbf24';   // 1     — jaune
    if (n <= 4)  return '#f59e0b';   // 2-4   — orange/ambre
    if (n <= 9)  return '#dc2626';   // 5-9   — rouge moyen
    return '#b91c1c';                // 10+   — rouge foncé
}

function choroplethStyle(feature) {
    var nom   = feature.properties.NOM_COM || feature.properties.commune || '';
    var count = pvCounts[nom] || pvCounts[nom.toLowerCase()] || 0;
    return {
        fillColor:   getPVColor(count),
        weight:      count > 0 ? 2.0 : 1.5,
        opacity:     0.9,
        color:       count > 0 ? (count >= 5 ? '#991b1b' : '#d97706') : '#718096',
        fillOpacity: count > 0 ? (count >= 5 ? 0.85 : 0.65) : 0.40,
    };
}

// ── Init carte ────────────────────────────────────────────────────────────────
function initCarte() {
    map = L.map('map', {
        center: [17.5, 8.5],
        zoom:   6,
        minZoom: 5,
        maxZoom: 18,
        maxBounds: [[8.0, -2.0], [24.5, 17.5]],
        maxBoundsViscosity: 0.85,
        zoomControl: true,
        preferCanvas: false
    });

    // Fond CartoDB Positron (plus professionnel que OSM standard)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors © <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 18
    }).addTo(map);

    addLegend();
}

// ── Légende ───────────────────────────────────────────────────────────────────
function addLegend() {
    var legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        var div = L.DomUtil.create('div', 'map-legend');
        div.innerHTML = '<h6><strong>PV Antiterroristes</strong></h6>';
        [
            ['#b91c1c', '10 et plus'],
            ['#dc2626', '5 à 9'],
            ['#f59e0b', '2 à 4'],
            ['#fbbf24', '1'],
            ['#e8f4f8', '0'],
        ].forEach(function(item) {
            div.innerHTML += '<i style="background:' + item[0] + ';border-color:#718096;"></i> ' + item[1] + '<br>';
        });
        return div;
    };
    legend.addTo(map);
}

// ── Rendu choroplèthe ─────────────────────────────────────────────────────────
function renderChoropleth(geojsonData) {
    if (geojsonLayer) { map.removeLayer(geojsonLayer); }

    var data = geojsonData;
    if (currentRegionFilter) {
        var rf = currentRegionFilter.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
        data = {
            type: 'FeatureCollection',
            features: geojsonData.features.filter(function(f) {
                var r = (f.properties.region_off || f.properties.region || '').toLowerCase()
                         .normalize('NFD').replace(/[\u0300-\u036f]/g,'');
                return r.includes(rf);
            })
        };
    }

    geojsonLayer = L.geoJSON(data, {
        style: choroplethStyle,
        onEachFeature: onEachFeature
    }).addTo(map);

    // Zoom sur le Niger (ou la sélection filtrée)
    if (geojsonLayer.getLayers().length > 0) {
        map.fitBounds(geojsonLayer.getBounds(), {
            padding: [10, 10],
            maxZoom: currentRegionFilter ? 8 : 6.5
        });
    }
}

function onEachFeature(feature, layer) {
    var nom   = feature.properties.NOM_COM || feature.properties.commune || 'Inconnue';
    var count = pvCounts[nom] || 0;
    var total = window._totalPV || 0;
    var pct   = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
    var region= feature.properties.region_off || feature.properties.region || '';
    var dept  = feature.properties.dept_off || feature.properties.departement || '';

    var popup = '<div style="min-width:200px">'
        + '<h6 style="font-weight:700;color:#1a3c5e;margin-bottom:4px">' + nom + '</h6>'
        + '<div style="font-size:12px;color:#555"><i>Région :</i> ' + region + '<br>'
        + '<i>Département :</i> ' + dept + '</div>'
        + '<hr style="margin:6px 0">'
        + '<div style="font-size:13px">PV antiterroristes : '
        + '<strong style="color:' + (count > 0 ? '#b91c1c' : '#198754') + '">' + count + '</strong>'
        + (count > 0 ? '<br><small>(' + pct + '% du total)</small>' : '')
        + '</div>'
        + (count > 0 ? '<div style="margin-top:6px"><a href="#tableRecap" onclick="zoomToCommune(\'' + nom.replace(/'/g,"\\'") + '\')" style="font-size:11px">Voir dans le tableau</a></div>' : '')
        + '</div>';
    layer.bindPopup(popup);

    layer.on({
        mouseover: function(e) {
            e.target.setStyle({ weight: 3.5, color: '#1a202c', fillOpacity: 0.95 });
            e.target.bringToFront();
        },
        mouseout: function(e) { geojsonLayer.resetStyle(e.target); },
        click: function(e) {
            map.fitBounds(e.target.getBounds(), { padding: [50, 50], maxZoom: 10 });
        }
    });
}

// Zoom sur une commune par nom
function zoomToCommune(communeName) {
    if (!geojsonLayer) return;
    geojsonLayer.eachLayer(function(layer) {
        if (layer.feature) {
            var nom = layer.feature.properties.NOM_COM || layer.feature.properties.commune || '';
            if (nom === communeName) {
                map.fitBounds(layer.getBounds(), { padding: [50,50], maxZoom: 10 });
                layer.openPopup();
            }
        }
    });
}
window.zoomToCommune = zoomToCommune;

// Réinitialiser le zoom Niger
function resetMapZoom() {
    if (geojsonLayer && geojsonLayer.getLayers().length > 0) {
        map.fitBounds(geojsonLayer.getBounds(), { padding: [10, 10], maxZoom: 6.5 });
    } else {
        map.setView([17.5, 8.5], 6);
    }
}
window.resetMapZoom = resetMapZoom;

// ── Chargement des données API ────────────────────────────────────────────────
function loadMapData() {
    var dateDebut = (document.getElementById('dateDebut') || {}).value || '';
    var dateFin   = (document.getElementById('dateFin')   || {}).value || '';
    var region    = (document.getElementById('regionFilter') || {}).value || '';
    currentRegionFilter = region;

    var params = new URLSearchParams();
    if (dateDebut) params.append('date_debut', dateDebut);
    if (dateFin)   params.append('date_fin',   dateFin);
    if (region)    params.append('region',     region);

    var url = (window.BASE_URL || '') + '/api/carte-data'
            + (params.toString() ? '?' + params.toString() : '');

    // Spinner
    var spinner = document.getElementById('totalPVBadge');
    if (spinner) spinner.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Chargement…';

    fetch(url, { credentials: 'same-origin' })
        .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function(apiData) {
            pvCounts    = {};
            pvByRegion  = {};
            window._totalPV = apiData.total_pv_anti || 0;

            (apiData.communes || []).forEach(function(c) {
                if (c.pv_count > 0) {
                    pvCounts[c.nom] = c.pv_count;
                    // aussi stocker en MAJUSCULES pour la correspondance NOM_COM
                    pvCounts[c.nom.toUpperCase()] = c.pv_count;
                }
            });
            pvByRegion = apiData.pvByRegion || {};

            // Badges stats
            var el = function(id) { return document.getElementById(id); };
            if (el('totalPVBadge'))  el('totalPVBadge').innerHTML  = '<i class="bi bi-file-earmark-text me-1"></i>' + apiData.total_pv_anti + ' PV antiterroristes';
            if (el('communesBadge')) el('communesBadge').innerHTML = '<i class="bi bi-pin-map me-1"></i>' + apiData.communes_actives + ' communes actives';

            updateTopCommune(apiData.communes || []);
            updateTable(apiData.communes || []);
            updateChart(pvByRegion);

            // Charger le GeoJSON et rendre la carte
            if (communesGeoJSON) {
                renderChoropleth(communesGeoJSON);
            } else {
                fetch((window.BASE_URL || '') + '/assets/data/niger_communes.geojson')
                    .then(function(r) { return r.json(); })
                    .then(function(geoData) { communesGeoJSON = geoData; renderChoropleth(communesGeoJSON); })
                    .catch(function(err) { console.error('GeoJSON error:', err); });
            }
        })
        .catch(function(err) {
            console.error('API carte error:', err);
            if (el && el('totalPVBadge')) el('totalPVBadge').innerHTML = '⚠️ Erreur chargement';
            if (!communesGeoJSON) {
                fetch((window.BASE_URL || '') + '/assets/data/niger_communes.geojson')
                    .then(function(r) { return r.json(); })
                    .then(function(geoData) { communesGeoJSON = geoData; renderChoropleth(communesGeoJSON); });
            } else {
                renderChoropleth(communesGeoJSON);
            }
        });
}

function applyRegionFilter() { loadMapData(); }

// ── Badge top commune ─────────────────────────────────────────────────────────
function updateTopCommune(communes) {
    var badge = document.getElementById('topCommuneBadge');
    if (!badge) return;
    var actives = (communes || []).filter(function(c) { return c.pv_count > 0; })
                                  .sort(function(a,b) { return b.pv_count - a.pv_count; });
    if (actives.length > 0) {
        var top = actives[0];
        badge.style.removeProperty('display');
        badge.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i>Commune la plus touchée : <strong>'
            + top.nom + '</strong> (' + top.pv_count + ' PV — ' + (top.region_nom || '') + ')';
    } else {
        badge.style.display = 'none';
    }
}

// ── Tableau récapitulatif ─────────────────────────────────────────────────────
function updateTable(communes) {
    var tbody = document.getElementById('tableRecap');
    if (!tbody) return;
    var total  = window._totalPV || 0;
    var sorted = (communes || []).filter(function(c) { return c.pv_count > 0; })
                                  .sort(function(a,b) { return b.pv_count - a.pv_count; });
    if (sorted.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Aucun PV antiterroriste enregistré</td></tr>';
        return;
    }
    tbody.innerHTML = sorted.map(function(c, i) {
        return '<tr>'
            + '<td>' + (i+1) + '</td>'
            + '<td class="fw-semibold"><button class="btn btn-link p-0 fw-semibold" onclick="zoomToCommune(\'' + c.nom.replace(/'/g,"\\'") + '\')">' + c.nom + '</button></td>'
            + '<td>' + (c.dept_nom || '—') + '</td>'
            + '<td>' + (c.region_nom || '—') + '</td>'
            + '<td><span class="badge" style="background:' + getPVColor(c.pv_count) + ';color:' + (c.pv_count >= 5 ? '#fff' : '#333') + '">'
            + c.pv_count + '</span> <small class="text-muted">('
            + (total > 0 ? ((c.pv_count/total)*100).toFixed(1) : 0) + '%)</small></td>'
            + '</tr>';
    }).join('');
}

// ── Graphique Chart.js doughnut ───────────────────────────────────────────────
function updateChart(pvByRegionData) {
    var canvas = document.getElementById('donutRegion');
    if (!canvas) return;
    var labels = Object.keys(pvByRegionData).filter(function(k) { return pvByRegionData[k] > 0; });
    var values = labels.map(function(k) { return pvByRegionData[k]; });
    if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
    if (labels.length === 0) {
        canvas.parentElement.innerHTML = '<p class="text-center text-muted py-4"><i class="bi bi-pie-chart fs-2 d-block mb-2"></i>Aucune donnée</p>';
        return;
    }
    var colors = ['#b91c1c','#dc2626','#f59e0b','#fbbf24','#1a3c5e','#4e79a7','#59a14f','#f28e2b'];
    chartInstance = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: values, backgroundColor: colors.slice(0, labels.length), borderWidth: 2 }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 8 } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce(function(a,b){return a+b;},0);
                            return ' ' + ctx.label + ': ' + ctx.raw + ' PV (' + ((ctx.raw/total)*100).toFixed(1) + '%)';
                        }
                    }
                }
            }
        }
    });
}
