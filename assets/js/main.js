<?php
/**
 * ASMS UI CORE CONTROLLER - Lightweight Utility
 * Handles Sidebar Drawer States and Persistence.
 */

document.addEventListener('DOMContentLoaded', () => {
    this_init_sidebar_persistence();
    this_bind_sidebar_events();
    this_init_mobile_nav();
});

function this_init_sidebar_persistence() {
    const is_collapsed = localStorage.getItem('asms_sidebar_state') === 'collapsed';
    if (is_collapsed) {
        document.body.classList.add('sidebar-collapsed');
        const sidebar = document.getElementById('tactical-sidebar');
        if(sidebar) sidebar.classList.add('collapsed');
    }
    
    const theme = localStorage.getItem('asms_theme_preference') || 'dark';
    if (theme === 'light') {
        document.body.classList.add('light-mode');
    }
}

function this_bind_sidebar_events() {
    const toggle_btn = document.querySelector('.sidebar-toggle-btn');
    const sidebar = document.getElementById('tactical-sidebar');
    
    if (toggle_btn) {
        toggle_btn.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            if(sidebar) sidebar.classList.toggle('collapsed');
            
            const state = document.body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded';
            localStorage.setItem('asms_sidebar_state', state);
        });
    }
}

function this_init_mobile_nav() {
    const mobile_toggle = document.getElementById('mobile-nav-toggle');
    const sidebar = document.getElementById('tactical-sidebar');
    
    if (mobile_toggle && sidebar) {
        mobile_toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
        
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !mobile_toggle.contains(e.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    }
}

function this_toggle_system_theme() {
    const body = document.body;
    body.classList.toggle('light-mode');
    const theme_state = body.classList.contains('light-mode') ? 'light' : 'dark';
    localStorage.setItem('asms_theme_preference', theme_state);
}

function this_toggle_sidebar() {
    const sidebar = document.getElementById('tactical-sidebar');
    document.body.classList.toggle('sidebar-collapsed');
    if(sidebar) sidebar.classList.toggle('collapsed');
}

function this_toggle_system_theme_legacy() {
    this_toggle_system_theme();
}
