<?php
/**
 * Bulk Product ZIP Upload Action
 * Processes a ZIP file containing CSV and images for bulk product upload
 */

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../Controllers/product_controller.php';
require_once __DIR__ . '/../Classes/category_class.php';
require_once __DIR__ . '/../Classes/vendor_class.php';

function jfail(string $msg, int $code = 400) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_SLASHES);
    exit;
}

function jsuccess(array $payload) {
    echo json_encode(['status' => 'success'] + $payload, JSON_UNESCAPED_SLASHES);
    exit;
}

if (!function_exists('is_logged_in') || !function_exists('is_admin') || !is_logged_in() || !is_admin()) {
    jfail('Unauthorized. Please log in as admin.', 401);
}

if (!isset($_FILES['zip_file']) || ($_FILES['zip_file']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    jfail('No ZIP file received (field name must be "zip_file").');
}

$zipTmp  = (string)$_FILES['zip_file']['tmp_name'];
$zipName = (string)$_FILES['zip_file']['name'];

if (!is_uploaded_file($zipTmp)) {
    jfail('Upload failed or file not found.');
}

$uploadsRoot = __DIR__ . '/../uploads';

// Ensure uploads root exists and is writable
if (!is_dir($uploadsRoot) && !@mkdir($uploadsRoot, 0755, true)) {
    jfail('Could not create uploads/ directory.');
}

// Working base is a hidden temp directory inside uploads
$workBase = $uploadsRoot . '/.tmp';
if (!is_dir($workBase) && !@mkdir($workBase, 0775, true)) {
    jfail('Could not create uploads temp directory.');
}

if (!is_writable($workBase)) {
    jfail('Uploads temp directory is not writable.');
}

$workDir = rtrim($workBase, '/') . '/bulkupload_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
if (!@mkdir($workDir, 0775, true)) {
    jfail('Could not create working directory.');
}

// Extract ZIP
$za = new ZipArchive();
if ($za->open($zipTmp) !== true) {
    jfail('Could not open ZIP archive.');
}

if (!$za->extractTo($workDir)) {
    $za->close();
    jfail('Failed to extract ZIP.');
}
$za->close();

// Find CSV (first *.csv in root preferred)
$csvPath = null;
$extractBase = $workDir;
$rootList = scandir($workDir) ?: [];

// 1) Check CSV files at the extraction root
foreach ($rootList as $f) {
    if ($f === '.' || $f === '..') continue;
    $p = $workDir . '/' . $f;
    if (is_file($p) && preg_match('/\.csv$/i', $f)) {
        $csvPath = $p;
        break;
    }
}

// 2) If not found, check each first-level directory for a CSV
if (!$csvPath) {
    foreach ($rootList as $f) {
        if ($f === '.' || $f === '..') continue;
        $p = $workDir . '/' . $f;
        if (is_dir($p)) {
            $sub = scandir($p) ?: [];
            foreach ($sub as $sf) {
                if ($sf === '.' || $sf === '..') continue;
                $sp = $p . '/' . $sf;
                if (is_file($sp) && preg_match('/\.csv$/i', $sf)) {
                    $csvPath = $sp;
                    $extractBase = $p; // Use this folder as the base for images
                    break 2;
                }
            }
        }
    }
}

if (!$csvPath) {
    jfail('No CSV file found in the ZIP. Put the CSV in the root of the archive or inside the top-level folder.');
}

// Open CSV
$fh = fopen($csvPath, 'r');
if (!$fh) {
    jfail('Could not open CSV for reading.');
}

// Read headers & index map
$headers = fgetcsv($fh);
if (!$headers) {
    fclose($fh);
    jfail('CSV is empty (no header row).');
}

// Normalize header names (lowercase, trimmed)
$norm = fn($s) => strtolower(trim((string)$s));
$headers = array_map($norm, $headers);

// Accept multiple aliases for cat/vendor
$aliases = [
    'product_cat'      => ['product_cat', 'cat', 'cat_id', 'category', 'product_category'],
    'product_vendor'   => ['product_vendor', 'vendor', 'vendor_id', 'product_vendor_id'],
    'product_title'    => ['product_title', 'title', 'name'],
    'product_price'    => ['product_price', 'price'],
    'product_desc'     => ['product_desc', 'description', 'product_description', 'desc'],
    'product_keywords' => ['product_keywords', 'keywords', 'product_keywords'],
    'product_image'    => ['product_image', 'image', 'image_file'],
    'stock'            => ['stock', 'product_stock', 'quantity']
];

$idx = [];
foreach ($aliases as $key => $alts) {
    $found = false;
    foreach ($alts as $a) {
        $pos = array_search($a, $headers, true);
        if ($pos !== false) {
            $idx[$key] = $pos;
            $found = true;
            break;
        }
    }
    if (!$found) {
        fclose($fh);
        jfail("CSV missing required column (one of): " . implode('|', $alts));
    }
}

// Initialize controllers and classes
$product_controller = new product_controller();
$category_class = new category_class();
$vendor_class = new vendor_class();

// Process rows
$created = 0;
$skipped = 0;
$rows = 0;
$errors = [];

while (($row = fgetcsv($fh)) !== false) {
    $rows++;
    
    // Ensure $row has at least up to the highest indexed header
    $maxIndex = max($idx);
    $row = array_pad($row, $maxIndex + 1, '');
    
    try {
        // Skip totally empty rows
        $titleCheck = trim((string)($row[$idx['product_title']] ?? ''));
        $catCheck   = (int)trim((string)($row[$idx['product_cat']] ?? '0'));
        
        // If both title empty and cat is zero, this is likely an empty row or trailing mapping section -> skip
        if ($titleCheck === '' && $catCheck === 0) {
            continue;
        }
        
        // Build payload (raw values). Category and vendor may be IDs or names.
        $raw_cat   = trim((string)($row[$idx['product_cat']] ?? ''));
        $raw_vendor = trim((string)($row[$idx['product_vendor']] ?? ''));
        
        $payload = [
            'product_cat'      => $raw_cat,
            'product_vendor'   => $raw_vendor,
            'product_title'    => trim((string)($row[$idx['product_title']] ?? '')),
            'product_price'    => trim((string)($row[$idx['product_price']] ?? '')),
            'product_desc'     => trim((string)($row[$idx['product_desc']] ?? '')),
            'product_keywords' => trim((string)($row[$idx['product_keywords']] ?? '')),
            'product_image'    => null,
            'stock'            => trim((string)($row[$idx['stock']] ?? '0')),
        ];
        
        // Basic validation
        
        // Resolve category id: numeric -> use as id; otherwise try find by name or create it
        $catId = 0;
        if ($payload['product_cat'] !== '') {
            if (is_numeric($payload['product_cat']) && (int)$payload['product_cat'] > 0) {
                $catId = (int)$payload['product_cat'];
            } else {
                $catName = trim((string)$payload['product_cat']);
                // Try to find by exact name
                $foundCat = $category_class->get_by_name($catName);
                
                if ($foundCat && isset($foundCat['cat_id'])) {
                    $catId = (int)$foundCat['cat_id'];
                } else {
                    // Create new category
                    $result = $category_class->add(['cat_name' => $catName]);
                    if (!$result['status'] || !isset($result['cat_id'])) {
                        throw new RuntimeException('Failed to create new category: ' . $catName . ' - ' . ($result['message'] ?? 'Unknown error'));
                    }
                    $catId = (int)$result['cat_id'];
                }
            }
        }
        
        // Resolve vendor id: numeric -> use as id; otherwise try find by name or create it
        $vendorId = 0;
        if ($payload['product_vendor'] !== '') {
            if (is_numeric($payload['product_vendor']) && (int)$payload['product_vendor'] > 0) {
                $vendorId = (int)$payload['product_vendor'];
            } else {
                $vendorName = trim((string)$payload['product_vendor']);
                // Try to find by exact name
                $vendors = $vendor_class->get_all();
                $foundId = 0;
                foreach ($vendors as $v) {
                    if (strcasecmp($v['vendor_name'], $vendorName) === 0) {
                        $foundId = (int)$v['vendor_id'];
                        break;
                    }
                }
                
                if ($foundId > 0) {
                    $vendorId = $foundId;
                } else {
                    // Create new vendor (with minimal required fields)
                    $result = $vendor_class->add([
                        'vendor_name' => $vendorName,
                        'vendor_email' => strtolower(str_replace(' ', '', $vendorName)) . '@vendor.local',
                        'vendor_contact' => '',
                        'product_stock' => 0
                    ]);
                    if (!$result['status'] || !isset($result['vendor_id'])) {
                        throw new RuntimeException('Failed to create new vendor: ' . $vendorName . ' - ' . ($result['message'] ?? 'Unknown error'));
                    }
                    $vendorId = (int)$result['vendor_id'];
                }
            }
        }
        
        if ($catId <= 0) {
            throw new RuntimeException('Invalid product_cat.');
        }
        if ($vendorId <= 0) {
            throw new RuntimeException('Invalid product_vendor.');
        }
        if ($payload['product_title'] === '') {
            throw new RuntimeException('product_title is required.');
        }
        if ($payload['product_price'] === '' || !is_numeric($payload['product_price'])) {
            throw new RuntimeException('product_price must be numeric.');
        }
        if ($payload['stock'] === '' || !is_numeric($payload['stock']) || (int)$payload['stock'] < 0) {
            throw new RuntimeException('stock must be a non-negative integer.');
        }
        
        // Image resolution (relative to the CSV folder / extract base)
        $imageRel = trim((string)($row[$idx['product_image']] ?? ''));
        $imageAbs = null;
        
        if ($imageRel !== '') {
            $fname = basename($imageRel);
            
            // Direct candidate
            $candidate = $extractBase . '/' . $fname;
            if (is_file($candidate)) {
                $imageAbs = $candidate;
            } else {
                // Try relative path if CSV contained a path (e.g., images/tee1.jpg)
                $relPathCandidate = $extractBase . '/' . ltrim($imageRel, "\/");
                if (is_file($relPathCandidate)) {
                    $imageAbs = $relPathCandidate;
                }
            }
            
            // Final fallback: recursive case-insensitive search under extractBase
            if (!$imageAbs) {
                $rit = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($extractBase, FilesystemIterator::SKIP_DOTS)
                );
                
                $extractLabel = trim((string)basename($extractBase));
                $nameNoExt = pathinfo($fname, PATHINFO_FILENAME);
                $candidates = [
                    $fname,
                    $extractLabel . '_' . $fname,
                    $extractLabel . '-' . $fname,
                    $extractLabel . $fname,
                    $nameNoExt . '_' . $fname,
                    $nameNoExt . '-' . $fname,
                ];
                
                foreach ($rit as $file) {
                    if (!$file->isFile()) continue;
                    $fn = $file->getFilename();
                    
                    // Exact case-insensitive filename match
                    if (strcasecmp($fn, $fname) === 0) {
                        $imageAbs = $file->getRealPath();
                        break;
                    }
                    
                    // Try candidate patterns
                    foreach ($candidates as $cand) {
                        if (strcasecmp($fn, $cand) === 0) {
                            $imageAbs = $file->getRealPath();
                            break 2;
                        }
                    }
                    
                    // Also accept filenames that contain the requested base name
                    if (stripos($fn, $nameNoExt) !== false) {
                        $imageAbs = $file->getRealPath();
                        break;
                    }
                }
            }
            
            if (!$imageAbs) {
                throw new RuntimeException("Image not found near CSV folder: {$fname}");
            }
        }
        
        // Create DB product (without image first)
        $payloadToDb = [
            'product_cat'      => $catId,
            'product_vendor'  => $vendorId,
            'product_title'   => $payload['product_title'],
            'product_price'   => $payload['product_price'],
            'product_desc'    => $payload['product_desc'],
            'product_keywords' => $payload['product_keywords'],
            'product_image'   => '',
            'stock'           => (int)$payload['stock']
        ];
        
        $result = $product_controller->add_product_ctr($payloadToDb);
        
        if (!$result['status'] || !isset($result['product_id'])) {
            throw new RuntimeException('DB insert failed: ' . ($result['message'] ?? 'Unknown error'));
        }
        
        $newId = (int)$result['product_id'];
        
        // Place image under uploads/ directory
        if ($imageAbs) {
            $ext = pathinfo($imageAbs, PATHINFO_EXTENSION) ?: 'png';
            $safe = 'product_' . $newId . '_' . time() . '.' . strtolower($ext);
            $destAbs = $uploadsRoot . '/' . $safe;
            
            if (!copy($imageAbs, $destAbs)) {
                throw new RuntimeException('Failed to copy image to uploads.');
            }
            
            $rel = 'uploads/' . $safe;
            
            // Update product with image path
            $updateResult = $product_controller->update_product_ctr([
                'product_id'       => $newId,
                'product_cat'      => $catId,
                'product_vendor'   => $vendorId,
                'product_title'    => $payload['product_title'],
                'product_price'    => $payload['product_price'],
                'product_desc'     => $payload['product_desc'],
                'product_keywords' => $payload['product_keywords'],
                'product_image'    => $rel,
                'stock'            => (int)$payload['stock']
            ]);
            
            if (!$updateResult['status']) {
                throw new RuntimeException('Failed to update product with image path: ' . ($updateResult['message'] ?? 'Unknown error'));
            }
        }
        
        $created++;
        
    } catch (Throwable $e) {
        $skipped++;
        $errors[] = "Row {$rows}: " . $e->getMessage();
        // Continue to next row
    }
}

fclose($fh);

// Cleanup tmp dir (best-effort)
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($workDir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
);

foreach ($it as $fs) {
    $fs->isDir() ? @rmdir($fs->getRealPath()) : @unlink($fs->getRealPath());
}
@rmdir($workDir);

// Done
jsuccess([
    'processed_rows' => $rows,
    'created'        => $created,
    'skipped'        => $skipped,
    'errors'         => $errors,
    'zip_name'       => $zipName,
]);

