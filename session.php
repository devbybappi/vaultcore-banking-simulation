<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ini_set('session.cookie_secure', '1');
    session_start();
}

// Log out authenticated users after 30 minutes of inactivity.
if (!empty($_SESSION['user_id'])) {
    $now = time();
    if (isset($_SESSION['last_activity']) && $now - (int)$_SESSION['last_activity'] > 1800) {
        $_SESSION = [];
        session_regenerate_id(true);
    } else {
        $_SESSION['last_activity'] = $now;
    }
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($submitted) {
    return is_string($submitted) && hash_equals(csrfToken(), $submitted);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function requireLogin($allowedTypes = []) {
    if (!isLoggedIn()) {
        header('Location: ../index.php?error=Please login first');
        exit;
    }
    if (!empty($allowedTypes) && !in_array(getUserType(), $allowedTypes)) {
        header('Location: ../index.php?error=Access denied');
        exit;
    }
}
?>