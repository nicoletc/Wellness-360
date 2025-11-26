-- =====================================================
-- Check Foreign Keys AFTER Renaming Tables
-- Run this to verify all foreign keys were updated correctly
-- =====================================================

SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    information_schema.KEY_COLUMN_USAGE
WHERE 
    TABLE_SCHEMA = 'final_db'
    AND REFERENCED_TABLE_NAME IS NOT NULL
    AND REFERENCED_TABLE_NAME IN ('customers', 'customer_products', 'carts', 'customer_orders', 'payments', 'order_details')
ORDER BY 
    TABLE_NAME, CONSTRAINT_NAME;

