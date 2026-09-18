<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/role_check.php';

requireRole(['superadmin','admin']);
enforce_post_with_csrf();

$camp_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$camp_id) {
    exit('Invalid request.');
}

mysqli_query($conn, "
DELETE attendance FROM attendance
JOIN registrations
ON attendance.registration_id = registrations.registration_id
WHERE registrations.camp_id='$camp_id'
");

mysqli_query($conn, "DELETE FROM registrations WHERE camp_id='$camp_id'");
mysqli_query($conn, "DELETE FROM camps WHERE camp_id='$camp_id'");

audit_log($conn, 'camp.delete', 'camp', $camp_id);

header('Location: ../camps/manage_camps.php');
exit;
?>
