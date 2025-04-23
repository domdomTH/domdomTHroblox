<?php
/**
 * Common functions for NOARZ
 */

/**
 * Sanitize user input
 * @param string $input Input to sanitize
 * @param string $type Type of input (text, email, url, html, etc.)
 * @return string Sanitized input
 */
function sanitize_input($input, $type = 'text') {
    $input = trim($input);

    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);

        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);

        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);

        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        case 'html':
            // Allow basic HTML tags but remove potentially harmful ones
            return strip_tags($input, '<p><br><a><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code><pre>');

        case 'text':
        default:
            // For plain text, remove all HTML tags
            return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Validate user input
 * @param string $input Input to validate
 * @param string $type Type of validation (email, url, username, password, etc.)
 * @return bool Valid or invalid
 */
function validate_input($input, $type) {
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;

        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL) !== false;

        case 'username':
            // Username should be 3-20 characters and contain only letters, numbers, and underscores
            return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $input) === 1;

        case 'password':
            // Password should be at least 6 characters
            return strlen($input) >= 6;

        case 'not_empty':
            return !empty(trim($input));

        default:
            return true;
    }
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool Valid or invalid
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 * @return bool Logged in or not
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool Admin or not
 */
function is_admin() {
    if (!is_logged_in()) {
        return false;
    }

    // Double-check admin status from database
    $user = db_get_row("SELECT is_admin FROM users WHERE id = ?", [$_SESSION['user_id']]);

    // Update session to match database
    if ($user) {
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
    } else {
        $_SESSION['is_admin'] = false;
    }

    return $_SESSION['is_admin'];
}

/**
 * Redirect to another page
 * @param string $url URL to redirect to
 * @param int $status HTTP status code
 */
function redirect($url, $status = 302) {
    header("Location: $url", true, $status);
    exit;
}

/**
 * Get user profile information
 * @param int $user_id User ID
 * @return array|null User profile data
 */
function get_user_profile($user_id) {
    return db_get_row("SELECT id, username, email, created_at, is_admin FROM users WHERE id = ?", [$user_id]);
}

/**
 * Update user profile
 * @param int $user_id User ID
 * @param array $data Profile data to update
 * @return bool Success or failure
 */
function update_user_profile($user_id, $data) {
    return db_update('users', $data, 'id = ?', [$user_id]);
}

/**
 * Get pagination data
 * @param int $total_items Total number of items
 * @param int $items_per_page Items per page
 * @param int $current_page Current page
 * @return array Pagination data
 */
function get_pagination($total_items, $items_per_page, $current_page = 1) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;

    return [
        'total_items' => $total_items,
        'items_per_page' => $items_per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => $offset
    ];
}

/**
 * Format date
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get excerpt from text
 * @param string $text Text to get excerpt from
 * @param int $length Maximum length of excerpt
 * @return string Excerpt
 */
function get_excerpt($text, $length = 150) {
    $text = strip_tags($text);

    if (strlen($text) <= $length) {
        return $text;
    }

    $excerpt = substr($text, 0, $length);
    $last_space = strrpos($excerpt, ' ');

    if ($last_space !== false) {
        $excerpt = substr($excerpt, 0, $last_space);
    }

    return $excerpt . '...';
}

/**
 * Search news articles
 * @param string $query Search query
 * @param array $filters Optional filters
 * @param int $limit Number of articles to return
 * @param int $offset Pagination offset
 * @return array Search results
 */
function search_news($query, $filters = [], $limit = 10, $offset = 0) {
    $sql = "
        SELECT n.*, u.username as author_name, u.is_admin
        FROM news n
        JOIN users u ON n.author_id = u.id
        WHERE (n.title LIKE ? OR n.content LIKE ?)
    ";

    $params = ["%$query%", "%$query%"];

    // Add filters
    if (!empty($filters['author_id'])) {
        $sql .= " AND n.author_id = ?";
        $params[] = $filters['author_id'];
    }

    if (!empty($filters['parent_id'])) {
        $sql .= " AND n.parent_id = ?";
        $params[] = $filters['parent_id'];
    }

    // Add ordering
    $sql .= " ORDER BY n.created_at DESC";

    // Add limit and offset
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    return db_get_rows($sql, $params);
}

