// carte.js — Leaflet choropleth map (marker-based)

let map, markersLayer;

function initCarte() {
    map = L.map('map').setView([17.6, 8.0], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 14
    }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);

    // Légende
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'legend leaflet-control');
        div.innerHTML = `
            <strong style="font-size:0.85rem;display:block;margin-bottom:6px"><i>PV antiterroristes / commune</i></strong>
            <div class="legend-item"><span class="legend-circle" style="background:#ffffff;border:1px solid #ccc"></span> 0 PV</div>
            <div class="legend-item"><span class="legend-circle" style="background:#ffffcc;border:1px solid #ccc"></span> 1–2 PV</div>
            <div class="legend-item"><span class="legend-circle" style="background:#ffeda0"></span> 3–5 PV</div>
            <div class="legend-item"><span class="legend-circle" style="background:#feb24c"></span> 6–10 PV</div>
            <div class="legend-item"><span class="legend-circle" style="background:#f03b20"></span> 11–20 PV</div>
            <div class="legend-item"><span class="legend-circle" style="background:#bd0026"></span> 20+ PV</div>
        `;
        return div;
    };
    legend.addTo(map);
}

function getPVColor(count) {
    if (count === 0) return '#ffffff';
    if (count <= 2)  return '#ffffcc';
    if (count <= 5)  return '#ffeda0';
    if (count <= 10) return '#feb24c';
    if (count <= 20) return '#f03b20';
    return '#bd0026';
}

function getPVRadius(count) {
    if (count === 0) return 6;
    if (count <= 2)  return 8;
    if (count <= 5)  return 12;
    if (count <= 10) return 16;
    if (count <= 20) return 20;
    return 25;
}

function loadMapData() {
    const dateDebut = document.getElementById('dateDebut').value;
    const dateFin   = document.getElementById('dateFin').value;
    let url = (window.BASE_URL || '') + '/api/carte-data';
    const params = new URLSearchParams();
    if (dateDebut) params.append('date_debut', dateDebut);
    if (dateFin)   params.append('date_fin', dateFin);
    if (params.toString()) url += '?' + params.toString();

    fetch(url)
        .then(r => r.json())
        .then(data => {
            markersLayer.clearLayers();

            document.getElementById('totalPVBadge').textContent = `${data.total_pv_anti} PV antiterroristes`;
            document.getElementById('communesBadge').textContent = `${data.communes_actives} communes actives`;

            data.communes.forEach(function(commune) {
                if (!commune.latitude || !commune.longitude) return;

                const color = getPVColor(commune.pv_count);
                const radius = getPVRadius(commune.pv_count);
                const border = commune.pv_count > 0 ? '#8b0000' : '#999';
                const opacity = commune.pv_count > 0 ? 0.85 : 0.35;

                const circle = L.circleMarker([parseFloat(commune.latitude), parseFloat(commune.longitude)], {
                    radius: radius,
                    fillColor: color,
                    color: border,
                    weight: 1.5,
                    opacity: 1,
                    fillOpacity: opacity
                });

                let piHtml = '';
                if (commune.primo_intervenants && commune.primo_intervenants.length > 0) {
                    piHtml = '<br><strong>Primo intervenants :</strong><br>' +
                        commune.primo_intervenants.map(pi => `<span class="badge bg-dark">${pi}</span>`).join(' ');
                }

                circle.bindPopup(`
                    <div style="min-width:200px">
                        <h6 class="mb-1"><i class="bi bi-geo-alt"></i> <strong>${commune.nom}</strong></h6>
                        <div class="text-muted small">${commune.dept_nom} — ${commune.region_nom}</div>
                        <hr style="margin:6px 0">
                        <div><strong style="color:${commune.pv_count > 0 ? '#dc3545' : '#666'}">${commune.pv_count}</strong> PV antiterroriste${commune.pv_count !== 1 ? 's' : ''}</div>
                        ${piHtml}
                    </div>
                `);

                if (commune.pv_count > 0) {
                    circle.bindTooltip(`${commune.nom}: ${commune.pv_count} PV`, { permanent: false, direction: 'top' });
                }

                markersLayer.addLayer(circle);
            });
        })
        .catch(err => console.error('Erreur chargement carte:', err));
}
