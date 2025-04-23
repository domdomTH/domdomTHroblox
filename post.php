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

// Get all existing posts for parent selection
$existing_posts = db_get_rows("SELECT id, title FROM news WHERE parent_id IS NULL ORDER BY created_at DESC");

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Process post form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $custom_url = $_POST['custom_url'] ?? '';
    $cover_photo_url = $_POST['cover_photo_url'] ?? '';
    $file_display_option = $_POST['file_display_option'] ?? 'download';
    $file_path = null;
    $cover_photo = null;
    $content_images = []; // Array to store content image paths

    // Validate input
    if (empty($title) || empty($content)) {
        $error = 'Please fill in all required fields';
    } else {
        // Check if cover photo URL is provided
        if (!empty($cover_photo_url)) {
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
                $cover_photo = 'uploads/' . $new_cover_file_name;
            } else {
                $error = 'Failed to upload cover photo';
            }
        }

        // Check if custom URL is provided for attachment
        if (!empty($custom_url)) {
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
                        $content_images[] = 'uploads/' . $new_img_name;
                    } else {
                        $error = 'Failed to upload content image';
                        break;
                    }
                }
            }
        }

        if (empty($error)) {
            // Insert news article
            $news_id = db_insert('news', [
                'title' => $title,
                'content' => $content,
                'author_id' => $_SESSION['user_id'],
                'file_path' => $file_path,
                'parent_id' => $parent_id,
                'cover_photo' => $cover_photo,
                'file_display_option' => $file_display_option
            ]);

            if ($news_id) {
                // Insert content images if any
                if (!empty($content_images)) {
                    // Ensure news_images table exists
                    ensure_news_images_table();

                    foreach ($content_images as $position => $image_path) {
                        db_insert('news_images', [
                            'news_id' => $news_id,
                            'image_path' => $image_path,
                            'position' => $position
                        ]);
                    }
                }

                $success = 'News article posted successfully!';
            } else {
                $error = 'Failed to post news article. Please try again.';
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
    <title>Post News - <?php echo SITE_NAME; ?></title>
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
                        <li><a href="post.php" class="active"><span class="nav-icon">📝</span>Post News</a></li>
                        <li><a href="admin.php"><span class="nav-icon">🛠️</span>Admin</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php"><span class="nav-icon">👤</span>My Profile</a></li>
                    <li><a href="logout.php"><span class="nav-icon">🔓</span>Logout <span class="nav-username">(<?php echo $_SESSION['username']; ?>)</span></a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="post-form">
                <h2>Post News Article</h2>

                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success"><?php echo $success; ?></div>
                    <div class="form-actions">
                        <a href="index.php" class="btn">Back to Home</a>
                        <a href="post.php" class="btn btn-secondary">Post Another Article</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="post.php" enctype="multipart/form-data" id="post-form">
                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <h3 class="form-section-title"><span class="icon">📋</span> Basic Information</h3>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="title">Title
                                        <span class="tooltip">
                                            <span class="tooltip-icon">ℹ️</span>
                                            <span class="tooltip-text">Enter a descriptive title for your article. This will appear as the headline.</span>
                                        </span>
                                    </label>
                                    <input type="text" id="title" name="title" required maxlength="100">
                                    <div class="char-counter" id="title-counter">0/100 characters</div>
                                </div>

                                <div class="form-group full-width">
                                    <label for="parent_id">Parent Post (Optional)
                                        <span class="tooltip">
                                            <span class="tooltip-icon">ℹ️</span>
                                            <span class="tooltip-text">Select a parent post to create this as a child post. Leave as 'None' to create a main post.</span>
                                        </span>
                                    </label>
                                    <select id="parent_id" name="parent_id">
                                        <option value="">None (Create as main post)</option>
                                        <?php foreach ($existing_posts as $post): ?>
                                            <option value="<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small>Select a parent post to create this as a child post</small>
                                </div>

                                <div class="form-group full-width">
                                    <label for="content">Content
                                        <span class="tooltip">
                                            <span class="tooltip-icon">ℹ️</span>
                                            <span class="tooltip-text">Write the main content of your article. You can use plain text only.</span>
                                        </span>
                                    </label>
                                    <textarea id="content" name="content" rows="10" required></textarea>
                                    <small>Write your article content here. Plain text only.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Cover Photo Section -->
                        <div class="form-section">
                            <h3 class="form-section-title"><span class="icon">🖼️</span> Cover Photo</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="cover_photo_url">Cover Photo URL (Optional)
                                        <span class="tooltip">
                                            <span class="tooltip-icon">ℹ️</span>
                                            <span class="tooltip-text">Enter a URL for an existing image to use as the cover photo.</span>
                                        </span>
                                    </label>
                                    <input type="text" id="cover_photo_url" name="cover_photo_url" placeholder="https://example.com/path/to/image.jpg">
                                    <small>Enter a URL for the cover photo</small>
                                </div>

                                <div class="form-group">
                                    <label>OR Upload Cover Photo (Optional)</label>
                                    <div class="file-upload" id="cover-upload">
                                        <input type="file" id="cover_photo" name="cover_photo" accept="image/*">
                                        <div class="file-upload-icon">📷</div>
                                        <div class="file-upload-text">Click or drag to upload an image</div>
                                    </div>
                                    <div class="file-preview" id="cover-preview">
                                        <img id="cover-preview-img" src="#" alt="Cover preview">
                                        <div class="file-name" id="cover-file-name"></div>
                                    </div>
                                    <small>Upload an image to use as the cover photo (Max: 10MB)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Content Images Section -->
                        <div class="form-section">
                            <h3 class="form-section-title"><span class="icon">🖼️</span> Content Images</h3>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label>Upload Multiple Content Images
                                        <span class="tooltip">
                                            <span class="tooltip-icon">ℹ️</span>
                                            <span class="tooltip-text">Upload multiple images to be displayed within your article content. These will appear between paragraphs.</span>
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
                                        <div class="preview-title">Selected Images: <span id="image-count">0</span></div>
                                        <div class="preview-container" id="content-images-preview-container"></div>
                                    </div>
                                    <div class="image-tips">
                                        <p><strong>Tips for adding multiple images:</strong></p>
                                        <ul>
                                            <li>Hold Ctrl (or Cmd on Mac) while clicking to select multiple files</li>
                                            <li>Images will be displayed in the order they are uploaded</li>
                                            <li>Each image can be up to 10MB in size</li>
                                            <li>Supported formats: JPG, PNG, GIF, WebP</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attachment Section -->
                        <div class="form-section">
                            <h3 class="form-section-title"><span class="icon">📎</span> Attachment</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="custom_url">Attachment URL (Optional)
                                        <span class="tooltip">
                                            <span class="tooltip-icon">ℹ️</span>
                                            <span class="tooltip-text">Enter a URL for an existing file that users can download or view.</span>
                                        </span>
                                    </label>
                                    <input type="text" id="custom_url" name="custom_url" placeholder="https://example.com/path/to/file">
                                    <small>Enter a custom URL for the download link</small>
                                </div>

                                <div class="form-group">
                                    <label>OR Upload File (Optional)</label>
                                    <div class="file-upload" id="file-upload">
                                        <input type="file" id="file" name="file">
                                        <div class="file-upload-icon">📄</div>
                                        <div class="file-upload-text">Click or drag to upload a file</div>
                                    </div>
                                    <div class="file-preview" id="file-preview">
                                        <div class="file-name" id="file-name"></div>
                                    </div>
                                    <small>Upload a script or file to share with readers (Max: 10MB)</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="file_display_option">File Display Option
                                    <span class="tooltip">
                                        <span class="tooltip-icon">ℹ️</span>
                                        <span class="tooltip-text">Choose how the file will be presented to users. Download will force a download, View will open in the browser if possible.</span>
                                    </span>
                                </label>
                                <div class="radio-button-group">
                                    <label id="download-label">
                                        <input type="radio" name="file_display_option" value="download" checked id="download-option">
                                        <span>
                                            <span class="option-icon">💾</span>
                                            Download
                                            <span class="option-text">Force file download</span>
                                        </span>
                                    </label>
                                    <label id="view-label">
                                        <input type="radio" name="file_display_option" value="view" id="view-option">
                                        <span>
                                            <span class="option-icon">👁️</span>
                                            View
                                            <span class="option-text">Open file in browser</span>
                                        </span>
                                    </label>
                                </div>
                                <small>Choose how the file will be presented to users</small>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="content-preview" id="content-preview">
                            <h3>Content Preview</h3>
                            <div class="preview-content" id="preview-content"></div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" id="preview-btn">Preview</button>
                            <button type="submit" class="btn">Post Article</button>
                        </div>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Character counter for title
                            const titleInput = document.getElementById('title');
                            const titleCounter = document.getElementById('title-counter');

                            titleInput.addEventListener('input', function() {
                                const count = this.value.length;
                                titleCounter.textContent = `${count}/100 characters`;

                                if (count > 80) {
                                    titleCounter.className = 'char-counter warning';
                                } else if (count > 90) {
                                    titleCounter.className = 'char-counter danger';
                                } else {
                                    titleCounter.className = 'char-counter';
                                }
                            });

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

                            // Cover photo preview
                            const coverPhotoInput = document.getElementById('cover_photo');
                            const coverPreview = document.getElementById('cover-preview');
                            const coverPreviewImg = document.getElementById('cover-preview-img');
                            const coverFileName = document.getElementById('cover-file-name');

                            coverPhotoInput.addEventListener('change', function() {
                                if (this.files && this.files[0]) {
                                    const file = this.files[0];
                                    const reader = new FileReader();

                                    reader.onload = function(e) {
                                        coverPreviewImg.src = e.target.result;
                                        coverFileName.textContent = file.name;
                                        coverPreview.style.display = 'block';
                                    };

                                    reader.readAsDataURL(file);
                                }
                            });

                            // File preview
                            const fileInput = document.getElementById('file');
                            const filePreview = document.getElementById('file-preview');
                            const fileName = document.getElementById('file-name');

                            fileInput.addEventListener('change', function() {
                                if (this.files && this.files[0]) {
                                    const file = this.files[0];
                                    fileName.textContent = file.name;
                                    filePreview.style.display = 'block';
                                }
                            });

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

                                    // Add a message about the order
                                    const orderMessage = document.createElement('div');
                                    orderMessage.className = 'order-message';
                                    orderMessage.innerHTML = `<p>Images will be displayed in the order shown above.</p>`;
                                    contentImagesPreview.appendChild(orderMessage);
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

                            // Content preview
                            const previewBtn = document.getElementById('preview-btn');
                            const contentPreview = document.getElementById('content-preview');
                            const previewContent = document.getElementById('preview-content');
                            const contentInput = document.getElementById('content');

                            previewBtn.addEventListener('click', function() {
                                const content = contentInput.value;
                                if (content.trim() !== '') {
                                    // Convert line breaks to <br> tags
                                    previewContent.innerHTML = content.replace(/\n/g, '<br>');
                                    contentPreview.style.display = 'block';
                                    // Scroll to preview
                                    contentPreview.scrollIntoView({ behavior: 'smooth' });
                                } else {
                                    alert('Please enter some content to preview.');
                                }
                            });

                            // URL validation
                            const coverPhotoUrl = document.getElementById('cover_photo_url');
                            const customUrl = document.getElementById('custom_url');

                            function validateUrl(input) {
                                if (input.value.trim() !== '' && !input.value.match(/^https?:\/\/.+/)) {
                                    input.setCustomValidity('Please enter a valid URL starting with http:// or https://');
                                } else {
                                    input.setCustomValidity('');
                                }
                            }

                            coverPhotoUrl.addEventListener('input', function() {
                                validateUrl(this);
                            });

                            customUrl.addEventListener('input', function() {
                                validateUrl(this);
                            });

                            // Form validation
                            const form = document.getElementById('post-form');

                            form.addEventListener('submit', function(e) {
                                let isValid = true;

                                // Validate title
                                if (titleInput.value.trim() === '') {
                                    isValid = false;
                                    alert('Please enter a title for your article.');
                                }

                                // Validate content
                                if (contentInput.value.trim() === '') {
                                    isValid = false;
                                    alert('Please enter content for your article.');
                                }

                                // Validate URLs if provided
                                if (coverPhotoUrl.value.trim() !== '') {
                                    validateUrl(coverPhotoUrl);
                                    if (coverPhotoUrl.validity.customError) {
                                        isValid = false;
                                        alert('Please enter a valid cover photo URL.');
                                    }
                                }

                                if (customUrl.value.trim() !== '') {
                                    validateUrl(customUrl);
                                    if (customUrl.validity.customError) {
                                        isValid = false;
                                        alert('Please enter a valid attachment URL.');
                                    }
                                }

                                if (!isValid) {
                                    e.preventDefault();
                                }
                            });
                        });
                    </script>
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
    <script src="js/mobile-menu.js"></script>
</body>
</html>
