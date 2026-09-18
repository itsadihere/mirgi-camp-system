<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/role_check.php';

requireRole(['superadmin', 'admin', 'editor']);

$message = '';
$error = '';

if (isset($_POST['update_video'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $link = trim($_POST['youtube_link'] ?? '');
        $title = trim($_POST['title'] ?? '');
        mysqli_query($conn, 'DELETE FROM featured_video');
        $stmt = mysqli_prepare($conn, 'INSERT INTO featured_video (youtube_link, title) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $link, $title);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $message = 'Featured video updated.';
    }
}

if (isset($_POST['add_news'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $caption = trim($_POST['caption'] ?? '');
        $source = trim($_POST['source'] ?? '');
        $date = $_POST['news_date'] ?? '';
        $tmp = $_FILES['news_image']['tmp_name'] ?? '';

        // Validate that the upload is a real image (never trust the extension).
        $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = (is_uploaded_file($tmp)) ? (getimagesize($tmp)['mime'] ?? '') : '';

        if (!is_uploaded_file($tmp)) {
            $error = 'Please choose an image to upload.';
        } elseif (!isset($allowedMime[$mime])) {
            $error = 'Please upload a valid JPG, PNG or WEBP image.';
        } elseif (($_FILES['news_image']['size'] ?? 0) > 5 * 1024 * 1024) {
            $error = 'Image must be 5 MB or smaller.';
        } else {
            $safeImage = time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMime[$mime];
            if (move_uploaded_file($tmp, '../assets/images/media_news/' . $safeImage)) {
                $stmt = mysqli_prepare($conn, 'INSERT INTO media_news (image, caption, source, news_date) VALUES (?, ?, ?, ?)');
                mysqli_stmt_bind_param($stmt, 'ssss', $safeImage, $caption, $source, $date);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                audit_log($conn, 'media.news_add', 'media_news', mysqli_insert_id($conn));
                $message = 'News uploaded.';
            } else {
                $error = 'Upload failed. Please try again.';
            }
        }
    }
}

if (isset($_POST['add_video'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $link = trim($_POST['video_link'] ?? '');
        $title = trim($_POST['video_title'] ?? '');
        $stmt = mysqli_prepare($conn, 'INSERT INTO media_videos (youtube_link, title) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $link, $title);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $message = 'Video added.';
    }
}

$video = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT * FROM featured_video LIMIT 1'));
?>

<div class="content">
<div class="card-box">
<h3>Media Manager</h3>
<?php if ($message !== '') { ?><div class="alert alert-success"><?= h($message); ?></div><?php } ?>
<?php if ($error !== '') { ?><div class="alert alert-danger"><?= h($error); ?></div><?php } ?>
<hr>
<h5>Featured Video</h5>
<form method="POST">
<?= csrf_input(); ?>
<label>YouTube Link</label>
<input type="text" name="youtube_link" class="form-control" value="<?= h($video['youtube_link'] ?? ''); ?>" required>
<label class="mt-2">Title</label>
<input type="text" name="title" class="form-control" value="<?= h($video['title'] ?? ''); ?>" required>
<br>
<button class="btn btn-spiritual" name="update_video">Update Featured Video</button>
</form>
<hr>
<h5>Add News Cutting</h5>
<form method="POST" enctype="multipart/form-data">
<?= csrf_input(); ?>
<label>Upload Image</label>
<input type="file" name="news_image" class="form-control" required>
<label class="mt-2">Caption</label>
<input type="text" name="caption" class="form-control" required>
<label class="mt-2">Source</label>
<input type="text" name="source" class="form-control" required>
<label class="mt-2">Date</label>
<input type="date" name="news_date" class="form-control" required>
<br>
<button class="btn btn-spiritual" name="add_news">Upload News</button>
</form>
<hr>
<h5>Existing News Coverage</h5>
<table class="table table-bordered">
<tr><th>Image</th><th>Caption</th><th>Source</th><th>Date</th><th>Action</th></tr>
<?php
$news = mysqli_query($conn, 'SELECT * FROM media_news ORDER BY news_date DESC');
while ($n = mysqli_fetch_assoc($news)) {
?>
<tr>
<td width="120"><img src="../assets/images/media_news/<?= rawurlencode($n['image']); ?>" width="100" alt="<?= h($n['caption']); ?>"></td>
<td><?= h($n['caption']); ?></td>
<td><?= h($n['source']); ?></td>
<td><?= h($n['news_date']); ?></td>
<td>
<form method="POST" action="delete_news.php" class="d-inline" onsubmit="return confirm('Delete this news?')">
<?= csrf_input(); ?>
<input type="hidden" name="id" value="<?= (int) $n['id']; ?>">
<button type="submit" class="btn btn-danger btn-sm">Delete</button>
</form>
</td>
</tr>
<?php } ?>
</table>
<hr>
<h5>Add Video to Library</h5>
<form method="POST">
<?= csrf_input(); ?>
<label>YouTube Link</label>
<input type="text" name="video_link" class="form-control" required>
<label class="mt-2">Title</label>
<input type="text" name="video_title" class="form-control" required>
<br>
<button class="btn btn-spiritual" name="add_video">Add Video</button>
</form>
<hr>
<h5>Existing Videos</h5>
<table class="table table-bordered">
<tr><th>Title</th><th>Link</th><th>Action</th></tr>
<?php
$videos = mysqli_query($conn, 'SELECT * FROM media_videos ORDER BY id DESC');
while ($v = mysqli_fetch_assoc($videos)) {
?>
<tr>
<td><?= h($v['title']); ?></td>
<td><?= h($v['youtube_link']); ?></td>
<td>
<form method="POST" action="delete_video.php" class="d-inline" onsubmit="return confirm('Delete video?')">
<?= csrf_input(); ?>
<input type="hidden" name="id" value="<?= (int) $v['id']; ?>">
<button type="submit" class="btn btn-danger btn-sm">Delete</button>
</form>
</td>
</tr>
<?php } ?>
</table>
</div>
</div>
