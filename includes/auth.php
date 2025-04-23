<?php
/**
 * Authentication functions for NOARZ
 */

/**
 * Authenticate user
 * @param string $username Username
 * @param string $password Password
 * @return array|bool User data or false on failure
 */
function authenticate_user($username, $password) {
    // Get user from database
    $user = db_get_row("SELECT * FROM users WHERE username = ?", [$username]);

    if (!$user) {
        return false;
    }

    // Check password (plain text for now, as per requirements)
    if ($password !== $user['password']) {
        return false;
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = (bool)$user['is_admin'];

    // Log login
    error_log('User logged in: ' . $user['username']);
    error_log('Admin status: ' . ($_SESSION['is_admin'] ? 'Yes' : 'No'));

    return $user;
}

/**
 * Register new user
 * @param string $username Username
 * @param string $password Password
 * @param string $email Email
 * @param bool $is_admin Admin status
 * @return int|bool User ID or false on failure
 */
function register_user($username, $password, $email, $is_admin = false) {
    // Validate input
    if (!validate_input($username, 'username')) {
        return false;
    }

    if (!validate_input($password, 'password')) {
        return false;
    }

    if (!validate_input($email, 'email')) {
        return false;
    }

    // Check if username or email already exists
    $existing = db_get_row("SELECT * FROM users WHERE username = ? OR email = ?", [$username, $email]);

    if ($existing) {
        return false;
    }

    // Insert user
    return db_insert('users', [
        'username' => $username,
        'password' => $password, // Plain text as per requirements
        'email' => $email,
        'is_admin' => $is_admin ? 1 : 0
    ]);
}

/**
 * Logout user
 */
function logout_user() {
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();
}

/**
 * Reset user password
 * @param string $email User email
 * @return bool Success or failure
 */
function reset_password($email) {
    // Get user from database
    $user = db_get_row("SELECT * FROM users WHERE email = ?", [$email]);

    if (!$user) {
        return false;
    }

    // Generate new password
    $new_password = generate_random_password();

    // Update user password
    $result = db_update('users', [
        'password' => $new_password
    ], 'id = ?', [$user['id']]);

    if (!$result) {
        return false;
    }

    // Send password reset email (in a real application)
    // For now, just return the new password
    return $new_password;
}

/**
 * Generate random password
 * @param int $length Password length
 * @return string Random password
 */
function generate_random_password($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }

    return $password;
}

/**
 * Require login
 * Redirects to login page if user is not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

/**
 * Require admin
 * Redirects to index page if user is not an admin
 */
function require_admin() {
    require_login();

    if (!is_admin()) {
        redirect('index.php');
    }
}
?>
