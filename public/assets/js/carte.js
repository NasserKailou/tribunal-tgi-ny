// carte.js — Leaflet choropleth map (GeoJSON-based)
// TGI-NY v2.0 — Carte choroplèthe des 266 communes du Niger

let map, communeLayer, pvCounts = {}, pvByRegion = {};
let allGeoData = null;
let currentRegionFilter = '';

// ─── Palette de couleurs choroplèthe ───────────────────────────────────────────
function getPVColor(count) {
    if (count === 0)   return '#ffffcc';
    if (count <= 2)    return '#ffeda0';
    if (count <= 5)    return '#fed976';
    if (count <= 10)   return '#feb24c';
    if (count <= 20)   return '#fd8d3c';
    if (count <= 50)   return '#f03b20';
    return '#bd0026';
}

function choroplethStyle(feature) {
    const count = pvCounts[feature.properties.commune] || 0;
    return {
        fillColor:   getPVColor(count),
        weight:      1,
        opacity:     1,
        color:       '#666',
        fillOpacity: count > 0 ? 0.85 : 0.5,
    };
}

// ─── Initialisation de la carte ───────────────────────────────────────────────
function initCarte() {
    map = L.map('map', { preferCanvas: false }).setView([17.0, 8.0], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 16,
    }).addTo(map);

    // Légende
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'legend leaflet-control');
        div.innerHTML = `
            <strong style="font-size:0.85rem;display:block;margin-bottom:6px">
                <i>PV antiterroristes / commune</i>
            </strong>
            <div class="legend-item"><span class="legend-box" style="background:#ffffcc;border:1px solid #ccc"></span> 0 PV</div>
            <div class="legend-item"><span class="legend-box" style="background:#ffeda0"></span> 1–2 PV</div>
            <div class="legend-item"><span class="legend-box" style="background:#fed976"></span> 3–5 PV</div>
            <div class="legend-item"><span class="legend-box" style="background:#feb24c"></span> 6–10 PV</div>
            <div class="legend-item"><span class="legend-box" style="background:#fd8d3c"></span> 11–20 PV</div>
            <div class="legend-item"><span class="legend-box" style="background:#f03b20"></span> 21–50 PV</div>
            <div class="legend-item"><span class="legend-box" style="background:#bd0026"></span> 51+ PV</div>
        `;
        return div;
    };
    legend.addTo(map);
}

// ─── Rendu GeoJSON choroplèthe ────────────────────────────────────────────────
function renderChoropleth(geojsonData) {
    if (communeLayer) {
        map.removeLayer(communeLayer);
    }

    let data = geojsonData;

    // Filtre par région
    if (currentRegionFilter) {
        data = {
            type: 'FeatureCollection',
            features: geojsonData.features.filter(
                f => f.properties.region === currentRegionFilter
            )
        };
    }

    communeLayer = L.geoJSON(data, {
        style: choroplethStyle,
        onEachFeature: function (feature, layer) {
            const props  = feature.properties;
            const count  = pvCounts[props.commune] || 0;
            const pct    = window._totalPV > 0 ? ((count / window._totalPV) * 100).toFixed(1) : '0.0';

            layer.bindPopup(
                `<div style="min-width:200px">
                    <h6 class="mb-1">
                        <i class="bi bi-geo-alt"></i>
                        <strong>${props.commune}</strong>
                    </h6>
                    <div class="text-muted small">${props.departement} — ${props.region}</div>
                    <hr style="margin:6px 0">
                    <div>
                        <strong style="color:${count > 0 ? '#dc3545' : '#555'};font-size:1.1rem">${count}</strong>
                        PV antiterroriste${count !== 1 ? 's' : ''}
                        ${count > 0 ? `<span class="text-muted small">(${pct}%)</span>` : ''}
                    </div>
                </div>`
            );

            layer.on('mouseover', function (e) {
                e.target.setStyle({ weight: 3, color: '#333', fillOpacity: 0.95 });
                e.target.bringToFront();
            });
            layer.on('mouseout', function (e) {
                communeLayer.resetStyle(e.target);
            });
        }
    }).addTo(map);

    // Zoom automatique si filtre région
    if (currentRegionFilter && data.features.length > 0) {
        try {
            map.fitBounds(communeLayer.getBounds(), { padding: [20, 20] });
        } catch (e) { /* ignore */ }
    }
}

