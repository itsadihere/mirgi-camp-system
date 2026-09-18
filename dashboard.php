<?php
include 'includes/auth_check.php';
include 'includes/db_connect.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$total_camps = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) as count FROM camps'))['count'];
$total_patients = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) as count FROM registrations'))['count'];
$total_registrations = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) as count FROM registrations'))['count'];
$total_present = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE attendance_status='Present'"))['count'];
$upcoming_camps = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM camps WHERE status='upcoming'"))['count'];
$completed_camps = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM camps WHERE status='completed'"))['count'];
$total_prereg = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) as count FROM pre_registrations'))['count'];
$pending_prereg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM pre_registrations WHERE approval_status='pending'"))['count'];
$approved_prereg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM pre_registrations WHERE approval_status='approved'"))['count'];
$diseaseQuery = "SELECT diseases.disease_name, COUNT(camps.camp_id) as total FROM camps JOIN diseases ON camps.disease_id = diseases.disease_id GROUP BY diseases.disease_name";
$diseaseResult = mysqli_query($conn, $diseaseQuery);
?>
<html>
<head><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body>
<div class="content">
<div class="card-box text-center">
<h2>अघोरान्ना परो मन्त्रो नास्ति तत्वं गुरो परम्</h2>
<p>Through medical seva, we serve humanity beyond disease, pain, and limitation.</p>
</div><br>
<h3>Seva Analytics Dashboard</h3>
<p>Welcome, <?= h($_SESSION['admin_name'] ?? 'User'); ?></p>
<div class="row g-4">
<div class="col-md-4"><a href="camps/manage_camps.php"><div class="card-box text-center"><h5>Total Medical Camps</h5><h2><?= (int) $total_camps; ?></h2></div></a></div>
<div class="col-md-4"><a href="patients/manage_patients.php"><div class="card-box text-center"><h5>Total Patients</h5><h2><?= (int) $total_patients; ?></h2></div></a></div>
<div class="col-md-4"><a href="patients/manage_patients.php"><div class="card-box text-center"><h5>Total Registration</h5><h2><?= (int) $total_registrations; ?></h2></div></a></div>
<div class="col-md-4"><a href="patients/mark_attendance.php"><div class="card-box text-center"><h5>Attendance Patients</h5><h2><?= (int) $total_present; ?></h2></div></a></div>
<div class="col-md-4"><a href="camps/manage_camps.php?status=upcoming"><div class="card-box text-center"><h5>Upcoming Camps</h5><h2><?= (int) $upcoming_camps; ?></h2></div></a></div>
<div class="col-md-4"><a href="camps/manage_camps.php?status=completed"><div class="card-box text-center"><h5>Completed Camps</h5><h2><?= (int) $completed_camps; ?></h2></div></a></div>
<div class="col-md-4"><a href="admin/manage_gallery.php"><div class="card-box text-center"><h5>Manage Gallery</h5><p>Upload & View Photos</p></div></a></div>
<div class="col-md-4"><a href="admin/manage_preregistrations.php" style="text-decoration:none;"><div class="card-box text-center"><h5>Pre-Registrations</h5><h2><?= (int) $total_prereg; ?></h2><p class="mb-0">Pending: <?= (int) $pending_prereg; ?> | Approved: <?= (int) $approved_prereg; ?></p></div></a></div>
<div class="col-md-4"><a href="admin/manage_media.php"><div class="card-box text-center"><h5>Manage Media Page</h5><p>Upload, Edit & View Media</p></div></a></div>

</div>
<div class="card-box mt-5">
<h4>Disease-wise Medical Camps</h4>
<table class="table table-bordered table-hover mt-3">
<thead style="background:#ffecd9;"><tr><th>Disease</th><th>Total Camps</th></tr></thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($diseaseResult)) { ?>
<tr><td><?= h($row['disease_name']); ?></td><td><?= (int) $row['total']; ?></td></tr>
<?php } ?>
</tbody>
</table>
</div>

</div>
</body>
</html>
