<?php
require_once 'config.php';
require_once 'db.php';

// Ensure settings table exists
ensure_site_settings_table();

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

$message = '';
$success = false;

// Process user management actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $action = $_POST['action'];
        $user_id = (int)$_POST['user_id'];

        // Make sure we're not modifying the current admin
        if ($user_id !== $_SESSION['user_id']) {
            if ($action === 'make_admin') {
                $result = db_update('users', ['is_admin' => 1], 'id = ?', [$user_id]);
                if ($result) {
                    $success = true;
                    $message = "User has been granted admin privileges.";
                }
            } elseif ($action === 'remove_admin') {
                $result = db_update('users', ['is_admin' => 0], 'id = ?', [$user_id]);
                if ($result) {
                    $success = true;
                    $message = "Admin privileges have been removed from user.";
                }
            } elseif ($action === 'reset_password') {
                $result = db_update('users', ['password' => '123456'], 'id = ?', [$user_id]);
                if ($result) {
                    $success = true;
                    $message = "User password has been reset to '123456'.";
                }
            } elseif ($action === 'delete_user') {
                // First check if user has any news articles
                $has_news = db_get_row("SELECT COUNT(*) as count FROM news WHERE author_id = ?", [$user_id]);

                if ($has_news && $has_news['count'] > 0) {
                    $message = "Cannot delete user with existing news articles. Delete their articles first.";
                } else {
                    $result = db_delete('users', 'id = ?', [$user_id]);
                    if ($result) {
                        $success = true;
                        $message = "User has been deleted.";
                    }
                }
            }
        } else {
            $message = "You cannot modify your own account from this page.";
        }
    }
}

// Get all users
$users = db_get_rows("SELECT * FROM users ORDER BY is_admin DESC, username ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
                        <li><a href="admin.php" class="active"><span class="nav-icon">🛠️</span>Admin</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php"><span class="nav-icon">👤</span>My Profile</a></li>
                    <li><a href="logout.php"><span class="nav-icon">🔓</span>Logout <span class="nav-username">(<?php echo $_SESSION['username']; ?>)</span></a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="admin-dashboard">
                <h2>Admin Dashboard</h2>

                <?php if ($message): ?>
                    <div class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Admin Stats -->
                <?php
                // Get site statistics
                $total_users = db_get_row("SELECT COUNT(*) as count FROM users")['count'];
                $total_admins = db_get_row("SELECT COUNT(*) as count FROM users WHERE is_admin = 1")['count'];
                $total_posts = db_get_row("SELECT COUNT(*) as count FROM news")['count'];
                $recent_posts = db_get_row("SELECT COUNT(*) as count FROM news WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'];
                ?>

                <div class="admin-stats">
                    <div class="stat-card">
                        <div class="stat-card-title">Total Users</div>
                        <div class="stat-card-value"><?php echo $total_users; ?></div>
                        <div class="stat-card-description">Registered accounts</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-title">Admin Users</div>
                        <div class="stat-card-value"><?php echo $total_admins; ?></div>
                        <div class="stat-card-description">Users with admin privileges</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-title">Total Posts</div>
                        <div class="stat-card-value"><?php echo $total_posts; ?></div>
                        <div class="stat-card-description">Published articles</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-title">Recent Posts</div>
                        <div class="stat-card-value"><?php echo $recent_posts; ?></div>
                        <div class="stat-card-description">Posted in the last 7 days</div>
                    </div>
                </div>

                <!-- Admin Cards -->
                <div class="admin-cards">
                    <div class="admin-card">
                        <div class="admin-card-title">
                            <span class="admin-card-icon">⚙️</span> Site Settings
                        </div>
                        <div class="admin-card-content">
                            Manage site appearance, logo, and general settings.
                        </div>
                        <div class="admin-card-actions">
                            <a href="site_settings.php" class="btn">Manage Settings</a>
                        </div>
                    </div>
                    <div class="admin-card">
                        <div class="admin-card-title">
                            <span class="admin-card-icon">📝</span> Manage Posts
                        </div>
                        <div class="admin-card-content">
                            Organize, edit, and delete posts. View post statistics and manage content.
                        </div>
                        <div class="admin-card-actions">
                            <a href="manage_posts.php" class="btn">Manage Posts</a>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-title">
                            <span class="admin-card-icon">✏️</span> Create Content
                        </div>
                        <div class="admin-card-content">
                            Create new posts, articles, and content for your cybernews site.
                        </div>
                        <div class="admin-card-actions">
                            <a href="post.php" class="btn">Create Post</a>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-title">
                            <span class="admin-card-icon">🔍</span> Search Content
                        </div>
                        <div class="admin-card-content">
                            Search through all content on your site to find specific articles.
                        </div>
                        <div class="admin-card-actions">
                            <a href="search.php" class="btn">Search</a>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-title">
                            <span class="admin-card-icon">📺</span> YouTube Integration
                        </div>
                        <div class="admin-card-content">
                            Manage your YouTube channel integration and featured videos.
                        </div>
                        <div class="admin-card-actions">
                            <a href="social_settings.php" class="btn">Manage Settings</a>
                            <a href="<?php echo htmlspecialchars(get_setting('youtube_url', 'https://youtube.com/channel/YOUR_CHANNEL_ID')); ?>" target="_blank" class="btn">Visit Channel</a>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-title">
                            <span class="admin-card-icon">💬</span> Discord Community
                        </div>
                        <div class="admin-card-content">
                            Manage your Discord server and community engagement.
                        </div>
                        <div class="admin-card-actions">
                            <a href="social_settings.php" class="btn">Manage Settings</a>
                            <a href="<?php echo htmlspecialchars(get_setting('discord_url', 'https://discord.gg/YOUR_INVITE_CODE')); ?>" target="_blank" class="btn">Visit Server</a>
                        </div>
                    </div>


                </div>

                <h3>User Management</h3>

                <div class="admin-actions">
                    <a href="#" class="btn" onclick="toggleUserTable(); return false;">Show/Hide User Table</a>
                </div>

                <table class="user-table" id="userTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="<?php echo $user['id'] === $_SESSION['user_id'] ? 'current-user' : ''; ?>">
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="admin-badge">Admin</span>
                                    <?php else: ?>
                                        Regular User
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <div class="user-actions">
                                            <form method="POST" action="admin.php">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

                                                <?php if (!$user['is_admin']): ?>
                                                    <button type="submit" name="action" value="make_admin">Make Admin</button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="remove_admin">Remove Admin</button>
                                                <?php endif; ?>

                                                <button type="submit" name="action" value="reset_password">Reset Password</button>
                                                <button type="submit" name="action" value="delete_user" class="danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">Delete</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <em>Current User</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <script>
                    function toggleUserTable() {
                        const table = document.getElementById('userTable');
                        if (table.style.display === 'none') {
                            table.style.display = 'table';
                        } else {
                            table.style.display = 'none';
                        }
                    }
                </script>
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
