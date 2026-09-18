<?php include_once 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h(APP_NAME); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/medical-camp-system/assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-layout">
<header class="admin-header main-header">
    <div class="admin-header__left">
        <button type="button" class="admin-menu-toggle" id="adminMenuToggle" aria-label="Open menu" aria-controls="adminSidebar" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <a href="/medical-camp-system/dashboard.php" class="admin-brand">
            <img src="/medical-camp-system/assets/images/logo.png" alt="<?= h(APP_NAME); ?> logo">
            <div class="admin-brand__text">
                <div class="admin-brand__title"><?= h(APP_NAME); ?></div>
                <div class="admin-brand__tag">Seva Management Platform</div>
            </div>
        </a>
    </div>
    <div class="admin-header__right">
        <div class="admin-user-chip">
            <span class="admin-user-chip__label">Welcome</span>
            <strong><?= h($_SESSION['admin_name'] ?? 'Guest'); ?></strong>
        </div>
    </div>
</header>
<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>
