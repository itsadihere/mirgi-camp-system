<?php
include '../includes/db_connect.php';

$camp_id = $_GET['camp_id'];

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=camp_attendance.xls");

echo "Reg No\tName\tStatus\n";

$query = "SELECT registrations.registration_number,
                 patients_master.full_name,
                 attendance.attendance_status
          FROM attendance
          JOIN registrations
          ON attendance.registration_id = registrations.registration_id
          JOIN patients_master
          ON registrations.patient_id = patients_master.patient_id
          WHERE registrations.camp_id='$camp_id'";

$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){
echo "{$row['registration_number']}\t
{$row['full_name']}\t
{$row['attendance_status']}\n";
}
?>