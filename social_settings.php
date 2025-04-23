<?php
require_once 'config.php';
require_once 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Double-check admin status from database
$user = db_get_row("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
if (!$user || !$user['is_admin']) {
    // Update session to match database
    $_SESSION['is_admin'] = $user ? (bool)$user['is_admin'] : false;

    // Redirect if not admin
    if (!$_SESSION['is_admin']) {
        header('Location: index.php');
        exit;
    }
}

// Ensure settings table exists
ensure_site_settings_table();

$message = '';
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate URLs
    $youtube_url = trim($_POST['youtube_url']);
    $discord_url = trim($_POST['discord_url']);
    $youtube_embed = trim($_POST['youtube_embed']);
    $discord_widget_id = trim($_POST['discord_widget_id']);

    // Basic validation
    $valid = true;

    if (!filter_var($youtube_url, FILTER_VALIDATE_URL)) {
        $message = "YouTube URL is not valid.";
        $valid = false;
    }

    if (!filter_var($discord_url, FILTER_VALIDATE_URL)) {
        $message = "Discord URL is not valid.";
        $valid = false;
    }

    if ($valid) {
        // Update settings
        $settings = [
            'youtube_url' => $youtube_url,
            'discord_url' => $discord_url,
            'youtube_embed' => $youtube_embed,
            'discord_widget_id' => $discord_widget_id
        ];

        $all_updated = true;

        foreach ($settings as $name => $value) {
            if (!update_setting($name, $value)) {
                $all_updated = false;
            }
        }

        if ($all_updated) {
            $success = true;
            $message = "Social media settings updated successfully.";
        } else {
            $message = "There was an error updating some settings.";
        }
    }
}

// Get current settings
$youtube_url = get_setting('youtube_url', 'https://youtube.com/channel/YOUR_CHANNEL_ID');
$discord_url = get_setting('discord_url', 'https://discord.gg/YOUR_INVITE_CODE');
$youtube_embed = get_setting('youtube_embed', 'LATEST_VIDEO_ID');
$discord_widget_id = get_setting('discord_widget_id', 'YOUR_SERVER_ID');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Settings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
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
                    <li><a href="search.php"><span class="nav-icon">🔍</span>Search</a></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li><a href="post.php"><span class="nav-icon">📝</span>Post News</a></li>
                        <li><a href="admin.php"><span class="nav-icon">🛠️</span>Admin</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php"><span class="nav-icon">👤</span>My Profile</a></li>
                    <li><a href="logout.php"><span class="nav-icon">🔓</span>Logout <span class="nav-username">(<?php echo $_SESSION['username']; ?>)</span></a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="admin-section">
                <h2>Social Media Settings</h2>

                <div class="admin-actions">
                    <a href="admin.php" class="btn">Back to Admin Dashboard</a>
                </div>

                <?php if ($message): ?>
                    <div class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="settings-form">
                    <form method="POST" action="social_settings.php">
                        <div class="form-group">
                            <label for="youtube_url">YouTube Channel URL</label>
                            <input type="url" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($youtube_url); ?>" required>
                            <small class="form-hint">Full URL to your YouTube channel (e.g., https://youtube.com/channel/UC...)</small>
                        </div>

                        <div class="form-group">
                            <label for="youtube_embed">YouTube Video ID for Homepage Embed</label>
                            <input type="text" id="youtube_embed" name="youtube_embed" value="<?php echo htmlspecialchars($youtube_embed); ?>" required>
                            <small class="form-hint">The ID of the YouTube video to embed (e.g., dQw4w9WgXcQ)</small>
                        </div>

                        <div class="form-group">
                            <label for="discord_url">Discord Invite URL</label>
                            <input type="url" id="discord_url" name="discord_url" value="<?php echo htmlspecialchars($discord_url); ?>" required>
                            <small class="form-hint">Full URL to your Discord server invite (e.g., https://discord.gg/...)</small>
                        </div>

                        <div class="form-group">
                            <label for="discord_widget_id">Discord Server ID for Widget</label>
                            <input type="text" id="discord_widget_id" name="discord_widget_id" value="<?php echo htmlspecialchars($discord_widget_id); ?>" required>
                            <small class="form-hint">Your Discord server ID for the widget (e.g., 123456789012345678)</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>

                <div class="settings-preview">
                    <h3>Preview</h3>

                    <div class="preview-section">
                        <h4>YouTube Embed Preview</h4>
                        <iframe class="youtube-embed" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_embed); ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>

                    <div class="preview-section">
                        <h4>Discord Widget Preview</h4>
                        <iframe class="discord-widget" src="https://discord.com/widget?id=<?php echo htmlspecialchars($discord_widget_id); ?>&theme=dark" allowtransparency="true" frameborder="0" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"></iframe>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <div class="social-icons">
                <a href="<?php echo htmlspecialchars(get_setting('youtube_url', 'https://youtube.com/channel/YOUR_CHANNEL_ID')); ?>" target="_blank" class="social-icon youtube" title="Follow us on YouTube">📺</a>
                <a href="<?php echo htmlspecialchars(get_setting('discord_url', 'https://discord.gg/YOUR_INVITE_CODE')); ?>" target="_blank" class="social-icon discord" title="Join our Discord">💬</a>
            </div>
        </footer>
    </div>
    <script src="js/mobile-menu.js"></script>
</body>
</html>
