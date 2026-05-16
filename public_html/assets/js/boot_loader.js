/**
 * BOOT LOADER - Initialization Sequence
 * Simulates system kernel mapping and security log checks.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Only run on dashboard
    if (!document.querySelector('.dashboard-wrapper')) return;

    this_initialize_boot_sequence();
});

async function this_initialize_boot_sequence() {
    // Create the boot overlay dynamically
    const boot_overlay = document.createElement('div');
    boot_overlay.id = 'boot-loader-overlay';
    boot_overlay.innerHTML = `
        <div class="glass-panel" style="padding: 40px; width: 400px; text-align: center;">
            <h3 style="color: var(--accent-cyan); letter-spacing: 5px; margin-bottom: 20px;">SYSTEM_INITIALIZING</h3>
            <div style="width: 100%; height: 2px; background: rgba(255,255,255,0.1); position: relative; overflow: hidden;">
                <div id="boot-progress-bar" style="width: 0%; height: 100%; background: var(--accent-cyan); transition: width 0.4s ease;"></div>
            </div>
            <p id="boot-status-text" style="color: var(--text-dim); font-size: 0.7rem; margin-top: 15px; letter-spacing: 2px;">MAPPING_SECURITY_LOGS...</p>
        </div>
    `;

    // Apply basic styles for overlay
    Object.assign(boot_overlay.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100%',
        height: '100%',
        background: 'var(--bg-color)',
        display: 'flex',
        justifyContent: 'center',
        align-items: 'center',
        zIndex: '9999',
        transition: 'opacity 0.8s ease'
    });

    document.body.appendChild(boot_overlay);

    const progress_bar = document.getElementById('boot-progress-bar');
    const status_text  = document.getElementById('boot-status-text');

    const boot_phases = [
        { progress: 20, text: "DECRYPTING_CORE_KERNEL..." },
        { progress: 50, text: "VERIFYING_OPERATOR_SIGNATURES..." },
        { progress: 80, text: "SYNCING_ARMORY_TELEMETRY..." },
        { progress: 100, text: "INITIALIZATION_COMPLETE." }
    ];

    // Simulate boot sequence with Promises
    for (const phase of boot_phases) {
        await new Promise(resolve => setTimeout(resolve, 600));
        progress_bar.style.width = `${phase.progress}%`;
        status_text.innerText = phase.text;
    }

    // Smooth fade out
    await new Promise(resolve => setTimeout(resolve, 500));
    boot_overlay.style.opacity = '0';
    
    setTimeout(() => {
        boot_overlay.remove();
        // Trigger reveal event for app.js
        window.dispatchEvent(new Event('system-ready'));
    }, 800);
}
