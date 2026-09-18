<?php
include 'includes/config.php';
include 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gallery | Shree Sarweshwari Samooh</title>
<link href="assets/css/style.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="public-page">
<?php include 'includes/navbar.php'; ?>
<section class="py-4 text-center bg-light"><div class="container"><h2 class="text-spiritual">Our Medical Camps</h2><p class="text-muted">Visual journey of our humanitarian medical outreach initiatives</p></div></section>
<div class="container py-5">
<?php
$albums = mysqli_query($conn, 'SELECT * FROM albums ORDER BY album_id DESC');
if (mysqli_num_rows($albums) == 0) {
    echo "<div class='alert alert-warning text-center'>No Albums Available</div>";
}
while ($album = mysqli_fetch_assoc($albums)) {
    $albumId = (int) $album['album_id'];
    $images = mysqli_query($conn, "SELECT * FROM gallery_images WHERE album_id='{$albumId}'");
    $image_count = mysqli_num_rows($images);
?>
<div class="album-section">
<div class="album-title d-flex justify-content-between align-items-center">
<span><?= h($album['album_name']); ?></span>
<small><?= (int) $image_count; ?> Photos</small>
</div>
<?php if ($image_count > 0) { ?>
<div class="album-slider">
<button class="slider-btn left">‹</button>
<div class="album-window"><div class="album-track">
<?php while ($img = mysqli_fetch_assoc($images)) { ?>
<img src="assets/images/gallery/<?= $albumId; ?>/<?= rawurlencode($img['file_name']); ?>" class="album-img" alt="<?= h($album['album_name']); ?>" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="openImage(this.src)">
<?php } ?>
<?php mysqli_data_seek($images, 0); ?>
<?php while ($img = mysqli_fetch_assoc($images)) { ?>
<img src="assets/images/gallery/<?= $albumId; ?>/<?= rawurlencode($img['file_name']); ?>" class="album-img" alt="<?= h($album['album_name']); ?>" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="openImage(this.src)">
<?php } ?>
</div></div>
<button class="slider-btn right">›</button>
</div>
<?php } else { ?>
<div class="alert alert-light text-center">No images in this album</div>
<?php } ?>
</div>
<?php } ?>
</div>
<div class="modal fade" id="imageModal"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><img id="modalImage" class="img-fluid rounded" alt="Gallery image"></div></div></div></div>
<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openImage(src){document.getElementById("modalImage").src = src;}
document.addEventListener("DOMContentLoaded", function(){document.querySelectorAll('.album-slider').forEach(slider => {const windowBox = slider.querySelector('.album-window');const leftBtn = slider.querySelector('.left');const rightBtn = slider.querySelector('.right');let auto = setInterval(() => {windowBox.scrollLeft += 1;if(windowBox.scrollLeft >= windowBox.scrollWidth / 2){windowBox.scrollLeft = 0;}}, 20);slider.addEventListener('mouseenter', () => clearInterval(auto));slider.addEventListener('mouseleave', () => {auto = setInterval(() => {windowBox.scrollLeft += 1;if(windowBox.scrollLeft >= windowBox.scrollWidth / 2){windowBox.scrollLeft = 0;}}, 20);});leftBtn.addEventListener('click', () => {windowBox.scrollBy({ left: -600, behavior: 'smooth' });});rightBtn.addEventListener('click', () => {windowBox.scrollBy({ left: 600, behavior: 'smooth' });});});});
</script>
</body>
</html>

