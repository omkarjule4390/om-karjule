<?php
/**
 * Dashboard sidebar navigation
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$user = currentUser();
?>
<aside class="sidebar glass-card">
    <div class="sidebar-profile text-center py-4">
        <div class="avatar-lg mx-auto mb-2"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
        <h6 class="mb-0"><?= e($user['full_name']) ?></h6>
        <small class="text-muted">@<?= e($user['username']) ?></small>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= e(baseUrl('dashboard.php')) ?>" class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="<?= e(baseUrl('error_solver.php')) ?>" class="sidebar-link <?= $currentPage === 'error_solver.php' ? 'active' : '' ?>">
            <i class="bi bi-bug-fill"></i> Error Solver
        </a>
        <a href="<?= e(baseUrl('history.php')) ?>" class="sidebar-link <?= $currentPage === 'history.php' ? 'active' : '' ?>">
            <i class="bi bi-clock-history"></i> History
        </a>
        <a href="<?= e(baseUrl('profile.php')) ?>" class="sidebar-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>">
            <i class="bi bi-person-fill"></i> Profile
        </a>
        <?php if (isAdmin()): ?>
        <a href="<?= e(baseUrl('admin_panel.php')) ?>" class="sidebar-link <?= $currentPage === 'admin_panel.php' ? 'active' : '' ?>">
            <i class="bi bi-shield-fill"></i> Admin Panel
        </a>
        <?php endif; ?>
        <hr class="sidebar-divider">
        <a href="<?= e(baseUrl('logout.php')) ?>" class="sidebar-link text-danger">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </nav>
</aside>
