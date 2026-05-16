<?php
/**
 * PROCUREMENT CENTER - Internal Asset Ingestion
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAST Security | Armory Procurement</title>
    <link rel="stylesheet" href="assets/css/tactical_core.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-theme">

    <div id="app-layout-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-workspace-window">
            <header style="margin-bottom: 40px;">
                <h1 style="letter-spacing: 4px; color: var(--text-primary);">INTERNAL_PROCUREMENT</h1>
                <p style="color: var(--accent-cyan); font-size: 0.75rem; letter-spacing: 3px; font-weight: 600; text-transform: uppercase;">Asset Ingestion Gateway : Authorized Only</p>
            </header>

            <div class="asset-tabs" style="margin-bottom: 30px; border-bottom: 1px solid var(--border-frost);">
                <button class="tab-btn active" id="tab-op" onclick="this_switch_procurement_view('operational')">OPERATIONAL_ASSETS</button>
                <button class="tab-btn" id="tab-ap" onclick="this_switch_procurement_view('apparel')">APPAREL_&_UNIFORMS</button>
                <button class="tab-btn" id="tab-lo" onclick="this_switch_procurement_view('logistics')">OFFICE_&_LOGISTICS</button>
            </div>

            <form id="procurement-form" class="glass-panel" style="padding: 40px;">
                
                <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">01. BASE_METRICS</h3>
                <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="input-group">
                        <label>ITEM NAME</label>
                        <input type="text" name="asset_name" class="glass-input" required>
                    </div>
                    <div class="input-group">
                        <label>SKU / CODE</label>
                        <input type="text" name="sku" class="glass-input" required>
                    </div>
                    <div class="input-group">
                        <label>UNIT COST (PKR)</label>
                        <input type="number" name="purchase_cost" class="glass-input" step="0.01" required>
                    </div>
                    <div class="input-group">
                        <label>MIN_THRESHOLD</label>
                        <input type="number" name="min_threshold" class="glass-input" value="5" required>
                    </div>
                    <div class="input-group">
                        <label>INITIAL STOCK</label>
                        <input type="number" name="current_stock" class="glass-input" value="1" required>
                    </div>
                </div>

                <div id="conditional-slot-container" style="margin-top: 40px; border-top: 1px solid var(--border-frost); padding-top: 30px;">
                    
                    <div id="slot-operational" class="dynamic-field-wrapper">
                        <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">02. WEAPONRY_SPECIFICATIONS</h3>
                        <input type="hidden" name="category_type" value="operational">
                        <input type="hidden" name="category_id" value="1">
                        <input type="hidden" name="tracking_type" value="serialized">
                        
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                            <div class="input-group">
                                <label>SERIAL NUMBER</label>
                                <input type="text" name="serial_number" class="glass-input">
                            </div>
                            <div class="input-group">
                                <label>BORE / CALIBER</label>
                                <select name="bore_caliber" class="glass-input">
                                    <option value="12 bore">12 Bore</option>
                                    <option value="30 bore">30 Bore</option>
                                    <option value=".22">.22 Cal</option>
                                    <option value="223 bore">223 Bore</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>LICENSE_NO</label>
                                <input type="text" name="license_number" class="glass-input">
                            </div>
                        </div>
                    </div>

                    <div id="slot-apparel" class="dynamic-field-wrapper" style="display: none; opacity: 0;">
                        <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">02. APPAREL_DIMENSIONS</h3>
                        <input type="hidden" name="category_type" value="apparel">
                        <input type="hidden" name="category_id" value="2">
                        <input type="hidden" name="tracking_type" value="bulk">
                        
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="input-group">
                                <label>SIZE</label>
                                <select name="item_size" class="glass-input">
                                    <option value="S">S</option><option value="M">M</option><option value="L">L</option><option value="XL">XL</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>MATERIAL</label>
                                <input type="text" name="material_type" class="glass-input">
                            </div>
                        </div>
                    </div>

                    <div id="slot-logistics" class="dynamic-field-wrapper" style="display: none; opacity: 0;">
                        <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">02. LOGISTICS_&_OFFICE</h3>
                        <input type="hidden" name="category_type" value="office">
                        <input type="hidden" name="category_id" value="4">
                        <input type="hidden" name="tracking_type" value="serialized">
                        
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="input-group">
                                <label>ASSET_TYPE</label>
                                <input type="text" name="asset_type_detail" class="glass-input">
                            </div>
                            <div class="input-group">
                                <label>LOCATION</label>
                                <input type="text" name="location_room" class="glass-input">
                            </div>
                        </div>
                    </div>

                </div>

                <div style="margin-top: 50px; display: flex; justify-content: flex-end; gap: 20px;">
                    <button type="submit" class="btn-fast btn-primary">AUTHORIZE_PROCUREMENT_INGESTION</button>
                </div>
            </form>
        </main>
    </div>

    <script src="assets/js/theme_controller.js"></script>
    <script src="assets/js/procurement_controller.js"></script>
</body>
</html>
