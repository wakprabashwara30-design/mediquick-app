-- ==========================================================
-- MediQuick Pharmacy Database Script
-- Database: `mediquick_db`
-- Assignment: CSE4206 - Web Application Development (Kurunegala)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `mediquick_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mediquick_db`;

-- Temporarily disable foreign key checks to cleanly drop existing tables
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `inquiries`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `prescriptions`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- 1. USERS TABLE (Customer, Staff & Admin accounts)
-- ==========================================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `role` ENUM('customer', 'admin') DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- 2. CATEGORIES TABLE
-- ==========================================================
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT 'bi-capsule'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- 3. PRODUCTS TABLE
-- ==========================================================
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `generic_name` VARCHAR(255) DEFAULT NULL,
    `category_id` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 50,
    `dosage` VARCHAR(100) DEFAULT NULL,
    `manufacturer` VARCHAR(150) DEFAULT 'State Pharmaceuticals (SPMC)',
    `requires_prescription` TINYINT(1) DEFAULT 0,
    `image` LONGTEXT NOT NULL,
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- 4. PRESCRIPTIONS TABLE
-- ==========================================================
CREATE TABLE `prescriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `patient_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `delivery_address` TEXT NOT NULL,
    `image` LONGTEXT NOT NULL,
    `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    `pharmacist_notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- 5. ORDERS TABLE
