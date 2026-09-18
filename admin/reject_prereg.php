<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/role_check.php';

requireRole(['superadmin', 'admin']);
enforce_post_with_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $status = 'rejected';
    $stmt = mysqli_prepare($conn, 'UPDATE pre_registrations SET approval_status = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    audit_log($conn, 'prereg.reject', 'pre_registration', $id);
}

header('Location: manage_preregistrations.php');
exit;
?>
