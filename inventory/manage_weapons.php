<?php
/**
 * WEAPON ARMORY MANAGEMENT - TACTICAL IMS
 * Features: Status tracking, license monitoring, and asset registration.
 */

session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';

// Handle Soft Delete
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("UPDATE Individual_Weapons SET is_deleted = 1 WHERE Weapon_ID = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: manage_weapons.php?status=deleted");
    exit();
}

// Handle Add New Weapon
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_weapon') {
    try {
        $sql = "INSERT INTO Individual_Weapons (weapon_serial, weapon_type, weapon_model, Status, expiry_date) 
                VALUES (?, ?, ?, 'Available', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['weapon_serial'],
            $_POST['weapon_type'],
            $_POST['weapon_model'],
            !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null
        ]);
        header("Location: manage_weapons.php?status=added");
        exit();
    } catch (PDOException $e) {
        $error = "CRITICAL_ERROR: Duplicate Serial Number or Database Failure.";
    }
}

// Filters
$filter_status = $_GET['status_filter'] ?? '';
$where = "is_deleted = 0";
$params = [];

if ($filter_status) {
    $where .= " AND Status = ?";
    $params[] = $filter_status;
}

$weapons = $pdo->prepare("SELECT * FROM Individual_Weapons WHERE $where ORDER BY created_at DESC");
$weapons->execute($params);
$weapon_list = $weapons->fetchAll();

include '../includes/header.php';
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <div class="branding">
            <h1 style="font-weight: 800; letter-spacing: 1px;">Weapon Inventory Control</h1>
            <p class="sub-text">IMS Phase-V | Ballistic Asset Tracking</p>
        </div>
        <div class="actions">
            <button class="btn-tactical btn-primary" onclick="document.getElementById('add-weapon-modal').style.display='flex'">
                Register New Weapon
            </button>
        </div>
    </header>

    <!-- FILTERS -->
    <section class="glass-panel" style="padding: 15px; margin-bottom: 25px;">
        <form method="GET" style="display: flex; gap: 20px; align-items: center;">
            <label style="color: var(--text-dim); font-size: 0.8rem; font-weight: 600;">Filter by Status:</label>
            <select name="status_filter" class="glass-input" style="width: 200px;" onchange="this.form.submit()">
                <option value="">All Weapons</option>
                <option value="Available" <?php echo $filter_status === 'Available' ? 'selected' : ''; ?>>Available</option>
                <option value="Assigned" <?php echo $filter_status === 'Assigned' ? 'selected' : ''; ?>>Assigned</option>
                <option value="Maintenance" <?php echo $filter_status === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
            </select>
        </form>
    </section>

    <!-- DATA TABLE -->
    <section class="glass-panel data-panel">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>Type</th>
                        <th>Model</th>
                        <th>Status</th>
                        <th>License Expiry</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($weapon_list)): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-dim); padding: 40px;">No weapons found in active inventory</td></tr>
                    <?php else: ?>
                        <?php foreach ($weapon_list as $w): ?>
                            <tr>
                                <td class="monospaced" style="font-weight: 700; color: var(--accent-cyan);"><?php echo htmlspecialchars($w['weapon_serial']); ?></td>
                                <td><?php echo htmlspecialchars($w['weapon_type']); ?></td>
                                <td><?php echo htmlspecialchars($w['weapon_model']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($w['Status']); ?>">
                                        <?php echo $w['Status']; ?>
                                    </span>
                                </td>
                                <td class="monospaced">
                                    <?php echo $w['expiry_date'] ? date('d-M-Y', strtotime($w['expiry_date'])) : 'Perpetual'; ?>
                                </td>
                                <td class="action-cell">
                                    <button class="btn-icon" title="Edit Weapon"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <a href="?delete_id=<?php echo $w['Weapon_ID']; ?>" class="btn-icon" style="color: var(--alert-orange);" onclick="return confirm('Confirm Decommission: This will soft-delete the weapon from active roster.')" title="Decommission">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- ADD WEAPON MODAL -->
<div id="add-weapon-modal" class="modal-overlay">
    <div class="glass-panel modal-card">
        <h2 style="color: var(--accent-cyan); margin-bottom: 25px; letter-spacing: 2px;">Register New Ballistic Unit</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_weapon">
            <div class="input-group">
                <label>Weapon Serial Number</label>
                <input type="text" name="weapon_serial" class="glass-input" required placeholder="e.g. GL-12345">
            </div>
            <div class="form-row">
                <div class="input-group">
                    <label>Weapon Type</label>
                    <select name="weapon_type" class="glass-input" required>
                        <option value="9mm Pistol">9mm Pistol</option>
                        <option value="12 Bore Shotgun">12 Bore Shotgun</option>
                        <option value="30 Bore Pistol">30 Bore Pistol</option>
                        <option value="SMG / Rifle">SMG / Rifle</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Model Name</label>
                    <input type="text" name="weapon_model" class="glass-input" placeholder="e.g. Glock 17">
                </div>
            </div>
            <div class="input-group">
                <label>License Expiry Date</label>
                <input type="date" name="expiry_date" class="glass-input">
            </div>
            <div style="display: flex; gap: 20px; margin-top: 30px;">
                <button type="submit" class="btn-authorize" style="flex: 1;">Register Weapon</button>
                <button type="button" class="btn-authorize" onclick="document.getElementById('add-weapon-modal').style.display='none'" style="flex: 1; border-color: var(--text-dim); color: var(--text-dim);">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.status-badge.available { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid #22c55e; }
.status-badge.assigned { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
.status-badge.maintenance { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid #f59e0b; }

.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(8px); }
.modal-card { width: 500px; padding: 40px; box-shadow: 0 0 30px rgba(0,0,0,0.5); }
</style>

<?php include '../includes/footer.php'; ?>
