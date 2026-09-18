<?php
/*
 * Local database configuration — NOT for version control.
 * Copy db_config.sample.php to db_config.php on each environment and fill in
 * that server's real credentials. Never commit this file.
 */
return [
    'host'     => '127.0.0.1',
    'user'     => 'root',
    'password' => '',
    'database' => 'mcms_db',
    'port'     => 3307, // XAMPP MariaDB (MySQL 8 occupies 3306 on this machine)
];
