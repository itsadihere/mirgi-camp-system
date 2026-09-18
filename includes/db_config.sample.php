<?php
/*
 * Sample database configuration. Copy this file to db_config.php and set the
 * real credentials for the target server. db_config.php itself must stay out
 * of version control (see .gitignore).
 */
return [
    'host'     => '127.0.0.1',
    'user'     => 'mcms_app',      // dedicated least-privilege DB user (not root)
    'password' => 'CHANGE_ME',     // strong password, set per environment
    'database' => 'mcms_db',
    'port'     => 3306,
];
