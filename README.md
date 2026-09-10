# MediQuick Pharmacy — Web Application

A dynamic, database-driven community pharmacy web application developed for **MediQuick Pharmacy** (No. 45, Colombo Road, Kurunegala, Sri Lanka).

---

## 📌 Features

### 1. Customer Storefront
* **Live Catalog & Search:** Browse Over-the-Counter (OTC) and Prescription (Rx) medications with real-time brand filtering.
* **Prescription Upload:** Upload doctor prescriptions with instant client-side JavaScript preview and Base64 storage in MySQL.
* **Shopping Cart & Checkout:** Frictionless Cash on Delivery (COD) checkout with delivery fee calculations.
* **1-Click Printable Tax Invoice:** Automated single-page A4 invoice download on order confirmation.
* **Customer Dashboard:** Real-time order tracking and prescription review history.

### 2. Pharmacist & Admin Portal (`admin/`)
* **KPI Dashboard:** Real-time overview of monthly revenue, total orders, pending prescriptions, and low stock alerts.
* **Medicine Inventory CRUD:** Add, update, and manage medicine categories, unit prices, dosages, and stock quantities.
* **Prescription Verification Queue:** Duty pharmacists can review high-resolution prescription photos in modal popups and approve/reject orders.
* **Order Dispatch Tracking:** Manage order statuses (Pending, Processing, Delivered, Cancelled).

---

## 🛠️ Technology Stack
* **Backend:** Procedural PHP 8.x
* **Database:** MySQL / MariaDB (`mediquick_db`)
* **Frontend:** HTML5, Vanilla CSS3, Bootstrap 5.3 CDN
* **Client Scripts:** Vanilla JavaScript (FileReader API, DOM manipulation, html2pdf.js)
* **Environment:** XAMPP / Apache

---

## 🚀 Installation & Local Setup

1. **Clone or Copy Repository:**
   Place the project folder inside your web server directory (e.g., `/Applications/XAMPP/xamppfiles/htdocs/` or your workspace).

2. **Start MySQL & Apache:**
   Start Apache and MySQL services via **XAMPP Control Panel**.

3. **Import Database:**
   * Open `http://localhost/phpmyadmin`
   * Create a new database named `mediquick_db`
   * Import `config/database.sql`

4. **Run Local Server:**
   ```bash
   php -S localhost:8000
   ```
   Open `http://localhost:8000` in your web browser.

---

## 👥 Default Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin / Pharmacist** | `admin@mediquick.lk` | `password123` |
| **Customer** | `kasun@gmail.com` | `password123` |

---

## 📄 Documentation
Comprehensive academic project report and architectural diagrams are available inside the [`documentation/`](./documentation/) directory.
