<?php
include '../includes/db_connect.php';

$camp_id = $_GET['camp_id'];

$query = "SELECT registrations.registration_number,
                 patients_master.full_name,
                 attendance.attendance_status
          FROM attendance
          JOIN registrations
          ON attendance.registration_id = registrations.registration_id
          JOIN patients_master
          ON registrations.patient_id = patients_master.patient_id
          WHERE registrations.camp_id='$camp_id'";
?>
<!DOCTYPE html>
<html>
<head>
<title>Print Attendance</title>
<style>
table { border-collapse: collapse; width:100%; }
th,td { border:1px solid #000; padding:8px; text-align:center; }
</style>
</head>
<body onload="window.print()">

<h3>Camp Attendance Report</h3>

<table>
<tr>
<th>Reg No</th>
<th>Name</th>
<th>Status</th>
</tr>

<?php
$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){
echo "<tr>
<td>{$row['registration_number']}</td>
<td>{$row['full_name']}</td>
<td>{$row['attendance_status']}</td>
</tr>";
}
?>

</table>

</body>
</html>