<?php
include 'includes/auth_check.php';
include 'includes/db_connect.php';
include 'includes/password_helper.php';

$user_id = (int) $_SESSION['user_id'];
$message = '';
$error = '';

if (isset($_POST['change'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } else {
            $stmt = mysqli_prepare($conn, 'SELECT password FROM users WHERE user_id = ? LIMIT 1');
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($user && verify_user_password($oldPassword, $user['password'])) {
                $newHash = hash_user_password($newPassword);
                $update = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE user_id = ?');
                mysqli_stmt_bind_param($update, 'si', $newHash, $user_id);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
                audit_log($conn, 'user.password_change', 'user', $user_id);
                $message = 'Password changed successfully.';
            } else {
                $error = 'Old password is incorrect.';
            }
        }
    }
}
?>

<div class="content">
<div class="card-box">
<h4>Change Password</h4>
<?php if ($message !== '') { ?><div class="alert alert-success"><?= h($message); ?></div><?php } ?>
<?php if ($error !== '') { ?><div class="alert alert-danger"><?= h($error); ?></div><?php } ?>
<form method="POST">
<?= csrf_input(); ?>
<label>Old Password</label>
<input type="password" name="old_password" class="form-control" required>
<label class="mt-2">New Password</label>
<input type="password" name="new_password" class="form-control" minlength="8" required>
<br>
<button class="btn btn-spiritual" name="change">Change Password</button>
</form>
</div>
</div>
