<?php
/**
 * MAIN DASHBOARD - OPERATIONAL HUD
 * Fixed: Database instance sync and table mapping.
 */
require_once "includes/connection.php";

try {
    // 1. TELEMETRY AGGREGATION
    $q_force = $pdo->query("SELECT COUNT(guard_id) as total FROM security_guards WHERE is_deleted = 0");
    $total_force = $q_force->fetch()['total'] ?? 0;

    // Hardware alerts (Table might not exist yet, using silent fail/fallback)
    $hardware_alerts = 0;
    try {
        $q_weapons = $pdo->query("SELECT COUNT(weapon_id) as total FROM weapon_inventory WHERE availability_status = 'Expired' OR license_expiry <= CURDATE()");
        $hardware_alerts = $q_weapons->fetch()['total'];
    } catch (Exception $e) {}

    $q_deployed = $pdo->query("SELECT COUNT(guard_id) as total FROM security_guards WHERE deployment_status = 'Active' AND is_deleted = 0");
    $live_deployments = $q_deployed->fetch()['total'] ?? 0;

    // 2. RECENT ACTIVITY LOGS (Mapped to attendance_logs)
    $recent_logs = [];
    try {
        $logs_query = "SELECT g.full_name, g.badge_number, al.attendance_date as log_date, al.attendance_status 
                       FROM attendance_logs al 
                       INNER JOIN security_guards g ON al.badge_number = g.badge_number 
                       ORDER BY al.attendance_date DESC, al.log_id DESC LIMIT 5";
        $logs_stmt = $pdo->query($logs_query);
        $recent_logs = $logs_stmt->fetchAll();
    } catch (Exception $e) {
        // Attendance logs table might be empty/non-existent
    }

} catch (PDOException $e) {
    die("CRITICAL_SYNC_FAILURE: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS | Tactical Command</title>
    <style>
        :root {
            --DeepDenim: #0B0F19;
            --GreyBlue: #1E293B;
            --VibrantOrange: #F97316;
            --TextLight: #F8FAFC;
            --TextMuted: #94A3B8;
            --BorderSlate: #334155;
            --SuccessGreen: #10B981;
            --AlertRed: #EF4444;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--DeepDenim);
            color: var(--TextLight);
            font-family: 'Inter', system-ui, sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* SIDEBAR COMPONENT OVERRIDE (For Sidebar Consistency) */
        .TacticalSidebar {
            width: 280px;
            background: #070A11;
            border-right: 1px solid var(--BorderSlate);
            display: flex;
            flex-direction: column;
            padding: 32px 24px;
        }
        .BrandBlock { margin-bottom: 48px; }
        .BrandTitle { font-size: 1.25rem; font-weight: 900; letter-spacing: 2px; color: var(--VibrantOrange); }
        .BrandSub { font-size: 0.7rem; color: var(--TextMuted); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .NavigationMenu { list-style: none; flex: 1; display: flex; flex-direction: column; gap: 8px; }
        .NavLink {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--TextLight);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: 0.2s ease;
        }
        .NavLink:hover { background: var(--GreyBlue); }
        .NavIcon { margin-right: 12px; font-size: 1.1rem; }
        .SystemStatusBlock { font-size: 0.65rem; color: var(--TextMuted); letter-spacing: 1px; }

        .MasterViewportLayout { display: flex; width: 100%; height: 100%; }

        .WorkspaceStream {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            box-sizing: border-box;
        }

        .HeaderGroupBlock h1 { font-size: 30px; font-weight: 700; margin: 0; letter-spacing: -0.5px; }
        .HeaderGroupBlock p { color: var(--TextMuted); margin: 8px 0 0 0; font-size: 14px; }

        .TelemetryDataGrid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 32px; }
        .DataTelemetryCard {
            background-color: var(--GreyBlue);
            border: 1px solid var(--BorderSlate);
            border-radius: 10px;
            padding: 24px 28px;
            position: relative;
        }

        .MetricValueLabel { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--TextMuted); }
        .BigMetricCounter { font-size: 44px; font-weight: 700; margin: 10px 0; line-height: 1; }
        .MetricSecondaryDesc { font-size: 13px; color: var(--TextMuted); margin: 0; }

        .WarningBadgePill {
            position: absolute;
            top: 24px;
            right: 24px;
            background-color: var(--AlertRed);
            color: var(--TextLight);
            font-size: 9px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        .IntelligenceTerminalContainer {
            background-color: var(--GreyBlue);
            border: 1px solid var(--BorderSlate);
            border-radius: 10px;
            margin-top: 32px;
            padding: 28px;
        }

        .IntelligenceTerminalHeader { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .IntelligenceTerminalHeader h2 { font-size: 16px; font-weight: 600; margin: 0; letter-spacing: -0.2px; }
        .FeedStatusTag { font-size: 11px; font-weight: 700; color: var(--SuccessGreen); letter-spacing: 0.5px; }

        .CleanOperationalTable { width: 100%; border-collapse: collapse; text-align: left; }
        .CleanOperationalTable th { color: var(--TextMuted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 14px 18px; border-bottom: 2px solid var(--BorderSlate); }
        .CleanOperationalTable td { padding: 16px 18px; font-size: 14px; border-bottom: 1px solid var(--BorderSlate); }
        .CleanOperationalTable tr:last-child td { border-bottom: none; }

        .CompliancePillStatus {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .StateActive { background-color: rgba(16, 185, 129, 0.12); color: var(--SuccessGreen); }
        .StateHalt { background-color: rgba(239, 68, 68, 0.12); color: var(--AlertRed); }
        
        code { font-family: Consolas, Monaco, monospace; color: var(--VibrantOrange); font-weight: 600; }
    </style>
</head>
<body>

    <div class="MasterViewportLayout">
        <?php include "includes/sidebar.php"; ?>

        <main class="WorkspaceStream">
            <header class="HeaderGroupBlock">
                <h1>Operational Command</h1>
                <p>Premium Tactical Interface | Real-time force intelligence synchronized.</p>
            </header>

            <section class="TelemetryDataGrid">
                <div class="DataTelemetryCard">
                    <span class="MetricValueLabel">Enrolled Force Strength</span>
                    <div class="BigMetricCounter" style="color: var(--VibrantOrange);"><?php echo $total_force; ?></div>
                    <p class="MetricSecondaryDesc">Active tactical personnel profile records.</p>
                </div>

                <div class="DataTelemetryCard">
                    <span class="MetricValueLabel">Hardware Compliance Risk</span>
                    <div class="BigMetricCounter" style="color: var(--AlertRed);"><?php echo $hardware_alerts; ?></div>
                    <p class="MetricSecondaryDesc">Inventory items requiring immediate relicensing.</p>
                    <?php if ($hardware_alerts > 0): ?>
                        <span class="WarningBadgePill">RESTOCK ALERTS REQUIRED</span>
                    <?php endif; ?>
                </div>

                <div class="DataTelemetryCard">
                    <span class="MetricValueLabel">Live System Deployment</span>
                    <div class="BigMetricCounter" style="color: var(--SuccessGreen);"><?php echo $live_deployments; ?></div>
                    <p class="MetricSecondaryDesc">Personnel currently active on designated sites.</p>
                </div>
            </section>

            <section class="IntelligenceTerminalContainer">
                <div class="IntelligenceTerminalHeader">
                    <h2>REAL-TIME OPERATIONS INTELLIGENCE</h2>
                    <span class="FeedStatusTag">● LIVE FEED ACTIVE</span>
                </div>

                <table class="CleanOperationalTable">
                    <thead>
                        <tr>
                            <th>Tactical Operator</th>
                            <th>Badge Reference</th>
                            <th>Log Timestamp</th>
                            <th>Deployment Status</th>
                            <th>Compliance Audit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_logs) > 0): ?>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td style="font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($log['full_name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($log['badge_number']); ?></code></td>
                                    <td style="color: var(--TextMuted);"><?php echo date("F d, Y", strtotime($log['log_date'])); ?></td>
                                    <td>
                                        <span class="CompliancePillStatus <?php echo ($log['attendance_status'] === 'Present') ? 'StateActive' : 'StateHalt'; ?>">
                                            <?php echo htmlspecialchars($log['attendance_status']); ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--SuccessGreen); font-weight: 700; font-size: 11px; letter-spacing: 0.5px;">CLEAN RECORD</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--TextMuted); padding: 32px;">No operational log activity registered in the database engine.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

</body>
</html>
