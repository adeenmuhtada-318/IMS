# IMS | Security Firm Tactical Interface

## Project Overview
A futuristic, lightweight Inventory Management System (IMS) designed for security firms. It features a high-contrast "Tactical Cyber-Dark" UI and a robust PHP/MySQL backend.

## Tech Stack
- **Frontend:** HTML5, Vanilla CSS3 (Custom "Tactical" variables), Vanilla JavaScript.
- **Backend:** PHP 8.x (PDO for secure database interactions).
- **Database:** MySQL (3rd Normal Form schema).

## Architecture
- `public/index.php`: Main entry point and dashboard UI.
- `assets/css/tactical.css`: Global styles including theme variables and modal designs.
- `assets/js/app.js`: Core frontend logic, AJAX handling, and DOM manipulation.
- `api/inventory_engine.php`: Unified backend router for all CRUD operations.
- `includes/db_connect.php`: Secure PDO connection layer.
- `database/schema.sql`: Database structure including Categories, Suppliers, Inventory, and Transactions.

## Key Features
- **Live Dashboard:** Real-time stats for total assets, critical stock levels, and categories.
- **Asset Logging:** Secure modal-based interface for adding new assets with serial numbers and critical thresholds.
- **Soft Deletion:** "Decommissioning" assets preserves historical data for audit requirements.
- **Tactical UI:** Focused on high readability and low visual fatigue for operators.

## Development Notes
- The system uses **Soft Deletion** (`is_deleted = 1`) to ensure transaction history integrity.
- Critical stock items are automatically highlighted in the UI (Orange/Cyan contrast).
- All database queries use **Prepared Statements** to prevent SQL Injection.

## Future Roadmap
- [ ] Implement Asset Movement Logs (Transactions).
- [ ] Add Supplier Management interface.
- [ ] Integrate User Authentication/Operator Login.
- [ ] Export inventory reports to PDF/CSV.
