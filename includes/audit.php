<?php
/*
 * Audit trail for accountability on medical/PII data.
 *
 * Call audit_log() after any sensitive state change (login, patient/user/camp
 * create-update-delete, campaign sends). Records who did what, to which record,
 * from where, and when.
 */

function audit_ensure_schema($conn)
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS audit_log (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        username VARCHAR(150) NULL,
        action VARCHAR(100) NOT NULL,
        entity_type VARCHAR(60) NULL,
        entity_id VARCHAR(60) NULL,
        details TEXT NULL,
        ip_address VARCHAR(45) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_audit_created (created_at),
        KEY idx_audit_user (user_id),
        KEY idx_audit_action (action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $initialized = true;
}

function audit_log($conn, $action, $entityType = null, $entityId = null, $details = null)
{
    audit_ensure_schema($conn);

    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    $username = $_SESSION['admin_name'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $entityId = ($entityId === null) ? null : (string) $entityId;
    if (is_array($details)) {
        $details = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $stmt = mysqli_prepare(
        $conn,
        'INSERT INTO audit_log (user_id, username, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        return;
    }
    mysqli_stmt_bind_param($stmt, 'issssss', $userId, $username, $action, $entityType, $entityId, $details, $ip);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
