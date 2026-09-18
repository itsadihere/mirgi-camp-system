<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/role_check.php';

requireRole(['superadmin']);
enforce_post_with_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    exit('Invalid request.');
}

$stmt = mysqli_prepare($conn, 'UPDATE attendance SET is_deleted = 1 WHERE attendance_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

audit_log($conn, 'attendance.delete', 'attendance', $id);

header('Location: ../dashboard.php');
exit;
