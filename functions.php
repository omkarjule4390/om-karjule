<?php
/**
 * Helper Functions - Security, validation, utilities
 */

require_once __DIR__ . '/config.php';

/**
 * Start secure session
 */
function initSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > SESSION_LIFETIME) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken(?string $token): bool
{
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token ?? '');
}

/**
 * Output CSRF hidden input
 */
function csrfField(): string
{
    $token = htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Sanitize output (XSS prevention)
 */
function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input
 */
function sanitizeInput(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Redirect helper
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Flash messages
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Log user activity
 */
function logActivity(?int $userId, string $action, string $details = ''): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO activity_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $ex) {
        error_log('Activity log failed: ' . $ex->getMessage());
    }
}

/**
 * Get supported programming languages
 */
function getLanguages(): array
{
    return [
        'python'     => 'Python',
        'c'          => 'C',
        'cpp'        => 'C++',
        'java'       => 'Java',
        'php'        => 'PHP',
        'javascript' => 'JavaScript',
        'html'       => 'HTML',
        'css'        => 'CSS',
    ];
}

/**
 * Map language to syntax highlighter class
 */
function getHighlightClass(string $lang): string
{
    $map = [
        'python' => 'language-python',
        'c' => 'language-c',
        'cpp' => 'language-cpp',
        'java' => 'language-java',
        'php' => 'language-php',
        'javascript' => 'language-javascript',
        'html' => 'language-markup',
        'css' => 'language-css',
    ];
    return $map[$lang] ?? 'language-none';
}

/**
 * Format date for display
 */
function formatDate(?string $datetime): string
{
    if (!$datetime) return 'N/A';
    return date('M d, Y h:i A', strtotime($datetime));
}

/**
 * JSON response helper
 */
function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get base URL path for assets
 */
function baseUrl(string $path = ''): string
{
    $script = dirname($_SERVER['SCRIPT_NAME']);
    $base = rtrim(str_replace('\\', '/', $script), '/');
    if (basename($base) === 'admin' || basename($base) === 'api') {
        $base = dirname($base);
    }
    return $base . '/' . ltrim($path, '/');
}
