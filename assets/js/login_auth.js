/**
 * LOGIN AUTHORIZATION HANDLER - PRODUCTION V5
 * Bridges the Glass UI with the Auth Engine.
 */

document.addEventListener('DOMContentLoaded', () => {
    const login_form  = document.getElementById('login-form');
    const error_box   = document.getElementById('error-display');
    const auth_btn    = document.getElementById('btn-authorize');

    login_form.onsubmit = async (e) => {
        e.preventDefault();
        
        // RESET UI STATE
        error_box.classList.add('hidden');
        auth_btn.classList.add('btn-loading');

        const form_data = new FormData(login_form);

        try {
            const response = await fetch('auth/login_processor.php', {
                method: 'POST',
                body: form_data
            });

            const result = await response.json();

            if (result.status === 'success') {
                window.location.href = 'dashboard.php';
            } else {
                error_box.innerText = result.message;
                error_box.classList.remove('hidden');
                auth_btn.classList.remove('btn-loading');
            }
        } catch (err) {
            error_box.innerText = 'CONNECTION_FAILURE: Auth server unreachable.';
            error_box.classList.remove('hidden');
            auth_btn.classList.remove('btn-loading');
        }
    };
});
