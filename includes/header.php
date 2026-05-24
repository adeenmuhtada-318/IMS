<?php
if (!defined('BASE_PATH')) {
    // detect depth
    $current = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $base    = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $depth   = substr_count(str_replace($base, '', $current), '/') - 1;
    $prefix  = $depth > 0 ? str_repeat('../', $depth) : '';
}

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
    <title>FAST Security IMS</title>
    <link rel="stylesheet" href="<?= $prefix ?>assets/css/tactical_core.css">
    <link rel="stylesheet" href="<?= $prefix ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dark-theme">

    <!-- 0. SYSTEM HUD TOP BAR (Fixed Telemetry) -->
    <div id="system-hud-bar">
        <div class="hud-left">
            <span class="hud-logo-text">Secure IMS</span>
        </div>
        <div class="hud-center">
            <div class="hud-status-node">
                <span class="pulse-dot"></span>
                <span class="hud-status-text">System Status: Active</span>
            </div>
        </div>
        <div class="hud-right">
            <div class="hud-telemetry">Field Force: <span id="hud-ff" class="hud-val">--</span></div>
            <div class="hud-telemetry">Supply Risk: <span id="hud-sr" class="hud-val">--</span></div>
            <div class="hud-telemetry">Blacklist: <span id="hud-bl" class="hud-val">--</span></div>
        </div>
    </div>

    <style>
        #system-hud-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 35px;
            background: rgba(10, 17, 40, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 251, 255, 0.2);
            z-index: 9999;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 40px;
            font-family: 'Inter', sans-serif;
            color: #fff;
        }
        .hud-logo-text { font-weight: 800; letter-spacing: 2px; color: var(--accent-cyan); font-size: 0.75rem; }
        .hud-status-node { display: flex; align-items: center; gap: 10px; }
        .pulse-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; box-shadow: 0 0 10px #22c55e; animation: hud-pulse 2s infinite; }
        @keyframes hud-pulse { 0% { transform: scale(0.95); opacity: 0.7; } 70% { transform: scale(1.1); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.7; } }
        .hud-status-text { font-size: 0.65rem; font-weight: 700; letter-spacing: 1px; color: #22c55e; }
        .hud-right { display: flex; gap: 30px; }
        .hud-telemetry { font-size: 0.65rem; font-weight: 600; color: var(--text-dim); }
        .hud-val { color: var(--accent-cyan); margin-left: 5px; }
        
        /* Adjust main container for HUD height */
        #app-layout-container { margin-top: 35px; height: calc(100vh - 35px); }
    </style>

    <script>
        function updateHUD() {
            fetch('<?= $prefix ?>api/api_router.php?action=dashboard_stats')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('hud-ff').innerText = data.field_force || 0;
                    document.getElementById('hud-sr').innerText = data.supply_risk || 0;
                    document.getElementById('hud-bl').innerText = data.blacklist || 0;
                })
                .catch(err => console.error('HUD_SYNC_ERROR:', err));
        }
        setInterval(updateHUD, 30000);
        document.addEventListener('DOMContentLoaded', updateHUD);
    </script>

<div id="app-layout-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-workspace-window">
