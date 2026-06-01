<?php
/**
 * User Registration Page
 */
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect(baseUrl('dashboard.php'));
}

$pageTitle = 'Register';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            $result = registerUser($username, $email, $password, $fullName);
            if ($result['success']) {
                $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$result['user_id']]);
                $user = $stmt->fetch();
                loginUser($user);
                logActivity($result['user_id'], 'register', 'New user registered');
                setFlash('success', 'Account created successfully! Welcome aboard.');
                redirect(baseUrl('dashboard.php'));
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="glass-card auth-card">
        <div class="text-center mb-4">
            <i class="bi bi-person-plus display-4 text-primary"></i>
            <h2 class="mt-2">Create Account</h2>
            <p class="text-muted">Start fixing code errors with AI</p>
        </div>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="" id="registerForm">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" required minlength="2"
                       value="<?= e($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required minlength="3"
                       pattern="[a-zA-Z0-9_]+" title="Letters, numbers, underscore only"
                       value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required
                       value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required
                       minlength="<?= PASSWORD_MIN_LENGTH ?>" id="password">
                <small class="text-muted">Minimum <?= PASSWORD_MIN_LENGTH ?> characters</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-glow w-100 mb-3">
                <i class="bi bi-person-plus"></i> Register
            </button>
        </form>

        <p class="text-center text-muted mb-0">
            Already have an account? <a href="<?= e(baseUrl('login.php')) ?>" class="text-primary">Login</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
