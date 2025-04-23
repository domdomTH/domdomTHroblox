<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/functions.php';

$error = '';

// Get redirect URL if provided
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . $redirect);
    exit;
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Get user from database
        $user = db_get_row("SELECT * FROM users WHERE username = ?", [$username]);

        if ($user && $password === $user['password']) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];

            // Debug information
            error_log('User logged in: ' . $user['username']);
            error_log('Admin status: ' . ($_SESSION['is_admin'] ? 'Yes' : 'No'));

            // Redirect to the requested page or index.php
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="login.php" class="active"><span class="nav-icon">🔐</span>Login</a></li>
                    <li><a href="register.php"><span class="nav-icon">📝</span>Register</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="login-form">
                <h2>Login</h2>

                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php<?php echo !empty($redirect) && $redirect !== 'index.php' ? '?redirect=' . urlencode($redirect) : ''; ?>">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn">Login</button>
                    </div>
                </form>

                <p>Don't have an account? <a href="register.php">Register here</a></p>
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
