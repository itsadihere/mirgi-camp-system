<?php
include_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    // Detect HTTPS (direct or via reverse proxy) so the Secure flag is only
    // set when the connection can actually carry a secure cookie.
    $isSecure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443)
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input()
    {
        return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token)
    {
        return is_string($token)
            && !empty($_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $token);
    }
}

if (!function_exists('enforce_post_with_csrf')) {
    function enforce_post_with_csrf()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            exit('Invalid request.');
        }
    }
}

if (!function_exists('request_too_fast')) {
    function request_too_fast($key, $seconds)
    {
        $now = time();
        $last = $_SESSION['_rate_limit'][$key] ?? 0;

        if (($now - $last) < $seconds) {
            return true;
        }

        $_SESSION['_rate_limit'][$key] = $now;
        return false;
    }
}
?>
