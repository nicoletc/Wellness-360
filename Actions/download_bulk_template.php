<?php
/**
 * Download Bulk Upload Template
 * Generates a CSV template with product data and category/vendor mappings
 */

declare(strict_types=1);

// Suppress any errors that might output before headers
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once __DIR__ . '/../settings/core.php';

// core.php already starts output buffering, so we just need to clear it when needed
require_once __DIR__ . '/../Classes/category_class.php';
require_once __DIR__ . '/../Classes/vendor_class.php';

if (!function_exists('is_logged_in') || !function_exists('is_admin') || !is_logged_in() || !is_admin()) {
    ob_end_clean();
    http_response_code(401);
    header('Content-Type: text/plain');
    echo 'Unauthorized';
    exit;
}

// Check if ZipArchive is available
if (!class_exists('ZipArchive')) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: text/plain');
    echo 'ZIP extension not available';
    exit;
}

// Define CSV columns for Wellness 360 products
$cols = [
    'product_cat',
    'product_vendor',
    'product_title',
    'product_price',
    'product_desc',
    'product_keywords',
    'product_image',
    'stock'
];

$csvLines = [];
$csvLines[] = implode(',', $cols);

// Add an example row (blank values)
$csvLines[] = '1,1,Example Product,9.99,"Short description","tag1, tag2",example.jpg,10';

// Fetch categories and vendors from DB and append mapping sections
try {
    $category_class = new category_class();
    $vendor_class = new vendor_class();

    // Ensure database connection
    if (!$category_class->db_connect()) {
        throw new Exception('Category database connection failed');
    }
    
    if (!$vendor_class->db_connect()) {
        throw new Exception('Vendor database connection failed');
    }

    $cats = $category_class->get_all();
    $vendors = $vendor_class->get_all();

    // Check if we got valid arrays
    if (!is_array($cats)) {
        $cats = [];
    }
    if (!is_array($vendors)) {
        $vendors = [];
    }
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: text/plain');
    error_log('Bulk template download error: ' . $e->getMessage());
    echo 'Error fetching data: ' . $e->getMessage();
    exit;
} catch (Error $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: text/plain');
    error_log('Bulk template download fatal error: ' . $e->getMessage());
    echo 'Fatal error occurred';
    exit;
}

$csvLines[] = '';
$csvLines[] = '';
$csvLines[] = ',,,Category ID Map,,,';
$csvLines[] = ',,,cat_id,cat_name';
foreach ($cats as $c) {
    if (isset($c['cat_id']) && isset($c['cat_name'])) {
        $csvLines[] = ',,,' . (int)$c['cat_id'] . ',"' . str_replace('"', '""', (string)$c['cat_name']) . '"';
    }
}

$csvLines[] = '';
$csvLines[] = ',,,Vendor ID Map,,,';
$csvLines[] = ',,,vendor_id,vendor_name';
foreach ($vendors as $v) {
    if (isset($v['vendor_id']) && isset($v['vendor_name'])) {
        $csvLines[] = ',,,' . (int)$v['vendor_id'] . ',"' . str_replace('"', '""', (string)$v['vendor_name']) . '"';
    }
}

$readme = "Bulk Upload Template for Wellness 360\n\n";
$readme .= "Instructions:\n";
$readme .= "- Keep images inside the ZIP alongside the CSV or in a subfolder (e.g. images/).\n";
$readme .= "- The CSV must contain a header row with these columns: " . implode(', ', $cols) . ".\n";
$readme .= "- For product_cat and product_vendor you may provide either the numeric id (see maps below) or the exact category/vendor name. If a name is provided and does not exist, it will be created.\n";
$readme .= "- product_image is the filename (or relative path inside the zip) of the image file to copy to the product uploads.\n";
$readme .= "- Prices must be numeric (e.g. 19.99).\n";
$readme .= "- Stock must be a non-negative integer (e.g. 10).\n";
$readme .= "- product_desc and product_keywords can contain commas if wrapped in quotes.\n\n";
$readme .= "Example row is provided in the CSV.\n";

