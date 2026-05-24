<?php
/**
 * GUARD PERSONNEL MANAGEMENT - REFACTORED V5.2
 * Features: Pagination, Filtering, Search, & RBAC Data Masking
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';
include '../includes/header.php';

// Show success message if guard was added
if (isset($_GET['status']) && $_GET['status'] === 'added') {
    echo "<script>alert('SUCCESS: New operator enrolled in the force.');</script>";
}

// 1. PARAMETER INGESTION
$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$page = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT));
$limit = 20;
$offset = ($page - 1) * $limit;

// 2. DYNAMIC QUERY BUILDING
$where = ["is_deleted = 0"];
$params = [];

if ($search) {
    $where[] = "(full_name LIKE ? OR guard_no LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_status) {
    $where[] = "duty_status = ?";
    $params[] = $filter_status;
}

$where_clause = implode(" AND ", $where);

try {
    // Total count for pagination
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM guards_personnel WHERE $where_clause");
    $stmt_count->execute($params);
    $total_records = $stmt_count->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // Main fetch
    $sql = "SELECT guard_id, guard_no, full_name, designation, guard_phone, home_district, duty_status 
            FROM guards_personnel 
            WHERE $where_clause 
            ORDER BY full_name ASC 
            LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $guards = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("GUARD_FETCH_ERROR: " . $e->getMessage());
    $guards = [];
}
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <div class="branding">
            <h1>Personnel Roster</h1>
            <p class="sub-text">IMS Phase-V: Operational Roster</p>
        </div>
        <div class="actions">
            <a href="bharti_form.php" class="btn-tactical btn-primary">
                Enroll New Guard
            </a>
        </div>
    </header>

    <!-- SEARCH & FILTER BAR -->
    <section class="filter-bar glass-panel">
        <form method="GET" class="filter-form">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Name / Guard ID...">
            </div>
            <select name="status" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Active Duty" <?php echo $filter_status === 'Active Duty' ? 'selected' : ''; ?>>Active Duty</option>
                <option value="Off Duty" <?php echo $filter_status === 'Off Duty' ? 'selected' : ''; ?>>Off Duty</option>
                <option value="On Leave" <?php echo $filter_status === 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
            </select>
            <button type="submit" class="btn-tactical">Apply Filters</button>
        </form>
    </section>

    <!-- ROSTER TABLE -->
    <section class="data-panel glass-panel">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Guard ID</th>
                        <th>Full Name</th>
                        <th>Designation</th>
                        <th>Home District</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($guards)): ?>
                        <tr><td colspan="6" class="empty-state">No records match criteria</td></tr>
                    <?php else: ?>
                        <?php foreach ($guards as $g): ?>
                            <tr>
                                <td class="monospaced"><?php echo htmlspecialchars($g['guard_no']); ?></td>
                                <td style="font-weight: 700;"><?php echo htmlspecialchars($g['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($g['designation']); ?></td>
                                <td><?php echo htmlspecialchars($g['home_district']); ?></td>
                                <td>
                                    <div class="status-toggle-wrapper">
                                        <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $g['duty_status'])); ?>">
                                            <?php echo $g['duty_status']; ?>
                                        </span>
                                        <button class="btn-toggle" title="Toggle Duty Status" onclick="this_toggle_duty(<?php echo $g['guard_id']; ?>, '<?php echo $g['duty_status']; ?>')">
                                            <i class="fa-solid fa-rotate"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="action-cell">
                                    <a href="view_guard.php?id=<?php echo $g['guard_id']; ?>" class="btn-icon" title="View Profile"><i class="fa-solid fa-user-shield"></i></a>
                                    <button class="btn-icon" title="Edit Record"><i class="fa-solid fa-user-pen"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>" class="<?php echo $page === $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </section>
</div>

<style>
.filter-bar { padding: 15px; margin-bottom: 20px; }
.filter-form { display: flex; gap: 15px; align-items: center; }
.search-box { flex: 1; position: relative; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-dim); }
.search-box input { width: 100%; padding: 8px 12px 8px 35px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-dim); border-radius: 4px; color: #fff; }

.status-toggle-wrapper { display: flex; align-items: center; gap: 8px; }
.btn-toggle { background: none; border: none; color: var(--accent-cyan); cursor: pointer; font-size: 0.8rem; opacity: 0.5; transition: opacity 0.2s; }
.btn-toggle:hover { opacity: 1; }

.empty-state { text-align: center; padding: 40px; color: var(--text-dim); letter-spacing: 2px; }
.pagination { padding: 15px; display: flex; justify-content: center; gap: 8px; border-top: 1px solid var(--border-dim); }
.pagination a { padding: 5px 12px; border: 1px solid var(--border-dim); color: var(--text-dim); text-decoration: none; border-radius: 3px; font-size: 0.8rem; }
.pagination a.active { background: var(--accent-cyan); color: #000; border-color: var(--accent-cyan); }
</style>

<script>
async function this_toggle_duty(id, current) {
    const next = current === 'Active Duty' ? 'Off Duty' : 'Active Duty';
    if (!confirm(`CONFIRM: Change status to ${next.toUpperCase()}?`)) return;

    try {
        const resp = await fetch('../api/api_router.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'toggle_duty', guard_id: id})
        });
        const res = await resp.json();
        if (res.status === 'success') window.location.reload();
    } catch (e) { alert('TOGGLE_FAILED'); }
}
function tactical_open_modal(id) { document.getElementById(id).style.display = 'flex'; }
</script>

<?php include '../includes/footer.php'; ?>
