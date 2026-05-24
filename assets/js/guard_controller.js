/**
 * GUARD CONTROLLER - Interactive Recruitment Engine
 * Optimized for multi-tab "Bharti Form"
 */

document.addEventListener('DOMContentLoaded', () => {
    this_initialize_guard_controller();
});

let current_active_step = 1;

function this_initialize_guard_controller() {
    const wizard_form = document.getElementById('bharti-wizard-form');
    if (!wizard_form) return;

    // Ex-Army Interactive Toggle
    const army_toggle = document.getElementById('ex-army-toggle');
    const army_node = document.getElementById('army-details-node');
    if (army_toggle) {
        army_toggle.addEventListener('change', (e) => {
            army_node.style.display = e.target.checked ? 'block' : 'none';
        });
    }

    wizard_form.onsubmit = (e) => {
        e.preventDefault();
        this_handle_onboarding_submission(wizard_form);
    };
}

function this_navigate_wizard(target_step) {
    // Basic validation before moving forward
    if (target_step > current_active_step) {
        if (!this_validate_step_inputs(current_active_step)) {
            alert("VALIDATION_FAILURE: Please complete required fields in this section.");
            return;
        }
    }

    const sections = document.querySelectorAll('.dynamic-field-wrapper');
    const tabs = document.querySelectorAll('.tab-btn');

    // Fade out current
    const current_section = document.getElementById(`wizard-step-${current_active_step}`);
    if(current_section) current_section.style.opacity = '0';

    setTimeout(() => {
        sections.forEach(s => s.style.display = 'none');
        tabs.forEach(t => t.classList.remove('active'));

        const target_section = document.getElementById(`wizard-step-${target_step}`);
        if(target_section) {
            target_section.style.display = 'block';
            setTimeout(() => {
                target_section.style.opacity = '1';
            }, 50);
        }
        
        const tab = document.getElementById(`tab-step-${target_step}`);
        if(tab) tab.classList.add('active');
        
        current_active_step = target_step;
    }, 300);
}

function this_validate_step_inputs(step) {
    const container = document.getElementById(`wizard-step-${step}`);
    if(!container) return true;
    const required_fields = container.querySelectorAll('[required]');
    let valid = true;

    required_fields.forEach(f => {
        if (!f.value.trim()) {
            f.style.borderColor = 'var(--alert-orange)';
            valid = false;
        } else {
            f.style.borderColor = '';
        }
    });

    return valid;
}

async function this_handle_onboarding_submission(form_element) {
    const btn = form_element.querySelector('button[type="submit"]');
    const original_text = btn.innerText;
    btn.innerText = 'COMMITTING_SIGNATURES...';
    btn.disabled = true;

    const fd = new FormData(form_element);
    
    // Construct Enterprise-Aligned Payload
    const payload = {
        action: 'onboard_guard',
        profile: {
            guard_no: fd.get('guard_no'),
            joining_date: fd.get('joining_date'),
            full_name: fd.get('full_name'),
            father_name: fd.get('father_name'),
            caste: fd.get('caste'),
            education: fd.get('education'),
            religion: fd.get('religion'),
            cnic: fd.get('cnic'),
            dob: fd.get('dob'),
            district: fd.get('district'),
            phone_number: fd.get('phone_number'),
            blood_group: fd.get('blood_group'),
            temporary_address: fd.get('temporary_address'),
            permanent_address: fd.get('permanent_address'),
            is_ex_army: fd.has('is_ex_army') ? 1 : 0,
            army_joining_date: fd.get('army_joining_date'),
            army_discharge_date: fd.get('army_discharge_date'),
            army_discharge_reason: fd.get('army_discharge_reason'),
            govt_relative_name: fd.get('govt_relative_name'),
            govt_relative_designation: fd.get('govt_relative_designation'),
            govt_relative_department: fd.get('govt_relative_department'),
            previous_experience_ref: fd.get('previous_experience_ref'),
            next_of_kin_mobile: fd.get('next_of_kin_mobile'),
            next_of_kin_name_address: fd.get('next_of_kin_name_address'),
            base_salary: fd.get('base_salary') || 0,
            police_verification_ref_no: fd.get('police_verification_ref_no')
        },
        witnesses: [
            { name: fd.get('witness_1_name'), phone: fd.get('witness_1_phone'), address: fd.get('witness_1_address') },
            { name: fd.get('witness_2_name'), phone: fd.get('witness_2_phone'), address: fd.get('witness_2_address') }
        ],
        kit: {
            shirt_trousers: fd.has('kit_shirt_trousers') ? 1 : 0,
            cap: fd.has('kit_cap') ? 1 : 0,
            belt: fd.has('kit_belt') ? 1 : 0,
            boots: fd.has('kit_boots') ? 1 : 0,
            jersey: fd.has('kit_jersey') ? 1 : 0
        }
    };

    try {
        const response = await fetch('../api/api_router.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });


        const result = await response.json();

        if (result.status === 'success') {
            alert("RECRUITMENT_SUCCESS: Personnel records synchronized.");
            window.location.href = 'dashboard.php';
        } else {
            alert("DEPLOYMENT_BLOCKED: " + result.message);
        }
    } catch (err) {
        console.error(err);
        alert("CRITICAL: API_GATEWAY_TIMEOUT");
    } finally {
        btn.innerText = original_text;
        btn.disabled = false;
    }
}
