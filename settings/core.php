<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();

/* Paths */
const APP_BASE   = '/final';
const PATH_LOGIN = 'View/login.php';
const PATH_REGISTER = 'View/register.php';
const PATH_HOME  = 'index.php';
const PATH_ADMIN = 'admin/overview.php';

/* Session keys & roles */
const SESS_USER_ID   = 'customer_id';
const SESS_USER_NAME = 'customer_name';
const SESS_USER_EMAIL = 'customer_email';
const SESS_USER_ROLE = 'user_role';
const ROLE_ADMIN     = 1;
const ROLE_CUSTOMER  = 2;

/* URL helper */
function app_url(string $path): string {
    return APP_BASE . '/' . ltrim($path, '/');
}

/* Redirect helper */
function redirect(string $path): void {
    if (preg_match('~^https?://~i', $path)) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . app_url($path));
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

