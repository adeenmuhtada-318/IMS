# SecurityFirm_Inventory - IMS Phase-V (Tactical Command)

## 🛡️ Project Overview
A specialized, enterprise-grade **Inventory Management System (IMS)** designed for high-stakes security firm operations. This "Tactical Cyber-Dark" platform features a hardened backend architecture and a high-contrast, low-fatigue operator interface. It is optimized for real-time asset tracking, personnel readiness monitoring, and compliance auditing.

---

## 🚀 Key Technical Features
- **Hardened Data Bridge:** Isolated `config.php` with native PDO query processing (`EMULATE_PREPARES => false`).
- **Atomic Transactional Deployments:** Asset checkouts are processed as indivisible units to ensure zero data inconsistency.
- **Security Logic Engine:** Multi-layer authentication featuring IP-based Rate Limiting, CSRF protection, and System-wide Audit Logging.
- **Proactive Compliance Monitor:** Live "Critical Expiration Ticker" tracking weapon licenses and gear validity.
- **Transactional Personnel Roster:** Real-time duty status management with one-click toggles and RBAC data masking.

---

## 🛠️ System Architecture (Consolidated)
```text
IMS/
├── config.php                 # Core System Parameters
├── index.php                  # Root Redirect
├── login.php                  # Authorization Gateway
├── dashboard.php              # Command KPI Center
├── api/                       # Async Request Handlers
├── assets/                    # Tactical CSS/JS & Theme Controllers
├── auth/                      # Authentication Engine
├── database/                  # SQL Schema (v5.1)
├── includes/                  # Core Dependencies & PDO Bridge
├── inventory/                 # Asset Management Modules
├── operations/                # Attendance & Field Logs
├── payroll/                   # Financial Processing
└── personnel/                 # Force Management Modules
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
| **System Admin** | `ADMIN_SECURE` | `TACTICAL_2026` |
| **Operations Director** | `director_operation@fast` | `mian_habibullah` |

---
*Developed for Security Firm Simulation & Educational Purposes.*
