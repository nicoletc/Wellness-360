-- =====================================================
-- Table Rename Script for Wellness 360 Database
-- Run this script in phpMyAdmin to rename tables
-- =====================================================
-- 
-- IMPORTANT: 
-- 1. Backup your database before running this script
-- 2. Run these commands one at a time or all together
-- 3. Make sure no one is using the database while renaming
-- =====================================================

-- Rename customer to customers
RENAME TABLE `customer` TO `customers`;

-- Rename products to customer_products
RENAME TABLE `products` TO `customer_products`;

-- Rename cart to carts
RENAME TABLE `cart` TO `carts`;

-- Rename orders to customer_orders
RENAME TABLE `orders` TO `customer_orders`;

-- Rename payment to payments
RENAME TABLE `payment` TO `payments`;

-- Rename orderdetails to order_details
RENAME TABLE `orderdetails` TO `order_details`;

-- =====================================================
-- Verification Query (run after renaming to confirm)
-- =====================================================
-- SELECT TABLE_NAME 
-- FROM information_schema.TABLES 
-- WHERE TABLE_SCHEMA = 'final_db' 
-- AND TABLE_NAME IN ('customers', 'customer_products', 'carts', 'customer_orders', 'payments', 'order_details')
-- ORDER BY TABLE_NAME;

