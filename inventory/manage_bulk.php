<?php
/**
 * BULK INVENTORY MANAGEMENT - TACTICAL IMS
 * Features: Stock monitoring, reorder alerts, and replenishment logging.
 */

session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';

// Handle Soft Delete
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("UPDATE Bulk_Inventory SET is_deleted = 1 WHERE Item_ID = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: manage_bulk.php?status=deleted");
    exit();
}

// Handle Add New Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_item') {
    try {
        $sql = "INSERT INTO Bulk_Inventory (item_name, item_category, Quantity_On_Hand, reorder_level, unit) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['item_name'],
            $_POST['item_category'],
            (int)$_POST['quantity'],
            (int)$_POST['reorder_level'],
            $_POST['unit']
        ]);
        header("Location: manage_bulk.php?status=added");
        exit();
    } catch (PDOException $e) {
        $error = "DATABASE_FAILURE: Unable to register new item.";
    }
}

// Fetch Items
$stmt = $pdo->query("SELECT * FROM Bulk_Inventory WHERE is_deleted = 0 ORDER BY item_name ASC");
$items = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <div class="branding">
            <h1>Bulk Logistics Central</h1>
            <p class="sub-text">IMS Phase-V | Apparel & Consumable Monitoring</p>
        </div>
        <div class="actions">
            <button class="btn-tactical btn-primary" onclick="document.getElementById('add-item-modal').style.display='flex'">
                Add New Item Batch
            </button>
        </div>
    </header>

    <!-- DATA TABLE -->
    <section class="glass-panel data-panel">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Quantity on Hand</th>
                        <th>Reorder Threshold</th>
                        <th>Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-dim); padding: 40px;">No bulk items found in system</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <?php $is_critical = ($item['Quantity_On_Hand'] <= $item['reorder_level']); ?>
                            <tr>
                                <td style="font-weight: 700;"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['item_category']); ?></td>
                                <td class="monospaced <?php echo $is_critical ? 'status-alert' : ''; ?>" style="font-weight: 800; font-size: 1.1rem;">
                                    <?php if($is_critical): ?><i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i><?php endif; ?>
                                    <?php echo $item['Quantity_On_Hand']; ?>
                                </td>
                                <td class="monospaced"><?php echo $item['reorder_level']; ?></td>
                                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                <td class="action-cell">
                                    <button class="btn-icon" title="Update Stock"><i class="fa-solid fa-plus-minus"></i></button>
                                    <a href="?delete_id=<?php echo $item['Item_ID']; ?>" class="btn-icon" style="color: var(--alert-orange);" onclick="return confirm('Confirm Removal: Delete this item category?')" title="Delete">
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

<!-- ADD ITEM MODAL -->
<div id="add-item-modal" class="modal-overlay">
    <div class="glass-panel modal-card">
        <h2 style="color: var(--accent-cyan); margin-bottom: 25px; letter-spacing: 2px;">Add New Logistics Item</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_item">
            <div class="input-group">
                <label>Item Description / Name</label>
                <input type="text" name="item_name" class="glass-input" required placeholder="e.g. Tactical Shirt (Large)">
            </div>
            <div class="input-group">
                <label>Category</label>
                <select name="item_category" class="glass-input" required>
                    <option value="Uniform">Uniform</option>
                    <option value="Footwear">Footwear</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Stationery">Stationery</option>
                    <option value="Maintenance">Maintenance</option>
                </select>
            </div>
            <div class="form-row">
                <div class="input-group">
                    <label>Initial Quantity</label>
                    <input type="number" name="quantity" class="glass-input" value="0" required>
                </div>
                <div class="input-group">
                    <label>Reorder Level</label>
                    <input type="number" name="reorder_level" class="glass-input" value="10" required>
                </div>
            </div>
            <div class="input-group">
                <label>Unit of Measure</label>
                <input type="text" name="unit" class="glass-input" value="Pcs">
            </div>
            <div style="display: flex; gap: 20px; margin-top: 30px;">
                <button type="submit" class="btn-authorize" style="flex: 1;">Add Item</button>
                <button type="button" class="btn-authorize" onclick="document.getElementById('add-item-modal').style.display='none'" style="flex: 1; border-color: var(--text-dim); color: var(--text-dim);">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.status-alert { color: var(--alert-orange) !important; animation: blink 1.5s infinite; }
@keyframes blink { 50% { opacity: 0.5; } }

.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(8px); }
.modal-card { width: 500px; padding: 40px; box-shadow: 0 0 30px rgba(0,0,0,0.5); }
</style>

<?php include '../includes/footer.php'; ?>
