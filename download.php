<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: login.php?redirect=news.php?id=' . (isset($_GET['id']) ? (int)$_GET['id'] : 0));
    exit;
}

// Get news ID from URL
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$download = isset($_GET['download']) && $_GET['download'] === 'true';

if ($news_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get news article with file
$article = db_get_row("SELECT * FROM news WHERE id = ? AND file_path IS NOT NULL", [$news_id]);

if (!$article || empty($article['file_path'])) {
    header('Location: news.php?id=' . $news_id);
    exit;
}

// Check if file path is a URL
if (strpos($article['file_path'], 'http') === 0) {
    // Redirect to the URL
    header('Location: ' . $article['file_path']);
    exit;
}

// Get file path for local files
$file_path = __DIR__ . '/' . $article['file_path'];

// Check if file exists
if (!file_exists($file_path)) {
    die('File not found');
}

// Get file information
$file_name = basename($article['file_path']);
$file_size = filesize($file_path);
$file_type = mime_content_type($file_path);

// Get file display option from the article
$file_display_option = $article['file_display_option'] ?? 'download';

// Determine if we should force download based on display option and URL parameter
$force_download = $download || $file_display_option === 'download';

// Check if it's a viewable file type
$viewable_types = [
    'image/', 'text/', 'application/pdf', 'video/', 'audio/',
    'application/javascript', 'application/json', 'application/xml'
];

$is_viewable = false;
foreach ($viewable_types as $type) {
    if (strpos($file_type, $type) === 0) {
        $is_viewable = true;
        break;
    }
}

if ($force_download || !$is_viewable) {
    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file_type);
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Length: ' . $file_size);
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Expires: 0');

    // Clear output buffer
    ob_clean();
    flush();

    // Output file
    readfile($file_path);
    exit;
} else {
    // Display the file directly in the browser
    header('Content-Type: ' . $file_type);
    header('Content-Length: ' . $file_size);
    header('Content-Disposition: inline; filename="' . $file_name . '"');
    header('Cache-Control: max-age=86400');

    readfile($file_path);
    exit;
}
?>
