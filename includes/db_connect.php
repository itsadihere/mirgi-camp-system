<?php

// Load environment-specific DB credentials from an untracked config file,
// falling back to safe defaults if it is absent.
$__dbConfigFile = __DIR__ . '/db_config.php';
$dbConfig = is_file($__dbConfigFile) ? require $__dbConfigFile : [];

$host     = $dbConfig['host']     ?? '127.0.0.1';
$user     = $dbConfig['user']     ?? 'root';
$password = $dbConfig['password'] ?? '';
$database = $dbConfig['database'] ?? 'mcms_db';
$port     = $dbConfig['port']     ?? 3306;

$conn = mysqli_connect($host, $user, $password, $database, (int) $port);

if (!$conn) {
    // Log the real error; never expose connection internals to the browser.
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(503);
    die('Service temporarily unavailable. Please try again later.');
}

include_once __DIR__ . '/audit.php';
include_once __DIR__ . '/whatsapp_service.php';
whatsapp_bootstrap($conn);
?>
