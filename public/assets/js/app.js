// app.js — Scripts généraux TGI-NY

document.addEventListener('DOMContentLoaded', function () {

    // Toggle sidebar
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar       = document.getElementById('sidebar');
    const mainContent   = document.getElementById('mainContent');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            if (sidebar) sidebar.classList.toggle('collapsed');
            if (mainContent) mainContent.classList.toggle('sidebar-collapsed');
        });
    }

    // Auto-dismiss flash messages après 5s
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) bsAlert.close();
            else alert.style.opacity = '0';
        }, 5000);
    });

    // Confirmations destructives
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm || 'Confirmer cette action ?')) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Mise à jour badge alertes toutes les 60s
    updateAlertesBadge();
    setInterval(updateAlertesBadge, 60000);
});

function updateAlertesBadge() {
    const BASE = window.BASE_URL || '';
    fetch(BASE + '/api/alertes-count')
        .then(r => r.json())
        .then(data => {
            document.querySelectorAll('.alertes-count-badge').forEach(el => {
                el.textContent = data.count;
                el.style.display = data.count > 0 ? '' : 'none';
            });
        })
        .catch(() => {});
}
