<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/functions.php';

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
$error = '';

// Process delete request
if (isset($_POST['delete_posts']) && isset($_POST['selected_posts']) && is_array($_POST['selected_posts'])) {
    $selected_posts = array_map('intval', $_POST['selected_posts']);

    if (!empty($selected_posts)) {
        // Start transaction
        $conn = db_connect();
        $conn->begin_transaction();

        try {
            // Comments functionality has been removed
            $placeholders = implode(',', array_fill(0, count($selected_posts), '?'));

            // Update any posts that have these as parents to have NULL parent_id
            $conn->query("UPDATE news SET parent_id = NULL WHERE parent_id IN ($placeholders)", $selected_posts);

            // Delete the selected posts
            $conn->query("DELETE FROM news WHERE id IN ($placeholders)", $selected_posts);

            // Commit transaction
            $conn->commit();

            $message = count($selected_posts) . ' post(s) deleted successfully.';
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = 'Error deleting posts: ' . $e->getMessage();
        }

        $conn->close();
    }
}

// Get all posts with usage information
$posts = db_get_rows("
    SELECT n.id, n.title, n.content, n.parent_id, n.created_at, n.author_id,
           n.file_path, n.cover_photo, n.file_display_option,
           u.username as author_name, u.is_admin as is_author_admin,
           (SELECT COUNT(*) FROM news WHERE parent_id = n.id) as child_count,
           0 as comment_count, -- Comments functionality has been removed
           (SELECT title FROM news WHERE id = n.parent_id) as parent_title
    FROM news n
    JOIN users u ON n.author_id = u.id
    ORDER BY n.created_at DESC
");

// Categorize posts
$unused_posts = [];
$low_activity_posts = [];
$active_posts = [];

foreach ($posts as $post) {
    // Consider a post unused if it has no children
    if ($post['child_count'] == 0) {
        $unused_posts[] = $post;
    }
    // Consider a post low activity if it has 1-2 children
    elseif ($post['child_count'] <= 2) {
        $low_activity_posts[] = $post;
    }
    // Otherwise, it's an active post
    else {
        $active_posts[] = $post;
    }
}

// Get total counts
$total_posts = count($posts);
$unused_count = count($unused_posts);
$low_activity_count = count($low_activity_posts);
$active_count = count($active_posts);

// Function to get excerpt
function get_post_excerpt($content, $length = 100) {
    $content = strip_tags($content);
    if (strlen($content) <= $length) {
        return $content;
    }
    $excerpt = substr($content, 0, $length);
    $last_space = strrpos($excerpt, ' ');
    if ($last_space !== false) {
        $excerpt = substr($excerpt, 0, $last_space);
    }
    return $excerpt . '...';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/navigation.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo SITE_NAME; ?></h1>
            <nav>
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
            <section class="admin-dashboard post-manager">
                <h2>Manage Posts</h2>

                <?php if ($message): ?>
                    <div class="success"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="admin-actions">
                    <a href="admin.php" class="btn">Back to Dashboard</a>
                    <a href="post.php" class="btn btn-secondary">Create New Post</a>
                </div>

                <div class="post-stats">
                    <div class="stat-box total">
                        <h3>Total Posts</h3>
                        <div class="count"><?php echo $total_posts; ?></div>
                    </div>

                    <div class="stat-box unused">
                        <h3>Unused Posts</h3>
                        <div class="count"><?php echo $unused_count; ?></div>
                        <div class="description">No child posts</div>
                    </div>

                    <div class="stat-box low">
                        <h3>Low Activity</h3>
                        <div class="count"><?php echo $low_activity_count; ?></div>
                        <div class="description">1-2 child posts</div>
                    </div>

                    <div class="stat-box active">
                        <h3>Active Posts</h3>
                        <div class="count"><?php echo $active_count; ?></div>
                        <div class="description">3+ child posts</div>
                    </div>
                </div>

                <div class="tab-navigation">
                    <div class="tab active" data-tab="unused">Unused Posts (<?php echo $unused_count; ?>)</div>
                    <div class="tab" data-tab="low-activity">Low Activity (<?php echo $low_activity_count; ?>)</div>
                    <div class="tab" data-tab="active">Active Posts (<?php echo $active_count; ?>)</div>
                    <div class="tab" data-tab="all">All Posts (<?php echo $total_posts; ?>)</div>
                </div>

                <form method="POST" action="manage_posts.php" id="posts-form">
                    <!-- Unused Posts Tab -->
                    <div class="tab-content active" id="unused-tab">
                        <?php if (empty($unused_posts)): ?>
                            <p>No unused posts found.</p>
                        <?php else: ?>
                            <div class="select-all-container">
                                <label>
                                    <input type="checkbox" id="select-all-unused" class="select-all">
                                    Select All Unused Posts
                                </label>
                            </div>

                            <table class="post-table">
                                <thead>
                                    <tr>
                                        <th width="30"><input type="checkbox" class="select-all-header" data-target="unused"></th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($unused_posts as $post): ?>
                                        <tr class="<?php echo $post['is_author_admin'] ? 'admin-post' : ''; ?> <?php echo $post['parent_id'] ? 'child-post' : ''; ?>">
                                            <td>
                                                <input type="checkbox" name="selected_posts[]" value="<?php echo $post['id']; ?>" class="post-checkbox unused-checkbox">
                                            </td>
                                            <td>
                                                <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                                <div class="post-excerpt"><?php echo get_post_excerpt($post['content']); ?></div>
                                                <div class="post-meta">
                                                    <?php if ($post['parent_id']): ?>
                                                        Child of: <?php echo htmlspecialchars($post['parent_title']); ?> |
                                                    <?php endif; ?>
                                                    Child Posts: <?php echo $post['child_count']; ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($post['author_name']); ?><?php echo $post['is_author_admin'] ? ' <span class="admin-badge">Admin</span>' : ''; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                            <td>
                                                <div class="post-actions">
                                                    <a href="news.php?id=<?php echo $post['id']; ?>" class="view-btn">View</a>
                                                    <a href="edit.php?id=<?php echo $post['id']; ?>" class="edit-btn">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="bulk-actions">
                                <button type="submit" name="delete_posts" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the selected posts? This action cannot be undone.')">
                                    <span style="margin-right: 5px;">🗑️</span> Delete Selected Posts
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Low Activity Posts Tab -->
                    <div class="tab-content" id="low-activity-tab">
                        <?php if (empty($low_activity_posts)): ?>
                            <p>No low activity posts found.</p>
                        <?php else: ?>
                            <div class="select-all-container">
                                <label>
                                    <input type="checkbox" id="select-all-low" class="select-all">
                                    Select All Low Activity Posts
                                </label>
                            </div>

                            <table class="post-table">
                                <thead>
                                    <tr>
                                        <th width="30"><input type="checkbox" class="select-all-header" data-target="low"></th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($low_activity_posts as $post): ?>
                                        <tr class="<?php echo $post['is_author_admin'] ? 'admin-post' : ''; ?> <?php echo $post['parent_id'] ? 'child-post' : ''; ?>">
                                            <td>
                                                <input type="checkbox" name="selected_posts[]" value="<?php echo $post['id']; ?>" class="post-checkbox low-checkbox">
                                            </td>
                                            <td>
                                                <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                                <div class="post-excerpt"><?php echo get_post_excerpt($post['content']); ?></div>
                                                <div class="post-meta">
                                                    <?php if ($post['parent_id']): ?>
                                                        Child of: <?php echo htmlspecialchars($post['parent_title']); ?> |
                                                    <?php endif; ?>
                                                    Child Posts: <?php echo $post['child_count']; ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($post['author_name']); ?><?php echo $post['is_author_admin'] ? ' <span class="admin-badge">Admin</span>' : ''; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                            <td>
                                                <div class="post-actions">
                                                    <a href="news.php?id=<?php echo $post['id']; ?>" class="view-btn">View</a>
                                                    <a href="edit.php?id=<?php echo $post['id']; ?>" class="edit-btn">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="bulk-actions">
                                <button type="submit" name="delete_posts" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the selected posts? This action cannot be undone.')">
                                    <span style="margin-right: 5px;">🗑️</span> Delete Selected Posts
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Active Posts Tab -->
                    <div class="tab-content" id="active-tab">
                        <?php if (empty($active_posts)): ?>
                            <p>No active posts found.</p>
                        <?php else: ?>
                            <div class="select-all-container">
                                <label>
                                    <input type="checkbox" id="select-all-active" class="select-all">
                                    Select All Active Posts
                                </label>
                            </div>

                            <table class="post-table">
                                <thead>
                                    <tr>
                                        <th width="30"><input type="checkbox" class="select-all-header" data-target="active"></th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_posts as $post): ?>
                                        <tr class="<?php echo $post['is_author_admin'] ? 'admin-post' : ''; ?> <?php echo $post['parent_id'] ? 'child-post' : ''; ?>">
                                            <td>
                                                <input type="checkbox" name="selected_posts[]" value="<?php echo $post['id']; ?>" class="post-checkbox active-checkbox">
                                            </td>
                                            <td>
                                                <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                                <div class="post-excerpt"><?php echo get_post_excerpt($post['content']); ?></div>
                                                <div class="post-meta">
                                                    <?php if ($post['parent_id']): ?>
                                                        Child of: <?php echo htmlspecialchars($post['parent_title']); ?> |
                                                    <?php endif; ?>
                                                    Child Posts: <?php echo $post['child_count']; ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($post['author_name']); ?><?php echo $post['is_author_admin'] ? ' <span class="admin-badge">Admin</span>' : ''; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                            <td>
                                                <div class="post-actions">
                                                    <a href="news.php?id=<?php echo $post['id']; ?>" class="view-btn">View</a>
                                                    <a href="edit.php?id=<?php echo $post['id']; ?>" class="edit-btn">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="bulk-actions">
                                <button type="submit" name="delete_posts" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the selected posts? This action cannot be undone.')">
                                    <span style="margin-right: 5px;">🗑️</span> Delete Selected Posts
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- All Posts Tab -->
                    <div class="tab-content" id="all-tab">
                        <?php if (empty($posts)): ?>
                            <p>No posts found.</p>
                        <?php else: ?>
                            <div class="select-all-container">
                                <label>
                                    <input type="checkbox" id="select-all-all" class="select-all">
                                    Select All Posts
                                </label>
                            </div>

                            <table class="post-table">
                                <thead>
                                    <tr>
                                        <th width="30"><input type="checkbox" class="select-all-header" data-target="all"></th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($posts as $post): ?>
                                        <tr class="<?php echo $post['is_author_admin'] ? 'admin-post' : ''; ?> <?php echo $post['parent_id'] ? 'child-post' : ''; ?>">
                                            <td>
                                                <input type="checkbox" name="selected_posts[]" value="<?php echo $post['id']; ?>" class="post-checkbox all-checkbox">
                                            </td>
                                            <td>
                                                <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                                <div class="post-excerpt"><?php echo get_post_excerpt($post['content']); ?></div>
                                                <div class="post-meta">
                                                    <?php if ($post['parent_id']): ?>
                                                        Child of: <?php echo htmlspecialchars($post['parent_title']); ?> |
                                                    <?php endif; ?>
                                                    Child Posts: <?php echo $post['child_count']; ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($post['author_name']); ?><?php echo $post['is_author_admin'] ? ' <span class="admin-badge">Admin</span>' : ''; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                            <td>
                                                <div class="post-actions">
                                                    <a href="news.php?id=<?php echo $post['id']; ?>" class="view-btn">View</a>
                                                    <a href="edit.php?id=<?php echo $post['id']; ?>" class="edit-btn">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="bulk-actions">
                                <button type="submit" name="delete_posts" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the selected posts? This action cannot be undone.')">
                                    <span style="margin-right: 5px;">🗑️</span> Delete Selected Posts
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));

                    // Add active class to clicked tab
                    this.classList.add('active');

                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                    });

                    // Show selected tab content
                    const tabId = this.getAttribute('data-tab') + '-tab';
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Select all functionality
            const selectAllCheckboxes = document.querySelectorAll('.select-all');

            selectAllCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    const id = this.id;
                    let targetClass = '';

                    if (id === 'select-all-unused') {
                        targetClass = 'unused-checkbox';
                    } else if (id === 'select-all-low') {
                        targetClass = 'low-checkbox';
                    } else if (id === 'select-all-active') {
                        targetClass = 'active-checkbox';
                    } else if (id === 'select-all-all') {
                        targetClass = 'all-checkbox';
                    }

                    if (targetClass) {
                        document.querySelectorAll('.' + targetClass).forEach(cb => {
                            cb.checked = isChecked;
                        });
                    }
                });
            });

            // Header select all functionality
            const selectAllHeaders = document.querySelectorAll('.select-all-header');

            selectAllHeaders.forEach(header => {
                header.addEventListener('change', function() {
                    const isChecked = this.checked;
                    const target = this.getAttribute('data-target');

                    // Update the main select all checkbox
                    const selectAllCheckbox = document.getElementById('select-all-' + target);
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = isChecked;
                    }

                    // Update all checkboxes in the tab
                    const checkboxClass = target + '-checkbox';
                    document.querySelectorAll('.' + checkboxClass).forEach(cb => {
                        cb.checked = isChecked;
                    });
                });
            });
        });
    </script>
</body>
</html>
