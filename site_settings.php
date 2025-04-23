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
    // Handle logo upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $logo_tmp = $_FILES['site_logo']['tmp_name'];
        $logo_name = $_FILES['site_logo']['name'];
        $logo_ext = strtolower(pathinfo($logo_name, PATHINFO_EXTENSION));

        // Validate file type
        $allowed_extensions = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
        if (in_array($logo_ext, $allowed_extensions)) {
            // Create images directory if it doesn't exist
            if (!file_exists('images')) {
                mkdir('images', 0755, true);
            }

            // Generate a unique filename
            $logo_path = 'images/logo.' . $logo_ext;

            // Check if we need to resize the image
            list($width, $height) = getimagesize($logo_tmp);
            $auto_resize = isset($_POST['auto_resize']) && $_POST['auto_resize'] === 'yes';
            $resize_needed = $auto_resize && ($width > 300 || $height > 300);
            $target_height = 100; // Target height for resized image

            if ($resize_needed && function_exists('imagecreatetruecolor')) {
                // Calculate new dimensions maintaining aspect ratio
                $ratio = $width / $height;
                $target_width = round($target_height * $ratio);

                // Create image resource based on file type
                switch ($logo_ext) {
                    case 'png':
                        $source = imagecreatefrompng($logo_tmp);
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $source = imagecreatefromjpeg($logo_tmp);
                        break;
                    case 'gif':
                        $source = imagecreatefromgif($logo_tmp);
                        break;
                    case 'webp':
                        if (function_exists('imagecreatefromwebp')) {
                            $source = imagecreatefromwebp($logo_tmp);
                        } else {
                            $resize_needed = false; // Can't resize webp if function doesn't exist
                        }
                        break;
                }

                if ($resize_needed) {
                    // Create a new true color image
                    $thumb = imagecreatetruecolor($target_width, $target_height);

                    // Preserve transparency for PNG and GIF
                    if ($logo_ext == 'png' || $logo_ext == 'gif') {
                        imagealphablending($thumb, false);
                        imagesavealpha($thumb, true);
                        $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                        imagefilledrectangle($thumb, 0, 0, $target_width, $target_height, $transparent);
                    }

                    // Resize the image
                    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $target_width, $target_height, $width, $height);

                    // Save the resized image
                    switch ($logo_ext) {
                        case 'png':
                            imagepng($thumb, $logo_path);
                            break;
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($thumb, $logo_path, 90);
                            break;
                        case 'gif':
                            imagegif($thumb, $logo_path);
                            break;
                        case 'webp':
                            if (function_exists('imagewebp')) {
                                imagewebp($thumb, $logo_path, 90);
                            }
                            break;
                    }

                    // Free up memory
                    imagedestroy($source);
                    imagedestroy($thumb);
                    $success = true;
                } else {
                    // If we couldn't resize, just move the file
                    $success = move_uploaded_file($logo_tmp, $logo_path);
                }
            } else {
                // If GD library is not available or resize not needed, just move the file
                $success = move_uploaded_file($logo_tmp, $logo_path);
            }

            // Update the setting if file was processed successfully
            if ($success) {
                // Update the setting
                if (update_setting('site_logo', $logo_path)) {
                    $success = true;
                    if ($resize_needed && function_exists('imagecreatetruecolor')) {
                        $message = "Site logo uploaded and automatically resized to optimal dimensions (from {$width}×{$height}px to {$target_width}×{$target_height}px).";
                    } else {
                        $message = "Site logo updated successfully. Original dimensions: {$width}×{$height}px.";
                    }
                } else {
                    $message = "Error updating site logo setting.";
                }
            } else {
                $message = "Error moving uploaded file.";
            }
        } else {
            $message = "Invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
        }
    } else if (isset($_POST['remove_logo']) && $_POST['remove_logo'] === 'yes') {
        // Handle logo removal
        $current_logo = get_setting('site_logo', 'images/logo.png');

        // Delete the file if it exists
        if (file_exists($current_logo)) {
            unlink($current_logo);
        }

        // Update the setting to default
        if (update_setting('site_logo', '')) {
            $success = true;
            $message = "Site logo removed successfully.";
        } else {
            $message = "Error removing site logo setting.";
        }
    }
}

