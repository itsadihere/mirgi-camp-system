<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/role_check.php';

requireRole(['superadmin','admin']);

include '../includes/header.php';
include '../includes/sidebar.php';

$message = "";

/* CREATE ALBUM */
if(isset($_POST['create_album'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message = "<div class='alert alert-danger'>Session expired. Please try again.</div>";
    } else {
        $album_name = trim($_POST['album_name'] ?? '');
        if($album_name === ''){
            $message = "<div class='alert alert-danger'>Album name is required.</div>";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO albums (album_name) VALUES (?)");
            mysqli_stmt_bind_param($stmt, 's', $album_name);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $message = "<div class='alert alert-success'>Album Created</div>";
        }
    }
}

/* UPLOAD IMAGE */
if(isset($_POST['upload_image'])){
    if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message = "<div class='alert alert-danger'>Session expired. Please try again.</div>";
    } else {
        $album_id = intval($_POST['album_id']);
        $tmp = $_FILES['photo']['tmp_name'] ?? '';

        // Validate that the upload is actually an image (not a renamed script).
        $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = '';
        if($album_id > 0 && is_uploaded_file($tmp)){
            $info = getimagesize($tmp);
            $mime = $info['mime'] ?? '';
        }

        if(!isset($allowedMime[$mime])){
            $message = "<div class='alert alert-danger'>Please upload a valid JPG, PNG or WEBP image.</div>";
        } elseif($_FILES['photo']['size'] > 5 * 1024 * 1024){
            $message = "<div class='alert alert-danger'>Image must be 5 MB or smaller.</div>";
        } else {
            $target_dir = "../assets/images/gallery/$album_id/";
            if(!is_dir($target_dir)){
                mkdir($target_dir, 0755, true);
            }

            $file_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMime[$mime];
            $target_file = $target_dir . $file_name;

            if(move_uploaded_file($tmp, $target_file)){
                $stmt = mysqli_prepare($conn, "INSERT INTO gallery_images (album_id, file_name) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, 'is', $album_id, $file_name);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $message = "<div class='alert alert-success'>Image Uploaded</div>";
            } else {
                $message = "<div class='alert alert-danger'>Upload failed. Please try again.</div>";
            }
        }
    }
}
?>

<div class="content">

<h3>Manage Gallery</h3>
<?= $message ?>

<hr>

<h5>Create New Album</h5>
<form method="POST">
<?= csrf_input(); ?>
<input type="text" name="album_name" class="form-control mb-2" maxlength="150" required>
<button name="create_album" class="btn btn-spiritual">Create Album</button>
</form>

<hr>

<h5>Upload Image</h5>
<form method="POST" enctype="multipart/form-data">
<?= csrf_input(); ?>
<select name="album_id" class="form-control mb-2" required>
<option value="">Select Album</option>
<?php
$albums = mysqli_query($conn,"SELECT * FROM albums ORDER BY album_id DESC");
while($a = mysqli_fetch_assoc($albums)){
    echo "<option value='" . (int) $a['album_id'] . "'>" . h($a['album_name']) . "</option>";
}
?>
</select>

<input type="file" name="photo" class="form-control mb-2" required>

<button name="upload_image" class="btn btn-spiritual">Upload</button>

</form>

</div>