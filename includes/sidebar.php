<?php include_once __DIR__ . '/config.php'; ?>
<aside class="sidebar" id="adminSidebar" aria-label="Sidebar navigation">
<button type="button" class="sidebar-close" id="adminSidebarClose" aria-label="Close menu">&times;</button>
<div class="sidebar-heading-wrap">
<h5 class="sidebar-heading">Seva Panel</h5>
<p class="sidebar-role">Role: <?= h($_SESSION['role'] ?? 'guest'); ?></p>
</div>
<nav class="sidebar-nav">
<a href="<?= h(app_url('dashboard.php')); ?>">Dashboard</a>
<a href="<?= h(app_url('camps/add_camp.php')); ?>">Create Camp</a>
<a href="<?= h(app_url('camps/manage_camps.php')); ?>">Manage Camps</a>
<a href="<?= h(app_url('patients/register_patient.php')); ?>">Patients Registration</a>
<a href="<?= h(app_url('patients/manage_patients.php')); ?>">Patients List</a>
<a href="<?= h(app_url('patients/mark_attendance.php')); ?>">Patient Attendance</a>
<a href="<?= h(app_url('patients/import_patients.php')); ?>">Bulk Import</a>
<a href="<?= h(app_url('admin/manage_whatsapp.php')); ?>">WhatsApp Campaigns</a>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') { ?>
<a href="<?= h(app_url('users/manage_users.php')); ?>">User Management</a>
<?php } ?>
<a href="<?= h(app_url('change_password.php')); ?>">Change Password</a>
<a href="<?= h(app_url('logout.php')); ?>">Logout</a>
</nav>
</aside>
<script>
(function () {
    const body = document.body;
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop');
    const openBtn = document.getElementById('adminMenuToggle');
    const closeBtn = document.getElementById('adminSidebarClose');

    if (!sidebar || !backdrop || !openBtn) {
        return;
    }

    const openMenu = () => {
        body.classList.add('sidebar-open');
        openBtn.setAttribute('aria-expanded', 'true');
    };

    const closeMenu = () => {
        body.classList.remove('sidebar-open');
        openBtn.setAttribute('aria-expanded', 'false');
    };

    openBtn.addEventListener('click', openMenu);
    backdrop.addEventListener('click', closeMenu);
    if (closeBtn) {
        closeBtn.addEventListener('click', closeMenu);
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            closeMenu();
        }
    });
})();
</script>
