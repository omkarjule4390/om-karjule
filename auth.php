<?php
/**
 * Authentication helpers
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

initSession();

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Require login - redirect if not authenticated
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('warning', 'Please login to continue.');
        redirect(baseUrl('login.php'));
    }
}

/**
 * Require admin access
 */
function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        setFlash('danger', 'Access denied. Admin privileges required.');
        redirect(baseUrl('dashboard.php'));
    }
}

/**
 * Get current user ID
 */
function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data from session
 */
function currentUser(): array
{
    return [
        'id'        => $_SESSION['user_id'] ?? null,
        'username'  => $_SESSION['username'] ?? '',
        'email'     => $_SESSION['email'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? '',
        'role'      => $_SESSION['user_role'] ?? 'user',
        'theme'     => $_SESSION['theme'] ?? 'dark',
        'avatar'    => $_SESSION['avatar'] ?? 'default-avatar.png',
    ];
}

/**
 * Login user and set session
 */
function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['full_name']  = $user['full_name'];
    $_SESSION['user_role']  = $user['role'];
    $_SESSION['theme']      = $user['theme'] ?? 'dark';
    $_SESSION['avatar']     = $user['avatar'] ?? 'default-avatar.png';
    $_SESSION['created']    = time();

    logActivity((int) $user['id'], 'login', 'User logged in');

    if ($user['role'] === 'admin') {
        $stmt = db()->prepare('UPDATE admin SET last_login = NOW() WHERE user_id = ?');
        $stmt->execute([$user['id']]);
    }
}

/**
 * Logout user
 */
function logoutUser(): void
{
    if (isLoggedIn()) {
        logActivity(currentUserId(), 'logout', 'User logged out');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Register new user
 */
function registerUser(string $username, string $email, string $password, string $fullName): array
{
    $errors = [];

    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (!isValidEmail($email)) {
        $errors[] = 'Invalid email address.';
    }
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }
    if (strlen($fullName) < 2) {
        $errors[] = 'Full name is required.';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'errors' => ['Username or email already exists.']];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$username, $email, $hash, $fullName]);

    return ['success' => true, 'user_id' => (int) db()->lastInsertId()];
}

/**
 * Authenticate user credentials
 */
function authenticateUser(string $login, string $password): ?array
{
    $stmt = db()->prepare(
        'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1'
    );
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return null;
}

/**
 * Create password reset token
 */
function createResetToken(string $email): ?string
{
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user) {
        return null; // Don't reveal if email exists
    }

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = db()->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
    $stmt->execute([$token, $expires, $user['id']]);

    return $token;
}

/**
 * Reset password with token
 */
function resetPasswordWithToken(string $token, string $newPassword): bool
{
    $stmt = db()->prepare(
        'SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() AND is_active = 1'
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if (!$user) {
        return false;
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?'
    );
    $stmt->execute([$hash, $user['id']]);
    return true;
}
