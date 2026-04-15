<?php $pageTitle = 'Carte Antiterroriste'; ?>
<style>
#map { height: calc(100vh - 200px); min-height: 500px; border-radius: 8px; }
.map-filter { background: #fff; padding: 12px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
.legend { background: #fff; padding: 10px 14px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
.legend-item { display:flex; align-items:center; gap:8px; margin-bottom:4px; font-size:0.85rem; }
.legend-circle { width:14px; height:14px; border-radius:50%; display:inline-block; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mt-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-map me-2 text-danger"></i>Carte des incidents antiterroristes</h4>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Imprimer</button>
</div>

<!-- Filtres date -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form class="row g-2 align-items-end" id="carteFilterForm">
            <div class="col-md-3">
                <label class="form-label small mb-1">Date début</label>
                <input type="date" id="dateDebut" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Date fin</label>
                <input type="date" id="dateFin" class="form-control form-control-sm" value="<?=date('Y-m-d')?>">
            </div>
            <div class="col-md-2">
                <button type="button" onclick="loadMapData()" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filtrer</button>
            </div>
            <div class="col-auto ms-auto">
                <span class="badge bg-danger fs-6" id="totalPVBadge">— PV antiterroristes</span>
                <span class="badge bg-secondary ms-2" id="communesBadge">— communes actives</span>
            </div>
        </form>
    </div>
</div>

<!-- Carte -->
<div id="map"></div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?=BASE_URL?>/assets/js/carte.js"></script>
<script>
window.BASE_URL = '<?=BASE_URL?>';
document.addEventListener('DOMContentLoaded', function() {
    initCarte();
    loadMapData();
});
</script>