// ─── Chargement des données PV depuis l'API ───────────────────────────────────
function loadMapData() {
    const dateDebut = document.getElementById('dateDebut').value;
    const dateFin   = document.getElementById('dateFin').value;
    const region    = document.getElementById('regionFilter') ? document.getElementById('regionFilter').value : '';
    currentRegionFilter = region;

    let url = (window.BASE_URL || '') + '/api/carte-data';
    const params = new URLSearchParams();
    if (dateDebut) params.append('date_debut', dateDebut);
    if (dateFin)   params.append('date_fin',   dateFin);
    if (params.toString()) url += '?' + params.toString();

    fetch(url)
        .then(r => r.json())
        .then(apiData => {
            // Reconstruire pvCounts indexé par nom de commune
            pvCounts    = {};
            pvByRegion  = {};
            window._totalPV = apiData.total_pv_anti || 0;

            (apiData.communes || []).forEach(function (c) {
                pvCounts[c.nom] = c.pv_count || 0;
                if (c.region_nom) {
                    pvByRegion[c.region_nom] = (pvByRegion[c.region_nom] || 0) + (c.pv_count || 0);
                }
            });

            // pvByRegion depuis apiData si disponible
            if (apiData.pvByRegion) {
                pvByRegion = apiData.pvByRegion;
            }

            // Badges
            const totalBadge    = document.getElementById('totalPVBadge');
            const communesBadge = document.getElementById('communesBadge');
            if (totalBadge)    totalBadge.textContent    = `${apiData.total_pv_anti} PV antiterroristes`;
            if (communesBadge) communesBadge.textContent = `${apiData.communes_actives} communes actives`;

            // Commune la plus touchée
            updateTopCommune(apiData.communes || []);

            // Tableau récapitulatif + graphique
            updateTable(apiData.communes || []);
            updateChart(pvByRegion);

            // Charger GeoJSON si pas encore en cache
            if (allGeoData) {
                renderChoropleth(allGeoData);
            } else {
                fetch((window.BASE_URL || '') + '/assets/data/niger_communes.geojson')
                    .then(r => r.json())
                    .then(geoData => {
                        allGeoData = geoData;
                        renderChoropleth(allGeoData);
                    })
                    .catch(err => console.error('Erreur chargement GeoJSON:', err));
            }
        })
        .catch(err => {
            console.error('Erreur API carte:', err);
            // Mode dégradé : charger uniquement le GeoJSON sans données PV
            if (!allGeoData) {
                fetch((window.BASE_URL || '') + '/assets/data/niger_communes.geojson')
                    .then(r => r.json())
                    .then(geoData => {
                        allGeoData = geoData;
                        renderChoropleth(allGeoData);
                    });
            } else {
                renderChoropleth(allGeoData);
            }
        });
}

// ─── Badge commune la plus touchée ───────────────────────────────────────────
function updateTopCommune(communes) {
    const badge = document.getElementById('topCommuneBadge');
    if (!badge) return;
    const sorted = (communes || []).filter(c => c.pv_count > 0).sort((a, b) => b.pv_count - a.pv_count);
    if (sorted.length > 0) {
        badge.style.display = '';
        badge.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i>
            Commune la plus touchée : <strong>${sorted[0].nom}</strong>
            — ${sorted[0].pv_count} PV (${sorted[0].region_nom || ''})`;
    } else {
        badge.style.display = 'none';
    }
}

// ─── Tableau récapitulatif ────────────────────────────────────────────────────
function updateTable(communes) {
    const tbody = document.getElementById('tableRecap');
    if (!tbody) return;
    const total = window._totalPV || 0;
    const sorted = (communes || []).slice().sort((a, b) => b.pv_count - a.pv_count);
    tbody.innerHTML = sorted.map(function (c) {
        const pct = total > 0 ? ((c.pv_count / total) * 100).toFixed(1) : '0.0';
        const cls = c.pv_count >= 11 ? 'table-danger' : c.pv_count >= 3 ? 'table-warning' : '';
        return `<tr class="${cls}">
            <td><strong>${c.nom}</strong></td>
            <td>${c.region_nom || '—'}</td>
            <td class="text-center">
                <span class="badge" style="background:${getPVColor(c.pv_count)};color:${c.pv_count>5?'#fff':'#333'}">${c.pv_count}</span>
            </td>
            <td class="text-center">${pct}%</td>
        </tr>`;
    }).join('');
}

// ─── Graphique doughnut Chart.js ─────────────────────────────────────────────
let donutChart = null;
function updateChart(regionData) {
    const canvas = document.getElementById('donutRegion');
    if (!canvas) return;

    const labels = Object.keys(regionData).filter(k => regionData[k] > 0);
    const values = labels.map(k => regionData[k]);

    const palette = ['#bd0026','#f03b20','#fd8d3c','#feb24c','#fed976','#ffeda0','#ffffcc','#d4e6b5'];

    if (donutChart) {
        donutChart.destroy();
    }

    if (labels.length === 0) {
        canvas.parentElement.innerHTML = '<p class="text-muted text-center py-3">Aucune donnée disponible</p>';
        return;
    }

    donutChart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: palette.slice(0, labels.length),
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'right', labels: { font: { size: 12 } } },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct   = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} PV (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
}

// ─── Filtre région sur la carte ───────────────────────────────────────────────
function applyRegionFilter() {
    const sel = document.getElementById('regionFilter');
    currentRegionFilter = sel ? sel.value : '';
    if (allGeoData) {
        renderChoropleth(allGeoData);
        if (!currentRegionFilter) {
            map.setView([17.0, 8.0], 5);
        }
    }
}
