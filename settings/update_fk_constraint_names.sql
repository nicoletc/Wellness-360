-- =====================================================
-- Optional: Update Foreign Key Constraint Names
-- =====================================================
-- 
-- NOTE: This is OPTIONAL and COSMETIC ONLY
-- RENAME TABLE already updated all foreign key REFERENCES
-- This script only updates the constraint NAMES if you want them to match
-- 
-- Run this AFTER renaming tables if you want constraint names updated
-- =====================================================

-- Get all foreign key constraints that need renaming
-- Then manually update them using ALTER TABLE

-- Example for carts table foreign keys:
-- ALTER TABLE `carts` DROP FOREIGN KEY `old_constraint_name`;
-- ALTER TABLE `carts` ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `customer_products`(`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;
-- ALTER TABLE `carts` ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`c_id`) REFERENCES `customers`(`customer_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Example for order_details table foreign keys:
-- ALTER TABLE `order_details` DROP FOREIGN KEY `old_constraint_name`;
-- ALTER TABLE `order_details` ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `customer_orders`(`order_id`);
-- ALTER TABLE `order_details` ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `customer_products`(`product_id`);

-- Example for customer_orders table foreign keys:
-- ALTER TABLE `customer_orders` DROP FOREIGN KEY `old_constraint_name`;
-- ALTER TABLE `customer_orders` ADD CONSTRAINT `customer_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`);

-- Example for payments table foreign keys:
-- ALTER TABLE `payments` DROP FOREIGN KEY `old_constraint_name`;
-- ALTER TABLE `payments` ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`);
-- ALTER TABLE `payments` ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `customer_orders`(`order_id`);

-- =====================================================
-- Better Approach: Let MySQL handle constraint names automatically
-- The constraint names don't affect functionality
-- Only update if you specifically need custom constraint names
-- =====================================================

