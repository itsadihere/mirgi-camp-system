<?php
/* ================= LOGOUT SYSTEM ================= */

include 'includes/security.php';

if (!empty($_SESSION['user_id'])) {
    include 'includes/db_connect.php';
    audit_log($conn, 'auth.logout', 'user', $_SESSION['user_id']);
}

/* Unset all session variables and destroy the session */
$_SESSION = array();
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

/* Prevent browser caching logged pages */
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

header('Location: ' . app_url('login.php'));
exit;
