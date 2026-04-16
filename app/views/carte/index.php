<?php $pageTitle = 'Carte Antiterroriste'; ?>
<style>
/* ── Carte ── */
#map {
    height: calc(100vh - 240px);
    min-height: 480px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}
.legend {
    background: #fff;
    padding: 10px 14px;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    font-size: 0.82rem;
}
.legend-item  { display:flex; align-items:center; gap:8px; margin-bottom:4px; }
.legend-box   { width:16px; height:14px; border-radius:3px; display:inline-block; border:1px solid #bbb; flex-shrink:0; }

/* ── Stats section ── */
.section-title {
    font-weight: 700;
    font-size: 1.05rem;
    border-left: 4px solid #dc3545;
    padding-left: 10px;
    margin-bottom: 14px;
}

/* ── Tableau ── */
#tableRecap td, #tableRecap th { vertical-align: middle; }
.table-recap-wrapper {
    max-height: 360px;
    overflow-y: auto;
}

/* ── Print ── */
@media print {
    .no-print, .sidebar, .navbar, .btn, form, #carteFilterForm, #printBtn { display: none !important; }
    #map { height: 60vh !important; page-break-after: always; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    body { font-size: 11pt; }
}
</style>

<!-- ── En-tête ──────────────────────────────────────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-3 mt-2 no-print">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-map me-2 text-danger"></i>Carte des incidents antiterroristes
    </h4>
    <button id="printBtn" onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-printer me-1"></i>Imprimer la carte
    </button>
</div>

<!-- ── Badge commune la plus touchée ─────────────────────────────────────────── -->
<div id="topCommuneBadge"
     class="alert alert-danger d-flex align-items-center py-2 mb-3 no-print"
     style="display:none!important">
</div>

<!-- ── Filtres ────────────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body py-2">
        <form class="row g-2 align-items-end" id="carteFilterForm" onsubmit="return false">
            <!-- Période -->
            <div class="col-md-2">
                <label class="form-label small mb-1">Date début</label>
                <input type="date" id="dateDebut" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Date fin</label>
                <input type="date" id="dateFin" class="form-control form-control-sm"
                       value="<?= date('Y-m-d') ?>">
            </div>
            <!-- Filtre région -->
            <div class="col-md-3">
                <label class="form-label small mb-1">Région</label>
                <select id="regionFilter" class="form-select form-select-sm"
                        onchange="applyRegionFilter()">
                    <option value="">— Toutes les régions —</option>
                    <?php
                    $regions = ['Agadez','Diffa','Dosso','Maradi','Niamey','Tahoua','Tillaberi','Zinder'];
                    foreach ($regions as $r) {
                        echo "<option value=\"$r\">$r</option>";
                    }
                    ?>
                </select>
            </div>
            <!-- Bouton filtre -->
            <div class="col-md-2">
                <button type="button" onclick="loadMapData()"
                        class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-funnel me-1"></i>Appliquer
                </button>
            </div>
            <!-- Badges stats -->
            <div class="col-auto ms-auto d-flex gap-2 flex-wrap justify-content-end">
                <span class="badge bg-danger fs-6" id="totalPVBadge">
                    <i class="bi bi-file-earmark-text me-1"></i>— PV antiterroristes
                </span>
                <span class="badge bg-secondary fs-6" id="communesBadge">
                    <i class="bi bi-pin-map me-1"></i>— communes actives
                </span>
            </div>
        </form>
    </div>
</div>

<!-- ── Carte Leaflet ──────────────────────────────────────────────────────────── -->
<div id="map" class="mb-4"></div>

<!-- ── Section graphiques + tableau ─────────────────────────────────────────── -->
<div class="row g-3 mt-1">

    <!-- Graphique doughnut par région -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="section-title">Répartition par région</div>
                <div style="position:relative;height:260px">
                    <canvas id="donutRegion"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau récapitulatif -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="section-title">Classement par commune</div>
                <div class="table-recap-wrapper">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th width="30">#</th>
                                <th>Commune</th>
                                <th>Département</th>
                                <th>Région</th>
                                <th class="text-center">PV</th>
                            </tr>
                        </thead>
                        <tbody id="tableRecap">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Chargement…
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── Leaflet + Chart.js ────────────────────────────────────────────────────── -->
<!-- Leaflet sans SRI (hash integrity bloque le chargement sur certains navigateurs) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/carte.js"></script>

<script>
window.BASE_URL = '<?= BASE_URL ?>';
document.addEventListener('DOMContentLoaded', function () {
    initCarte();
    loadMapData();
});
</script>