// Get current logo
$current_logo = get_setting('site_logo', 'images/logo.png');
$logo_exists = !empty($current_logo) && file_exists($current_logo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - <?php echo SITE_NAME; ?></title>
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
                <h2>Site Settings</h2>

                <div class="admin-actions">
                    <a href="admin.php" class="btn">Back to Admin Dashboard</a>
                    <a href="social_settings.php" class="btn">Social Media Settings</a>
                </div>

                <?php if ($message): ?>
                    <div class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="settings-form">
                    <form method="POST" action="site_settings.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="site_logo">Site Logo</label>
                            <input type="file" id="site_logo" name="site_logo" accept="image/png,image/jpeg,image/gif,image/webp">
                            <div class="checkbox-group" style="margin-top: 10px;">
                                <label>
                                    <input type="checkbox" name="auto_resize" value="yes" checked>
                                    Automatically resize large images (recommended)
                                </label>
                                <small class="form-hint">Images larger than 300px will be resized to approximately 100px height while maintaining aspect ratio.</small>
                            </div>
                            <small class="form-hint">Upload a logo to display next to the site name. <strong>Recommended specs:</strong></small>
                            <ul class="form-hint-list">
                                <li>Height: 45px (will be automatically scaled)</li>
                                <li>Width: Ideally square or slightly wider than tall</li>
                                <li>Format: PNG with transparency for best results</li>
                                <li>Style: Simple, high-contrast design that works on dark backgrounds</li>
                                <li>Color: Match your brand colors or use colors that complement the cyberpunk theme</li>
                            </ul>
                        </div>

                        <?php if ($logo_exists): ?>
                        <div class="form-group">
                            <label>Current Logo</label>
                            <div class="current-logo-preview">
                                <div class="logo-preview-container">
                                    <div class="logo-preview-header">
                                        <div class="logo-preview-title">How it appears in header:</div>
                                    </div>
                                    <div class="logo-preview-box">
                                        <div class="logo-container" style="background: var(--background-alt); padding: 15px; border-radius: 5px;">
                                            <img src="<?php echo htmlspecialchars($current_logo); ?>" alt="Current Logo" class="site-logo">
                                            <span style="font-size: 1.8rem; color: var(--primary-color); text-transform: uppercase; letter-spacing: 2px;"><?php echo SITE_NAME; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="logo-preview-original">
                                    <div class="logo-preview-title">Original image:</div>
                                    <img src="<?php echo htmlspecialchars($current_logo); ?>" alt="Current Logo" style="max-height: 100px; border: 1px solid #333; padding: 10px; background: rgba(0,0,0,0.2);">
                                </div>
                            </div>
                            <div class="checkbox-group" style="margin-top: 15px;">
                                <label>
                                    <input type="checkbox" name="remove_logo" value="yes">
                                    Remove current logo
                                </label>
                                <small class="form-hint">Check this to remove the current logo without uploading a new one.</small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const logoInput = document.getElementById('site_logo');
                            const previewContainer = document.createElement('div');
                            previewContainer.className = 'upload-preview';
                            previewContainer.style.display = 'none';
                            previewContainer.innerHTML = `
                                <h4>Upload Preview</h4>
                                <div class="preview-info"></div>
                                <div class="preview-container">
                                    <div class="logo-preview-header">
                                        <div class="logo-preview-title">How it will appear in header:</div>
                                    </div>
                                    <div class="logo-preview-box">
                                        <div class="logo-container" style="background: var(--background-alt); padding: 15px; border-radius: 5px;">
                                            <img id="preview-image" src="" alt="Logo Preview" class="site-logo">
                                            <span style="font-size: 1.8rem; color: var(--primary-color); text-transform: uppercase; letter-spacing: 2px;"><?php echo SITE_NAME; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="size-warning" style="display: none; color: var(--admin-warning); margin-top: 10px;"></div>
                            `;

                            // Insert after the file input
                            logoInput.parentNode.insertBefore(previewContainer, logoInput.nextSibling);

                            logoInput.addEventListener('change', function(e) {
                                const file = e.target.files[0];
                                if (!file) {
                                    previewContainer.style.display = 'none';
                                    return;
                                }

                                const reader = new FileReader();
                                reader.onload = function(event) {
                                    const img = new Image();
                                    img.onload = function() {
                                        const previewImage = document.getElementById('preview-image');
                                        previewImage.src = event.target.result;

                                        // Show dimensions and file size
                                        const infoDiv = previewContainer.querySelector('.preview-info');
                                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                                        infoDiv.innerHTML = `
                                            <div class="info-item"><strong>Original Dimensions:</strong> ${img.width} × ${img.height} pixels</div>
                                            <div class="info-item"><strong>File Size:</strong> ${fileSizeMB} MB</div>
                                            <div class="info-item"><strong>Display Height:</strong> 45px (scaled automatically)</div>
                                        `;

                                        // Show warning if dimensions are too large
                                        const warningDiv = previewContainer.querySelector('.size-warning');
                                        if (img.width > 500 || img.height > 500) {
                                            warningDiv.innerHTML = `<strong>Note:</strong> This image is quite large (${img.width} × ${img.height}px). Consider resizing it to around 100-200px height for better performance.`;
                                            warningDiv.style.display = 'block';
                                        } else {
                                            warningDiv.style.display = 'none';
                                        }

                                        previewContainer.style.display = 'block';
                                    };
                                    img.src = event.target.result;
                                };
                                reader.readAsDataURL(file);
                            });
                        });
                    </script>
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
