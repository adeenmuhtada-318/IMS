# IMS | Security Firm Tactical Interface

## Project Overview
A futuristic, lightweight Inventory Management System (IMS) designed for security firms. It features a high-contrast "Tactical Cyber-Dark" UI and a robust PHP/MySQL backend.

## Tech Stack
- **Frontend:** HTML5, Vanilla CSS3 (Custom "Tactical" variables), Vanilla JavaScript.
- **Backend:** PHP 8.x (PDO for secure database interactions).
- **Database:** MySQL (3rd Normal Form schema).

## Architecture
- `index.php`: Root entry point (redirects to login).
- `login.php`: Secure operator authorization gateway.
- `dashboard.php`: Main operational command center.
- `api/api_router.php`: Unified backend router for async operations.
- `includes/connection.php`: Secure PDO database bridge.
- `database/schema.sql`: Master database schema (v5.1).

## Key Features
- **Live Dashboard:** Real-time stats for weapons, patrols, and personnel.
- **Personnel Roster:** Advanced guard management with duty status toggles.
- **Armory Control:** Serialized tracking for weapons and bulk inventory.
- **Payroll Engine:** Automated salary generation based on operational logs.
- **User Management (New):** Dedicated admin panel for operator account creation and role management.
- **Refined UI:** Transitioning from "Tactical Cyber-Dark" to a more user-friendly "Modern Navy" theme with softer colors and improved accessibility.

## Development Notes
- **Hardened Configuration:** `config.php` is isolated in the root directory.
- **Native Prepared Statements:** `PDO::ATTR_EMULATE_PREPARES => false` enforced for security.
- **Soft Deletion:** `is_deleted = 1` used to preserve audit trails.
- **API Routing:** Unified `api/api_router.php` handles async requests with role-based access control (RBAC).

## Recent Changes (May 2026)
- **Auth Overhaul:** Fixed role mismatch in Admin management (accepting 'Admin' and 'Admin/CEO').
- **API Enhancements:** Added `get_users` and `create_user` actions to `api_router.php`.
- **UI Cleanup:** 
    - Removed default text decoration (underlines) from buttons and links.
    - Softened color scheme (from deep black/cyan to navy/sky blue).
    - Simplified terminology (e.g., "OPERATOR_ACCOUNTS" -> "System Operators").
- **Admin Panel:** Implemented "Add New User" functionality with modal interface.

## Future Roadmap
- [ ] Implement Asset Movement Logs (Transactions).
- [ ] Add Supplier Management interface.
- [ ] Export inventory reports to PDF/CSV.
