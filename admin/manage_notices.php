<?php
require_once __DIR__ . '/../db_init.php';

$base_path = '../';

// Auth Guard
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$msg = '';
$error = '';

// Handle Create Notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_create_notice'])) {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $cat_option = trim($_POST['category_option'] ?? 'General');
    $custom_cat = trim($_POST['custom_category'] ?? '');
    $category = ($cat_option === 'Other' && !empty($custom_cat)) ? $custom_cat : $cat_option;

    $date_bs = trim($_POST['published_date_bs'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
    $file_path = '';

    // Handle File Upload
    if (isset($_FILES['notice_file']) && $_FILES['notice_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['notice_file']['tmp_name'];
        $fileName = $_FILES['notice_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = __DIR__ . '/../uploads/notices/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $newFileName = 'notice-' . time() . '-' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $file_path = 'uploads/notices/' . $newFileName;
            } else {
                $error = "Error saving uploaded circular file.";
            }
        } else {
            $error = "Invalid file type! Only PDF, JPG, PNG files permitted.";
        }
    }

    if (empty($error) && !empty($title) && !empty($date_bs) && !empty($description)) {
        $stmt = $pdo->prepare("
            INSERT INTO notices (title, category, published_date_bs, description, file_path, is_pinned)
            VALUES (:title, :category, :published_date_bs, :description, :file_path, :is_pinned)
        ");
        $stmt->execute([
            ':title' => $title,
            ':category' => $category,
            ':published_date_bs' => $date_bs,
            ':description' => $description,
            ':file_path' => $file_path,
            ':is_pinned' => $is_pinned
        ]);
        $msg = "Notice circular <strong>" . htmlspecialchars($title) . "</strong> published successfully!";
    } elseif (empty($error)) {
        $error = "Please fill in all required fields.";
    }
}

// Handle Toggle Pin Action via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_toggle_pin'])) {
    verify_csrf();
    $notice_id = (int)$_POST['notice_id'];
    $stmt = $pdo->prepare("UPDATE notices SET is_pinned = NOT is_pinned WHERE id = :id");
    $stmt->execute([':id' => $notice_id]);
    $msg = "Notice pinned status toggled successfully!";
}

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete_notice'])) {
    verify_csrf();
    $notice_id = (int)$_POST['notice_id'];
    $pdo->prepare("DELETE FROM notices WHERE id = :id")->execute([':id' => $notice_id]);
    $msg = "Notice circular deleted successfully.";
}

// Fetch Notices
$notices = $pdo->query("SELECT * FROM notices ORDER BY is_pinned DESC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice Circulars Manager - Apex Administration Panel</title>
    <meta name="theme-color" content="#091b33">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
    <link rel="icon" type="image/png" href="<?= $base_path ?>assets/icons/icon-192.png">
    <style>
        .admin-nav { background-color: var(--primary-navy); color: var(--text-white); padding: 1rem 0; }
        .admin-subnav { background-color: var(--secondary-navy); color: var(--text-white); padding: 0.75rem 0; }
        .admin-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; }
        @media (max-width: 900px) { .admin-grid { grid-template-columns: 1fr; } }
        .radio-group { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.35rem; }
        .radio-label { font-size: 0.875rem; font-weight: 600; color: var(--primary-navy); display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer; }
    </style>
