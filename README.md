# MediQuick Pharmacy — Web Application

A dynamic, database-driven community pharmacy web application developed for **MediQuick Pharmacy** (No. 45, Colombo Road, Kurunegala, Sri Lanka).

---

## 📌 Features

### 1. Customer Storefront
* **Live Catalog & Search:** Browse Over-the-Counter (OTC) and Prescription (Rx) medications with real-time brand filtering.
* **Prescription Upload & Quotation:** Upload doctor prescriptions with instant preview, receive pharmacist price quotations & dosage instructions.
* **Prescription Online Payment & COD:** Pay for approved prescriptions directly from the dashboard via **PayHere Online Payment** (Visa/Mastercard/Mobile) or **Cash on Delivery**.
* **Shopping Cart & Checkout:** Frictionless checkout with PayHere Sandbox integration and delivery fee calculations.
* **1-Click Printable Tax Invoice:** Automated single-page A4 invoice download on order confirmation.
* **Customer Dashboard:** Real-time order tracking, prescription review history, and 1-click prescription checkout.

### 2. Pharmacist & Admin Portal (`admin/`)
* **KPI Dashboard:** Real-time overview of monthly revenue, total orders, pending prescriptions, and low stock alerts.
* **Prescription Verification & Quotation Queue:** Duty pharmacists inspect prescription slips, enter LKR price quotations, provide clinical dosage notes, and approve orders.
* **Medicine Inventory CRUD:** Add, update, and manage medicine categories, unit prices, dosages, and stock quantities.
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