/**
 * Count search results
 * @param string $query Search query
 * @param array $filters Optional filters
 * @return int Number of results
 */
function count_search_results($query, $filters = []) {
    $sql = "
        SELECT COUNT(*) as count
        FROM news n
        JOIN users u ON n.author_id = u.id
        WHERE (n.title LIKE ? OR n.content LIKE ?)
    ";

    $params = ["%$query%", "%$query%"];

    // Add filters
    if (!empty($filters['author_id'])) {
        $sql .= " AND n.author_id = ?";
        $params[] = $filters['author_id'];
    }

    if (!empty($filters['parent_id'])) {
        $sql .= " AND n.parent_id = ?";
        $params[] = $filters['parent_id'];
    }

    $result = db_get_row($sql, $params);
    return $result ? $result['count'] : 0;
}

/**
 * Get related news articles
 * @param int $news_id News article ID
 * @param int $limit Number of articles to return
 * @return array Related news articles
 */
function get_related_news($news_id, $limit = 5) {
    // Get the current article
    $article = db_get_row("SELECT * FROM news WHERE id = ?", [$news_id]);

    if (!$article) {
        return [];
    }

    // First, try to get articles with the same parent
    if ($article['parent_id']) {
        $siblings = db_get_rows("
            SELECT n.*, u.username as author_name, u.is_admin
            FROM news n
            JOIN users u ON n.author_id = u.id
            WHERE n.parent_id = ? AND n.id != ?
            ORDER BY n.created_at DESC
            LIMIT ?
        ", [$article['parent_id'], $news_id, $limit]);

        if (count($siblings) >= $limit) {
            return $siblings;
        }
    }

    // Then, try to get articles by the same author
    $by_author = db_get_rows("
        SELECT n.*, u.username as author_name, u.is_admin
        FROM news n
        JOIN users u ON n.author_id = u.id
        WHERE n.author_id = ? AND n.id != ?
        ORDER BY n.created_at DESC
        LIMIT ?
    ", [$article['author_id'], $news_id, $limit]);

    if (count($by_author) >= $limit) {
        return $by_author;
    }

    // Finally, get the most recent articles
    return db_get_rows("
        SELECT n.*, u.username as author_name, u.is_admin
        FROM news n
        JOIN users u ON n.author_id = u.id
        WHERE n.id != ?
        ORDER BY n.created_at DESC
        LIMIT ?
    ", [$news_id, $limit]);
}

/**
 * Get site statistics
 * @return array Site statistics
 */
function get_site_statistics() {
    $stats = [];

    // Total users
    $users = db_get_row("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $users ? $users['count'] : 0;

    // Total admins
    $admins = db_get_row("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
    $stats['total_admins'] = $admins ? $admins['count'] : 0;

    // Total news articles
    $news = db_get_row("SELECT COUNT(*) as count FROM news");
    $stats['total_news'] = $news ? $news['count'] : 0;

    // Comments functionality has been removed

    // Recent activity
    $stats['recent_users'] = db_get_rows("
        SELECT id, username, created_at
        FROM users
        ORDER BY created_at DESC
        LIMIT 5
    ");

    $stats['recent_news'] = db_get_rows("
        SELECT n.id, n.title, n.created_at, u.username
        FROM news n
        JOIN users u ON n.author_id = u.id
        ORDER BY n.created_at DESC
        LIMIT 5
    ");

    // Recent comments section removed

    return $stats;
}

/**
 * Get the code protection script tag
 * @return string HTML script tag for code protection
 */
function get_code_protection() {
    return '<script src="js/code-protection.js"></script>';
}
?>
