/**
 * DASHBOARD APP CONTROLLER
 * Handles dynamic content loading for the Glass interface.
 */

document.addEventListener('DOMContentLoaded', () => {
    const content_area = document.getElementById('main-content-area');
    const view_title   = document.getElementById('view-title');
    const nav_links    = document.querySelectorAll('.nav-link');

    // Initial load
    refreshTelemetry();

    // Module Navigation
    nav_links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            nav_links.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            const module = link.getAttribute('data-module');
            view_title.innerText = `SYSTEM_${module.toUpperCase()}`;
            
            if (module === 'telemetry') refreshTelemetry();
            else content_area.innerHTML = `<div class="glass-panel" style="padding: 40px; text-align: center; color: var(--text-dim);">MODULE_0${link.innerText.split('_')[0]}_UNDER_CONSTRUCTION</div>`;
        });
    });

    async function refreshTelemetry() {
        content_area.innerHTML = '<div style="color: var(--accent-cyan);">SCANNING_DATABASE...</div>';
        
        try {
            // Reusing existing API router
            const response = await fetch('api_router.php?action=dashboard');
            const data = await response.json();
            renderStats(data.stats);
        } catch (err) {
            content_area.innerHTML = '<div class="status-alert">CRITICAL_ERROR: Unable to retrieve telemetry.</div>';
        }
    }

    function renderStats(stats) {
        content_area.innerHTML = `
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div class="glass-panel" style="padding: 30px;">
                    <div style="color: var(--text-dim); font-size: 0.75rem; margin-bottom: 10px;">TOTAL_ASSET_READY</div>
                    <div style="font-size: 2.5rem; color: var(--accent-cyan); font-weight: 300;">${stats.total_items}</div>
                </div>
                <div class="glass-panel" style="padding: 30px; border-left: 4px solid var(--alert-orange);">
                    <div style="color: var(--text-dim); font-size: 0.75rem; margin-bottom: 10px;">CRITICAL_ALERTS</div>
                    <div style="font-size: 2.5rem; color: var(--alert-orange); font-weight: 300;">${stats.critical}</div>
                </div>
                <div class="glass-panel" style="padding: 30px;">
                    <div style="color: var(--text-dim); font-size: 0.75rem; margin-bottom: 10px;">VALUATION_WAC</div>
                    <div style="font-size: 2.5rem; color: var(--accent-cyan); font-weight: 300;">$${stats.valuation}</div>
                </div>
            </div>
            
            <div class="glass-panel" style="margin-top: 30px; padding: 40px; text-align: center; color: var(--text-dim);">
                QUANTUM_DATA_VISUALIZATION_READY
            </div>
        `;
    }
});
