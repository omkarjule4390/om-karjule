<?php
/**
 * Forgot Password Page
 */
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect(baseUrl('dashboard.php'));
}

$pageTitle = 'Forgot Password';
$message = '';
$resetLink = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $action = $_POST['action'] ?? 'request';

        if ($action === 'request') {
            $email = sanitizeInput($_POST['email'] ?? '');
            if (!isValidEmail($email)) {
                $errors[] = 'Please enter a valid email address.';
            } else {
                $token = createResetToken($email);
                // Always show success to prevent email enumeration
                $message = 'If an account exists with that email, a reset link has been generated.';
                if ($token) {
                    $resetLink = baseUrl('forgot_password.php?token=' . $token);
                    logActivity(null, 'password_reset_requested', "Email: {$email}");
                }
            }
        } elseif ($action === 'reset') {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (strlen($password) < PASSWORD_MIN_LENGTH) {
                $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
            } elseif ($password !== $confirm) {
                $errors[] = 'Passwords do not match.';
            } elseif (resetPasswordWithToken($token, $password)) {
                setFlash('success', 'Password reset successfully! Please login.');
                redirect(baseUrl('login.php'));
            } else {
                $errors[] = 'Invalid or expired reset link. Please request a new one.';
            }
        }
    }
}

$token = $_GET['token'] ?? '';
$isResetMode = !empty($token);

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="glass-card auth-card">
        <div class="text-center mb-4">
            <i class="bi bi-key display-4 text-primary"></i>
            <h2 class="mt-2"><?= $isResetMode ? 'Reset Password' : 'Forgot Password' ?></h2>
        </div>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?= e($err) ?></div>
        <?php endforeach; ?>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= e($message) ?></div>
            <?php if ($resetLink): ?>
                <div class="alert alert-info">
                    <strong>Demo reset link (XAMPP has no mail server):</strong><br>
                    <a href="<?= e($resetLink) ?>" class="text-break"><?= e($resetLink) ?></a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($isResetMode): ?>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="reset">
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required minlength="<?= PASSWORD_MIN_LENGTH ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-glow w-100">Reset Password</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="request">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= e($_POST['email'] ?? '') ?>" placeholder="your@email.com">
                </div>
                <button type="submit" class="btn btn-primary btn-glow w-100 mb-3">Send Reset Link</button>
            </form>
        <?php endif; ?>

        <p class="text-center mt-3 mb-0">
            <a href="<?= e(baseUrl('login.php')) ?>" class="text-primary"><i class="bi bi-arrow-left"></i> Back to Login</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
