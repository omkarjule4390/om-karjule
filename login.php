<?php
/**
 * User Login Page
 */
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect(baseUrl('dashboard.php'));
}

$pageTitle = 'Login';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $login = sanitizeInput($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            $errors[] = 'Please enter username/email and password.';
        } else {
            $user = authenticateUser($login, $password);
            if ($user) {
                loginUser($user);
                setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
                redirect(baseUrl($user['role'] === 'admin' ? 'admin_panel.php' : 'dashboard.php'));
            } else {
                $errors[] = 'Invalid credentials. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="glass-card auth-card">
        <div class="text-center mb-4">
            <i class="bi bi-box-arrow-in-right display-4 text-primary"></i>
            <h2 class="mt-2">Welcome Back</h2>
            <p class="text-muted">Login to your account</p>
        </div>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" name="login" class="form-control" required
                       value="<?= e($_POST['login'] ?? '') ?>" placeholder="Enter username or email">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter password">
            </div>
            <div class="mb-3 text-end">
                <a href="<?= e(baseUrl('forgot_password.php')) ?>" class="text-primary small">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-glow w-100 mb-3">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>

        <p class="text-center text-muted mb-0">
            Don't have an account? <a href="<?= e(baseUrl('register.php')) ?>" class="text-primary">Register</a>
        </p>

        <div class="mt-4 p-3 rounded" style="background: rgba(99,102,241,0.1);">
            <small class="text-muted d-block mb-1"><strong>Demo accounts:</strong></small>
            <small class="text-muted">Admin: admin / Admin@123</small><br>
            <small class="text-muted">User: demo / Demo@123</small>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
