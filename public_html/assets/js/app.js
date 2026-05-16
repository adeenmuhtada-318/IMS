/**
 * ASMS CORE ENGINE - Advanced Client-Side Logic
 * Optimized for: Apache (XAMPP) & MySQL Workbench Backend
 * 
 * DESIGN RATIONALE:
 * We strictly decouple UI behavior from HTML. This script manages dynamic 
 * form states, handles asynchronous data transmission, and provides 
 * high-fidelity visual feedback via glassmorphic overlays.
 */

document.addEventListener('DOMContentLoaded', () => {
    this_initialize_asms_controller();
});

/**
 * Main Controller Bootstrapper
 */
function this_initialize_asms_controller() {
    // 1. Setup Category Toggling
    const category_dropdown = document.querySelector('#asset_category_select');
    if (category_dropdown) {
        category_dropdown.addEventListener('change', (e) => {
            this_toggle_dynamic_form_fields(e.target.value);
        });
    }

    // 2. Setup Asset Submission
    const asset_form = document.querySelector('#asms_asset_form');
    if (asset_form) {
        asset_form.addEventListener('submit', (e) => {
            this_handle_form_submission(e, asset_form);
        });
    }

    // 3. Initial state check for low stock highlighting
    this_refresh_stock_visuals();
}

/**
 * DYNAMIC FORM TOGGLE LOGIC
 * Manages visibility of sub-fields (Operational, Apparel, etc.) 
 * with premium smooth transitions.
 */
function this_toggle_dynamic_form_fields(selected_category) {
    // We target all wrappers starting with 'asms_fields_'
    const all_field_wrappers = document.querySelectorAll('.dynamic-field-wrapper');
    
    all_field_wrappers.forEach(wrapper => {
        // Step A: Immediate Fade-Out for non-selected
        wrapper.style.opacity = '0';
        
        // Wait for fade-out transition before hiding from layout
        setTimeout(() => {
            wrapper.style.display = 'none';
            
            // Step B: If this matches the selection, bring it into view
            if (wrapper.id === `asms_fields_${selected_category}`) {
                wrapper.style.display = 'block';
                // Trigger reflow for transition
                wrapper.offsetHeight; 
                wrapper.style.opacity = '1';
                wrapper.style.transform = 'translateY(0)';
            }
        }, 300);
    });
}

/**
 * ASYNCHRONOUS FORM SUBMISSION
 * Uses Fetch API to dispatch data to the ASMS backend.
 */
async function this_handle_form_submission(event, form_element) {
    event.preventDefault();

    // Visual loading state
    const submit_btn = form_element.querySelector('button[type="submit"]');
    const original_text = submit_btn.innerText;
    submit_btn.innerText = 'SYNCING_WITH_SERVER...';
    submit_btn.disabled = true;

    // Serialize form data (including dynamic sub-fields)
    const form_payload = new FormData(form_element);

    try {
        const response = await fetch('api/add_asset.php', {
            method: 'POST',
            body: form_payload
        });

        const result_data = await response.json();

        if (result_data.status === 'success') {
            this_display_glass_toast('ASSET_LOGGED: Data Synchronized Successfully', 'success');
            form_element.reset();
            this_toggle_dynamic_form_fields('none'); // Reset view
            
            // Refresh dashboard items if needed
            if (typeof this_fetch_live_telemetry === 'function') {
                this_fetch_live_telemetry();
            }
        } else {
            this_display_glass_toast(`ERROR: ${result_data.message}`, 'error');
        }

    } catch (critical_error) {
        console.error("ASMS_SYSTEM_ERROR:", critical_error);
        this_display_glass_toast('CRITICAL_FAILURE: Backend unreachable', 'error');
    } finally {
        submit_btn.innerText = original_text;
        submit_btn.disabled = false;
    }
}

/**
 * GLASSMORPHIC NOTIFICATION SYSTEM
 */
function this_display_glass_toast(status_message, type) {
    const toast_container = document.getElementById('asms_toast_container') || this_create_toast_container();
    
    const toast_element = document.createElement('div');
    toast_element.className = `glass-toast toast-${type}`;
    toast_element.innerHTML = `
        <div class="toast-content">
            <span class="status-icon">${type === 'success' ? '✅' : '⚠️'}</span>
            <span class="message">${status_message}</span>
        </div>
    `;

    toast_container.appendChild(toast_element);

    // Entrance Animation
    setTimeout(() => {
        toast_element.classList.add('active');
    }, 10);

    // Auto-Removal after duration
    setTimeout(() => {
        toast_element.classList.remove('active');
        setTimeout(() => toast_element.remove(), 500);
    }, 4000);
}

/**
 * LOW STOCK HIGHLIGHTING ENGINE
 * Scans asset cards/rows and flags items below min_threshold.
 */
function this_refresh_stock_visuals() {
    const asset_entries = document.querySelectorAll('.asset-entry-card');
    
    asset_entries.forEach(entry => {
        const current_stock = parseInt(entry.dataset.stock || 0);
        const min_threshold = parseInt(entry.dataset.threshold || 5);

        if (current_stock <= min_threshold) {
            // High-contrast Tactical Alert Orange
            entry.style.borderLeft = '4px solid #ff7b00';
            entry.classList.add('low-stock-glow');
        } else {
            entry.style.borderLeft = '1px solid rgba(255,255,255,0.1)';
            entry.classList.remove('low-stock-glow');
        }
    });
}

/**
 * UI Helper: Toast Container Generator
 */
function this_create_toast_container() {
    const container = document.createElement('div');
    container.id = 'asms_toast_container';
    document.body.appendChild(container);
    return container;
}
