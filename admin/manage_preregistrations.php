<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/role_check.php';

requireRole(['superadmin', 'admin']);

$where = "pr.approval_status='pending'";

if (isset($_GET['status']) && $_GET['status'] != '') {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $where .= " AND pr.approval_status='$status'";
}

if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (pm.full_name LIKE '%$search%' OR pm.mobile LIKE '%$search%')";
}

$query = "
SELECT pr.id, pr.approval_status, pr.created_at, pm.full_name, pm.mobile, pm.state, pm.district
FROM pre_registrations pr
JOIN patients_master pm ON pr.patient_id = pm.patient_id
WHERE $where
ORDER BY pr.created_at DESC
";
$result = mysqli_query($conn, $query);
?>

<div class="content">
<h3>Pre-Registered Patients</h3>
<form method="GET" class="row g-2 mb-3">
<div class="col-md-3">
<select name="status" class="form-select">
<option value="">All Status</option>
<option value="pending" <?= (($_GET['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
<option value="approved" <?= (($_GET['status'] ?? '') === 'approved') ? 'selected' : ''; ?>>Approved</option>
<option value="rejected" <?= (($_GET['status'] ?? '') === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
</select>
</div>
<div class="col-md-4">
<input type="text" name="search" placeholder="Search Name or Mobile" value="<?= h($_GET['search'] ?? ''); ?>" class="form-control">
</div>
<div class="col-md-2">
<button class="btn btn-primary w-100">Filter</button>
</div>
</form>
<table class="table table-bordered table-hover">
<thead style="background:#ffecd9;">
<tr><th>Name</th><th>Mobile</th><th>State</th><th>District</th><th>Status</th><th>Action</th></tr>
</thead>
<tbody>
<?php if ($result && mysqli_num_rows($result) > 0) { ?>
<?php while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<td><?= h($row['full_name']); ?></td>
<td><?= h($row['mobile']); ?></td>
<td><?= h($row['state']); ?></td>
<td><?= h($row['district']); ?></td>
<td>
<?php
if ($row['approval_status'] == 'pending') {
    echo "<span class='badge bg-warning'>Pending</span>";
} elseif ($row['approval_status'] == 'approved') {
    echo "<span class='badge bg-success'>Approved</span>";
} else {
    echo "<span class='badge bg-danger'>Rejected</span>";
}
?>
</td>
<td>
<?php if ($row['approval_status'] == 'pending') { ?>
<a href="approve_prereg.php?id=<?= (int) $row['id']; ?>" class="btn btn-sm btn-success">Assign</a>
<form method="POST" action="reject_prereg.php" class="d-inline" onsubmit="return confirm('Reject this pre-registration?')">
<?= csrf_input(); ?>
<input type="hidden" name="id" value="<?= (int) $row['id']; ?>">
<button type="submit" class="btn btn-sm btn-danger">Reject</button>
</form>
<?php } else { ?>
<span class="text-muted">No Action</span>
<?php } ?>
</td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><td colspan="6" class="text-center">No Records Found</td></tr>
<?php } ?>
</tbody>
</table>
</div>
