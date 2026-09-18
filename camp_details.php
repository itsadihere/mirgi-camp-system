<?php
include 'includes/db_connect.php';
include 'includes/config.php';

$campId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$camp = null;

if ($campId) {
    $stmt = mysqli_prepare($conn, "
        SELECT camps.*, diseases.disease_name
        FROM camps
        LEFT JOIN diseases ON camps.disease_id = diseases.disease_id
        WHERE camps.camp_id = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmt, 'i', $campId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $camp = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $camp ? h($camp['camp_name']) . ' | ' . h(APP_NAME) : 'Camp Details | ' . h(APP_NAME); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="public-page">
<?php include 'includes/navbar.php'; ?>

<section class="py-5">
<div class="container">
<?php if (!$camp) { ?>
<div class="alert alert-warning">The requested camp could not be found.</div>
<a href="camps.php" class="btn btn-spiritual">Back to Camps</a>
<?php } else { ?>
<div class="row g-4">
<div class="col-lg-8">
<div class="public-panel h-100">
<span class="badge <?= $camp['status'] === 'upcoming' ? 'bg-success' : 'bg-secondary'; ?> mb-3"><?= h(ucfirst($camp['status'] ?: 'scheduled')); ?></span>
<h1 class="mb-3"><?= h($camp['camp_name']); ?></h1>
<p class="lead text-muted"><?= h($camp['district']); ?>, <?= h($camp['state']); ?></p>
<div class="detail-grid">
<div><strong>Disease Focus</strong><span><?= h($camp['disease_name'] ?: 'General medical support'); ?></span></div>
<div><strong>Camp Date</strong><span><?= h(date('d M Y', strtotime($camp['camp_date']))); ?></span></div>
<div><strong>Time</strong><span><?= h(trim(($camp['start_time'] ?: '') . ' - ' . ($camp['end_time'] ?: ''), ' -') ?: 'Schedule to be announced'); ?></span></div>
<div><strong>Doctor</strong><span><?= h($camp['doctor_name'] ?: 'Assigned medical team'); ?></span></div>
<div><strong>Venue</strong><span><?= h($camp['venue_name'] ?: 'Venue to be announced'); ?></span></div>
<div><strong>Address</strong><span><?= h($camp['address'] ?: 'Address will be shared by the organizers.'); ?></span></div>
</div>
</div>
</div>
<div class="col-lg-4">
<div class="public-panel h-100">
<h2 class="h4 mb-3">Facilities</h2>
<p class="text-muted"><?= nl2br(h($camp['facilities'] ?: 'Facility details will be updated soon.')); ?></p>
<?php if ($camp['status'] === 'upcoming') { ?>
<a href="pre-register.php?camp_id=<?= (int) $camp['camp_id']; ?>" class="btn btn-spiritual w-100">Pre Register for This Camp</a>
<?php } ?>
</div>
</div>
</div>
<?php } ?>
</div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

