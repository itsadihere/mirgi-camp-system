<?php
include 'includes/db_connect.php';
include 'includes/config.php';

$campResult = mysqli_query($conn, "
    SELECT camps.*, diseases.disease_name
    FROM camps
    LEFT JOIN diseases ON camps.disease_id = diseases.disease_id
    ORDER BY
        CASE WHEN camps.status = 'upcoming' THEN 0 ELSE 1 END,
        camps.camp_date ASC
");
$campCount = $campResult ? mysqli_num_rows($campResult) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Camps | <?= h(APP_NAME); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="public-page">
<?php include 'includes/navbar.php'; ?>

<section class="public-hero py-5">
<div class="container">
<div class="row align-items-center g-4">
<div class="col-lg-8">
<span class="eyebrow">Camp directory</span>
<h1 class="display-5 fw-bold mb-3">Explore upcoming and completed medical camps</h1>
<p class="lead text-muted mb-0">Browse outreach locations, dates, disease focus, and camp coordinators. Patients can use the details page before pre-registering.</p>
</div>
<div class="col-lg-4">
<div class="public-panel">
<div class="stat-number"><?= (int) $campCount; ?></div>
<div class="text-muted">Total camps listed</div>
</div>
</div>
</div>
</div>
</section>

<section class="pb-5">
<div class="container">
<?php if ($campResult && $campCount > 0) { ?>
<div class="row g-4">
<?php while ($camp = mysqli_fetch_assoc($campResult)) { ?>
<div class="col-md-6 col-xl-4">
<article class="camp-card h-100">
<div class="d-flex justify-content-between align-items-start mb-3 gap-3">
<span class="badge <?= $camp['status'] === 'upcoming' ? 'bg-success' : 'bg-secondary'; ?>">
<?= h(ucfirst($camp['status'] ?: 'scheduled')); ?>
</span>
<span class="small text-muted"><?= h(date('d M Y', strtotime($camp['camp_date']))); ?></span>
</div>
<h3 class="h5 mb-2"><?= h($camp['camp_name']); ?></h3>
<p class="text-muted mb-3"><?= h($camp['district']); ?>, <?= h($camp['state']); ?></p>
<dl class="small mb-4">
<div><dt class="d-inline fw-semibold">Focus:</dt> <dd class="d-inline ms-1"><?= h($camp['disease_name'] ?: 'General medical camp'); ?></dd></div>
<div><dt class="d-inline fw-semibold">Venue:</dt> <dd class="d-inline ms-1"><?= h($camp['venue_name'] ?: 'Venue to be announced'); ?></dd></div>
<div><dt class="d-inline fw-semibold">Doctor:</dt> <dd class="d-inline ms-1"><?= h($camp['doctor_name'] ?: 'Assigned medical team'); ?></dd></div>
</dl>
<div class="d-flex gap-2 flex-wrap">
<a href="camp_details.php?id=<?= (int) $camp['camp_id']; ?>" class="btn btn-spiritual btn-sm">View Details</a>
<?php if ($camp['status'] === 'upcoming') { ?>
<a href="pre-register.php?camp_id=<?= (int) $camp['camp_id']; ?>" class="btn btn-outline-dark btn-sm">Pre Register</a>
<?php } ?>
</div>
</article>
</div>
<?php } ?>
</div>
<?php } else { ?>
<div class="alert alert-warning text-center">No camps are available right now.</div>
<?php } ?>
</div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

