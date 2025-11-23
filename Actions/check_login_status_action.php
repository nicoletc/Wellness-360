<?php
/**
 * Check Login Status Action
 * Returns whether the user is currently logged in
 */

require_once __DIR__ . '/../settings/core.php';

header('Content-Type: application/json');

json_response([
    'status' => 'success',
    'is_logged_in' => is_logged_in(),
    'user_id' => is_logged_in() ? current_user_id() : null
]);
?>

