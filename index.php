<?php
require_once 'config.php';
require_once 'db.php';

// Ensure settings table exists
ensure_site_settings_table();

// Get all parent news articles (those without a parent), ordered by newest first
$parent_news = db_get_rows("
    SELECT n.*, u.username as author_name, u.is_admin
    FROM news n
    JOIN users u ON n.author_id = u.id
    WHERE n.parent_id IS NULL
    ORDER BY n.created_at DESC
");

// Get all child news articles (those with a parent)
$child_news = db_get_rows("
    SELECT n.*, u.username as author_name, u.is_admin, p.title as parent_title
    FROM news n
    JOIN users u ON n.author_id = u.id
    JOIN news p ON n.parent_id = p.id
    ORDER BY n.parent_id, n.created_at DESC
");

// Organize child news by parent_id for easier access
$children_by_parent = [];
foreach ($child_news as $child) {
    $parent_id = $child['parent_id'];
    if (!isset($children_by_parent[$parent_id])) {
        $children_by_parent[$parent_id] = [];
    }
    $children_by_parent[$parent_id][] = $child;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Latest Cyber News</title>
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
                    <li><a href="index.php" class="active"><span class="nav-icon">🏠</span>Home</a></li>
                    <li><a href="search.php"><span class="nav-icon">🔍</span>Search</a></li>
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
            <section class="search-box">
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="Search news...">
                    <button type="submit">Search</button>
                </form>
            </section>



            <section class="community-section">
                <div class="community-card youtube">
                    <h3><span class="card-icon">📺</span> Join us on YouTube</h3>
                    <p>🎥✨ ยินดีต้อนรับเข้าสู่ช่องของเรา! ✨🎮</p>
                    <a href="<?php echo htmlspecialchars(get_setting('youtube_url', 'https://youtube.com/channel/YOUR_CHANNEL_ID')); ?>" target="_blank" class="btn"><span class="btn-icon">▶️</span> Subscribe Now</a>
                    <iframe class="youtube-embed" src="https://www.youtube.com/embed/<?php echo htmlspecialchars(get_setting('youtube_embed', 'LATEST_VIDEO_ID')); ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>

                <div class="community-card discord">
                    <h3><span class="card-icon">💬</span> Join our Discord</h3>
                    <p>💬✨ เข้ามาเป็นส่วนหนึ่งของแก๊งเราใน Discord!</p>
                    <a href="<?php echo htmlspecialchars(get_setting('discord_url', 'https://discord.gg/YOUR_INVITE_CODE')); ?>" target="_blank" class="btn"><span class="btn-icon">🔗</span> Join Server</a>
                    <iframe class="discord-widget" src="https://discord.com/widget?id=<?php echo htmlspecialchars(get_setting('discord_widget_id', 'YOUR_SERVER_ID')); ?>&theme=dark" allowtransparency="true" frameborder="0" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"></iframe>
                </div>
            </section>

            <section class="news-grid">
                <h2>Latest News</h2>

                <?php if (empty($parent_news) && empty($child_news)): ?>
                    <p class="no-news">No news articles available yet.</p>
                <?php else: ?>
                    <div class="news-container">
                        <?php foreach ($parent_news as $article): ?>
                            <article class="news-card <?php echo $article['is_admin'] ? 'admin-post' : ''; ?>">
                                <?php if ($article['cover_photo']): ?>
                                    <div class="cover-photo-thumbnail">
                                        <a href="news.php?id=<?php echo $article['id']; ?>">
                                            <?php if (strpos($article['cover_photo'], 'http') === 0): ?>
                                                <img src="<?php echo htmlspecialchars($article['cover_photo']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                            <?php elseif (file_exists(__DIR__ . '/' . $article['cover_photo'])): ?>
                                                <img src="<?php echo htmlspecialchars($article['cover_photo']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <div class="content-wrapper">
                                    <h3><a href="news.php?id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                                    <div class="news-meta">
                                        <span class="author">By <?php echo htmlspecialchars($article['author_name']); ?><?php echo $article['is_admin'] ? ' <span class="admin-badge">Admin</span>' : ''; ?></span>
                                        <span class="date"><?php echo date('M d, Y', strtotime($article['created_at'])); ?></span>
                                    </div>

                                    <?php if ($article['is_admin'] && $article['file_path'] && !$article['cover_photo']): ?>
                                        <div class="news-image">
                                            <?php if (strpos($article['file_path'], 'http') === 0): ?>
                                                <img src="<?php echo htmlspecialchars($article['file_path']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                            <?php elseif (file_exists(__DIR__ . '/' . $article['file_path']) && strpos(mime_content_type(__DIR__ . '/' . $article['file_path']), 'image/') === 0): ?>
                                                <img src="download.php?id=<?php echo $article['id']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="news-excerpt">
                                        <?php echo nl2br(htmlspecialchars(substr($article['content'], 0, 150) . (strlen($article['content']) > 150 ? '...' : ''))); ?>
                                    </div>
                                    <div class="article-actions">
                                        <a href="news.php?id=<?php echo $article['id']; ?>" class="read-more">Read More</a>
                                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                            <a href="edit.php?id=<?php echo $article['id']; ?>" class="edit-btn">Edit</a>
                                        <?php endif; ?>
                                    </div>
                                </div><!-- End content-wrapper -->

                                <?php if (isset($children_by_parent[$article['id']])): ?>
                                    <div class="child-posts">
                                        <h4>Related Posts:</h4>
                                        <ul>
                                            <?php foreach ($children_by_parent[$article['id']] as $child): ?>
                                                <li class="<?php echo $child['is_admin'] ? 'admin-child' : ''; ?>">
                                                    <?php if ($child['cover_photo']): ?>
                                                        <div class="child-cover-photo">
                                                            <a href="news.php?id=<?php echo $child['id']; ?>">
                                                                <?php if (strpos($child['cover_photo'], 'http') === 0): ?>
                                                                    <img src="<?php echo htmlspecialchars($child['cover_photo']); ?>" alt="<?php echo htmlspecialchars($child['title']); ?>">
                                                                <?php elseif (file_exists(__DIR__ . '/' . $child['cover_photo'])): ?>
                                                                    <img src="<?php echo htmlspecialchars($child['cover_photo']); ?>" alt="<?php echo htmlspecialchars($child['title']); ?>">
                                                                <?php endif; ?>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="child-title-row">
                                                        <a href="news.php?id=<?php echo $child['id']; ?>"><?php echo htmlspecialchars($child['title']); ?></a>
                                                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                                            <a href="edit.php?id=<?php echo $child['id']; ?>" class="child-edit-btn">Edit</a>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span class="child-meta">By <?php echo htmlspecialchars($child['author_name']); ?><?php echo $child['is_admin'] ? ' <span class="admin-badge">Admin</span>' : ''; ?> - <?php echo date('M d, Y', strtotime($child['created_at'])); ?></span>

                                                    <?php if ($child['is_admin'] && $child['file_path']): ?>
                                                        <div class="child-image">
                                                            <?php if (strpos($child['file_path'], 'http') === 0): ?>
                                                                <img src="<?php echo htmlspecialchars($child['file_path']); ?>" alt="<?php echo htmlspecialchars($child['title']); ?>">
                                                            <?php elseif (file_exists(__DIR__ . '/' . $child['file_path']) && strpos(mime_content_type(__DIR__ . '/' . $child['file_path']), 'image/') === 0): ?>
                                                                <img src="download.php?id=<?php echo $child['id']; ?>" alt="<?php echo htmlspecialchars($child['title']); ?>">
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <div class="post-news-cta">
                        <a href="post.php" class="btn">Post New Article</a>
                    </div>
                <?php endif; ?>
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

    <div class="floating-social">
        <a href="<?php echo htmlspecialchars(get_setting('youtube_url', 'https://youtube.com/channel/YOUR_CHANNEL_ID')); ?>" target="_blank" class="social-icon youtube" title="Follow us on YouTube">📺</a>
        <a href="<?php echo htmlspecialchars(get_setting('discord_url', 'https://discord.gg/YOUR_INVITE_CODE')); ?>" target="_blank" class="social-icon discord" title="Join our Discord">💬</a>
    </div>
    <script src="js/mobile-menu.js"></script>
    <?php echo get_code_protection(); ?>
</body>
</html>
