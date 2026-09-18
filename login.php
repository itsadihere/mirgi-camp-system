<?php
include 'includes/security.php';
include 'includes/db_connect.php';
include 'includes/password_helper.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Simple progressive lockout: after 5 failed attempts, block for 15 minutes.
$maxAttempts = 5;
$lockoutSeconds = 900;
$attempts = $_SESSION['_login_attempts'] ?? 0;
$lockedUntil = $_SESSION['_login_locked_until'] ?? 0;

if (isset($_POST['login'])) {
    if ($lockedUntil > time()) {
        $wait = (int) ceil(($lockedUntil - time()) / 60);
        $error = 'Too many failed attempts. Please try again in about ' . $wait . ' minute(s).';
    } elseif (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $plainPassword = $_POST['password'] ?? '';

        $stmt = mysqli_prepare($conn, 'SELECT user_id, full_name, role, password, status FROM users WHERE username = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && (int) $user['status'] === 1 && verify_user_password($plainPassword, $user['password'])) {
            if (password_needs_upgrade($user['password'])) {
                $newHash = hash_user_password($plainPassword);
                $update = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE user_id = ?');
                mysqli_stmt_bind_param($update, 'si', $newHash, $user['user_id']);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['user_id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            unset($_SESSION['_login_attempts'], $_SESSION['_login_locked_until']);

            audit_log($conn, 'auth.login', 'user', $_SESSION['user_id']);
            header('Location: dashboard.php');
            exit;
        }

        $attempts++;
        $_SESSION['_login_attempts'] = $attempts;
        if ($attempts >= $maxAttempts) {
            $_SESSION['_login_locked_until'] = time() + $lockoutSeconds;
            $_SESSION['_login_attempts'] = 0;
        }

        audit_log($conn, 'auth.login_failed', 'user', null, ['username' => $username]);
        $error = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
background: linear-gradient(to right,#fff5e6,#ffe0b3);
height:100vh;
display:flex;
align-items:center;
justify-content:center;
font-family:Segoe UI;
}
.login-card{
background:#fff;
padding:30px;
border-radius:12px;
box-shadow:0 4px 20px rgba(0,0,0,0.15);
width:100%;
max-width:380px;
}
.logo-box{
text-align:center;
margin-bottom:15px;
}
.logo-box img{
height:70px;
}
.app-title{
text-align:center;
color:#8b0000;
font-weight:700;
margin-top:10px;
}
.btn-spiritual{
background:linear-gradient(to right,#ffb347,#ff7a00);
color:white;
border:none;
}
.btn-spiritual:hover{
background:linear-gradient(to right,#ff7a00,#e65100);
color:white;
}
</style>
</head>
<body>
<div class="login-card">
<div class="logo-box">
<img src="assets/images/logo.png" alt="Logo">
</div>
<h4 class="app-title">AghorArogya</h4>
<p class="text-center text-muted mb-3">Seva Management Login</p>
<?php if (isset($_GET['timeout'])) { ?>
<div class="alert alert-info">You were signed out due to inactivity. Please log in again.</div>
<?php } ?>
<?php if ($error !== '') { ?>
<div class="alert alert-danger"><?= h($error); ?></div>
<?php } ?>
<form method="POST">
<?= csrf_input(); ?>
<label>Username</label>
<input type="text" name="username" class="form-control" required>
<label class="mt-2">Password</label>
<input type="password" name="password" class="form-control" required>
<br>
<button class="btn btn-spiritual w-100" name="login">Login</button>
</form>
</div>
</body>
</html>
