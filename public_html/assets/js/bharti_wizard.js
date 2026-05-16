/**
 * BHARTI WIZARD CONTROLLER
 * Handles multi-step navigation and form submission for the Bharti Form.
 */

document.addEventListener('DOMContentLoaded', () => {
    this_initialize_bharti_wizard();
});

function this_initialize_bharti_wizard() {
    const wizard_form = document.getElementById('bharti-wizard-form');
    if (!wizard_form) return;

    wizard_form.onsubmit = async (e) => {
        e.preventDefault();
        this_submit_bharti_form(wizard_form);
    };
}

/**
 * WIZARD NAVIGATION ENGINE
 * Manages section visibility with glassmorphic smooth transitions.
 */
function this_navigate_wizard(step_number) {
    const all_sections = document.querySelectorAll('.dynamic-field-wrapper');
    const all_tabs = document.querySelectorAll('.tab-btn');

    all_sections.forEach((section, index) => {
        // Smooth Fade Out
        section.style.opacity = '0';
        
        setTimeout(() => {
            section.style.display = 'none';
            all_tabs[index].classList.remove('active');

            // Reveal Target Step
            if (index === (step_number - 1)) {
                section.style.display = 'block';
                all_tabs[index].classList.add('active');
                
                // Trigger reflow for transition
                section.offsetHeight;
                section.style.opacity = '1';
                section.style.transform = 'translateY(0)';
            }
        }, 300);
    });
}

/**
 * ASYNCHRONOUS DEPLOYMENT HANDLER
 * Packs multi-dimensional data for the Guard Actions API.
 */
async function this_submit_bharti_form(form_element) {
    const submit_btn = form_element.querySelector('button[type="submit"]');
    submit_btn.innerText = 'DEPLOYING_PERSONNEL...';
    submit_btn.disabled = true;

    const fd = new FormData(form_element);
    
    // Construct Multi-Dimensional Payload for GuardManager
    const payload = {
        profile: {
            full_name: fd.get('full_name'),
            cnic: fd.get('cnic'),
            phone_number: fd.get('phone_number'),
            home_address: fd.get('home_address'),
            blood_group: fd.get('blood_group'),
            joining_date: new Date().toISOString().split('T')[0]
        },
        witnesses: [
            {
                name: fd.get('witness_1_name'),
                cnic: fd.get('witness_1_cnic'),
                phone: fd.get('witness_1_phone')
            },
            {
                name: fd.get('witness_2_name'),
                cnic: fd.get('witness_2_cnic'),
                phone: fd.get('witness_2_phone')
            }
        ],
        certifications: [
            {
                course_name: fd.get('cert_name'),
                institute: fd.get('cert_institute'),
                completion_date: fd.get('cert_issue_date'),
                expiry_date: fd.get('cert_expiry_date')
            }
        ]
    };

    try {
        const response = await fetch('../api/guard_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert("SUCCESS: Personnel deployed and synchronized.");
            window.location.href = 'dashboard.php';
        } else {
            alert("DEPLOYMENT_FAILURE: " + result.message);
        }
    } catch (err) {
        console.error("WIZARD_CRITICAL_FAILURE:", err);
        alert("CRITICAL: Network isolation or server timeout.");
    } finally {
        submit_btn.innerText = 'AUTHORIZE_DEPLOYMENT';
        submit_btn.disabled = false;
    }
}
