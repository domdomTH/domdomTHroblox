<?php
require_once 'config.php';
require_once 'db.php';

// Ensure settings table exists
ensure_site_settings_table();

// Get news ID from URL
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($news_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get news article
$article = db_get_row("
    SELECT n.*, u.username as author_name, u.is_admin, p.id as parent_id, p.title as parent_title
    FROM news n
    JOIN users u ON n.author_id = u.id
    LEFT JOIN news p ON n.parent_id = p.id
    WHERE n.id = ?
", [$news_id]);

// Ensure news_images table exists
ensure_news_images_table();

// Get content images for this article
$content_images = db_get_rows("
    SELECT * FROM news_images
    WHERE news_id = ?
    ORDER BY position ASC
", [$news_id]);

if (!$article) {
    header('Location: index.php');
    exit;
}

// Comments functionality has been removed

// Get child posts if this is a parent post
$child_posts = db_get_rows("
    SELECT n.*, u.username as author_name, u.is_admin
    FROM news n
    JOIN users u ON n.author_id = u.id
    WHERE n.parent_id = ?
    ORDER BY n.created_at DESC
", [$news_id]);

// Comment form processing has been removed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - <?php echo SITE_NAME; ?></title>
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
            <article class="news-detail">
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <div class="admin-controls">
                    <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn">Edit Article</a>
                </div>
                <?php endif; ?>

                <?php if ($article['cover_photo']): ?>
                    <div class="cover-photo">
                        <?php if (strpos($article['cover_photo'], 'http') === 0): ?>
                            <img src="<?php echo htmlspecialchars($article['cover_photo']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <?php elseif (file_exists(__DIR__ . '/' . $article['cover_photo'])): ?>
                            <img src="<?php echo htmlspecialchars($article['cover_photo']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="news-detail-content">
                    <h2><?php echo htmlspecialchars($article['title']); ?></h2>

                    <div class="news-meta">
                        <span class="author">By <?php echo htmlspecialchars($article['author_name']); ?><?php echo $article['is_admin'] ? ' <span class="admin-badge">Admin</span>' : ''; ?></span>
                        <span class="date"><?php echo date('M d, Y', strtotime($article['created_at'])); ?></span>
                        <?php if ($article['parent_title']): ?>
                            <span class="parent-post">Child post of <a href="news.php?id=<?php echo $article['parent_id']; ?>"><?php echo htmlspecialchars($article['parent_title']); ?></a></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($article['is_admin'] && $article['file_path'] && !$article['cover_photo']): ?>
                        <div class="news-image detail-image">
                            <?php if (strpos($article['file_path'], 'http') === 0): ?>
                                <img src="<?php echo htmlspecialchars($article['file_path']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                            <?php elseif (file_exists(__DIR__ . '/' . $article['file_path']) && strpos(mime_content_type(__DIR__ . '/' . $article['file_path']), 'image/') === 0): ?>
                                <img src="download.php?id=<?php echo $article['id']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="news-content">
                        <?php
                        // Display article content
                        echo '<div class="article-content">';
                        echo nl2br(htmlspecialchars($article['content']));
                        echo '</div>';

                        // Display photo gallery if we have images
                        if (!empty($content_images)) {
                            echo '<div class="image-gallery-section">';
                            echo '<h4>Photo Gallery</h4>';
                            echo '<p class="gallery-description">Click on any image to view in full-screen mode</p>';
                            echo '<div class="gallery-grid">';

                            foreach ($content_images as $index => $image) {
                                $image_number = $index + 1;
                                echo '<div class="gallery-item" onclick="openImageViewer(' . $image_number . ')">';
                                if (strpos($image['image_path'], 'http') === 0) {
                                    echo '<img src="' . htmlspecialchars($image['image_path']) . '" alt="Gallery image ' . $image_number . '">';
                                } elseif (file_exists(__DIR__ . '/' . $image['image_path'])) {
                                    echo '<img src="' . htmlspecialchars($image['image_path']) . '" alt="Gallery image ' . $image_number . '">';
                                }
                                echo '<div class="gallery-item-number">' . $image_number . '</div>';
                                echo '</div>';
                            }

                            echo '</div>';
                            echo '</div>';
                        }
                        ?>

                        <!-- Image Viewer Modal -->
                        <div id="image-viewer-modal" class="modal">
                            <span class="close-modal">&times;</span>
                            <div class="modal-content">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <img id="modal-image" src="">
                                    <div class="modal-caption" id="modal-caption"></div>
                                    <a class="prev" onclick="changeImage(-1)">&#10094;</a>
                                    <a class="next" onclick="changeImage(1)">&#10095;</a>
                                    <div class="image-counter" id="image-counter"></div>
                                <?php else: ?>
                                    <div class="locked-content">
                                        <div class="lock-icon">🔒</div>
                                        <h3>Login Required</h3>
                                        <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to view full-size images.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <script>
                        // Image viewer functionality
                        let currentImageIndex = 0;
                        const contentImages = [
                            <?php foreach ($content_images as $index => $image): ?>
                                {
                                    src: '<?php echo htmlspecialchars(strpos($image['image_path'], 'http') === 0 ? $image['image_path'] : $image['image_path']); ?>',
                                    number: <?php echo $index + 1; ?>
                                }<?php echo ($index < count($content_images) - 1) ? ',' : ''; ?>
                            <?php endforeach; ?>
                        ];

                        function openImageViewer(imageNumber) {
                            const modal = document.getElementById('image-viewer-modal');
                            <?php if (isset($_SESSION['user_id'])): ?>
                            const modalImg = document.getElementById('modal-image');
                            const captionText = document.getElementById('modal-caption');
                            const counterText = document.getElementById('image-counter');

                            // Find the image index by number
                            currentImageIndex = imageNumber - 1; // Adjust for 0-based index
                            if (currentImageIndex < 0 || currentImageIndex >= contentImages.length) {
                                currentImageIndex = 0;
                            }

                            modalImg.src = contentImages[currentImageIndex].src;
                            captionText.innerHTML = 'Image ' + contentImages[currentImageIndex].number;
                            counterText.innerHTML = (currentImageIndex + 1) + ' / ' + contentImages.length;
                            <?php endif; ?>

                            modal.style.display = 'block';

                            // Add keyboard navigation
                            document.addEventListener('keydown', handleKeyPress);
                        }

                        function changeImage(step) {
                            currentImageIndex += step;

                            // Loop around if we reach the end
                            if (currentImageIndex >= contentImages.length) {
                                currentImageIndex = 0;
                            } else if (currentImageIndex < 0) {
                                currentImageIndex = contentImages.length - 1;
                            }

                            const modalImg = document.getElementById('modal-image');
                            const captionText = document.getElementById('modal-caption');
                            const counterText = document.getElementById('image-counter');

                            modalImg.src = contentImages[currentImageIndex].src;
                            captionText.innerHTML = 'Image ' + contentImages[currentImageIndex].number;
                            counterText.innerHTML = (currentImageIndex + 1) + ' / ' + contentImages.length;
                        }

                        // Handle keyboard navigation
                        function handleKeyPress(e) {
                            if (e.key === 'ArrowRight') {
                                changeImage(1);
                            } else if (e.key === 'ArrowLeft') {
                                changeImage(-1);
                            } else if (e.key === 'Escape') {
                                closeModal();
                            }
                        }

                        // Close the modal
                        function closeModal() {
                            document.getElementById('image-viewer-modal').style.display = 'none';
                            // Remove keyboard event listener when modal is closed
                            document.removeEventListener('keydown', handleKeyPress);
                        }

                        document.querySelector('.close-modal').addEventListener('click', closeModal);

                        // Close modal when clicking outside the image
                        window.addEventListener('click', function(event) {
                            const modal = document.getElementById('image-viewer-modal');
                            if (event.target === modal) {
                                closeModal();
                            }
                        });

                        // Scroll to image function
                        function scrollToImage(imageNumber) {
                            const imageElement = document.getElementById('image-' + imageNumber);
                            if (imageElement) {
                                imageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                        </script>
                    </div>

                    <?php if (!empty($child_posts)): ?>
                        <div class="child-posts detail-child-posts">
                            <h3>Related Posts</h3>
                            <ul>
                                <?php foreach ($child_posts as $child): ?>
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

                                        <?php if ($child['is_admin'] && $child['file_path'] && !$child['cover_photo']): ?>
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

                    <?php if ($article['file_path']): ?>
                        <div class="download-section">
                            <?php
                            $file_display_option = $article['file_display_option'] ?? 'download';
                            $button_text = $file_display_option === 'download' ? 'Download File' : 'View File';
                            $button_class = $file_display_option === 'download' ? 'download-btn' : 'view-btn';
                            $target = $file_display_option === 'download' ? '_self' : '_blank';
                            $download_param = $file_display_option === 'download' ? '&download=true' : '';

                            // Determine if it's a local file or external URL
                            $is_local_file = strpos($article['file_path'], 'http') !== 0;
                            $file_url = $is_local_file ?
                                        "download.php?id={$article['id']}{$download_param}" :
                                        htmlspecialchars($article['file_path']);
                            ?>

                            <h3><?php echo $file_display_option === 'download' ? 'Download' : 'View'; ?></h3>
                            <p>This article includes a <?php echo $file_display_option === 'download' ? 'downloadable' : 'viewable'; ?> file:</p>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <!-- Registered users can download/view the file -->
                                <a href="<?php echo $file_url; ?>" class="<?php echo $button_class; ?>" target="<?php echo $target; ?>"><?php echo $button_text; ?></a>
                            <?php else: ?>
                                <!-- Unregistered users see a locked button -->
                                <div class="locked-button-container">
                                    <a href="login.php" class="locked-btn">
                                        <span class="lock-icon">🔒</span> <?php echo $button_text; ?>
                                    </a>
                                    <p class="login-message">Please <a href="login.php">login</a> or <a href="register.php">register</a> to access this file.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div><!-- End news-detail-content -->

                <!-- Comments section has been removed -->
            </article>
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
</body>
</html>
