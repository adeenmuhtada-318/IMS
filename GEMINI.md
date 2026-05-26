# IMS | Security Firm Tactical Interface

## Project Overview
A futuristic, lightweight Inventory Management System (IMS) designed for security firms. It features a high-contrast "Modern Navy" UI and a robust PHP/MySQL backend.

## Tech Stack
- **Frontend:** HTML5, Vanilla CSS3 (Strict PascalCase Design Tokens), Vanilla JavaScript.
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
- **Independent Viewport Engine:** Native vertical scrolling for workspace while keeping navigation stationary.
- **Collapsible Tactical Sidebar:** JavaScript-driven state engine (260px/70px) for maximum workspace efficiency.
- **Personnel Onboarding Terminal:** Advanced Guard Registration (Bharti Form) with clustered data widgets.
- **Live Telemetry HUD:** Real-time synchronization of Field Force, Supply Risk, and Blacklist metrics.
- **Dual-Theme Support:** Flawless DarkMode (#0B0F19) and LightMode (#F9FAFB) transition system.

## Development Notes
- **Strict Selector Standard:** All layout components utilize PascalCase (No hyphens/underscores).
- **Hardware-Level Animation:** Micro-interactions optimized with hardware-accelerated transforms.
- **Hardened Configuration:** `config.php` is isolated in the root directory.
- **API Routing:** Unified `api/api_router.php` handles async requests with role-based access control (RBAC).

## Recent Changes (May 2026)
- **UI Overhaul:** Implemented "Modern Navy" theme across all core views.
- **Viewport Fix:** Resolved overflow issues by implementing independent scrolling right side canvas.
- **Symmetry Engine:** Re-engineered Hub Matrix with symmetrical grid balancing.
- **Form Transformation:** Upgraded Bharti Form (Registration) to a widget-based high-contrast interface.
- **Design Token Refactor:** Migrated entire stylesheet to hyphen-free PascalCase variables and classes.

## Future Roadmap
- [ ] Implement Asset Movement Logs (Transactions).
- [ ] Add Supplier Management interface.
- [ ] Export inventory reports to PDF/CSV.
