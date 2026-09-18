<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/role_check.php';

requireRole(['superadmin', 'admin']);

$prereg_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$prereg_id) {
    die('Invalid Request');
}

$stmt = mysqli_prepare($conn, '
SELECT pr.*, pm.*
FROM pre_registrations pr
JOIN patients_master pm ON pr.patient_id = pm.patient_id
WHERE pr.id = ?
LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $prereg_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    die('Record Not Found');
}

$message = '';
$error = '';

if (isset($_POST['assign'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $camp_id = filter_input(INPUT_POST, 'camp_id', FILTER_VALIDATE_INT);
        $patient_id = (int) $data['patient_id'];

        if (!$camp_id) {
            $error = 'Please select a camp.';
        } else {
            mysqli_begin_transaction($conn);

            try {
                $campStmt = mysqli_prepare($conn, 'SELECT camp_date FROM camps WHERE camp_id = ? AND status = ? LIMIT 1');
                $upcoming = 'upcoming';
                mysqli_stmt_bind_param($campStmt, 'is', $camp_id, $upcoming);
                mysqli_stmt_execute($campStmt);
                $campResult = mysqli_stmt_get_result($campStmt);
                $camp = mysqli_fetch_assoc($campResult);
                mysqli_stmt_close($campStmt);

                if (!$camp) {
                    throw new Exception('Selected camp is not available.');
                }

                $duplicateStmt = mysqli_prepare($conn, 'SELECT registration_id FROM registrations WHERE patient_id = ? AND camp_id = ? LIMIT 1');
                mysqli_stmt_bind_param($duplicateStmt, 'ii', $patient_id, $camp_id);
                mysqli_stmt_execute($duplicateStmt);
                $duplicateResult = mysqli_stmt_get_result($duplicateStmt);
                $duplicate = mysqli_fetch_assoc($duplicateResult);
                mysqli_stmt_close($duplicateStmt);

                if ($duplicate) {
                    throw new Exception('This patient is already registered for the selected camp.');
                }

                $prefix = date('d-m-Y', strtotime($camp['camp_date'])) . '-';
                $likePrefix = $prefix . '%';
                $sequenceStmt = mysqli_prepare($conn, 'SELECT registration_number FROM registrations WHERE camp_id = ? AND registration_number LIKE ? ORDER BY registration_id DESC LIMIT 1');
                mysqli_stmt_bind_param($sequenceStmt, 'is', $camp_id, $likePrefix);
                mysqli_stmt_execute($sequenceStmt);
                $sequenceResult = mysqli_stmt_get_result($sequenceStmt);
                $lastRow = mysqli_fetch_assoc($sequenceResult);
                mysqli_stmt_close($sequenceStmt);

                $nextSequence = 1;
                if ($lastRow && preg_match('/(\d+)$/', $lastRow['registration_number'], $matches)) {
                    $nextSequence = ((int) $matches[1]) + 1;
                }
                $reg_number = $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);

                $insertStmt = mysqli_prepare($conn, 'INSERT INTO registrations (patient_id, camp_id, registration_number) VALUES (?, ?, ?)');
                mysqli_stmt_bind_param($insertStmt, 'iis', $patient_id, $camp_id, $reg_number);
                mysqli_stmt_execute($insertStmt);
                mysqli_stmt_close($insertStmt);

                $approvalStatus = 'approved';
                $verifiedBy = (int) $_SESSION['user_id'];
                $updateStmt = mysqli_prepare($conn, 'UPDATE pre_registrations SET approval_status = ?, selected_camp_id = ?, verified_by = ? WHERE id = ?');
                mysqli_stmt_bind_param($updateStmt, 'siii', $approvalStatus, $camp_id, $verifiedBy, $prereg_id);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);

                mysqli_commit($conn);
                audit_log($conn, 'prereg.approve', 'pre_registration', $prereg_id, ['camp_id' => $camp_id, 'reg_no' => $reg_number]);
                $message = 'Patient assigned successfully. Reg No: ' . $reg_number;
            } catch (Throwable $e) {
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Assign Camp</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h4>Assign Camp to Pre-Registered Patient</h4>
<?php if ($message !== '') { ?><div class="alert alert-success"><?= h($message); ?></div><?php } ?>
<?php if ($error !== '') { ?><div class="alert alert-danger"><?= h($error); ?></div><?php } ?>
<p><strong>Name:</strong> <?= h($data['full_name']); ?></p>
<p><strong>Mobile:</strong> <?= h($data['mobile']); ?></p>
<p><strong>State:</strong> <?= h($data['state']); ?></p>
<p><strong>District:</strong> <?= h($data['district']); ?></p>
<hr>
<form method="POST">
<?= csrf_input(); ?>
<label>Select Camp</label>
<select name="camp_id" class="form-select" required>
<option value="">-- Select Upcoming Camp --</option>
<?php
$camps = mysqli_query($conn, "SELECT * FROM camps WHERE status='upcoming' ORDER BY camp_date ASC");
while ($c = mysqli_fetch_assoc($camps)) {
    echo "<option value='{$c['camp_id']}'>" . h($c['camp_name']) . ' - ' . h($c['district']) . ' (' . h($c['camp_date']) . ")</option>";
}
?>
</select>
<br>
<button name="assign" class="btn btn-success">Assign & Generate Registration</button>
</form>
</body>
</html>

