<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/role_check.php';

requireRole(['superadmin','admin']);
enforce_post_with_csrf();

$camp_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';

if (!$camp_id || !in_array($status, ['completed', 'upcoming'], true)) {
    exit('Invalid request.');
}

$stmt = mysqli_prepare($conn, 'UPDATE camps SET status = ? WHERE camp_id = ?');
mysqli_stmt_bind_param($stmt, 'si', $status, $camp_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

audit_log($conn, 'camp.status_update', 'camp', $camp_id, ['status' => $status]);

header('Location: ../camps/manage_camps.php');
exit;
?>
