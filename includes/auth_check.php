<?php
include_once __DIR__ . '/security.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ' . app_url('login.php'));
    exit;
}

// Idle-session timeout: log the user out after a period of inactivity
// (important on shared clinic computers).
if (!defined('SESSION_IDLE_TIMEOUT')) {
    define('SESSION_IDLE_TIMEOUT', 1800); // 30 minutes
}

if (isset($_SESSION['last_activity']) && (time() - (int) $_SESSION['last_activity']) > SESSION_IDLE_TIMEOUT) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . app_url('login.php?timeout=1'));
    exit;
}

$_SESSION['last_activity'] = time();
?>
