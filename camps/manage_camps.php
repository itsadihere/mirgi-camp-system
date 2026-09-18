<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/header.php';
include '../includes/sidebar.php';

$status_filter = $_GET['status'] ?? '';
if ($status_filter !== '') {
    $status_filter = mysqli_real_escape_string($conn, $status_filter);
    $query = "SELECT camps.*, diseases.disease_name FROM camps JOIN diseases ON camps.disease_id = diseases.disease_id WHERE camps.status='$status_filter' ORDER BY camp_date DESC";
} else {
    $query = 'SELECT camps.*, diseases.disease_name FROM camps JOIN diseases ON camps.disease_id = diseases.disease_id ORDER BY camp_date DESC';
}
$result = mysqli_query($conn, $query);
?>
<div class="content">
<div class="card-box">
<h4>Seva Shivir Directory</h4>
<p>All organized medical outreach camps</p>
<br>
<div class="card-box mb-4">
<h5>Generate Camp Report (PDF)</h5>
<form method="GET" action="../reports/camp_report.php" target="_blank">
<div class="row align-items-end">
<div class="col-md-6">
<label>Select Camp</label>
<select name="camp_id" class="form-control" required>
<option value="">-- Select Camp --</option>
<?php
$camps = mysqli_query($conn, 'SELECT * FROM camps ORDER BY camp_date DESC');
while ($c = mysqli_fetch_assoc($camps)) {
    echo "<option value='{$c['camp_id']}'>" . h($c['camp_name']) . ' (' . h($c['camp_date']) . ")</option>";
}
?>
</select>
</div>
<div class="col-md-3"><button class="btn btn-danger mt-4">Generate PDF Report</button></div>
</div>
</form>
</div>
<br>
<div class="table-responsive">
<table class="table table-bordered table-hover">
<thead style="background:#ffecd9;"><tr><th>ID</th><th>Shivir Name</th><th>Disease Focus</th><th>Date</th><th>Location</th><th>Doctor</th><th>Status</th></tr></thead>
<tbody>
<?php if (mysqli_num_rows($result) > 0) { ?>
<?php while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<td><?= (int) $row['camp_id']; ?></td>
<td><?= h($row['camp_name']); ?></td>
<td><?= h($row['disease_name']); ?></td>
<td><?= h(date('d M Y', strtotime($row['camp_date']))); ?></td>
<td><?= h($row['district']); ?>, <?= h($row['state']); ?></td>
<td><?= h($row['doctor_name']); ?></td>
<td>
<a href="edit_camp.php?id=<?= (int) $row['camp_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
<form method="POST" action="../actions/delete_camp.php" class="d-inline" onsubmit="return confirm('Delete this camp?')">
<?= csrf_input(); ?>
<input type="hidden" name="id" value="<?= (int) $row['camp_id']; ?>">
<button type="submit" class="btn btn-sm btn-danger">Delete</button>
</form>
<form method="POST" action="../actions/update_camp_status.php" class="d-inline">
<?= csrf_input(); ?>
<input type="hidden" name="id" value="<?= (int) $row['camp_id']; ?>">
<?php if ($row['status'] == 'upcoming') { ?>
<input type="hidden" name="status" value="completed">
<button type="submit" class="btn btn-sm btn-success">Mark Complete</button>
<?php } else { ?>
<input type="hidden" name="status" value="upcoming">
<button type="submit" class="btn btn-sm btn-secondary">Reopen</button>
<?php } ?>
</form>
</td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="7" class="text-center">No Seva Shivir Created Yet</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>
</body>
</html>
