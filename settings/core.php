<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();

/* Paths */
// Detect base path dynamically from current request
function get_base_path(): string {
    static $base_path = null;
    if ($base_path === null) {
        // Get the script directory relative to document root
        $script_dir = dirname($_SERVER['SCRIPT_NAME']);
        // Remove /final if it's in the path (since we're already in /final/)
        $script_dir = str_replace('/final', '', $script_dir);
        // Get the base path (everything before /final/)
        $base_path = rtrim($script_dir, '/');
        // If we're at root level, base_path will be empty, so use /
        if (empty($base_path)) {
            $base_path = '/';
        }
    }
    return $base_path;
}

const APP_BASE   = '/final';
const PATH_LOGIN = 'View/login.php';
const PATH_REGISTER = 'View/register.php';
const PATH_HOME  = 'index.php';
const PATH_ADMIN = 'Admin/overview.php';

/* Session keys & roles */
const SESS_USER_ID   = 'customer_id';
const SESS_USER_NAME = 'customer_name';
const SESS_USER_EMAIL = 'customer_email';
const SESS_USER_ROLE = 'user_role';
const ROLE_ADMIN     = 1;
const ROLE_CUSTOMER  = 2;

/* URL helper */
function app_url(string $path): string {
    // Get the base path dynamically (includes user home if present)
    $base_path = get_base_path();
    
    if (str_starts_with($path, '/')) {
        // Already absolute - check if it needs base path
        if (str_starts_with($path, '/final/')) {
            return $base_path . $path;
        } else if (str_starts_with($path, $base_path)) {
            return $path;
        } else {
            return $base_path . APP_BASE . $path;
        }
    } else {
        // Relative path - add base path + /final/ + path
        return $base_path . APP_BASE . '/' . ltrim($path, '/');
    }
}

/* Redirect helper */
function redirect(string $path): void {
    if (preg_match('~^https?://~i', $path)) {
        header('Location: ' . $path);
    } else {
        // Get the base path dynamically (includes user home if present)
        $base_path = get_base_path();
        
        if (str_starts_with($path, '/')) {
            // Already absolute - check if it needs base path
            if (str_starts_with($path, '/final/')) {
                // Has /final/ - prepend base path
                $redirect_url = $base_path . $path;
            } else if (str_starts_with($path, $base_path)) {
                // Already has base path - use as-is
                $redirect_url = $path;
            } else {
                // Absolute path - prepend base path and /final/
                $redirect_url = $base_path . APP_BASE . $path;
            }
        } else {
            // Relative path (e.g., 'index.php' or 'View/login.php')
            // Add base path + /final/ + path
            $redirect_url = $base_path . APP_BASE . '/' . ltrim($path, '/');
        }
        
        // Log for debugging (remove in production if needed)
        error_log("Redirect: path='$path' -> redirect_url='$redirect_url' (base_path='$base_path')");
        
        header('Location: ' . $redirect_url);
    }
    exit;
}

/* JSON responder */
function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json;');
    echo json_encode($data);
    exit;
}

/* Auth / role helpers */
function is_logged_in(): bool {
    return !empty($_SESSION[SESS_USER_ID]);
}

/* Admin helpers */
/* Image path helper - normalizes image paths for display */
function get_image_path(string $image_path): string {
    // If path already starts with ../ or ../../, use as-is (already relative to View folder)
    if (str_starts_with($image_path, '../') || str_starts_with($image_path, '../../')) {
        return $image_path;
    }
    // If path starts with /, it's absolute - use as-is
    if (str_starts_with($image_path, '/')) {
        return $image_path;
    }
    // If path starts with uploads/, add ../ to make it relative to View folder
    if (str_starts_with($image_path, 'uploads/')) {
        return '../' . $image_path;
    }
    // Default: assume it's relative to View folder, add ../
    return '../' . $image_path;
}

/* Image path helper for root-level pages (like index.php) */
function get_root_image_path(string $image_path): string {
    // If path starts with ../../uploads/, convert to ../uploads/ (for root level)
    if (str_starts_with($image_path, '../../uploads/')) {
        return '../' . substr($image_path, 6); // Remove ../../ and add ../
    }
    // If path already starts with ../uploads/, use as-is (correct for root)
    if (str_starts_with($image_path, '../uploads/')) {
        return $image_path;
    }
    // If path starts with /, it's absolute - use as-is
    if (str_starts_with($image_path, '/')) {
        return $image_path;
    }
    // If path starts with uploads/, add ../ to make it relative to root
    if (str_starts_with($image_path, 'uploads/')) {
        return '../' . $image_path;
    }
    // Default: assume it needs ../uploads/ prefix
    return '../uploads/' . ltrim($image_path, '/');
}

function get_new_message_count(): int {
    static $count = null;
    
    // Always get fresh count (static only caches within same page load)
    if ($count === null) {
        require_once __DIR__ . '/../Classes/ContactMessageModel.php';
        $messageModel = new ContactMessageModel();
        $count = $messageModel->getNewMessageCount();
    }
    
    return $count;
}

function current_user_id(): ?int {
    return isset($_SESSION[SESS_USER_ID]) ? (int)$_SESSION[SESS_USER_ID] : null;
}

function current_user_name(): string {
    return $_SESSION[SESS_USER_NAME] ?? '';
}

function current_user_email(): string {
    return $_SESSION[SESS_USER_EMAIL] ?? '';
}

function current_user_role(): int {
    return isset($_SESSION[SESS_USER_ROLE]) ? (int)$_SESSION[SESS_USER_ROLE] : ROLE_CUSTOMER;
}

function is_admin(): bool {
    return current_user_role() === ROLE_ADMIN;
}

function has_role(int $role): bool {
    return current_user_role() === $role;
}

/* Guards */
function require_login(?string $to = null): void {
    if (!is_logged_in()) {
        redirect($to ?? PATH_LOGIN);
    }
}

function require_role(array $allowed, ?string $to = null): void {
    require_login($to);
    if (!in_array(current_user_role(), $allowed, true)) {
        redirect($to ?? PATH_HOME);
    }
}

function require_admin(?string $to = null): void {
    require_login($to ?? PATH_LOGIN);
    if (!is_admin()) {
        redirect($to ?? PATH_HOME);
    }
}

/* CSRF Protection */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* Input sanitization */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/* Flash messages */
function set_flash_message(string $type, string $message): void {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function get_flash_message(): ?array {
    if (isset($_SESSION['flash_type']) && isset($_SESSION['flash_message'])) {
        $message = [
            'type' => $_SESSION['flash_type'],
            'message' => $_SESSION['flash_message']
        ];
        unset($_SESSION['flash_type'], $_SESSION['flash_message']);
        return $message;
    }
    return null;
}

