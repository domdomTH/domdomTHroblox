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

$error = '';
$success = '';

// Get news ID from URL
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($news_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get news article
$article = db_get_row("
    SELECT n.*, u.username as author_name
    FROM news n
    JOIN users u ON n.author_id = u.id
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

// Get all existing posts for parent selection (excluding this post and its children)
$existing_posts = db_get_rows("SELECT id, title FROM news WHERE id != ? AND parent_id IS NULL ORDER BY created_at DESC", [$news_id]);

if (!$article) {
    header('Location: index.php');
    exit;
}

// Process edit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $custom_url = $_POST['custom_url'] ?? '';
    $cover_photo_url = $_POST['cover_photo_url'] ?? '';
    $file_path = $article['file_path'];
    $cover_photo = $article['cover_photo'];
    $file_display_option = $_POST['file_display_option'] ?? 'download';
    $content_images_to_add = []; // Array to store new content images

    // Check if admin wants to remove cover photo or file
    $remove_cover_photo = isset($_POST['remove_cover_photo']) && $_POST['remove_cover_photo'] === 'yes';
    $remove_file = isset($_POST['remove_file']) && $_POST['remove_file'] === 'yes';

    // Get content images to remove
    $remove_content_images = isset($_POST['remove_content_images']) ? $_POST['remove_content_images'] : [];

    // Validate input
    if (empty($title) || empty($content)) {
        $error = 'Please fill in all required fields';
    } else {
        // Handle cover photo removal
        if ($remove_cover_photo) {
            // Delete the existing cover photo if it's a local file
            if ($cover_photo && strpos($cover_photo, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $cover_photo)) {
                unlink(__DIR__ . '/' . $cover_photo);
            }
            $cover_photo = null;
        }
        // Check if cover photo URL is provided
        elseif (!empty($cover_photo_url)) {
            // If there's an existing uploaded cover photo, delete it
            if ($cover_photo && strpos($cover_photo, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $cover_photo)) {
                unlink(__DIR__ . '/' . $cover_photo);
            }
            $cover_photo = $cover_photo_url;
        }
        // Handle cover photo upload if present
        if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
            $cover_file_name = $_FILES['cover_photo']['name'];
            $cover_file_tmp = $_FILES['cover_photo']['tmp_name'];
            $cover_file_size = $_FILES['cover_photo']['size'];
            $cover_file_ext = strtolower(pathinfo($cover_file_name, PATHINFO_EXTENSION));

            // Generate unique filename
            $new_cover_file_name = 'cover_' . uniqid() . '_' . $cover_file_name;
            $cover_upload_path = UPLOAD_DIR . $new_cover_file_name;

            // Check file size (limit to 10MB)
            if ($cover_file_size > 10485760) {
                $error = 'Cover photo size must be less than 10MB';
            }
            // Move uploaded file
            elseif (move_uploaded_file($cover_file_tmp, $cover_upload_path)) {
                // Delete old cover photo if exists
                if ($cover_photo && strpos($cover_photo, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $cover_photo)) {
                    unlink(__DIR__ . '/' . $cover_photo);
                }
                $cover_photo = 'uploads/' . $new_cover_file_name;
            } else {
                $error = 'Failed to upload cover photo';
            }
        }

        // Handle file removal
        if ($remove_file) {
            // Delete the existing file if it's a local file
            if ($file_path && strpos($file_path, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $file_path)) {
                unlink(__DIR__ . '/' . $file_path);
            }
            $file_path = null;
        }
        // Check if custom URL is provided for attachment
        elseif (!empty($custom_url)) {
            // If there's an existing uploaded file, delete it
            if ($file_path && strpos($file_path, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $file_path)) {
                unlink(__DIR__ . '/' . $file_path);
            }
            $file_path = $custom_url;
        }
        // Handle file upload if present and no custom URL
        elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['file']['name'];
            $file_tmp = $_FILES['file']['tmp_name'];
            $file_size = $_FILES['file']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Generate unique filename
            $new_file_name = uniqid() . '_' . $file_name;
            $upload_path = UPLOAD_DIR . $new_file_name;

            // Check file size (limit to 10MB)
            if ($file_size > 10485760) {
                $error = 'File size must be less than 10MB';
            }
            // Move uploaded file
            elseif (move_uploaded_file($file_tmp, $upload_path)) {
                // Delete old file if exists
                if ($file_path && strpos($file_path, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $file_path)) {
                    unlink(__DIR__ . '/' . $file_path);
                }
                $file_path = 'uploads/' . $new_file_name;
            } else {
                $error = 'Failed to upload file';
            }
        }

        // Handle content images upload
        if (isset($_FILES['content_images']) && is_array($_FILES['content_images']['name'])) {
            for ($i = 0; $i < count($_FILES['content_images']['name']); $i++) {
                if ($_FILES['content_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $img_name = $_FILES['content_images']['name'][$i];
                    $img_tmp = $_FILES['content_images']['tmp_name'][$i];
                    $img_size = $_FILES['content_images']['size'][$i];
                    $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));

                    // Generate unique filename
                    $new_img_name = 'content_' . uniqid() . '_' . $img_name;
                    $img_upload_path = UPLOAD_DIR . $new_img_name;

                    // Check file size (limit to 10MB)
                    if ($img_size > 10485760) {
                        $error = 'Content image size must be less than 10MB';
                        break;
                    }
                    // Move uploaded file
                    elseif (move_uploaded_file($img_tmp, $img_upload_path)) {
                        $content_images_to_add[] = 'uploads/' . $new_img_name;
                    } else {
                        $error = 'Failed to upload content image';
                        break;
                    }
                }
            }
        }

        if (empty($error)) {
            // Debug information
            error_log('Updating news article with ID: ' . $news_id);
            error_log('Title: ' . $title);
            error_log('Content length: ' . strlen($content));
            error_log('Parent ID: ' . ($parent_id ? $parent_id : 'NULL'));
            error_log('File path: ' . ($file_path ? $file_path : 'NULL'));
            error_log('Cover photo: ' . ($cover_photo ? $cover_photo : 'NULL'));

            // Update news article
            $update_data = [
                'title' => $title,
                'content' => $content,
                'file_path' => $file_path,
                'parent_id' => $parent_id,
                'cover_photo' => $cover_photo,
                'file_display_option' => $file_display_option
            ];

            // Filter out null values that might cause issues
            foreach ($update_data as $key => $value) {
                if ($value === null) {
                    $update_data[$key] = NULL;
                }
            }

            $result = db_update('news', $update_data, 'id = ?', [$news_id]);

            if ($result) {
                // Handle content images to remove
                if (!empty($remove_content_images)) {
                    foreach ($remove_content_images as $image_id) {
                        // Get the image path before deleting
                        $image = db_get_row("SELECT * FROM news_images WHERE id = ? AND news_id = ?", [(int)$image_id, $news_id]);

                        if ($image) {
                            // Delete the file if it exists
                            if (strpos($image['image_path'], 'uploads/') === 0 && file_exists(__DIR__ . '/' . $image['image_path'])) {
                                unlink(__DIR__ . '/' . $image['image_path']);
                            }

                            // Delete from database
                            db_delete('news_images', 'id = ?', [(int)$image_id]);
                        }
                    }
                }

                // Add new content images
                if (!empty($content_images_to_add)) {
                    // Ensure news_images table exists
                    ensure_news_images_table();

                    // Get the current highest position
                    $max_position = 0;
                    if (!empty($content_images)) {
                        foreach ($content_images as $img) {
                            $max_position = max($max_position, $img['position']);
                        }
                    }

                    // Insert new images with incremented positions
                    foreach ($content_images_to_add as $index => $image_path) {
                        db_insert('news_images', [
                            'news_id' => $news_id,
                            'image_path' => $image_path,
                            'position' => $max_position + $index + 1
                        ]);
                    }
                }

                $success = 'News article updated successfully!';
                // Refresh article data
                $article = db_get_row("
                    SELECT n.*, u.username as author_name
                    FROM news n
                    JOIN users u ON n.author_id = u.id
                    WHERE n.id = ?
                ", [$news_id]);

                // Refresh content images
                $content_images = db_get_rows("
                    SELECT * FROM news_images
                    WHERE news_id = ?
                    ORDER BY position ASC
                ", [$news_id]);
            } else {
                $error = 'Failed to update news article. Please try again.';
            }
        }
    }
}

