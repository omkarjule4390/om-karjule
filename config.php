<?php
/**
 * Application Configuration
 * AI Code Error Solver Website
 */

// Prevent direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Database configuration (XAMPP defaults)
define('DB_HOST', 'localhost');
define('DB_NAME', 'ai_code_solver');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'AI Code Error Solver');
define('APP_URL', 'http://localhost/ai');
define('APP_VERSION', '1.0.0');

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// AI API Configuration
// Set your API key here or in environment variable
define('AI_PROVIDER', 'openai'); // 'openai' or 'gemini'
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_MODEL', 'gpt-4o-mini');
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('GEMINI_MODEL', 'gemini-1.5-flash');

// Enable demo mode when no API key is set (uses rule-based analysis)
define('AI_DEMO_MODE', empty(OPENAI_API_KEY) && empty(GEMINI_API_KEY));

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
