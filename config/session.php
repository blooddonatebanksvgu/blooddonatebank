<?php
/**
 * Session Configuration
 * Blood Bank Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Check session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header("Location: /blood_bank/login.php?timeout=1");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Get current user role
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user name
function getUserName() {
    return $_SESSION['user_name'] ?? 'User';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /blood_bank/login.php");
        exit();
    }
    checkSessionTimeout();
}

// Require specific role
function requireRole($allowedRoles) {
    requireLogin();
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    if (!in_array(getUserRole(), $allowedRoles)) {
        header("Location: /blood_bank/access_denied.php");
        exit();
    }
}

// Redirect based on role
function redirectToRoleDashboard() {
    $role = getUserRole();
    switch ($role) {
        case 'admin':
            header("Location: /blood_bank/admin/dashboard.php");
            break;
        case 'bloodbank':
            header("Location: /blood_bank/bloodbank/dashboard.php");
            break;
        case 'donor':
            header("Location: /blood_bank/donor/dashboard.php");
            break;
        case 'patient':
            header("Location: /blood_bank/patient/dashboard.php");
            break;
        default:
            header("Location: /blood_bank/login.php");
    }
    exit();
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
