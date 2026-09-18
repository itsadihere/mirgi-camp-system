<?php
include 'includes/security.php';
include 'includes/db_connect.php';

$message = '';
$error = '';
$status_result = '';
$selectedCampId = filter_input(INPUT_GET, 'camp_id', FILTER_VALIDATE_INT) ?: 0;

if (isset($_POST['check_status'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $status_result = "<div class='alert alert-danger'>Session expired. Please try again.</div>";
    } elseif (request_too_fast('public_status_check', 5)) {
        $status_result = "<div class='alert alert-warning'>Please wait a few seconds before trying again.</div>";
    } else {
        $mobile_check = preg_replace('/\D+/', '', $_POST['check_mobile'] ?? '');

        if (strlen($mobile_check) < 10 || strlen($mobile_check) > 15) {
            $status_result = "<div class='alert alert-danger'>Please enter a valid mobile number.</div>";
        } else {
            $stmt = mysqli_prepare($conn, "
                SELECT pr.approval_status, r.registration_number, c.camp_name, c.camp_date
                FROM patients_master pm
                JOIN pre_registrations pr ON pm.patient_id = pr.patient_id
                LEFT JOIN registrations r ON pm.patient_id = r.patient_id
                LEFT JOIN camps c ON r.camp_id = c.camp_id
                WHERE pm.mobile = ?
                ORDER BY pr.created_at DESC
                LIMIT 1
            ");
            mysqli_stmt_bind_param($stmt, 's', $mobile_check);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $data = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($data) {
                if ($data['approval_status'] === 'pending') {
                    $status_result = "<div class='alert alert-warning'>Your registration is under verification.</div>";
                } elseif ($data['approval_status'] === 'rejected') {
                    $status_result = "<div class='alert alert-danger'>Your registration was rejected. Please contact support.</div>";
                } else {
                    $status_result = "<div class='alert alert-success'><strong>Registration Approved</strong><br>Reg No: " . h($data['registration_number']) . "<br>Camp: " . h($data['camp_name']) . "<br>Date: " . h($data['camp_date']) . "</div>";
                }
            } else {
                $status_result = "<div class='alert alert-danger'>No registration found with this mobile number.</div>";
            }
        }
    }
}

if (isset($_POST['submit'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } elseif (!empty($_POST['website'] ?? '')) {
        $error = 'Invalid submission.';
    } elseif (request_too_fast('public_prereg_submit', 10)) {
        $error = 'Please wait a few seconds before submitting again.';
    } else {
        $name = trim($_POST['full_name'] ?? '');
        $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
        $gender = trim($_POST['gender'] ?? '');
        $mobile = preg_replace('/\D+/', '', $_POST['mobile'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $camp_id = filter_input(INPUT_POST, 'camp_id', FILTER_VALIDATE_INT) ?: null;
        $allowedGenders = ['Male', 'Female', 'Other'];

        if ($name === '' || !$age || $age < 1 || $age > 120 || !in_array($gender, $allowedGenders, true) || strlen($mobile) < 10 || strlen($mobile) > 15 || $state === '' || $district === '') {
            $error = 'Please fill all details correctly before submitting.';
        } else {
            $patientStmt = mysqli_prepare($conn, 'SELECT patient_id FROM patients_master WHERE full_name = ? AND mobile = ? LIMIT 1');
            mysqli_stmt_bind_param($patientStmt, 'ss', $name, $mobile);
            mysqli_stmt_execute($patientStmt);
            $patientResult = mysqli_stmt_get_result($patientStmt);
            $patient = mysqli_fetch_assoc($patientResult);
            mysqli_stmt_close($patientStmt);

            if ($patient) {
                $patient_id = (int) $patient['patient_id'];
            } else {
                $insertPatient = mysqli_prepare($conn, 'INSERT INTO patients_master (full_name, age, gender, mobile, state, district) VALUES (?, ?, ?, ?, ?, ?)');
                mysqli_stmt_bind_param($insertPatient, 'sissss', $name, $age, $gender, $mobile, $state, $district);
                mysqli_stmt_execute($insertPatient);
                $patient_id = mysqli_insert_id($conn);
                mysqli_stmt_close($insertPatient);
            }

            $dupStmt = mysqli_prepare($conn, 'SELECT id FROM pre_registrations WHERE patient_id = ? AND approval_status = ? ORDER BY created_at DESC LIMIT 1');
            $pendingStatus = 'pending';
            mysqli_stmt_bind_param($dupStmt, 'is', $patient_id, $pendingStatus);
            mysqli_stmt_execute($dupStmt);
            $dupResult = mysqli_stmt_get_result($dupStmt);
            $existingPending = mysqli_fetch_assoc($dupResult);
            mysqli_stmt_close($dupStmt);

            if ($existingPending) {
                $error = 'A pending pre-registration already exists for this patient. Please wait for verification.';
            } else {
                $insertPreReg = mysqli_prepare($conn, 'INSERT INTO pre_registrations (patient_id, preferred_state, preferred_district, selected_camp_id) VALUES (?, ?, ?, ?)');
                mysqli_stmt_bind_param($insertPreReg, 'issi', $patient_id, $state, $district, $camp_id);
                mysqli_stmt_execute($insertPreReg);
                mysqli_stmt_close($insertPreReg);
                $message = 'Thank you. Your pre-registration has been received.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Pre Registration</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="public-page bg-spiritual">
<?php include 'includes/navbar.php'; ?>
<div class="container py-5">
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow p-4">
<div class="text-center mb-3"><img src="assets/images/logo.png" height="60" alt="Logo"></div>
<h3 class="text-center mb-4 text-spiritual">Pre Register for Medical Camp</h3>
<?php if ($message !== '') { ?><div class="alert alert-success text-center"><?= h($message); ?></div><?php } ?>
<?php if ($error !== '') { ?><div class="alert alert-danger text-center"><?= h($error); ?></div><?php } ?>
<form method="POST">
<?= csrf_input(); ?>
<input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off">
<div class="mb-3"><label>Full Name</label><input type="text" name="full_name" class="form-control" maxlength="120" required></div>
<div class="mb-3"><label>Age</label><input type="number" name="age" class="form-control" min="1" max="120" required></div>
<div class="mb-3"><label>Gender</label><select name="gender" class="form-select" required><option value="">Select Gender</option><option>Male</option><option>Female</option><option>Other</option></select></div>
<div class="mb-3"><label>Mobile Number</label><input type="text" name="mobile" class="form-control" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" required></div>
<div class="mb-3"><label>State</label><input type="text" name="state" class="form-control" maxlength="100" required></div>
<div class="mb-3"><label>District</label><input type="text" name="district" class="form-control" maxlength="100" required></div>
<div class="mb-3">
<label>Select Upcoming Camp (Optional)</label>
<select name="camp_id" class="form-select">
<option value="">-- Any Upcoming Camp --</option>
<?php
$camps = mysqli_query($conn, "SELECT * FROM camps WHERE status='upcoming' ORDER BY camp_date ASC");
if ($camps && mysqli_num_rows($camps) > 0) {
    while ($c = mysqli_fetch_assoc($camps)) {
        $selected = ((int) $c['camp_id'] === (int) $selectedCampId) ? 'selected' : '';
        echo "<option value='{$c['camp_id']}' {$selected}>" . h($c['camp_name']) . ' - ' . h($c['district']) . ' (' . h($c['camp_date']) . ")</option>";
    }
} else {
    echo "<option value=''>No Upcoming Camps Available</option>";
}
?>
</select>
</div>
<div class="d-grid"><button type="submit" name="submit" class="btn btn-spiritual">Submit Pre Registration</button></div>
</form>
</div>
</div>
</div>
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow p-4">
<h4 class="text-center text-spiritual mb-3">Check Your Registration Status</h4>
<form method="POST">
<?= csrf_input(); ?>
<div class="mb-3"><input type="text" name="check_mobile" class="form-control" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" placeholder="Enter Mobile Number" required></div>
<div class="d-grid"><button type="submit" name="check_status" class="btn btn-spiritual">Check Status</button></div>
</form>
<br>
<?= $status_result ?>
</div>
</div>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

