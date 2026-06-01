<?php
/**
 * User Profile Page
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Profile';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $action = $_POST['action'] ?? 'profile';

        if ($action === 'profile') {
            $fullName = sanitizeInput($_POST['full_name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $theme = ($_POST['theme'] ?? 'dark') === 'light' ? 'light' : 'dark';

            if (strlen($fullName) < 2) {
                $errors[] = 'Full name is required.';
            } elseif (!isValidEmail($email)) {
                $errors[] = 'Invalid email address.';
            } else {
                // Check email uniqueness
                $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
                $stmt->execute([$email, currentUserId()]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email already in use.';
                } else {
                    $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, theme = ? WHERE id = ?');
                    $stmt->execute([$fullName, $email, $theme, currentUserId()]);
                    $_SESSION['full_name'] = $fullName;
                    $_SESSION['email'] = $email;
                    $_SESSION['theme'] = $theme;
                    $success = 'Profile updated successfully.';
                    logActivity(currentUserId(), 'profile_update', 'Profile updated');
                }
            }
        } elseif ($action === 'password') {
            $current = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $stmt = db()->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([currentUserId()]);
            $user = $stmt->fetch();

            if (!password_verify($current, $user['password'])) {
                $errors[] = 'Current password is incorrect.';
            } elseif (strlen($newPass) < PASSWORD_MIN_LENGTH) {
                $errors[] = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
            } elseif ($newPass !== $confirm) {
                $errors[] = 'New passwords do not match.';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
                $stmt->execute([$hash, currentUserId()]);
                $success = 'Password changed successfully.';
                logActivity(currentUserId(), 'password_change', 'Password updated');
            }
        }
    }
}

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([currentUserId()]);
$userData = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="dashboard-content">
        <h2 class="mb-4"><i class="bi bi-person"></i> My Profile</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?= e($err) ?></div>
        <?php endforeach; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="glass-card p-4">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-2"><?= strtoupper(substr($userData['full_name'], 0, 1)) ?></div>
                        <h5><?= e($userData['full_name']) ?></h5>
                        <p class="text-muted">@<?= e($userData['username']) ?></p>
                        <span class="badge bg-primary"><?= e(ucfirst($userData['role'])) ?></span>
                        <p class="text-muted small mt-2">Member since <?= formatDate($userData['created_at']) ?></p>
                    </div>

                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="profile">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required
                                   value="<?= e($userData['full_name']) ?>"
                                   style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= e($userData['email']) ?>"
                                   style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Theme</label>
                            <select name="theme" class="form-select"
                                    style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                                <option value="dark" <?= $userData['theme'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                                <option value="light" <?= $userData['theme'] === 'light' ? 'selected' : '' ?>>Light</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-glow">Save Profile</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="glass-card p-4">
                    <h5 class="mb-3"><i class="bi bi-shield-lock"></i> Change Password</h5>
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="password">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required
                                   style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required
                                   minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                   style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required
                                   style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border-glass)">
                        </div>
                        <button type="submit" class="btn btn-outline-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
