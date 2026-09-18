<?php include 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About | <?= h(APP_NAME); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="public-page">
<?php include 'includes/navbar.php'; ?>

<section class="public-hero public-hero-soft py-5">
<div class="container">
<div class="row align-items-center g-4">
<div class="col-lg-7">
<span class="eyebrow">About the initiative</span>
<h1 class="display-5 fw-bold text-spiritual mb-3">Service, dignity, and accessible care at the center of every camp</h1>
<p class="lead text-muted mb-0">Shree Sarweshwari Samooh carries forward a public-service mission rooted in compassion, discipline, and community health outreach.</p>
</div>
<div class="col-lg-5">
<div class="public-panel">
<img src="assets/images/baba2.jpg" class="rounded-4 shadow-sm mb-3" alt="Aghoreshwar Bhagwan Ram Ji">
<p class="mb-0 text-muted">The medical camp program connects spiritual service with practical healthcare support for underserved patients.</p>
</div>
</div>
</div>
</div>
</section>

<section class="py-5 bg-white orangeborder">
<div class="container">
<div class="row align-items-center g-4">
<div class="col-md-4 text-center">
<div class="baba-frame-container">
<div class="baba-frame mb-4">
<img src="assets/images/baba2.jpg" alt="Baba Bhagwan Ram portrait one">
</div>
<div class="baba-frame">
<img src="assets/images/baba1.jpg" alt="Baba Bhagwan Ram portrait two">
</div>
</div>
</div>
<div class="col-md-8">
<h2 class="text-spiritual mb-3">Shree Sarweshwari Samooh</h2>
<p>Founded on September 21, 1961, Shree Sarweshwari Samooh is a socio-spiritual movement inspired by the teachings of Parampujya Aghoreshwar Bhagwan Ram Ji. Its work brings the values of Aghor into everyday social action.</p>
<p>The organization’s service philosophy centers on reducing suffering through healthcare access, social reform, and community participation. Medical camps are one expression of that wider mission.</p>
<div class="row g-3 mt-1">
<div class="col-sm-6">
<div class="public-panel h-100">
<h5 class="mt-0">Social reform</h5>
<p class="mb-0 text-muted">Campaigns against exclusion, addiction, and practices that harm dignity and public wellbeing.</p>
</div>
</div>
<div class="col-sm-6">
<div class="public-panel h-100">
<h5 class="mt-0">Healthcare outreach</h5>
<p class="mb-0 text-muted">Free consultations, patient support, and focused camps designed to reach families with limited access to care.</p>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

<section class="py-5">
<div class="container">
<div class="row g-4">
<div class="col-md-6">
<div class="public-panel h-100">
<h3 class="text-spiritual">Epilepsy care and beyond</h3>
<p class="mb-0">The trust is widely recognized for free epilepsy camps that support patients from different parts of India and neighboring regions. These camps combine diagnosis, consultation, medicine access, and continuity planning.</p>
</div>
</div>
<div class="col-md-6">
<div class="public-panel h-100">
<h3 class="text-spiritual">A model of seva</h3>
<p class="mb-0">Alongside camp-based treatment, the larger mission emphasizes self-reliance, disciplined volunteerism, and holistic wellbeing through community-centered care.</p>
</div>
</div>
</div>
<div class="text-center mt-4">
<a href="camps.php" class="btn btn-spiritual px-4 me-2">View Camps</a>
<a href="pre-register.php" class="btn btn-outline-dark px-4">Pre Register</a>
</div>
</div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

