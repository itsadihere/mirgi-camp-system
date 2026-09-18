<?php
include_once __DIR__ . '/config.php';

$currentPage = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');
$homePage = $currentPage === 'index_hi.php' ? 'index_hi.php' : 'index.php';
$navItems = [
    $homePage => 'Home',
    'about.php' => 'About',
    'camps.php' => 'Camps',
    'media.php' => 'Media',
    'gallery.php' => 'Gallery',
    'pre-register.php' => 'Pre Register',
];
?>
<nav class="navbar navbar-expand-lg navbar-dark main-header">
<div class="container">
<a class="navbar-brand d-flex align-items-center fw-bold" href="<?= h(app_url($homePage)); ?>">
<img src="<?= h(app_url(APP_LOGO)); ?>" height="40" class="me-2" alt="<?= h(APP_NAME); ?> logo">
<span>Shree Sarweshwari Samooh</span>
</a>

<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse justify-content-end" id="navbarNav">
<ul class="navbar-nav align-items-center">
<?php foreach ($navItems as $file => $label) { ?>
<li class="nav-item">
<a class="nav-link text-white <?= $currentPage === $file ? 'active' : ''; ?>" href="<?= h(app_url($file)); ?>"><?= h($label); ?></a>
</li>
<?php } ?>
<li class="nav-item ms-3">
<a class="btn btn-light btn-sm px-3" href="<?= h(app_url('login.php')); ?>">Login</a>
</li>
</ul>
</div>
</div>
</nav>
