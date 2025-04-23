<?php
require_once 'config.php';
require_once 'db.php';

$error = '';
$success = '';

/**
 * Validate email address with enhanced checks
 * @param string $email Email address to validate
 * @return bool True if email is valid, false otherwise
 */
function validate_email($email) {
    // Basic format validation using PHP's filter_var
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Check email length
    if (strlen($email) > 254) {
        return false;
    }

    // Extract domain from email
    $domain = substr(strrchr($email, "@"), 1);

    // Check if domain has valid DNS records
    if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
        return false;
    }

    return true;
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address. The domain must exist.';
    } else {
        // Check if username or email already exists
        $existing_user = db_get_row("SELECT * FROM users WHERE username = ? OR email = ?", [$username, $email]);

        if ($existing_user) {
            if ($existing_user['username'] === $username) {
                $error = 'Username already taken';
            } else {
                $error = 'Email already registered';
            }
        } else {
            // Store password as plain text
            // Note: This is not secure for production environments

            // Check if this is the admin user (Dom)
            $is_admin = ($username === 'Dom') ? 1 : 0;

            // Insert new user
            $user_id = db_insert('users', [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'is_admin' => $is_admin
            ]);

            if ($user_id) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/social.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>
                <div class="logo-container">
                    <?php if (file_exists(get_setting('site_logo', 'images/logo.png'))): ?>
                    <img src="<?php echo htmlspecialchars(get_setting('site_logo', 'images/logo.png')); ?>" alt="<?php echo SITE_NAME; ?> Logo" class="site-logo">
                    <?php endif; ?>
                    <span><?php echo SITE_NAME; ?></span>
                </div>
            </h1>
            <button class="menu-toggle" id="menuToggle">☰</button>
            <nav class="mobile-hidden" id="mainNav">
                <ul>
                    <li><a href="index.php"><span class="nav-icon">🏠</span>Home</a></li>
                    <li><a href="login.php"><span class="nav-icon">🔐</span>Login</a></li>
                    <li><a href="register.php" class="active"><span class="nav-icon">📝</span>Register</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="register-form">
                <h2>Register</h2>

                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success"><?php echo $success; ?></div>
                    <p><a href="login.php" class="btn">Login Now</a></p>
                <?php else: ?>
                    <form method="POST" action="register.php">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required placeholder="Enter a valid email address">
                            <small class="form-hint">Must be a valid email with an existing domain</small>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn">Register</button>
                        </div>
                    </form>

                    <p>Already have an account? <a href="login.php">Login here</a></p>
                <?php endif; ?>
            </section>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <div class="social-icons">
                <a href="https://youtube.com/channel/YOUR_CHANNEL_ID" target="_blank" class="social-icon youtube" title="Follow us on YouTube">📺</a>
                <a href="https://discord.gg/YOUR_INVITE_CODE" target="_blank" class="social-icon discord" title="Join our Discord">💬</a>
            </div>
        </footer>
    </div>
    <script src="js/mobile-menu.js"></script>
</body>
</html>
