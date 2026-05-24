<?php
/**
 * ALERT TICKER PARTIAL - PRODUCTION V5
 * Logic: Fetches expiring assets from view, filters dismissed ones, and caps output.
 */

$user_id = $_SESSION['user_id'];
$window  = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'expiry_window_days'")->fetchColumn() ?: 30;

// FETCH UN-DISMISSED ALERTS
$sql_alerts = "
    SELECT v.* 
    FROM vw_expiring_assets v
    LEFT JOIN dismissed_alerts d ON 
        d.alert_key COLLATE utf8mb4_general_ci = CONCAT(v.Category, '_', v.Identifier, '_', v.Expiry) COLLATE utf8mb4_general_ci AND 
        d.user_id = ?
    WHERE v.Expiry BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL $window DAY)
    AND d.id IS NULL
    ORDER BY v.Expiry ASC
    LIMIT 50";

$stmt_alerts = $pdo->prepare($sql_alerts);
$stmt_alerts->execute([$user_id]);
$alerts = $stmt_alerts->fetchAll();

if (!empty($alerts)): ?>
<section class="alert-ticker-container" id="global-alert-ticker">
    <div class="ticker-header">Critical Expiration Alerts [Threshold: <?php echo $window; ?> Days]</div>
    <div class="ticker-content">
        <?php foreach ($alerts as $a): ?>
            <div class="alert-node" data-type="<?php echo $a['Category']; ?>" data-item="<?php echo htmlspecialchars($a['Identifier']); ?>" data-expiry="<?php echo $a['Expiry']; ?>">
                <span class="alert-type">[<?php echo ucwords(strtolower($a['Category'])); ?>]</span>
                <span class="alert-item"><?php echo htmlspecialchars($a['Identifier']); ?></span>
                <span class="alert-date">Expires: <?php echo date('d.M.Y', strtotime($a['Expiry'])); ?></span>
                <button class="btn-dismiss" onclick="this_dismiss_alert(this)">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
async function this_dismiss_alert(btn) {
    const node = btn.closest('.alert-node');
    const data = {
        action: 'dismiss_alert',
        item_type: node.dataset.type,
        item_name: node.dataset.item,
        expiry: node.dataset.expiry
    };

    try {
        const resp = await fetch('<?= $prefix ?>api/api_router.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const res = await resp.json();
        if (res.status === 'success') {
            node.remove();
            if (document.querySelectorAll('.alert-node').length === 0) {
                document.getElementById('global-alert-ticker').remove();
            }
        }
    } catch (e) { console.error('DISMISS_FAILED'); }
}
</script>
<?php endif; ?>
