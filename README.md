# SecurityFirm_Inventory - IMS Phase-V (Tactical Command)

## 🛡️ Project Overview
A specialized, enterprise-grade **Inventory Management System (IMS)** designed for high-stakes security firm operations. This **"Modern Navy"** tactical platform features a hardened backend architecture and a high-contrast, low-fatigue operator interface. It is optimized for real-time asset tracking, personnel readiness monitoring, and compliance auditing.

---

## 🚀 Key Technical Features
- **Rigid Grid Shell Architecture:** Enforced `MainLayoutWrapper` grid with isolated `LeftSidebarPanel` and independently scrollable `RightSideViewport`.
- **Advanced Collapsible Sidebar:** High-performance transition engine between expanded (260px) and collapsed (70px) navigation states.
- **Hardware-Accelerated UI:** Premium interactive micro-interactions, cubic-bezier transitions, and hardware-level hover elevation effects.
- **Hardened Data Bridge:** Isolated `config.php` with native PDO query processing (`EMULATE_PREPARES => false`).
- **Security Logic Engine:** Multi-layer authentication featuring System-wide Audit Logging, CSRF protection, and RBAC.
- **Real-Time Telemetry:** Live "HUD" metrics tracking Field Force strength, Supply Risk, and Blacklist status.

---

## 🛠️ System Architecture (Consolidated)
```text
IMS/
├── config.php                 # Core System Parameters
├── index.php                  # Root Redirect
├── login.php                  # Authorization Gateway
├── dashboard.php              # Command KPI Center
├── api/                       # Async Request Handlers
├── assets/                    # Tactical CSS/JS & PascalCase Theme Engine
├── auth/                      # Authentication Engine
├── database/                  # SQL Schema (v5.1)
├── includes/                  # Core Dependencies & PDO Bridge
├── inventory/                 # Asset Management Modules
├── operations/                # Attendance & Field Logs
├── payroll/                   # Financial Processing
└── personnel/                 # Force Management & Bharti Terminal
```

---

## 📦 Local Deployment & Setup
1. **Environment:** Ensure XAMPP/WAMP is running (PHP 8.x + MariaDB/MySQL).
2. **Database:** Create a database named `SecurityFirm_Inventory`.
3. **Schema:** Import `database/schema.sql`.
4. **Configuration:** Verify credentials in `config.php`.
5. **Access:** Navigate to `http://localhost/IMS/` in your browser.

---

## 🔑 Authorized Access (Default)
| Role | Operator ID | Passkey |
| :--- | :--- | :--- |
| **System Admin** | `ADMIN_SECURE` | `Password@123` |

---
*Developed for Security Firm Simulation & Educational Purposes.*