// Process delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // Delete content images first
    $images = db_get_rows("SELECT * FROM news_images WHERE news_id = ?", [$news_id]);
    foreach ($images as $image) {
        if (strpos($image['image_path'], 'uploads/') === 0 && file_exists(__DIR__ . '/' . $image['image_path'])) {
            unlink(__DIR__ . '/' . $image['image_path']);
        }
    }
    db_delete('news_images', 'news_id = ?', [$news_id]);

    // Delete comments (due to foreign key constraint)
    db_delete('comments', 'news_id = ?', [$news_id]);

    // Delete the news article
    $result = db_delete('news', 'id = ?', [$news_id]);

    if ($result) {
        // Delete file if exists
        if ($article['file_path'] && file_exists(__DIR__ . '/' . $article['file_path'])) {
            unlink(__DIR__ . '/' . $article['file_path']);
        }

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/post-form.css">
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
            <section class="edit-form">
                <h2>Edit News Article</h2>

                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="edit.php?id=<?php echo $news_id; ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="parent_id">Parent Post (Optional)</label>
                        <select id="parent_id" name="parent_id">
                            <option value="">None (Create as main post)</option>
                            <?php foreach ($existing_posts as $post): ?>
                                <option value="<?php echo $post['id']; ?>" <?php echo ($article['parent_id'] == $post['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Select a parent post to create this as a child post</small>
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                    </div>

                    <?php if ($article['cover_photo']): ?>
                    <div class="form-group media-preview">
                        <label>Current Cover Photo</label>
                        <div class="preview-container">
                            <?php if (strpos($article['cover_photo'], 'http') === 0): ?>
                                <img src="<?php echo htmlspecialchars($article['cover_photo']); ?>" alt="Cover Photo">
                            <?php elseif (file_exists(__DIR__ . '/' . $article['cover_photo'])): ?>
                                <img src="<?php echo htmlspecialchars($article['cover_photo']); ?>" alt="Cover Photo">
                            <?php else: ?>
                                <p>Cover photo file not found: <?php echo htmlspecialchars(basename($article['cover_photo'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" name="remove_cover_photo" value="yes">
                                Remove this cover photo
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="cover_photo_url">Cover Photo URL (Optional)</label>
                        <input type="text" id="cover_photo_url" name="cover_photo_url" placeholder="https://example.com/path/to/image.jpg" value="<?php echo (strpos($article['cover_photo'], 'http') === 0) ? htmlspecialchars($article['cover_photo']) : ''; ?>">
                        <small>Enter a URL for the cover photo</small>
                    </div>

                    <div class="form-group">
                        <label for="cover_photo">OR Upload Cover Photo (Optional)</label>
                        <input type="file" id="cover_photo" name="cover_photo" accept="image/*">
                        <small>Upload an image to use as the cover photo (Max: 10MB)</small>
                    </div>

                    <?php if ($article['file_path']): ?>
                    <div class="form-group media-preview">
                        <label>Current Attachment</label>
                        <div class="file-info">
                            <p><strong>File:</strong> <?php echo basename($article['file_path']); ?></p>
                            <?php if (strpos($article['file_path'], 'http') === 0): ?>
                                <p><strong>URL:</strong> <a href="<?php echo htmlspecialchars($article['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($article['file_path']); ?></a></p>
                            <?php elseif (file_exists(__DIR__ . '/' . $article['file_path'])): ?>
                                <p><strong>Size:</strong> <?php echo round(filesize(__DIR__ . '/' . $article['file_path']) / 1024, 2); ?> KB</p>
                                <p><strong>Type:</strong> <?php echo mime_content_type(__DIR__ . '/' . $article['file_path']); ?></p>
                            <?php else: ?>
                                <p>File not found: <?php echo htmlspecialchars(basename($article['file_path'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" name="remove_file" value="yes">
                                Remove this file
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="custom_url">Attachment URL (Optional)</label>
                        <input type="text" id="custom_url" name="custom_url" placeholder="https://example.com/path/to/file" value="<?php echo (strpos($article['file_path'], 'http') === 0) ? htmlspecialchars($article['file_path']) : ''; ?>">
                        <small>Enter a custom URL for the download link</small>
                    </div>

                    <div class="form-group">
                        <label for="file">OR Upload File (Optional)</label>
                        <input type="file" id="file" name="file">
                        <small>Upload a script or file to share with readers (Max: 10MB)</small>
                    </div>

                    <!-- Content Images Section -->
                    <div class="form-section">
                        <h3 class="form-section-title"><span class="icon">🖼️</span> Content Images</h3>

                        <?php if (!empty($content_images)): ?>
                        <div class="form-group media-preview">
                            <label>Current Content Images <span class="image-count"><?php echo count($content_images); ?> images</span></label>
                            <div class="content-images-container">
                                <?php foreach ($content_images as $index => $image): ?>
                                    <div class="content-image-item">
                                        <div class="image-number"><?php echo $index + 1; ?></div>
                                        <?php if (strpos($image['image_path'], 'http') === 0): ?>
                                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Content Image">
                                        <?php elseif (file_exists(__DIR__ . '/' . $image['image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Content Image">
                                        <?php else: ?>
                                            <p>Image file not found: <?php echo htmlspecialchars(basename($image['image_path'])); ?></p>
                                        <?php endif; ?>
                                        <div class="image-path"><?php echo htmlspecialchars(basename($image['image_path'])); ?></div>
                                        <div class="checkbox-group">
                                            <label class="remove-image-label">
                                                <input type="checkbox" name="remove_content_images[]" value="<?php echo $image['id']; ?>">
                                                <span class="remove-text">Remove this image</span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="image-management-tips">
                                <p><strong>Tips:</strong></p>
                                <ul>
                                    <li>Check the boxes to remove images you no longer want</li>
                                    <li>Images are displayed in the order shown above</li>
                                    <li>Add new images below to append to the existing ones</li>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="content_images">Add More Content Images (Optional)
                                <span class="tooltip">
                                    <span class="tooltip-icon">ℹ️</span>
                                    <span class="tooltip-text">Upload additional images to be displayed within your article content. These will be added after any existing images.</span>
                                </span>
                            </label>
                            <div class="file-upload enhanced" id="content-images-upload">
                                <input type="file" id="content_images" name="content_images[]" accept="image/*" multiple>
                                <div class="file-upload-icon">🖼️</div>
                                <div class="file-upload-text">
                                    <strong>Click or drag to upload multiple content images</strong>
                                    <span class="upload-subtext">You can select multiple files at once</span>
                                </div>
                            </div>
                            <div class="content-images-preview" id="content-images-preview">
                                <div class="preview-title">Selected New Images: <span id="image-count">0</span></div>
                                <div class="preview-container" id="content-images-preview-container"></div>
                            </div>
                            <small>Upload images to be displayed within your article content (Max: 10MB each)</small>
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Content images preview with enhanced functionality
                        const contentImagesInput = document.getElementById('content_images');
                        const contentImagesPreview = document.getElementById('content-images-preview');
                        const contentImagesContainer = document.getElementById('content-images-preview-container');
                        const imageCountDisplay = document.getElementById('image-count');

                        contentImagesInput.addEventListener('change', function() {
                            if (this.files && this.files.length > 0) {
                                contentImagesContainer.innerHTML = ''; // Clear previous previews
                                imageCountDisplay.textContent = this.files.length;

                                for (let i = 0; i < this.files.length; i++) {
                                    const file = this.files[i];
                                    const reader = new FileReader();
                                    const previewItem = document.createElement('div');
                                    previewItem.className = 'preview-item';

                                    // Add image number indicator
                                    const imageNumber = document.createElement('div');
                                    imageNumber.className = 'image-number';
                                    imageNumber.textContent = (i + 1);
                                    previewItem.appendChild(imageNumber);

                                    reader.onload = function(e) {
                                        const imageWrapper = document.createElement('div');
                                        imageWrapper.className = 'preview-image-wrapper';
                                        imageWrapper.innerHTML = `<img src="${e.target.result}" alt="Content image preview">`;
                                        previewItem.appendChild(imageWrapper);

                                        const nameElement = document.createElement('div');
                                        nameElement.className = 'preview-item-name';
                                        nameElement.textContent = file.name;
                                        previewItem.appendChild(nameElement);

                                        // Add file size info
                                        const sizeElement = document.createElement('div');
                                        sizeElement.className = 'preview-item-size';
                                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                                        sizeElement.textContent = `${fileSizeMB} MB`;
                                        previewItem.appendChild(sizeElement);
                                    };

                                    reader.readAsDataURL(file);
                                    contentImagesContainer.appendChild(previewItem);
                                }

                                contentImagesPreview.style.display = 'block';
                            } else {
                                imageCountDisplay.textContent = '0';
                                contentImagesContainer.innerHTML = '';
                                contentImagesPreview.style.display = 'none';
                            }
                        });

                        // Make the file upload area more interactive
                        const contentImagesUpload = document.getElementById('content-images-upload');

                        contentImagesUpload.addEventListener('dragover', function(e) {
                            e.preventDefault();
                            this.classList.add('dragover');
                        });

                        contentImagesUpload.addEventListener('dragleave', function() {
                            this.classList.remove('dragover');
                        });

                        contentImagesUpload.addEventListener('drop', function(e) {
                            e.preventDefault();
                            this.classList.remove('dragover');
                            contentImagesInput.files = e.dataTransfer.files;

                            // Trigger the change event manually
                            const event = new Event('change');
                            contentImagesInput.dispatchEvent(event);
                        });
                    });
                    </script>

                    <div class="form-group">
                        <label for="file_display_option">File Display Option
                            <span class="tooltip">
                                <span class="tooltip-icon">ℹ️</span>
                                <span class="tooltip-text">Choose how the file will be presented to users. Download will force a download, View will open in the browser if possible.</span>
                            </span>
                        </label>
                        <div class="radio-button-group">
                            <label id="download-label">
                                <input type="radio" name="file_display_option" value="download" <?php echo (!isset($article['file_display_option']) || $article['file_display_option'] === 'download') ? 'checked' : ''; ?> id="download-option">
                                <span>
                                    <span class="option-icon">💾</span>
                                    Download
                                    <span class="option-text">Force file download</span>
                                </span>
                            </label>
                            <label id="view-label">
                                <input type="radio" name="file_display_option" value="view" <?php echo (isset($article['file_display_option']) && $article['file_display_option'] === 'view') ? 'checked' : ''; ?> id="view-option">
                                <span>
                                    <span class="option-icon">👁️</span>
                                    View
                                    <span class="option-text">Open file in browser</span>
                                </span>
                            </label>
                        </div>
                        <small>Choose how the file will be presented to users</small>
                    </div>

                    <div class="form-group button-group">
                        <button type="submit" class="btn">Update Article</button>
                        <a href="news.php?id=<?php echo $news_id; ?>" class="btn btn-secondary">Cancel</a>
                        <a href="edit.php?id=<?php echo $news_id; ?>&action=delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this article? This action cannot be undone.')">Delete Article</a>
                    </div>
                </form>

                <?php if (isset($_GET['action']) && $_GET['action'] === 'delete' && !isset($_GET['confirm'])): ?>
                    <div class="delete-confirmation">
                        <h3>Confirm Deletion</h3>
                        <p>Are you sure you want to delete this article? This action cannot be undone.</p>
                        <div class="button-group">
                            <a href="edit.php?id=<?php echo $news_id; ?>&action=delete&confirm=yes" class="btn btn-danger">Yes, Delete</a>
                            <a href="edit.php?id=<?php echo $news_id; ?>" class="btn">Cancel</a>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Enhanced radio button interaction
                const downloadOption = document.getElementById('download-option');
                const viewOption = document.getElementById('view-option');
                const downloadLabel = document.getElementById('download-label');
                const viewLabel = document.getElementById('view-label');

                // Set initial state
                if (downloadOption.checked) {
                    downloadLabel.classList.add('selected');
                } else if (viewOption.checked) {
                    viewLabel.classList.add('selected');
                }

                // Add click handlers for the entire label
                downloadLabel.addEventListener('click', function() {
                    downloadOption.checked = true;
                    downloadLabel.classList.add('selected');
                    viewLabel.classList.remove('selected');
                });

                viewLabel.addEventListener('click', function() {
                    viewOption.checked = true;
                    viewLabel.classList.add('selected');
                    downloadLabel.classList.remove('selected');
                });

                // Also handle the radio button change event
                downloadOption.addEventListener('change', function() {
                    if (this.checked) {
                        downloadLabel.classList.add('selected');
                        viewLabel.classList.remove('selected');
                    }
                });

                viewOption.addEventListener('change', function() {
                    if (this.checked) {
                        viewLabel.classList.add('selected');
                        downloadLabel.classList.remove('selected');
                    }
                });
            });
        </script>

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
