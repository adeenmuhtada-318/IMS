<?php
/**
 * UNIFIED TACTICAL SIDEBAR - Fixed Routing
 * Location: C:/xampp/htdocs/IMS/includes/sidebar.php
 */
?>
<div class="TacticalSidebar">
    <div class="BrandBlock">
        <div class="BrandTitle">FAST SECURITY</div>
        <div class="BrandSub">OPERATIONAL CONTROL</div>
    </div>
    
    <nav class="NavigationMenu">
        <a href="/IMS/dashboard.php" class="NavLink">
            <span class="NavIcon">📊</span> Dashboard
        </a>
        <a href="/IMS/modules/staff/manage_guards.php" class="NavLink">
            <span class="NavIcon">👥</span> Personnel Registry
        </a>
        <a href="/IMS/modules/attendance/manage_attendance.php" class="NavLink">
            <span class="NavIcon">📋</span> Attendance Logs
        </a>
        <a href="/IMS/modules/payroll/payroll_dashboard.php" class="NavLink">
            <span class="NavIcon">💰</span> Payroll Engine
        </a>
        <a href="/IMS/modules/recruitment/recruitment_form.php" class="NavLink">
            <span class="NavIcon">📝</span> New Recruitment
        </a>
    </nav>
    
    <div class="SystemStatus">
        <span class="StatusDot"></span> SYSTEM SECURE
    </div>
</div>

<style>
    .TacticalSidebar {
        width: 250px; min-width: 250px;
        background: #070A11; border-right: 1px solid rgba(255, 255, 255, 0.05);
        display: flex; flex-direction: column; padding: 30px 20px; height: 100vh; position: sticky; top: 0;
    }
    .BrandBlock { margin-bottom: 45px; padding-left: 10px; }
    .BrandTitle { font-size: 1.3rem; font-weight: 900; letter-spacing: 2px; color: #F97316; }
    .BrandSub { font-size: 0.65rem; color: #94A3B8; text-transform: uppercase; margin-top: 5px; }

    .NavigationMenu { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .NavLink {
        display: flex; align-items: center; padding: 14px 16px;
        color: #F8FAFC; text-decoration: none; border-radius: 8px;
        font-size: 0.9rem; font-weight: 600; transition: 0.2s ease;
    }
    .NavLink:hover { background: #1E293B; color: #F97316; }
    .NavIcon { margin-right: 15px; font-size: 1.2rem; }

    .SystemStatus { font-size: 0.65rem; color: #94A3B8; display: flex; align-items: center; gap: 8px; margin-top: auto; }
    .StatusDot { width: 6px; height: 6px; background: #10B981; border-radius: 50%; box-shadow: 0 0 8px #10B981; }
</style>
