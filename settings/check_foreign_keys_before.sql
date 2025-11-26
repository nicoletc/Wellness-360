-- =====================================================
-- Check Foreign Keys BEFORE Renaming Tables
-- Run this to see all foreign key constraints
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
    AND REFERENCED_TABLE_NAME IN ('customer', 'products', 'cart', 'orders', 'payment', 'orderdetails')
ORDER BY 
    TABLE_NAME, CONSTRAINT_NAME;