-- ==========================================================
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `delivery_address` TEXT NOT NULL,
    `city` VARCHAR(100) DEFAULT 'Kurunegala',
    `total_amount` DECIMAL(10,2) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
    `payment_status` ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
    `payhere_payment_id` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('Pending', 'Processing', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- 6. ORDER ITEMS TABLE
-- ==========================================================
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `total_price` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- 7. INQUIRIES TABLE (Contact Us Messages)
-- ==========================================================
CREATE TABLE `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('New', 'Replied') DEFAULT 'New',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- 8. BLOGS TABLE (Health Articles & Wellness Guides)
-- ==========================================================
CREATE TABLE IF NOT EXISTS `blogs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `author` VARCHAR(150) NOT NULL DEFAULT 'MediQuick Pharmacist',
    `image` LONGTEXT NOT NULL,
    `summary` TEXT NOT NULL,
    `content` LONGTEXT NOT NULL,
    `views` INT DEFAULT 120,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================================
-- SAMPLE SEED DATA
-- Default password for all sample users is: password123
-- ==========================================================

-- Insert Categories
INSERT INTO `categories` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Prescription Medicines', 'Antibiotics, blood pressure, diabetes, cardiovascular', 'bi-file-earmark-medical'),
(2, 'Over the Counter (OTC)', 'Pain relief, fever, cold, flu, digestive health', 'bi-capsule'),
(3, 'Vitamins & Supplements', 'Multivitamins, Vitamin C, Zinc, fish oils', 'bi-heart-pulse'),
(4, 'Diagnostic & First Aid', 'Thermometers, BP monitors, bandages, antiseptics', 'bi-bandaid'),
(5, 'Baby & Maternal Care', 'Baby skincare, pediatric vitamins, feeding essentials', 'bi-emoji-smile');

-- Insert Sample Users
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`) VALUES
(1, 'MediQuick Staff Admin', 'admin@mediquick.lk', '$2y$10$cbm5OOcfoWtci9qtoG/TbuNfBmaif/t3.25NNDpXvhaueJ2mFTCa6', '+94 37 222 3456', 'No. 45, Colombo Road, Kurunegala', 'admin'),
(2, 'Kasun Perera', 'kasun@gmail.com', '$2y$10$uMMkfD4t8PM9f6q/OftvXuoMFFW5g04ZrPuI0RAgV.SJD1AyPPHRG', '+94 77 123 4567', 'No. 12, Kandy Road, Kurunegala', 'customer'),
(3, 'Anoma Jayawardena', 'anoma@gmail.com', '$2y$10$uMMkfD4t8PM9f6q/OftvXuoMFFW5g04ZrPuI0RAgV.SJD1AyPPHRG', '+94 71 555 8899', 'No. 88, Bauddhaloka Mawatha, Kurunegala', 'customer');

-- Insert Sample Products (Authentic Sri Lankan NMRA Pharmaceuticals)
INSERT INTO `products` (`id`, `name`, `generic_name`, `category_id`, `price`, `stock`, `dosage`, `manufacturer`, `requires_prescription`, `image`, `description`) VALUES
(1, 'Panadol Extra Tablets', 'Paracetamol 500mg + Caffeine 65mg', 2, 180.00, 500, '12 Caplets Pack', 'GlaxoSmithKline (GSK)', 0, 'assets/img/products/panadol-extra.jpg', 'Fast, targeted pain relief for tough headaches, migraines, toothache, muscle aches, and fever.'),
(2, 'Amoxicillin Capsules 500mg', 'Amoxicillin Trihydrate', 1, 450.00, 200, '10 Capsules Strip', 'State Pharmaceuticals (SPMC)', 1, 'assets/img/products/amoxicillin-500mg.jpg', 'Broad-spectrum penicillin antibiotic for treating respiratory tract, ENT, and bacterial skin infections.'),
(3, 'Atorvastatin Tablets 20mg', 'Atorvastatin Calcium', 1, 850.00, 60, '30 Film-Coated Tablets Box', 'AstraZeneca Pharmaceuticals', 1, 'assets/img/products/atorvastatin-20mg.jpg', 'Prescription statin medication to manage cholesterol levels and reduce risk of cardiovascular disease.'),
(4, 'Metformin HCl Tablets 500mg', 'Metformin Hydrochloride', 1, 380.00, 150, '30 Tablets Box (3x10)', 'State Pharmaceuticals (SPMC)', 1, 'assets/img/products/metformin-500mg.jpg', 'First-line oral anti-diabetic medication to regulate and balance blood glucose levels in Type 2 diabetes.'),
(5, 'C-Vite Vitamin C 1000mg Effervescent', 'Ascorbic Acid 1000mg + Zinc 15mg', 3, 1250.00, 100, '20 Effervescent Tablets Tube', 'Bayer Healthcare', 0, 'assets/img/products/cvite-zinc.jpg', 'Daily antioxidant effervescent drink tablets providing high-potency Vitamin C and Zinc for immune defense.'),
(6, 'Digital Blood Pressure Monitor', 'LCD Arm-Type Digital Pulse Sensor', 4, 6800.00, 25, 'Medium/Large Cuff Device', 'Omron Healthcare Japan', 0, 'assets/img/products/omron-bp-monitor.jpg', 'Clinical-grade automatic upper arm blood pressure monitor with hypertension indicator and memory recall.'),
(7, 'Digital Infrared Forehead Thermometer', 'Instant Non-Contact Sensor Gun', 4, 3450.00, 30, '1-Second Infrared Gun', 'Beurer Medical Germany', 0, 'assets/img/products/infrared-thermometer.jpg', 'Hygienic non-contact infrared forehead digital thermometer with color-coded fever warning screen.'),
(8, 'Cetrizine 10mg Antihistamine', 'Cetirizine Hydrochloride', 2, 120.00, 300, '10 Tablets Strip', 'State Pharmaceuticals (SPMC)', 0, 'assets/img/products/cetrizine-10mg.jpg', 'Fast non-drowsy relief from allergic rhinitis, sneezing, itchy watery eyes, hives, and pollen allergies.'),
(9, 'Baby Cheramy Gripe Water 100ml', 'Dill Seed Oil + Sodium Bicarbonate', 5, 320.00, 50, '100ml Glass Bottle', 'Hemas Consumer Brands', 0, 'assets/img/products/baby-gripe-water.jpg', 'Trusted Ayurvedic-herbal infant gripe water for rapid relief from infant colic, tummy wind, and griping pain.');

-- Insert Sample Orders
INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `phone`, `email`, `delivery_address`, `city`, `total_amount`, `payment_method`, `status`) VALUES
(1, 2, 'Kasun Perera', '+94 77 123 4567', 'kasun@gmail.com', 'No. 12, Kandy Road, Kurunegala', 'Kurunegala', 1430.00, 'Cash on Delivery', 'Delivered'),
(2, 3, 'Anoma Jayawardena', '+94 71 555 8899', 'anoma@gmail.com', 'No. 88, Bauddhaloka Mawatha, Kurunegala', 'Kurunegala', 1250.00, 'Card Payment', 'Processing');

-- Insert Sample Order Items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `total_price`) VALUES
(1, 1, 1, 'Panadol Extra Tablets', 180.00, 1, 180.00),
(2, 1, 5, 'C-Vite Vitamin C 1000mg Effervescent', 1250.00, 1, 1250.00),
(3, 2, 5, 'C-Vite Vitamin C 1000mg Effervescent', 1250.00, 1, 1250.00);

-- Insert Sample Prescriptions
INSERT INTO `prescriptions` (`id`, `user_id`, `patient_name`, `phone`, `delivery_address`, `image`, `status`, `pharmacist_notes`) VALUES
(1, 2, 'Kasun Perera', '+94 77 123 4567', 'No. 12, Kandy Road, Kurunegala', 'assets/img/prescriptions/sample-rx-amoxicillin.jpg', 'Approved', 'Verified SLMC license. Dispensed 5-day course.'),
(2, 3, 'Anoma Jayawardena', '+94 71 555 8899', 'No. 88, Bauddhaloka Mawatha, Kurunegala', 'assets/img/prescriptions/sample-rx-insulin.jpg', 'Pending', 'Awaiting pharmacist review.');

-- Insert Sample Inquiries
INSERT INTO `inquiries` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`) VALUES
(1, 'Samantha Bandara', 'samantha@gmail.com', '+94 77 444 3322', 'Delivery Area in Kurunegala', 'Do you deliver to Wariyapola area for prescription medicines?', 'New'),
(2, 'Kamal Herath', 'kamal.h@outlook.com', '+94 71 999 1100', 'Diabetes Meter Calibration', 'Can you provide strips for the Omron diagnostic monitor?', 'Replied');

