<?php
/**
 * Fetch Workshop Attendees Action
 * Returns all registered attendees for a specific workshop
 */

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/db_class.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Get workshop ID
$workshop_id = isset($_GET['workshop_id']) ? (int)$_GET['workshop_id'] : 0;

if ($workshop_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid workshop ID.']);
    exit;
}

try {
    $db = new db_connection();
    if (!$db->db_connect()) {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    // Get workshop title
    $workshopSql = "SELECT workshop_title FROM workshops WHERE workshop_id = $workshop_id";
    $workshopResult = $db->db_fetch_one($workshopSql);
    
    if (!$workshopResult) {
        http_response_code(404);
        echo json_encode(['status' => false, 'message' => 'Workshop not found.']);
        exit;
    }

    // Get attendees
    $sql = "SELECT 
                r.registration_id,
                c.customer_id,
                c.customer_name,
                c.customer_email,
                r.registered_at,
                r.status
            FROM workshop_registrations r
            JOIN customer c ON c.customer_id = r.customer_id
            WHERE r.workshop_id = $workshop_id
            ORDER BY r.registered_at DESC";
    
    $attendees = $db->db_fetch_all($sql);
    
    if ($attendees === false) {
        $attendees = [];
    }

    echo json_encode([
        'status' => true,
        'workshop_title' => $workshopResult['workshop_title'],
        'attendees' => $attendees
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

