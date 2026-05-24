/**
 * PROCUREMENT CONTROLLER - Form Interaction Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    this_initialize_procurement_engine();
});

function this_initialize_procurement_engine() {
    const p_form = document.getElementById('procurement-form');
    if (!p_form) return;

    p_form.onsubmit = async (e) => {
        e.preventDefault();
        this_submit_procurement(p_form);
    };
}

function this_switch_procurement_view(category) {
    const slots = document.querySelectorAll('.dynamic-field-wrapper');
    const tabs = document.querySelectorAll('.tab-btn');

    slots.forEach(slot => {
        slot.style.display = 'none';
        slot.style.opacity = '0';
    });

    const active_slot = document.getElementById(`slot-${category}`);
    if (active_slot) {
        active_slot.style.display = 'block';
        setTimeout(() => {
            active_slot.style.opacity = '1';
        }, 10);
    }

    tabs.forEach(tab => {
        tab.classList.remove('active');
        if (tab.innerText.toLowerCase().includes(category)) {
            tab.classList.add('active');
        }
    });
}

async function this_submit_procurement(form_element) {
    const btn = form_element.querySelector('button[type="submit"]');
    const original_text = btn.innerText;
    
    btn.innerText = 'AUTHORIZING_INGESTION...';
    btn.disabled = true;

    const fd = new FormData(form_element);
    fd.append('action', 'add_asset');
    
    try {
        const response = await fetch('../api/api_router.php', {
            method: 'POST',
            body: fd
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert("TRANSACTION_COMMITTED: Asset logged in CTI structure.");
            form_element.reset();
            this_switch_procurement_view('operational');
        } else {
            alert("PROCUREMENT_REJECTED: " + result.message);
        }

    } catch (err) {
        console.error("CORE_PROCUREMENT_FAILURE:", err);
        alert("CRITICAL_SYSTEM_ERROR: Database sync failure.");
    } finally {
        btn.innerText = original_text;
        btn.disabled = false;
    }
}
