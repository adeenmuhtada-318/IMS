/**
 * ISOLATED FORM BEHAVIOR - Recruitment
 * Handles inline pattern masking and real-time validation.
 */

document.addEventListener('DOMContentLoaded', () => {
    const enrollmentForm = document.getElementById('EnrollmentWizard');
    
    // 1. DYNAMIC PATTERN MAPPING: CNIC (00000-0000000-0)
    const cnicInput = document.querySelector('input[name="cnic"]');
    if (cnicInput) {
        cnicInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '');
            let final = '';
            if (val.length > 0) final += val.substring(0, 5);
            if (val.length > 5) final += '-' + val.substring(5, 12);
            if (val.length > 12) final += '-' + val.substring(12, 13);
            e.target.value = final;
            
            // Inline Feedback Hook
            validateField(e.target, val.length === 13);
        });
    }

    // 2. DYNAMIC PATTERN MAPPING: Mobile (0300-0000000)
    const phoneInput = document.querySelector('input[name="phone_number"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '');
            let final = '';
            if (val.length > 0) final += val.substring(0, 4);
            if (val.length > 4) final += '-' + val.substring(4, 11);
            e.target.value = final;

            // Inline Feedback Hook
            validateField(e.target, val.length === 11);
        });
    }

    /**
     * FIELD VALIDATOR HOOK
     */
    function validateField(input, isValid) {
        const parent = input.closest('.FieldGroup');
        if (input.value === '') {
            parent.classList.remove('InvalidField');
            return;
        }
        
        if (!isValid) {
            parent.classList.add('InvalidField');
        } else {
            parent.classList.remove('InvalidField');
        }
    }

    // 3. SUBMIT INTERCEPT
    if (enrollmentForm) {
        enrollmentForm.addEventListener('submit', (e) => {
            const requiredInputs = enrollmentForm.querySelectorAll('[required]');
            let formValid = true;

            requiredInputs.forEach(input => {
                if (input.value.trim() === '') {
                    formValid = false;
                    input.closest('.FieldGroup').classList.add('InvalidField');
                }
            });

            if (!formValid) {
                e.preventDefault();
                alert('CRITICAL: Personnel data fields must be correctly formatted before submission.');
            }
        });
    }
});
