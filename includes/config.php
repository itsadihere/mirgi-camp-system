<?php

/* ========== APPLICATION CONFIG ========== */

/* Environment detection: debug on localhost, silent in production.
   Errors are always logged; they are only shown on screen in debug. */
if (!defined('APP_DEBUG')) {
    $__host = strtolower($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ''));
    $__isLocal = $__host === 'localhost'
        || strpos($__host, 'localhost:') === 0
        || strpos($__host, '127.0.0.1') === 0
        || strpos($__host, '::1') === 0;
    define('APP_DEBUG', $__isLocal);
}

error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', APP_DEBUG ? '1' : '0');

require_once __DIR__ . '/logger.php';

if (!defined('APP_NAME')) {
    define('APP_NAME', 'AghorArogya');
}

if (!defined('APP_TAGLINE')) {
    define('APP_TAGLINE', 'Maa Sarveshwari Medical Seva Portal');
}

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', '/medical-camp-system');
}

/* Future editable */

if (!defined('APP_LOGO')) {
    define('APP_LOGO', 'assets/images/logo.png');
}

if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('app_url')) {
    function app_url($path = '')
    {
        $path = ltrim($path, '/');
        return APP_BASE_PATH . ($path !== '' ? '/' . $path : '');
    }
}

?>
