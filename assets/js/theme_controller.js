/**
 * THEME CONTROLLER - Persistent State Management
 * Handles the toggle between Light and Dark obsidian themes.
 */

document.addEventListener('DOMContentLoaded', () => {
    this_initialize_theme_engine();
});

function this_initialize_theme_engine() {
    const toggle_btn = document.getElementById('theme-toggle');
    if (!toggle_btn) return;

    // 1. Load preference from localStorage
    const saved_theme = localStorage.getItem('ims_user_theme') || 'dark-theme';
    
    // 2. Apply initial state to body
    document.body.classList.add(saved_theme);
    
    // Sync HTML attribute for CSS variables if needed
    document.documentElement.setAttribute('data-theme', saved_theme === 'light-theme' ? 'light' : 'dark');

    toggle_btn.addEventListener('click', () => {
        this_toggle_system_theme();
    });
}

function this_toggle_system_theme() {
    const body = document.body;
    const is_dark = !body.classList.contains('light-mode');
    
    if (is_dark) {
        body.classList.add('light-mode');
        localStorage.setItem('ims_user_theme', 'light-mode');
        document.documentElement.setAttribute('data-theme', 'light');
    } else {
        body.classList.remove('light-mode');
        localStorage.setItem('ims_user_theme', 'dark-theme');
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}
