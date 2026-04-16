// carte.js — TGI-NY v3.0 — Choroplèthe GeoJSON 266 communes du Niger

let map, communeLayer, pvCounts = {}, pvByRegion = {};
let allGeoData = null;
let currentRegionFilter = '';

// ── Couleurs choroplèthe ──────────────────────────────────────────────────────
function getPVColor(n) {
    if (n === 0)   return '#f7f7f7';
    if (n <= 2)    return '#ffeda0';
    if (n <= 5)    return '#fed976';
    if (n <= 10)   return '#feb24c';
    if (n <= 20)   return '#fd8d3c';
    if (n <= 50)   return '#f03b20';
    return '#bd0026';
}

function choroplethStyle(feature) {
    const count = pvCounts[feature.properties.commune] || 0;
    return {
        fillColor:   getPVColor(count),
        weight:      count > 0 ? 2 : 0.5,
        opacity:     1,
        color:       count > 0 ? '#333' : '#bbb',
        fillOpacity: count > 0 ? 0.85 : 0.45,
    };
}

// ── Init carte ────────────────────────────────────────────────────────────────
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
        div.style.cssText = 'background:#fff;padding:10px 14px;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,0.2);font-size:12px;';
        div.innerHTML = `
            <div style="font-weight:700;margin-bottom:6px;color:#1a3c5e">PV antiterroristes</div>
            ${[
                ['#f7f7f7','0 PV'],['#ffeda0','1–2'],['#fed976','3–5'],
                ['#feb24c','6–10'],['#fd8d3c','11–20'],['#f03b20','21–50'],['#bd0026','51+']
            ].map(([c,l]) =>
                `<div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                    <span style="width:16px;height:16px;border-radius:2px;background:${c};border:1px solid #ccc;display:inline-block"></span>
                    <span>${l}</span>
                </div>`
            ).join('')}
        `;
        return div;
    };
    legend.addTo(map);
}

// ── Rendu choroplèthe ─────────────────────────────────────────────────────────
function renderChoropleth(geojsonData) {
    if (communeLayer) { map.removeLayer(communeLayer); }

    let data = geojsonData;
    if (currentRegionFilter) {
        data = {
            type: 'FeatureCollection',
            features: geojsonData.features.filter(
                f => f.properties.region.toLowerCase().replace(/é/g,'e').replace(/è/g,'e').replace(/ê/g,'e')
                     === currentRegionFilter.toLowerCase().replace(/é/g,'e').replace(/è/g,'e').replace(/ê/g,'e')
            )
        };
    }

    communeLayer = L.geoJSON(data, {
        style: choroplethStyle,
        onEachFeature: function (feature, layer) {
            const props  = feature.properties;
            const count  = pvCounts[props.commune] || 0;
            const total  = window._totalPV || 0;
            const pct    = total > 0 ? ((count / total) * 100).toFixed(1) : 0;

            layer.bindPopup(`
                <div style="min-width:180px">
                    <div style="font-weight:700;font-size:13px;color:#1a3c5e;margin-bottom:4px">${props.commune}</div>
                    <div style="font-size:12px;color:#555">
                        <i>Dept :</i> ${props.departement}<br>
                        <i>Région :</i> ${props.region}
                    </div>
                    <hr style="margin:6px 0">
                    <div style="font-size:13px">
                        PV antiterroristes : <strong style="color:${count>0?'#bd0026':'#198754'}">${count}</strong>
                        ${count > 0 ? `<br><small>(${pct}% du total)</small>` : ''}
                    </div>
                </div>
            `);

            layer.on('mouseover', function (e) {
                e.target.setStyle({ weight: 3, color: '#1a3c5e', fillOpacity: 0.95 });
                e.target.bringToFront();
            });
            layer.on('mouseout', function (e) {
                communeLayer.resetStyle(e.target);
            });
        }
    }).addTo(map);

    if (currentRegionFilter && data.features.length > 0) {
        map.fitBounds(communeLayer.getBounds(), { padding: [30, 30] });
    } else {
        map.setView([17.0, 8.0], 5);
    }
}

