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

$attendance = mysqli_prepare($conn, 'UPDATE attendance SET is_deleted = 1 WHERE registration_id = ?');
mysqli_stmt_bind_param($attendance, 'i', $id);
mysqli_stmt_execute($attendance);
mysqli_stmt_close($attendance);

$registration = mysqli_prepare($conn, 'UPDATE registrations SET is_deleted = 1 WHERE registration_id = ?');
mysqli_stmt_bind_param($registration, 'i', $id);
mysqli_stmt_execute($registration);
mysqli_stmt_close($registration);

audit_log($conn, 'registration.delete', 'registration', $id);

header('Location: ../patients/manage_patients.php');
exit;
?>
