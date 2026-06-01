<?php
/**
 * Shared header with navbar
 */
if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/config.php';
}
require_once __DIR__ . '/auth.php';

$user = currentUser();
$isAuth = isLoggedIn();
$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = ($user['theme'] ?? 'dark') === 'light' ? 'theme-light' : 'theme-dark';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($user['theme'] ?? 'dark') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AI-powered code error detection and correction for beginners">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <link href="<?= e(baseUrl('css/style.css')) ?>" rel="stylesheet">
</head>
<body class="<?= $bodyClass ?>">
    <!-- Animated background -->
    <div class="bg-gradient-animation"></div>
    <div class="bg-grid"></div>

    <nav class="navbar navbar-expand-lg navbar-glass fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(baseUrl('index.php')) ?>">
                <i class="bi bi-cpu-fill text-primary"></i>
                <span class="brand-text">AI Code Solver</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="<?= e(baseUrl('index.php')) ?>">Home</a></li>
                    <?php if ($isAuth): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e(baseUrl('dashboard.php')) ?>">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(baseUrl('error_solver.php')) ?>">Solver</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(baseUrl('history.php')) ?>">History</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(baseUrl('contact.php')) ?>">Contact</a></li>
                    <li class="nav-item">
                        <button class="btn btn-icon theme-toggle" id="themeToggle" title="Toggle theme">
                            <i class="bi bi-moon-stars-fill"></i>
                        </button>
                    </li>
                    <?php if ($isAuth): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                <span class="avatar-sm"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
                                <?= e($user['username']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end glass-dropdown">
                                <li><a class="dropdown-item" href="<?= e(baseUrl('profile.php')) ?>"><i class="bi bi-person"></i> Profile</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?= e(baseUrl('admin_panel.php')) ?>"><i class="bi bi-shield-lock"></i> Admin</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= e(baseUrl('logout.php')) ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-outline-primary btn-sm" href="<?= e(baseUrl('login.php')) ?>">Login</a></li>
                        <li class="nav-item"><a class="btn btn-primary btn-sm btn-glow" href="<?= e(baseUrl('register.php')) ?>">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <?php
        $flash = getFlash();
        if ($flash):
        ?>
        <div class="container mt-5 pt-4">
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show glass-alert" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>
