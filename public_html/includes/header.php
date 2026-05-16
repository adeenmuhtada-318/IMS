<?php
/**
 * ASMS GLOBAL HEADER TEMPLATE
 * Architect: Senior UI Template Developer
 * 
 * DESIGN RATIONALE:
 * Centralizing the structural wrapper ensures that every page inherits 
 * the 'Global Page Lock' framework and responsive sidebar dynamics 
 * without redundant markup.
 */

// Safety check: All ASMS pages require an active session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASMS | FAST Security Services</title>
    
    <!-- MASTER DESIGN SYSTEM -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- EXTERNAL ASSETS (Tactical Icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo isset($_COOKIE['asms_theme']) && $_COOKIE['asms_theme'] === 'light' ? 'light-mode' : 'dark-theme'; ?>">

    <!-- 1. MASTER APP LAYOUT WRAPPER (Flex-Container) -->
    <div id="app-layout-container">
        
        <!-- 2. UNIFIED SIDEBAR COMPONENT -->
        <?php include('sidebar.php'); ?>

        <!-- 3. PRIMARY WORKSPACE WINDOW (Independent Scrolling) -->
        <main class="main-workspace-window">
            
            <!-- BRANDING HEADER (Persistent on all sub-pages) -->
            <header style="margin-bottom: 40px; display: flex; align-items: center; gap: 20px;">
                <div class="brand-logo" style="width: 45px; height: 45px; fill: var(--accent-cyan);">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                    </svg>
                </div>
                <div>
                    <h2 style="font-weight: 800; letter-spacing: 1px; color: var(--text-primary); margin: 0; font-size: 1.4rem;">FAST SECURITY</h2>
                    <span style="color: var(--accent-cyan); font-size: 0.7rem; letter-spacing: 3px; font-weight: 600; text-transform: uppercase;">Management System v4.5</span>
                </div>
            </header>
