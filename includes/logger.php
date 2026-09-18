<?php
/*
 * Central application logging + global error handling.
 *
 * Logs are written to a daily file in the (web-inaccessible) logs/ directory.
 * In production (APP_DEBUG=false) uncaught errors show a generic message to the
 * user while the real detail is logged; in debug they surface on screen.
 */

if (!defined('APP_LOG_DIR')) {
    define('APP_LOG_DIR', __DIR__ . '/../logs');
}

if (!function_exists('app_log')) {
    function app_log($level, $message, array $context = [])
    {
        if (!is_dir(APP_LOG_DIR)) {
            @mkdir(APP_LOG_DIR, 0750, true);
        }

        $user = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : '-';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '-';
        $line = '[' . date('Y-m-d H:i:s') . '] ' . strtoupper($level) . ': ' . $message;
        if (!empty($context)) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $line .= " [user={$user} ip={$ip}]" . PHP_EOL;

        @error_log($line, 3, APP_LOG_DIR . '/app-' . date('Y-m-d') . '.log');
    }
}

// Route PHP's own error log into the same directory.
if (!is_dir(APP_LOG_DIR)) {
    @mkdir(APP_LOG_DIR, 0750, true);
}
@ini_set('error_log', APP_LOG_DIR . '/php-' . date('Y-m-d') . '.log');

set_exception_handler(function ($e) {
    app_log('error', 'Uncaught exception: ' . $e->getMessage(), [
        'file' => $e->getFile() . ':' . $e->getLine(),
    ]);

    if (!defined('APP_DEBUG') || !APP_DEBUG) {
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo 'An unexpected error occurred. Please try again later.';
    } else {
        echo 'Exception: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
});

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        app_log('fatal', $err['message'], ['file' => ($err['file'] ?? '?') . ':' . ($err['line'] ?? '?')]);
        if ((!defined('APP_DEBUG') || !APP_DEBUG) && !headers_sent()) {
            http_response_code(500);
        }
    }
});
