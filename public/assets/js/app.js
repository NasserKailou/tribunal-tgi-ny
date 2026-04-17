// app.js — Scripts généraux TGI-NY v2.0
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const sidebar       = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent   = document.getElementById('mainContent');
    const overlay       = document.getElementById('sidebarOverlay');

    // ── Sidebar toggle responsive ──────────────────────────────
    function isMobile() { return window.innerWidth < 992; }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            if (isMobile()) {
                // Mobile: ouvrir/fermer avec overlay
                sidebar.classList.toggle('open');
                if (overlay) overlay.classList.toggle('show');
            } else {
                // Desktop: réduire sidebar + décaler contenu
                sidebar.classList.toggle('collapsed');
                if (mainContent) mainContent.classList.toggle('sidebar-collapsed');
            }
        });
    }

    // Fermer sidebar au clic sur l'overlay
    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    // Fermer sidebar mobile lors du clic sur un lien
    if (sidebar) {
        sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (isMobile()) {
                    sidebar.classList.remove('open');
                    if (overlay) overlay.classList.remove('show');
                }
            });
        });
    }

    // Gérer le redimensionnement
    window.addEventListener('resize', function () {
        if (!isMobile()) {
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('show');
        }
    });

    // ── Auto-dismiss flash messages après 6s ───────────────────
    document.querySelectorAll('.alert-dismissible').forEach(function (alertEl) {
        setTimeout(function () {
            try {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                if (bsAlert) bsAlert.close();
            } catch (e) {
                alertEl.style.transition = 'opacity 0.5s';
                alertEl.style.opacity = '0';
                setTimeout(function () { alertEl.remove(); }, 500);
            }
        }, 6000);
    });

    // ── Confirmations destructives ─────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const msg = el.dataset.confirm || 'Confirmer cette action ?';
            if (!window.confirm(msg)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // ── Mise à jour badge alertes toutes les 60s ───────────────
    updateAlertesBadge();
    setInterval(updateAlertesBadge, 60000);

    // ── Tooltips Bootstrap ──────────────────────────────────────
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });

    // ── Focus premier input dans modals ────────────────────────
    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('shown.bs.modal', function () {
            const firstInput = modal.querySelector('input:not([type=hidden]), select, textarea');
            if (firstInput) firstInput.focus();
        });
    });

    // ── Prevent double submit ──────────────────────────────────
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            const submitBtns = form.querySelectorAll('[type=submit]');
            setTimeout(function () {
                submitBtns.forEach(function (btn) {
                    btn.disabled = true;
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>En cours…';
                    // Re-enable after 10s fallback
                    setTimeout(function () {
                        btn.disabled = false;
                        btn.innerHTML = orig;
                    }, 10000);
                });
            }, 50);
        });
    });

});

// ── Update alertes badge ──────────────────────────────────────
function updateAlertesBadge() {
    var BASE = window.BASE_URL || '';
    fetch(BASE + '/api/alertes-count', { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            document.querySelectorAll('.alertes-count-badge').forEach(function (el) {
                el.textContent = data.count || 0;
                el.style.display = (data.count > 0) ? '' : 'none';
            });
        })
        .catch(function () { /* Silencieux */ });
}