// Create a zip in memory (stream)
try {
    $zip = new ZipArchive();
    
    // Use the existing tmp folder in the project root
    $tempDir = __DIR__ . '/../tmp';
    
    // Check if tmp directory exists
    if (!is_dir($tempDir)) {
        throw new Exception('Temp directory does not exist: ' . $tempDir . '. Please create the tmp/ folder in the project root.');
    }
    
    // Check if it's writable
    if (!is_writable($tempDir)) {
        // Try to change permissions
        @chmod($tempDir, 0775);
        if (!is_writable($tempDir)) {
            throw new Exception('Temp directory is not writable: ' . $tempDir . '. Please check directory permissions.');
        }
    }
    
    $tmpName = rtrim($tempDir, '/') . '/bulk_template_' . bin2hex(random_bytes(4)) . '.zip';
    
    // Remove file if it exists (to avoid conflicts)
    if (file_exists($tmpName)) {
        @unlink($tmpName);
    }
    
    // Try to create the ZIP file (use CREATE only, not OVERWRITE)
    $result = $zip->open($tmpName, ZipArchive::CREATE);
    
    if ($result !== true) {
        $errorMsg = 'Failed to create ZIP archive';
        
        // Common error codes
        $errorCodes = [
            ZipArchive::ER_OK => 'No error',
            ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
            ZipArchive::ER_RENAME => 'Renaming temporary file failed',
            ZipArchive::ER_CLOSE => 'Closing zip archive failed',
            ZipArchive::ER_SEEK => 'Seek error',
            ZipArchive::ER_READ => 'Read error',
            ZipArchive::ER_WRITE => 'Write error',
            ZipArchive::ER_CRC => 'CRC error',
            ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
            ZipArchive::ER_NOENT => 'No such file',
            ZipArchive::ER_EXISTS => 'File already exists',
            ZipArchive::ER_OPEN => 'Can\'t open file',
            ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
            ZipArchive::ER_ZLIB => 'Zlib error',
            ZipArchive::ER_MEMORY => 'Memory allocation failure',
            ZipArchive::ER_CHANGED => 'Entry has been changed',
            ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
            ZipArchive::ER_EOF => 'Premature EOF',
            ZipArchive::ER_INVAL => 'Invalid argument',
            ZipArchive::ER_NOZIP => 'Not a zip archive',
            ZipArchive::ER_INTERNAL => 'Internal error',
            ZipArchive::ER_INCONS => 'Zip archive inconsistent',
            ZipArchive::ER_REMOVE => 'Can\'t remove file',
            ZipArchive::ER_DELETED => 'Entry has been deleted'
        ];
        
        if (isset($errorCodes[$result])) {
            $errorMsg .= '. Error code ' . $result . ': ' . $errorCodes[$result];
        } else {
            $errorMsg .= '. Error code: ' . $result;
        }
        
        $errorMsg .= '. Temp file path: ' . $tmpName;
        $errorMsg .= '. Temp dir writable: ' . (is_writable($tempDir) ? 'yes' : 'no');
        
        throw new Exception($errorMsg);
    }

    $csvContent = implode("\r\n", $csvLines);
    if ($zip->addFromString('bulkproductsupload.csv', $csvContent) !== true) {
        $zip->close();
        throw new Exception('Failed to add CSV to ZIP');
    }

    if ($zip->addFromString('README.txt', $readme) !== true) {
        $zip->close();
        throw new Exception('Failed to add README to ZIP');
    }

    if ($zip->close() !== true) {
        throw new Exception('Failed to close ZIP archive');
    }

    // Check if file was created and has content
    if (!file_exists($tmpName) || filesize($tmpName) === 0) {
        throw new Exception('ZIP file was not created or is empty');
    }

    // Clear any output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Set headers for file download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="bulk_template.zip"');
    header('Content-Length: ' . filesize($tmpName));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    // Output the file
    readfile($tmpName);

    // Clean up
    @unlink($tmpName);
    exit;

} catch (Exception $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(500);
    header('Content-Type: text/plain');
    error_log('Bulk template ZIP creation error: ' . $e->getMessage());
    echo 'Error creating ZIP: ' . $e->getMessage();
    if (isset($tmpName) && file_exists($tmpName)) {
        @unlink($tmpName);
    }
    exit;
} catch (Error $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(500);
    header('Content-Type: text/plain');
    error_log('Bulk template ZIP creation fatal error: ' . $e->getMessage());
    echo 'Fatal error occurred while creating ZIP';
    if (isset($tmpName) && file_exists($tmpName)) {
        @unlink($tmpName);
    }
    exit;
}

