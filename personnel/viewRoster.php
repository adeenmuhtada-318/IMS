<?php
/**
 * PERSONNEL RECORDS DIRECTORY - MODERN NAVY UI
 * Refactored View: Roster Audit & Personnel Management
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';

// Show success message if guard was added (Logic preserved)
if (isset($_GET['status']) && $_GET['status'] === 'added') {
    echo "<script>alert('SUCCESS: New operator enrolled in the force.');</script>";
}

// 1. PARAMETER INGESTION (Logic preserved)
$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$page = max(1, filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT));
$limit = 20;
$offset = ($page - 1) * $limit;

// 2. DYNAMIC QUERY BUILDING (Logic preserved)
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel Roster | Fast Security IMS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* INLINE COMPONENT OVERRIDES */
        .StatusBadge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .StatusActive { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .StatusOff { background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); }
        .StatusLeave { background: rgba(255, 107, 0, 0.1); color: #ff6b00; border: 1px solid rgba(255, 107, 0, 0.2); }

        .ActionIconBtn {
            color: var(--TextDim);
            font-size: 1.1rem;
            margin-right: 12px;
            transition: color 0.2s ease;
        }
        .ActionIconBtn:hover { color: var(--VoltCyan); }

        .PaginationWrapper {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 24px;
        }
        .PageLink {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--BorderDeep);
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }
        .PageLink.ActivePage { background: var(--VoltCyan); color: #000; border-color: var(--VoltCyan); }
        .PageLink:hover:not(.ActivePage) { border-color: var(--VoltCyan); color: var(--VoltCyan); }

        /* Filter Block Sync */
        .FilterControlBlock {
            margin-bottom: 32px;
        }
    </style>
</head>
<body class="DarkMode">

    <div id="MainLayoutWrapper">
        
        <!-- SIDEBAR NAVIGATION PANEL -->
        <aside id="LeftSidebarPanel">
            <div class="SidebarBrandingArea">
                <div class="BrandingTitle">FAST SECURITY IMS</div>
                <button id="SidebarToggleAction">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <nav class="NavigationLinkList">
                <div class="NavigationItem">
                    <a href="../dashboard.php" class="NavigationAnchor">
                        <span class="MenuIconNode">📊</span>
                        <span class="MenuTextLabel">Dashboard</span>
                    </a>
                </div>
                <div class="NavigationItem">
                    <a href="onboarding.php" class="NavigationAnchor ActiveMenuItem">
                        <span class="MenuIconNode">👥</span>
                        <span class="MenuTextLabel">Human Resource Portal</span>
                    </a>
                </div>
            </nav>

            <div class="UserStatusComponent">
                <div class="OperatorAccountHeader">Operator Account</div>
                <span class="SystemActiveFlag">SYSTEM ACTIVE</span>
            </div>
        </aside>

        <!-- MAIN WORKSPACE VIEWPORT -->
        <main id="RightSideViewport">
            
            <div class="ThemeModeToggle" id="ThemeToggleBtn">
                <i class="fa-solid fa-circle-half-stroke"></i>
                <span>Switch Theme</span>
            </div>

            <div class="PortalIdentityBlock">
                <h1 class="HubTitleHeading">Personnel Records Directory</h1>
                <p class="HubSubText">View, audit, and manage master roster profiles with real-time operational logs.</p>
            </div>

            <!-- MODERN SEARCH & FILTER CONTROLS -->
            <section class="FilterControlBlock">
                <form method="GET" style="display: flex; width: 100%; gap: 20px;">
                    <div style="flex: 1; position: relative;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--TextDim);"></i>
                        <input type="text" name="search" class="ModernInput" style="width: 100%; padding-left: 45px;" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Full Name or Guard ID...">
                    </div>
                    <select name="status" class="ModernInput" style="width: 200px;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Active Duty" <?php echo $filter_status === 'Active Duty' ? 'selected' : ''; ?>>Active Duty</option>
                        <option value="Off Duty" <?php echo $filter_status === 'Off Duty' ? 'selected' : ''; ?>>Off Duty</option>
                        <option value="On Leave" <?php echo $filter_status === 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
                    </select>
                    <button type="submit" class="PrimaryActionButton" style="padding: 12px 24px; font-size: 0.85rem;">Execute Search</button>
                </form>
            </section>

            <!-- ROSTER DATA TABLE REFINEMENT -->
            <div class="DataMatrixContainer">
                <table class="RosterDataTable">
                    <thead>
                        <tr>
                            <th>Guard ID</th>
                            <th>Full Name</th>
                            <th>Designation</th>
                            <th>Home District</th>
                            <th>Duty Status</th>
                            <th style="text-align: right;">Record Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($guards)): ?>
                            <tr><td colspan="6" style="text-align: center; padding: 60px; color: var(--TextDim);">NO RECORDS DETECTED IN ACTIVE DATABASE</td></tr>
                        <?php else: ?>
                            <?php foreach ($guards as $g): ?>
                                <tr class="RosterRow" onclick="window.location.href='view_guard.php?id=<?php echo $g['guard_id']; ?>'">
                                    <td style="font-family: 'Courier New', monospace; font-weight: 700; color: var(--VoltCyan);"><?php echo htmlspecialchars($g['guard_no']); ?></td>
                                    <td style="font-weight: 700;"><?php echo htmlspecialchars($g['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($g['designation']); ?></td>
                                    <td><?php echo htmlspecialchars($g['home_district']); ?></td>
                                    <td>
                                        <?php 
                                            $badge_class = 'StatusOff';
                                            if($g['duty_status'] === 'Active Duty') $badge_class = 'StatusActive';
                                            if($g['duty_status'] === 'On Leave') $badge_class = 'StatusLeave';
                                        ?>
                                        <span class="StatusBadge <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($g['duty_status']); ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="view_guard.php?id=<?php echo $g['guard_id']; ?>" class="ActionIconBtn" title="View Profile"><i class="fa-solid fa-user-shield"></i></a>
                                        <button class="ActionIconBtn" title="Edit Profile" onclick="event.stopPropagation();"><i class="fa-solid fa-user-pen"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- PAGINATION SYSTEM -->
                <?php if ($total_pages > 1): ?>
                <div class="PaginationWrapper">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($filter_status); ?>" 
                           class="PageLink <?php echo $page === $i ? 'ActivePage' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
        // SIDEBAR TOGGLE MECHANISM
        const toggleBtn = document.getElementById('SidebarToggleAction');
        const mainWrapper = document.getElementById('MainLayoutWrapper');

        toggleBtn.addEventListener('click', () => {
            mainWrapper.classList.toggle('SidebarCollapsed');
        });

        // THEME ENGINE
        const themeBtn = document.getElementById('ThemeToggleBtn');
        const body = document.body;

        themeBtn.addEventListener('click', () => {
            if (body.classList.contains('DarkMode')) {
                body.classList.remove('DarkMode');
                body.classList.add('LightMode');
                localStorage.setItem('ThemePreference', 'LightMode');
            } else {
                body.classList.remove('LightMode');
                body.classList.add('DarkMode');
                localStorage.setItem('ThemePreference', 'DarkMode');
            }
        });

        // INITIALIZE THEME
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('ThemePreference');
            if (savedTheme === 'LightMode') {
                body.classList.remove('DarkMode');
                body.classList.add('LightMode');
            }
        });
    </script>
</body>
</html>
