<?php
include '../includes/auth_check.php';
include '../includes/db_connect.php';
include '../includes/role_check.php';

/* Only superadmin */
requireRole(['superadmin']);

// DB credentials come from the shared connection config ($host, $user,
// $password, $database are defined in includes/db_connect.php).
$db   = $database;
$pass = $password;

// Build the credential args safely (only include a password if one is set).
$authArgs = '--host=' . escapeshellarg($host) . ' --user=' . escapeshellarg($user);
if ($pass !== '') {
    $authArgs .= ' --password=' . escapeshellarg($pass);
}

$backup_message = "";
$backup_error   = "";

/* ================= BACKUP ================= */

if (isset($_POST['backup'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $backup_error = "Session expired. Please try again.";
    } else {
        $backup_file = __DIR__ . "/../backups/backup_" . date("Y-m-d_H-i-s") . ".sql";

        $command = "mysqldump $authArgs " . escapeshellarg($db) . " > " . escapeshellarg($backup_file);
        exec($command, $out, $code);

        $backup_message = ($code === 0)
            ? "Backup created successfully."
            : "";
        if ($code !== 0) {
            $backup_error = "Backup failed. Check that mysqldump is available on the server.";
        }
    }
}

/* ================= RESTORE ================= */

if (isset($_POST['restore'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $backup_error = "Session expired. Please try again.";
    } elseif (empty($_FILES['backup_file']['tmp_name']) || !is_uploaded_file($_FILES['backup_file']['tmp_name'])) {
        $backup_error = "Please select a valid .sql file to restore.";
    } elseif (strtolower(pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION)) !== 'sql') {
        $backup_error = "Only .sql backup files are allowed.";
    } else {
        $file = $_FILES['backup_file']['tmp_name'];

        $command = "mysql $authArgs " . escapeshellarg($db) . " < " . escapeshellarg($file);
        exec($command, $out, $code);

        $backup_message = ($code === 0)
            ? "Database restored successfully."
            : "";
        if ($code !== 0) {
            $backup_error = "Restore failed. Verify the file is a valid backup for this database.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Backup & Restore</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h3>Database Backup & Restore</h3>

<?php if($backup_message!=""){ ?>
<div class="alert alert-success">
<?= h($backup_message) ?>
</div>
<?php } ?>
<?php if($backup_error!=""){ ?>
<div class="alert alert-danger">
<?= h($backup_error) ?>
</div>
<?php } ?>

<!-- Backup Button -->
<form method="POST">
<?= csrf_input(); ?>
<button name="backup" class="btn btn-success">
Create Backup
</button>
</form>

<hr>

<!-- Restore -->
<form method="POST" enctype="multipart/form-data" onsubmit="return confirm('This will OVERWRITE the current database. Continue?');">
<?= csrf_input(); ?>
<label>Select Backup File (.sql)</label>
<input type="file" name="backup_file" class="form-control" accept=".sql" required>
<br>
<button name="restore" class="btn btn-danger">
Restore Database
</button>
</form>

</body>
</html>