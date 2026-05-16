# SECURE_IMS - Tactical Inventory Management System

A futuristic, high-contrast Inventory Management System designed for security firms. This application allows operators to track assets, monitor critical stock levels, and decommission items in a "cyber-dark" tactical environment.

## 🚀 Quick Start for Beginners

### 1. Requirements
To run this application, you need a local web server that supports **PHP** and **MySQL**.
- **Windows:** Download and install [XAMPP](https://www.apachefriends.org/index.html).
- **Mac:** Download and install [MAMP](https://www.mamp.info/).

### 2. Setup Instructions
1. **Move Project Files:**
   - Copy the entire `IMS` folder into your server's root directory:
     - For XAMPP: `C:\xampp\htdocs\IMS`
     - For MAMP: `/Applications/MAMP/htdocs/IMS`

2. **Start the Server:**
   - Open the **XAMPP Control Panel** and click **Start** next to "Apache" and "MySQL".

3. **Setup the Database:**
   - Open your browser and go to `http://localhost/phpmyadmin`.
   - Click **New** on the left sidebar and create a database named `security_ims_db`.
   - Click on your new database, go to the **Import** tab.
   - Click **Choose File** and select the file located at `E:\IMS\database\schema.sql`.
   - Scroll down and click **Import**.

4. **Access the App:**
   - Open your browser and visit: `http://localhost/IMS/public/index.php`

## 🛠 Features
- **Command Dashboard:** Live view of total assets and critical alerts.
- **Asset Logging:** Add new gear with serial numbers and custom thresholds.
- **Decommissioning:** Safely remove assets from active duty (soft-delete).
- **Tactical UI:** Designed for low eye strain and high readability.

## 📂 Project Structure
- `/public`: Contains the main entry point (`index.php`).
- `/api`: The PHP engine handling data requests.
- `/assets`: CSS and JavaScript for the "Tactical" look and feel.
- `/database`: Contains the SQL schema for setup.
- `/includes`: Database connection configuration.

## 📝 License
Created for educational and security firm simulation purposes.
