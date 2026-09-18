<?php
include 'includes/config.php';
include 'includes/db_connect.php';

function youtube_embed_id($url)
{
    $parts = parse_url($url);
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
        if (!empty($query['v'])) {
            return preg_replace('/[^a-zA-Z0-9_-]/', '', $query['v']);
        }
    }

    if (!empty($parts['path'])) {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', trim($parts['path'], '/'));
    }

    return '';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Media | Shree Sarweshwari Samooh</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.main-header{background: linear-gradient(90deg,#ff9a00,#ff6a00);}
.section-title{font-weight:700;color:#a80000;margin-bottom:25px;}
.featured-video-box iframe{max-width:100%;border-radius:10px;}
.media-news-section{background:#fff6ed;padding:60px 0;border-top:3px solid #ff9a00;border-bottom:3px solid #ff9a00;}
.news-slider{position:relative;overflow:hidden;}
.news-window{overflow-x:auto;overflow-y:hidden;scroll-behavior:smooth;}
.news-window::-webkit-scrollbar{display:none;}
.news-track{display:flex;gap:15px;}
.news-card{min-width:300px;background:white;border:3px solid;border-image:linear-gradient(45deg,#ff9a00,#ff6a00) 1;padding:10px;}
.news-card img{width:100%;height:200px;object-fit:cover;cursor:pointer;}
.news-caption{margin-top:8px;font-size:14px;}
.news-arrow{position:absolute;top:40%;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:white;border:none;width:40px;height:50px;font-size:22px;cursor:pointer;z-index:5;}
.news-arrow.left{left:0;}
.news-arrow.right{right:0;}
.video-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:25px;}
.video-card iframe{width:100%;height:200px;border-radius:8px;}
.video-title{margin-top:8px;font-weight:600;}
.main-footer{background:#222;color:white;}
</style>
</head>
<body class="public-page">
<?php include 'includes/navbar.php'; ?>
<section class="text-center py-5">
<div class="container">
<h2 class="section-title">Media & Coverage</h2>
<?php
$video = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT * FROM featured_video LIMIT 1'));
if ($video) {
    $youtubeId = youtube_embed_id($video['youtube_link']);
    if ($youtubeId !== '') {
?>
<div class="container featured-video-box">
<div class="ratio ratio-16x9 mx-auto" style="max-width:800px;">
<iframe src="https://www.youtube.com/embed/<?= h($youtubeId); ?>" title="<?= h($video['title']); ?>" frameborder="0" allowfullscreen></iframe>
</div>
<h5 class="mt-3"><?= h($video['title']); ?></h5>
</div>
<?php } } ?>
</div>
</section>
<section class="media-news-section">
<div class="container">
<h3 class="section-title text-center">Media Coverage</h3>
<div class="news-slider">
<button class="news-arrow left">‹</button>
<div class="news-window"><div class="news-track">
<?php
$news = mysqli_query($conn, 'SELECT * FROM media_news ORDER BY news_date DESC');
while ($n = mysqli_fetch_assoc($news)) {
?>
<div class="news-card">
<img src="assets/images/media_news/<?= rawurlencode($n['image']); ?>" alt="<?= h($n['caption']); ?>" onclick="openImage(this.src)">
<div class="news-caption"><strong><?= h($n['caption']); ?></strong><br><small><?= h($n['source']); ?> | <?= h(date('d M Y', strtotime($n['news_date']))); ?></small></div>
</div>
<?php } ?>
</div></div>
<button class="news-arrow right">›</button>
</div>
</div>
</section>
<section class="py-5">
<div class="container">
<h3 class="section-title text-center">Video Library</h3>
<div class="video-grid">
<?php
$videos = mysqli_query($conn, 'SELECT * FROM media_videos ORDER BY id DESC');
while ($v = mysqli_fetch_assoc($videos)) {
    $vid = youtube_embed_id($v['youtube_link']);
    if ($vid === '') {
        continue;
    }
?>
<div class="video-card">
<iframe src="https://www.youtube.com/embed/<?= h($vid); ?>" frameborder="0" allowfullscreen></iframe>
<div class="video-title"><?= h($v['title']); ?></div>
</div>
<?php } ?>
</div>
</div>
</section>
<div class="modal fade" id="imageModal"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-body text-center"><img id="modalImage" class="img-fluid" alt="Media image"></div></div></div></div>
<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openImage(src){document.getElementById("modalImage").src = src;new bootstrap.Modal(document.getElementById("imageModal")).show();}
document.querySelectorAll(".news-slider").forEach(slider=>{const windowBox=slider.querySelector(".news-window");let auto=setInterval(()=>{windowBox.scrollLeft+=1;if(windowBox.scrollLeft>=windowBox.scrollWidth/2){windowBox.scrollLeft=0;}},20);slider.addEventListener("mouseenter",()=>clearInterval(auto));slider.addEventListener("mouseleave",()=>{auto=setInterval(()=>{windowBox.scrollLeft+=1;if(windowBox.scrollLeft>=windowBox.scrollWidth/2){windowBox.scrollLeft=0;}},20);});slider.querySelector(".left").onclick=()=>{windowBox.scrollBy({left:-400,behavior:"smooth"});};slider.querySelector(".right").onclick=()=>{windowBox.scrollBy({left:400,behavior:"smooth"});};});
</script>
</body>
</html>

