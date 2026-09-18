<?php
include '../includes/db_connect.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=registrations.xls");

echo "Reg No\tName\tMobile\tCamp\n";

$query = "SELECT registrations.registration_number,
                 patients_master.full_name,
                 patients_master.mobile,
                 camps.camp_name
          FROM registrations
          JOIN patients_master
          ON registrations.patient_id = patients_master.patient_id
          JOIN camps
          ON registrations.camp_id = camps.camp_id";

$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){
echo "{$row['registration_number']}\t
{$row['full_name']}\t
{$row['mobile']}\t
{$row['camp_name']}\n";
}
?>