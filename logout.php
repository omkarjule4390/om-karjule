<?php
/**
 * Logout handler
 */
require_once __DIR__ . '/includes/auth.php';

logoutUser();
setFlash('success', 'You have been logged out successfully.');
redirect(baseUrl('index.php'));