</head>
<body class="admin-body">

    <!-- Admin Nav -->
    <nav class="admin-nav">
        <div class="container">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <img src="<?= $base_path ?>assets/images/logo.png" alt="Apex Logo" style="width: 44px; height: 44px;">
                    <div>
                        <span style="font-size: 1.1rem; font-weight: 800; color: #ffffff;">Notice Circulars Manager</span>
                        <div style="font-size: 0.8rem; color: #f1f5f9;">Apex Executive Panel</div>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <a href="dashboard.php" class="btn btn-outline btn-sm" style="color: #ffffff; border-color: rgba(255,255,255,0.4);">← Dashboard</a>
                    <a href="logout.php" class="btn btn-sm" style="background-color: #ef4444; color: #ffffff;">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Subnav -->
    <div class="admin-subnav">
        <div class="container">
            <div style="display: flex; gap: 1.75rem; font-size: 0.9375rem; font-weight: 700; flex-wrap: wrap;">
                <a href="dashboard.php" style="color: #ffffff;">📊 Control Dashboard</a>
                <a href="manage_notices.php" style="color: #38bdf8; text-decoration: underline;">📢 Notice Circulars Manager</a>
                <a href="manage_events.php" style="color: #ffffff;">🖼️ Events & Gallery Manager</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="section" style="padding-top: 2rem;">
        <div class="container">

            <?php if (!empty($msg)): ?>
                <div style="background-color: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.95rem;">
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.95rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="admin-grid">

                <!-- Publish Form -->
                <div style="background-color: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); padding: 1.75rem; box-shadow: var(--shadow-sm); height: fit-content;">
                    <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--primary-navy); margin-bottom: 1.25rem;">Publish New Circular</h2>
                    
                    <form action="manage_notices.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.2rem;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action_create_notice" value="1">

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;" for="title">Notice Title *</label>
                            <input type="text" name="title" id="title" class="form-control" style="width: 100%; padding: 0.7rem; border-radius: 6px; border: 1px solid #cbd5e1; outline: none;" placeholder="e.g. Entrance Assessment Schedule" required>
                        </div>

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;">Select Category *</label>
                            <div class="radio-group">
                                <label class="radio-label"><input type="radio" name="category_option" value="Academic" checked onclick="toggleCustomCat(false)"> Academic</label>
                                <label class="radio-label"><input type="radio" name="category_option" value="Examinations" onclick="toggleCustomCat(false)"> Examinations</label>
                                <label class="radio-label"><input type="radio" name="category_option" value="Holidays" onclick="toggleCustomCat(false)"> Holidays</label>
                                <label class="radio-label"><input type="radio" name="category_option" value="General" onclick="toggleCustomCat(false)"> General</label>
                                <label class="radio-label"><input type="radio" name="category_option" value="Other" id="radioOther" onclick="toggleCustomCat(true)"> Other (Custom)</label>
                            </div>
                            <input type="text" name="custom_category" id="custom_category" placeholder="Enter custom category name..." style="display: none; width: 100%; margin-top: 0.6rem; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.9rem;">
                        </div>

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;" for="published_date_bs">Published Date (B.S.) *</label>
                            <input type="text" name="published_date_bs" id="published_date_bs" style="width: 100%; padding: 0.7rem; border-radius: 6px; border: 1px solid #cbd5e1; outline: none;" value="2083-05-10" required>
                        </div>

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;" for="description">Detailed Circular Description *</label>
                            <textarea name="description" id="description" rows="4" style="width: 100%; padding: 0.7rem; border-radius: 6px; border: 1px solid #cbd5e1; outline: none;" placeholder="Write official notice description..." required></textarea>
                        </div>

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;" for="notice_file">Attach Document / Image (PDF, JPG, PNG)</label>
                            <input type="file" name="notice_file" id="notice_file" style="width: 100%; font-size: 0.85rem;" accept=".pdf,.jpg,.jpeg,.png,.webp">
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.25rem;">
                            <label class="switch-toggle">
                                <input type="checkbox" name="is_pinned" value="1">
                                <span class="slider-round"></span>
                            </label>
                            <span style="font-size: 0.875rem; font-weight: 700; color: var(--primary-navy);">Pin Notice to Top of Public Bulletin</span>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; height: 44px; margin-top: 0.5rem;">
                            Publish Circular →
                        </button>
                    </form>
                </div>

                <!-- Notices Table -->
                <div style="background-color: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); padding: 1.75rem; box-shadow: var(--shadow-sm);">
                    <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--primary-navy); margin-bottom: 1.25rem;">Published Notices List</h2>

                    <div class="table-responsive">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                            <thead>
                                <tr style="background-color: var(--primary-navy); color: #ffffff; text-align: left;">
                                    <th style="padding: 0.75rem;">Cat</th>
                                    <th style="padding: 0.75rem;">Title</th>
                                    <th style="padding: 0.75rem;">Date B.S.</th>
                                    <th style="padding: 0.75rem;">Pinned Switch</th>
                                    <th style="padding: 0.75rem;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notices as $n): ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 0.75rem;"><span class="notice-cat-tag <?= htmlspecialchars($n['category']) ?>"><?= htmlspecialchars($n['category']) ?></span></td>
                                        <td style="padding: 0.75rem;"><strong><?= htmlspecialchars($n['title']) ?></strong></td>
                                        <td style="padding: 0.75rem;"><?= htmlspecialchars($n['published_date_bs']) ?></td>
                                        <td style="padding: 0.75rem;">
                                            <form action="manage_notices.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="action_toggle_pin" value="1">
                                                <input type="hidden" name="notice_id" value="<?= $n['id'] ?>">
                                                <label class="switch-toggle" style="vertical-align: middle;">
                                                    <input type="checkbox" onchange="this.form.submit()" <?= $n['is_pinned'] ? 'checked' : '' ?>>
                                                    <span class="slider-round"></span>
                                                </label>
                                            </form>
                                        </td>
                                        <td style="padding: 0.75rem;">
                                            <form action="manage_notices.php" method="POST" onsubmit="return confirm('Delete this notice?');" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="action_delete_notice" value="1">
                                                <input type="hidden" name="notice_id" value="<?= $n['id'] ?>">
                                                <button type="submit" class="btn btn-sm" style="background-color: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; font-size: 0.75rem;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <script>
        function toggleCustomCat(isOther) {
            const input = document.getElementById('custom_category');
            if (input) {
                input.style.display = isOther ? 'block' : 'none';
                if (isOther) input.focus();
            }
        }
    </script>
    <!-- Admin Floating Mobile Dock -->
    <div class="admin-mobile-dock">
        <ul class="admin-dock-items">
            <li class="admin-dock-item">
                <a href="dashboard.php">
                    <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="admin-dock-item">
                <a href="manage_notices.php" class="active">
                    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 11H7V9h2v2zm4 0h-2V9h2v2zm4 0h-2V9h2v2z"/></svg>
                    <span>Notices</span>
                </a>
            </li>
            <li class="admin-dock-item">
                <a href="manage_events.php">
                    <svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                    <span>Events</span>
                </a>
            </li>
            <li class="admin-dock-item">
                <a href="<?= $base_path ?>index.php" target="_blank">
                    <svg viewBox="0 0 24 24"><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                    <span>Public Site</span>
                </a>
            </li>
        </ul>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js').catch(err => console.log('Admin SW Registration:', err));
            });
        }
    </script>
</body>
</html>
