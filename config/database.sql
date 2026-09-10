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
(1, 'MediQuick Staff Admin', 'admin@mediquick.lk', '$2y$10$e8wF5qGzW5sQ6R6e.J6Jee7fXgKqPqM1p1H1c2d3e4f5g6h7i8j9k', '+94 37 222 3456', 'No. 45, Colombo Road, Kurunegala', 'admin'),
(2, 'Kasun Perera', 'kasun@gmail.com', '$2y$10$e8wF5qGzW5sQ6R6e.J6Jee7fXgKqPqM1p1H1c2d3e4f5g6h7i8j9k', '+94 77 123 4567', 'No. 12, Kandy Road, Kurunegala', 'customer'),
(3, 'Anoma Jayawardena', 'anoma@gmail.com', '$2y$10$e8wF5qGzW5sQ6R6e.J6Jee7fXgKqPqM1p1H1c2d3e4f5g6h7i8j9k', '+94 71 555 8899', 'No. 88, Bauddhaloka Mawatha, Kurunegala', 'customer');

-- Insert Sample Products
INSERT INTO `products` (`id`, `name`, `generic_name`, `category_id`, `price`, `stock`, `dosage`, `manufacturer`, `requires_prescription`, `image`, `description`) VALUES
(1, 'Panadol Extra Tablets', 'Paracetamol 500mg + Caffeine 65mg', 2, 180.00, 500, '12 Caplets Pack', 'GlaxoSmithKline (GSK)', 0, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500&auto=format&fit=crop&q=80', 'Fast effective relief of tough headaches, toothache, fever, and bodily aches.'),
(2, 'Amoxicillin Capsules 500mg', 'Amoxicillin Trihydrate', 1, 450.00, 200, '10 Capsules Strip', 'State Pharmaceuticals (SPMC)', 1, 'https://images.unsplash.com/photo-1471864190281-a93a3070b6de?w=500&auto=format&fit=crop&q=80', 'Broad spectrum penicillin antibiotic for bacterial respiratory and ear infections.'),
(3, 'Atorvastatin Tablets 20mg', 'Atorvastatin Calcium', 1, 850.00, 60, '30 Tablets Box', 'AstraZeneca', 1, 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?w=500&auto=format&fit=crop&q=80', 'Prescription medication to lower blood cholesterol and protect cardiovascular health.'),
(4, 'Metformin HCl Tablets 500mg', 'Metformin Hydrochloride', 1, 380.00, 150, '20 Tablets Strip', 'Cipla Pharmaceuticals', 1, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500&auto=format&fit=crop&q=80', 'Oral diabetes medicine that helps control blood sugar levels.'),
(5, 'C-Vite Vitamin C 1000mg Effervescent', 'Ascorbic Acid + Zinc', 3, 1250.00, 100, '20 Effervescent Tablets Tube', 'Bayer Healthcare', 0, 'https://images.unsplash.com/photo-1577401239170-897942555fb3?w=500&auto=format&fit=crop&q=80', 'Immune support dietary supplement with high antioxidant Vitamin C and Zinc.'),
(6, 'Digital Blood Pressure Monitor', 'LCD Arm Type Digital Sensor', 4, 6800.00, 25, 'Medium/Large Cuff Device', 'Omron Japan', 0, 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=500&auto=format&fit=crop&q=80', 'Accurate digital upper-arm blood pressure and heart pulse rate monitor with memory.'),
(7, 'Digital Infrared Forehead Thermometer', 'Instant Non-Contact Sensor', 4, 3450.00, 30, '1-Second Sensor Gun', 'Beurer Medical Germany', 0, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=500&auto=format&fit=crop&q=80', 'Non-contact hygienic infrared digital thermometer for quick fever detection.'),
(8, 'Cetrizine 10mg Antihistamine', 'Cetirizine Hydrochloride', 2, 120.00, 300, '10 Tablets Strip', 'State Pharmaceuticals (SPMC)', 0, 'https://images.unsplash.com/photo-1471864190281-a93a3070b6de?w=500&auto=format&fit=crop&q=80', 'Relief from allergy symptoms such as runny nose, sneezing, itchy eyes, and skin hives.');

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
(1, 2, 'Kasun Perera', '+94 77 123 4567', 'No. 12, Kandy Road, Kurunegala', 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=800&auto=format&fit=crop&q=80', 'Approved', 'Verified SLMC license. Dispensed 5-day course.'),
(2, 3, 'Anoma Jayawardena', '+94 71 555 8899', 'No. 88, Bauddhaloka Mawatha, Kurunegala', 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?w=800&auto=format&fit=crop&q=80', 'Pending', 'Awaiting pharmacist review.');

-- Insert Sample Inquiries
INSERT INTO `inquiries` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`) VALUES
(1, 'Samantha Bandara', 'samantha@gmail.com', '+94 77 444 3322', 'Delivery Area in Kurunegala', 'Do you deliver to Wariyapola area for prescription medicines?', 'New'),
(2, 'Kamal Herath', 'kamal.h@outlook.com', '+94 71 999 1100', 'Diabetes Meter Calibration', 'Can you provide strips for the Omron diagnostic monitor?', 'Replied');