// ── Chargement des données ────────────────────────────────────────────────────
function loadMapData() {
    const dateDebut = document.getElementById('dateDebut') ? document.getElementById('dateDebut').value : '';
    const dateFin   = document.getElementById('dateFin')   ? document.getElementById('dateFin').value   : '';
    const region    = document.getElementById('regionFilter') ? document.getElementById('regionFilter').value : '';
    currentRegionFilter = region;

    const params = new URLSearchParams();
    if (dateDebut) params.append('date_debut', dateDebut);
    if (dateFin)   params.append('date_fin',   dateFin);
    if (region)    params.append('region',     region);

    const url = (window.BASE_URL || '') + '/api/carte-data' + (params.toString() ? '?' + params.toString() : '');

    fetch(url, { credentials: 'same-origin' })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(apiData => {
            pvCounts   = {};
            pvByRegion = {};
            window._totalPV = apiData.total_pv_anti || 0;

            // Index par nom de commune (insensible à la casse)
            (apiData.communes || []).forEach(function (c) {
                if (c.pv_count > 0) {
                    pvCounts[c.nom] = c.pv_count;
                }
            });
            pvByRegion = apiData.pvByRegion || {};

            // Badges
            const el = id => document.getElementById(id);
            if (el('totalPVBadge'))    el('totalPVBadge').textContent    = `${apiData.total_pv_anti} PV antiterroristes`;
            if (el('communesBadge'))   el('communesBadge').textContent   = `${apiData.communes_actives} communes actives`;

            updateTopCommune(apiData.communes || []);
            updateTable(apiData.communes || []);
            updateChart(pvByRegion);

            // Charger GeoJSON si nécessaire
            if (allGeoData) {
                renderChoropleth(allGeoData);
            } else {
                fetch((window.BASE_URL || '') + '/assets/data/niger_communes.geojson')
                    .then(r => r.json())
                    .then(geoData => { allGeoData = geoData; renderChoropleth(allGeoData); })
                    .catch(err => console.error('GeoJSON error:', err));
            }
        })
        .catch(err => {
            console.error('API carte error:', err);
            // Mode dégradé : GeoJSON sans données
            if (!allGeoData) {
                fetch((window.BASE_URL || '') + '/assets/data/niger_communes.geojson')
                    .then(r => r.json())
                    .then(geoData => { allGeoData = geoData; renderChoropleth(allGeoData); });
            } else {
                renderChoropleth(allGeoData);
            }
        });
}

// ── Badge top commune ─────────────────────────────────────────────────────────
function updateTopCommune(communes) {
    const badge = document.getElementById('topCommuneBadge');
    if (!badge) return;
    const top = (communes || []).filter(c => c.pv_count > 0).sort((a, b) => b.pv_count - a.pv_count)[0];
    if (top) {
        badge.style.display  = '';
        badge.innerHTML = `<i class="bi bi-geo-alt-fill me-1"></i>Commune la plus touchée : <strong>${top.nom}</strong> (${top.pv_count} PV — ${top.region_nom})`;
    } else {
        badge.style.display = 'none';
    }
}

// ── Tableau récapitulatif ─────────────────────────────────────────────────────
function updateTable(communes) {
    const tbody = document.getElementById('tableBody');
    if (!tbody) return;
    const total = window._totalPV || 0;
    const sorted = (communes || []).filter(c => c.pv_count > 0).sort((a, b) => b.pv_count - a.pv_count);

    if (sorted.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Aucun PV antiterroriste enregistré</td></tr>';
        return;
    }
    tbody.innerHTML = sorted.map((c, i) => `
        <tr>
            <td>${i + 1}</td>
            <td class="fw-semibold">${c.nom}</td>
            <td>${c.dept_nom || '—'}</td>
            <td>${c.region_nom || '—'}</td>
            <td>
                <span class="badge" style="background:${getPVColor(c.pv_count)};color:${c.pv_count>5?'#fff':'#333'}">${c.pv_count}</span>
                <small class="text-muted">(${total > 0 ? ((c.pv_count/total)*100).toFixed(1) : 0}%)</small>
            </td>
        </tr>
    `).join('');
}

// ── Graphique doughnut par région ─────────────────────────────────────────────
let chartInstance = null;
function updateChart(pvByRegionData) {
    const canvas = document.getElementById('regionChart');
    if (!canvas) return;
    const labels = Object.keys(pvByRegionData).filter(k => pvByRegionData[k] > 0);
    const values = labels.map(k => pvByRegionData[k]);

    if (chartInstance) { chartInstance.destroy(); }
    if (labels.length === 0) {
        canvas.parentElement.innerHTML = '<p class="text-center text-muted py-3">Aucune donnée</p>';
        return;
    }
    const colors = ['#bd0026','#f03b20','#fd8d3c','#feb24c','#fed976','#ffeda0','#1a3c5e','#4e79a7'];
    chartInstance = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{ data: values, backgroundColor: colors.slice(0, labels.length), borderWidth: 2 }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.raw} PV (${((ctx.raw/values.reduce((a,b)=>a+b,0))*100).toFixed(1)}%)`
                    }
                }
            }
        }
    });
}
