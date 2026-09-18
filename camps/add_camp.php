<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/role_check.php';

requireRole(['superadmin', 'admin']);

include '../includes/header.php';
include '../includes/sidebar.php';

$message = '';
$error = '';

if (isset($_POST['save_camp'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
    $camp_name = trim($_POST['camp_name'] ?? '');
    $disease_id = filter_input(INPUT_POST, 'disease_id', FILTER_VALIDATE_INT);
    $camp_date = $_POST['camp_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $venue_name = trim($_POST['venue_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $doctor_name = trim($_POST['doctor_name'] ?? '');
    $facilities = trim($_POST['facilities'] ?? '');

    if ($camp_name === '' || !$disease_id || $camp_date === '') {
        $error = 'Please fill the required camp details.';
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO camps (camp_name, disease_id, camp_date, start_time, end_time, venue_name, address, state, district, doctor_name, facilities) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sisssssssss', $camp_name, $disease_id, $camp_date, $start_time, $end_time, $venue_name, $address, $state, $district, $doctor_name, $facilities);
        mysqli_stmt_execute($stmt);
        $campId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        audit_log($conn, 'camp.create', 'camp', $campId, ['name' => $camp_name, 'date' => $camp_date]);

        $queued = whatsapp_schedule_new_camp_state_alerts($conn, $campId);
        $message = 'Camp created successfully.' . ($queued > 0 ? ' WhatsApp queue prepared for ' . $queued . ' patients.' : '');
    }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Medical Camp</title>
</head>
<body>
<div class="content">
<div class="card-box">
<h4>Create Epilepsy Camp</h4>
<p>Create a new medical seva camp</p>
<?php if ($message !== '') { ?><div class="alert alert-success"><?= h($message); ?></div><?php } ?>
<?php if ($error !== '') { ?><div class="alert alert-danger"><?= h($error); ?></div><?php } ?>
<form method="POST">
<?= csrf_input(); ?>
<h5 class="mt-3">Shivir Details</h5>
<div class="row">
<div class="col-md-6"><label>Shivir Name</label><input type="text" name="camp_name" class="form-control" required></div>
<div class="col-md-6"><label>Disease Focus</label><select name="disease_id" class="form-control" required><option value="">Select Disease</option><?php $diseases = mysqli_query($conn, 'SELECT * FROM diseases'); while ($d = mysqli_fetch_assoc($diseases)) { echo "<option value='{$d['disease_id']}'>" . h($d['disease_name']) . '</option>'; } ?></select></div>
</div>
<h5 class="mt-4">Date & Time</h5>
<div class="row"><div class="col-md-4"><label>Camp Date</label><input type="date" name="camp_date" class="form-control" required></div><div class="col-md-4"><label>Start Time</label><input type="time" name="start_time" class="form-control"></div><div class="col-md-4"><label>End Time</label><input type="time" name="end_time" class="form-control"></div></div>
<h5 class="mt-4">Location Details</h5>
<div class="row"><div class="col-md-6"><label>Venue Name</label><input type="text" name="venue_name" class="form-control"></div><div class="col-md-6"><label>Doctor Name</label><input type="text" name="doctor_name" class="form-control"></div><div class="col-md-6 mt-3"><label>State</label><input type="text" name="state" class="form-control"></div><div class="col-md-6 mt-3"><label>District</label><input type="text" name="district" class="form-control"></div><div class="col-md-12 mt-3"><label>Full Address</label><textarea name="address" class="form-control"></textarea></div></div>
<h5 class="mt-4">Facilities & Services</h5>
<div class="row"><div class="col-md-12"><label>Available Facilities</label><textarea name="facilities" class="form-control" placeholder="Medicines, Diagnosis, Surgery screening etc."></textarea></div></div>
<br>
<button type="submit" name="save_camp" class="btn btn-spiritual">Create Seva Shivir</button>
</form>
</div>
</div>
</body>
</html>
