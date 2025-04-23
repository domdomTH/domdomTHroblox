<?php
require_once 'config.php';
require_once 'db.php';
require_once 'includes/functions.php';

// Get search query
$query = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 10;

// Initialize filters
$filters = [];

// Add author filter if provided
if (isset($_GET['author']) && !empty($_GET['author'])) {
    $filters['author_id'] = intval($_GET['author']);
}

// Get total results count
$total_items = count_search_results($query, $filters);

// Get pagination data
$pagination = get_pagination($total_items, $items_per_page, $page);

// Get search results
$results = [];
if (!empty($query)) {
    $results = search_news($query, $filters, $pagination['items_per_page'], $pagination['offset']);
}

// Get all authors for filter dropdown
$authors = db_get_rows("SELECT id, username FROM users ORDER BY username ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/social.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        .search-form {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .search-form .form-group {
            flex: 1;
        }

        .search-form button {
            height: 40px;
        }

        .search-filters {
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-filters select {
            padding: 8px;
            background-color: var(--background-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 3px;
        }

        .search-results {
            margin-bottom: 30px;
        }

        .search-result {
            margin-bottom: 20px;
            padding: 20px;
            background-color: var(--background-alt);
            border: 1px solid var(--border-color);
            border-radius: 5px;
        }

        .search-result h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .search-result .news-meta {
            margin-bottom: 10px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .search-result .news-excerpt {
            margin-bottom: 15px;
        }

        .search-highlight {
            background-color: rgba(255, 255, 0, 0.2);
            padding: 0 2px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a, .pagination span {
            display: inline-block;
            padding: 5px 10px;
            background-color: var(--background-alt);
            border: 1px solid var(--border-color);
            border-radius: 3px;
            text-decoration: none;
            color: var(--text-color);
        }

        .pagination a:hover {
            background-color: var(--primary-color);
            color: var(--background-color);
        }

        .pagination .current {
            background-color: var(--primary-color);
            color: var(--background-color);
        }

        .no-results {
            text-align: center;
            padding: 30px;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
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
                    <li><a href="search.php" class="active"><span class="nav-icon">🔍</span>Search</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <li><a href="post.php"><span class="nav-icon">📝</span>Post News</a></li>
                            <li><a href="admin.php"><span class="nav-icon">🛠️</span>Admin</a></li>
                        <?php endif; ?>
                        <li><a href="profile.php"><span class="nav-icon">👤</span>My Profile</a></li>
                        <li><a href="logout.php"><span class="nav-icon">🔓</span>Logout <span class="nav-username">(<?php echo $_SESSION['username']; ?>)</span></a></li>
                    <?php else: ?>
                        <li><a href="login.php"><span class="nav-icon">🔐</span>Login</a></li>
                        <li><a href="register.php"><span class="nav-icon">📝</span>Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>

        <main>
            <section class="search-section">
                <h2>Search</h2>

                <form method="GET" action="search.php" class="search-form">
                    <div class="form-group">
                        <label for="q">Search Query</label>
                        <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Enter search terms..." required>
                    </div>

                    <button type="submit" class="btn">Search</button>
                </form>

                <?php if (!empty($query)): ?>
                    <div class="search-filters">
                        <span>Filter by:</span>

                        <select name="author" onchange="location = this.value;">
                            <option value="search.php?q=<?php echo urlencode($query); ?>">All Authors</option>
                            <?php foreach ($authors as $author): ?>
                                <option value="search.php?q=<?php echo urlencode($query); ?>&author=<?php echo $author['id']; ?>" <?php echo (isset($filters['author_id']) && $filters['author_id'] == $author['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($author['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h3>Search Results for "<?php echo htmlspecialchars($query); ?>"</h3>
                    <p><?php echo $total_items; ?> results found</p>

                    <?php if (!empty($results)): ?>
                        <div class="search-results">
                            <?php foreach ($results as $result): ?>
                                <article class="search-result">
                                    <h3><a href="news.php?id=<?php echo $result['id']; ?>"><?php echo highlight_search_terms(htmlspecialchars($result['title']), $query); ?></a></h3>

                                    <div class="news-meta">
                                        <span class="author">By <?php echo htmlspecialchars($result['author_name']); ?><?php echo $result['is_admin'] ? ' <span class="admin-badge">Admin</span>' : ''; ?></span>
                                        <span class="date"><?php echo format_date($result['created_at']); ?></span>
                                    </div>

                                    <div class="news-excerpt">
                                        <?php echo highlight_search_terms(get_excerpt($result['content']), $query); ?>
                                    </div>

                                    <a href="news.php?id=<?php echo $result['id']; ?>" class="read-more">Read More</a>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($pagination['total_pages'] > 1): ?>
                            <div class="pagination">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <a href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $pagination['current_page'] - 1; ?><?php echo isset($filters['author_id']) ? '&author=' . $filters['author_id'] : ''; ?>">Previous</a>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <?php if ($i == $pagination['current_page']): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?><?php echo isset($filters['author_id']) ? '&author=' . $filters['author_id'] : ''; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <a href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $pagination['current_page'] + 1; ?><?php echo isset($filters['author_id']) ? '&author=' . $filters['author_id'] : ''; ?>">Next</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <p>No results found for your search query.</p>
                            <p>Try using different keywords or removing filters.</p>
                        </div>
                    <?php endif; ?>
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

<?php
/**
 * Highlight search terms in text
 * @param string $text Text to highlight
 * @param string $query Search query
 * @return string Highlighted text
 */
function highlight_search_terms($text, $query) {
    $terms = explode(' ', $query);

    foreach ($terms as $term) {
        if (strlen($term) < 3) {
            continue;
        }

        $text = preg_replace('/(' . preg_quote($term, '/') . ')/i', '<span class="search-highlight">$1</span>', $text);
    }

    return $text;
}
?>
