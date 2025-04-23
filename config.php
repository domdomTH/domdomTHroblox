<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'noarz');

// Website configuration
define('SITE_NAME', 'NOARZ');
define('SITE_URL', 'http://localhost/noarz');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// File upload configuration
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_FILE_TYPES', [
    'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain', 'text/csv', 'application/json', 'application/xml', 'text/xml',
    'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed',
    'application/javascript', 'text/css', 'text/html'
]);

// Session configuration
session_start();

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include common functions if file exists
$includes_files = [
    __DIR__ . '/includes/functions.php',
    __DIR__ . '/includes/auth.php',
    __DIR__ . '/includes/validation.php'
];

foreach ($includes_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}
?>
