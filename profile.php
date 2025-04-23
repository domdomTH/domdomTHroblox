<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/validation.php';

// Require login
require_login();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user profile
$user = get_user_profile($user_id);

if (!$user) {
    redirect('index.php');
}

// Process profile update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_form_csrf($_POST)) {
        $error = 'Invalid form submission';
    } else {
        // Get form data
        $email = sanitize_input($_POST['email'] ?? '', 'email');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate form data
        $validation_rules = [
            'email' => [
                'required' => true,
                'type' => 'email'
            ],
            'current_password' => [
                'required' => !empty($new_password),
                'min_length' => 6
            ],
            'new_password' => [
                'required' => false,
                'min_length' => 6
            ],
            'confirm_password' => [
                'required' => !empty($new_password),
                'custom' => function($value, $data) {
                    return $value !== $data['new_password'] ? 'Passwords do not match' : null;
                }
            ]
        ];

        $errors = validate_form($_POST, $validation_rules);

        // Check if email is already in use by another user
        if (empty($errors['email'])) {
            $existing_user = db_get_row("SELECT * FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
            if ($existing_user) {
                $errors['email'] = 'Email is already in use';
            }
        }

        // Check current password if changing password
        if (empty($errors['current_password']) && !empty($new_password)) {
            // Get current user with password
            $current_user = db_get_row("SELECT * FROM users WHERE id = ?", [$user_id]);

            if ($current_password !== $current_user['password']) {
                $errors['current_password'] = 'Current password is incorrect';
            }
        }

        if (empty($errors)) {
            // Update user profile
            $update_data = [
                'email' => $email
            ];

            // Update password if provided
            if (!empty($new_password)) {
                $update_data['password'] = $new_password;
            }

            $result = update_user_profile($user_id, $update_data);

            if ($result) {
                $success = 'Profile updated successfully';

                // Refresh user data
                $user = get_user_profile($user_id);
            } else {
                $error = 'Failed to update profile';
            }
        } else {
            // Display first error
            foreach ($errors as $field => $message) {
                $error = $message;
                break;
            }
        }
    }
}

// Get user's news articles
$user_news = db_get_rows("
    SELECT n.*
    FROM news n
    WHERE n.author_id = ?
    ORDER BY n.created_at DESC
    LIMIT 10
", [$user_id]);

// Comments functionality has been removed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/social.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        .profile-section {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .profile-info {
            background-color: var(--background-alt);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
        }

        .profile-info h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .profile-form {
            background-color: var(--background-alt);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
        }

        .profile-form h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .profile-stats {
            margin-top: 20px;
        }

        .profile-stats p {
            margin: 5px 0;
        }

        .profile-activity {
            margin-top: 40px;
        }

        .profile-activity h3 {
            margin-bottom: 20px;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .activity-tabs {
            display: flex;
            margin-bottom: 20px;
        }

        .activity-tab {
            padding: 10px 20px;
            background-color: var(--background-alt);
            border: 1px solid var(--border-color);
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            cursor: pointer;
            margin-right: 5px;
        }

        .activity-tab.active {
            background-color: var(--primary-color);
            color: var(--background-color);
        }

        .activity-content {
            background-color: var(--background-alt);
            border: 1px solid var(--border-color);
            border-radius: 0 5px 5px 5px;
            padding: 20px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .news-item, .comment-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .news-item:last-child, .comment-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .news-item h4, .comment-item h4 {
            margin-top: 0;
            margin-bottom: 5px;
        }

        .news-meta, .comment-meta {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .comment-text {
            margin-top: 10px;
            font-style: italic;
        }
    </style>
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
                    <li><a href="profile.php" class="active"><span class="nav-icon">👤</span>My Profile</a></li>
                    <li><a href="logout.php"><span class="nav-icon">🔓</span>Logout <span class="nav-username">(<?php echo $_SESSION['username']; ?>)</span></a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2>My Profile</h2>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="profile-section">
                <div class="profile-info">
                    <h3>Account Information</h3>

                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Account Type:</strong> <?php echo $user['is_admin'] ? 'Administrator' : 'Regular User'; ?></p>
                    <p><strong>Member Since:</strong> <?php echo format_date($user['created_at'], 'F j, Y'); ?></p>

                    <div class="profile-stats">
                        <h4>Statistics</h4>
                        <p><strong>Articles Posted:</strong> <?php echo count($user_news); ?></p>
                        <!-- Comments functionality has been removed -->
                    </div>
                </div>

                <div class="profile-form">
                    <h3>Update Profile</h3>

                    <form method="POST" action="profile.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                            <small>Required only if changing password</small>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                            <small>Leave blank to keep current password</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="profile-activity">
                <h3>My Activity</h3>

                <div class="activity-tabs">
                    <div class="activity-tab active" data-tab="news">My Articles</div>
                    <!-- Comments tab removed -->
                </div>

                <div class="activity-content">
                    <div class="tab-content active" id="news-tab">
                        <?php if (empty($user_news)): ?>
                            <p>You haven't posted any articles yet.</p>
                        <?php else: ?>
                            <?php foreach ($user_news as $news): ?>
                                <div class="news-item">
                                    <h4><a href="news.php?id=<?php echo $news['id']; ?>"><?php echo htmlspecialchars($news['title']); ?></a></h4>
                                    <div class="news-meta">
                                        <span>Posted on <?php echo format_date($news['created_at']); ?></span>
                                        <!-- Comment count removed -->
                                    </div>
                                    <p><?php echo get_excerpt($news['content']); ?></p>
                                    <div class="button-group">
                                        <a href="news.php?id=<?php echo $news['id']; ?>" class="btn btn-small">View</a>
                                        <?php if ($user['is_admin']): ?>
                                            <a href="edit.php?id=<?php echo $news['id']; ?>" class="btn btn-small">Edit</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Comments tab content removed -->
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <div class="social-icons">
                <a href="https://youtube.com/channel/YOUR_CHANNEL_ID" target="_blank" class="social-icon youtube" title="Follow us on YouTube">📺</a>
                <a href="https://discord.gg/YOUR_INVITE_CODE" target="_blank" class="social-icon discord" title="Join our Discord">💬</a>
            </div>
        </footer>
    </div>

    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.activity-tab');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Hide all tab content
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });

                    // Show selected tab content
                    const tabId = this.getAttribute('data-tab') + '-tab';
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
    <script src="js/mobile-menu.js"></script>
</body>
</html>