-- Insert Sample Health Blogs
INSERT INTO `blogs` (`id`, `title`, `category`, `author`, `image`, `summary`, `content`, `views`) VALUES
(1, 'Understanding Antibiotics: Why Completing the Full Course Matters', 'Prescription Care', 'Dr. Rohan Gunawardena (SLMC-PH)', 'assets/img/blogs/antibiotic-course-guide.jpg', 'Stopping antibiotics early when symptoms improve leads to antibiotic resistance. Learn how bacteria develop immunity and how to safely take penicillin and amoxicillin.', 'Antibiotics are powerful medicines that fight bacterial infections. When used properly, they can save lives. However, growing antibiotic resistance is becoming a critical public health issue. Even if you feel significantly better after 2 or 3 days, bacteria may still be active in your body. Completing the full prescribed course (typically 5 to 7 days) ensures all targeted pathogenic bacteria are eradicated, preventing resilient strains from mutating.', 245),
(2, 'Safe Storage of Insulin and Heat-Sensitive Medications in Tropical Climates', 'Diabetes & Storage', 'MediQuick Clinical Team', 'assets/img/blogs/insulin-cold-chain-storage.jpg', 'In Sri Lankan tropical temperatures, unmonitored heat degrades insulin efficacy. Discover optimal 2°C–8°C refrigeration practices and traveling storage tips.', 'Medications like insulin, eye drops, and certain biologics require strict temperature controls. Tropical ambient temperatures exceeding 30°C cause active protein structures to break down rapidly. Unopened insulin vials must remain refrigerated between 2°C and 8°C. Once in use, pens can be stored at controlled room temperature below 25°C away from direct sunlight for up to 28 days.', 189),
(3, 'Managing Hypertension: How to Accurately Measure Blood Pressure at Home', 'Cardiovascular Health', 'Staff Pharmacist Kurunegala', 'assets/img/blogs/hypertension-bp-monitoring.jpg', 'Accurate upper-arm blood pressure monitoring requires resting 5 minutes, correct cuff positioning at heart level, and avoiding caffeine prior to readings.', 'Regular blood pressure monitoring at home helps patients and doctors evaluate hypertension treatments. To obtain accurate readings, rest quietly for 5 minutes before measurement, ensure the cuff fits snugly around the bare upper arm at heart level, keep feet flat on the floor, and avoid smoking, caffeine, or exercise 30 minutes prior to testing.', 312),
(4, 'Immunity Boosters: The Science Behind Vitamin C and Zinc Supplements', 'Wellness & Immunity', 'Nutritional Health Dept', 'assets/img/blogs/vitamin-c-zinc-immunity.jpg', 'How daily antioxidants enhance cellular immunity against seasonal viral infections and flu in North Western province weather conditions.', 'Vitamin C (Ascorbic Acid) and Zinc work synergistically to support the body\'s natural defense mechanisms. Vitamin C acts as a potent antioxidant protecting cellular integrity, while Zinc is essential for immune cell development and communication. Daily supplementation during rainy seasons helps reduce the severity and duration of common respiratory infections.', 174);

