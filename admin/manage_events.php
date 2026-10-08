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

// Handle Create Event with Multiple Photos Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_create_event'])) {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $cat_option = trim($_POST['category_option'] ?? 'Academic Fairs');
    $custom_cat = trim($_POST['custom_category'] ?? '');
    $category = ($cat_option === 'Other' && !empty($custom_cat)) ? $custom_cat : $cat_option;
    $date_bs = trim($_POST['event_date_bs'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $image_paths = ['assets/images/events/science_fair.jpg']; // Fallback

    // Handle Multiple Files Upload
    if (isset($_FILES['event_images']) && !empty($_FILES['event_images']['name'][0])) {
        $uploadedPaths = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $uploadDir = __DIR__ . '/../uploads/events/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($_FILES['event_images']['name'] as $key => $fileName) {
            if ($_FILES['event_images']['error'][$key] === UPLOAD_ERR_OK) {
                $tmpPath = $_FILES['event_images']['tmp_name'][$key];
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (in_array($ext, $allowedExtensions)) {
                    $newFileName = 'event-' . time() . '-' . $key . '-' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);
                    $destPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($tmpPath, $destPath)) {
                        $uploadedPaths[] = 'uploads/events/' . $newFileName;
                    }
                }
            }
        }

        if (!empty($uploadedPaths)) {
            $image_paths = $uploadedPaths;
        }
    }

    if (empty($error) && !empty($title) && !empty($date_bs) && !empty($description)) {
        $stmt = $pdo->prepare("
            INSERT INTO events (title, category, event_date_bs, description, image_paths)
            VALUES (:title, :category, :event_date_bs, :description, :image_paths)
        ");
        $stmt->execute([
            ':title' => $title,
            ':category' => $category,
            ':event_date_bs' => $date_bs,
            ':description' => $description,
            ':image_paths' => json_encode($image_paths)
        ]);
        $msg = "Campus event <strong>" . htmlspecialchars($title) . "</strong> with <strong>" . count($image_paths) . "</strong> photo(s) published successfully!";
    } elseif (empty($error)) {
        $error = "Please fill in all required event fields.";
    }
}

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete_event'])) {
    verify_csrf();
    $event_id = (int)$_POST['event_id'];
    $pdo->prepare("DELETE FROM events WHERE id = :id")->execute([':id' => $event_id]);
    $msg = "Event gallery record deleted successfully.";
}

// Fetch Events
$events = $pdo->query("SELECT * FROM events ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events & Gallery Manager - Apex Administration Panel</title>
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
    <link rel="icon" type="image/png" href="<?= $base_path ?>assets/icons/icon-192.png">
    <style>
        .admin-nav { background-color: var(--primary-navy); color: var(--text-white); padding: 1rem 0; }
        .admin-subnav { background-color: var(--secondary-navy); color: var(--text-white); padding: 0.75rem 0; }
        .admin-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; }
        @media (max-width: 900px) { .admin-grid { grid-template-columns: 1fr; } }
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
                        <span style="font-size: 1.1rem; font-weight: 800; color: #ffffff;">Events & Multi-Photo Gallery Manager</span>
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
                <a href="manage_notices.php" style="color: #ffffff;">📢 Notice Circulars Manager</a>
                <a href="manage_events.php" style="color: #38bdf8; text-decoration: underline;">🖼️ Events & Gallery Manager</a>
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

                <!-- Upload Form Card -->
                <div style="background-color: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); padding: 1.75rem; box-shadow: var(--shadow-sm); height: fit-content;">
                    <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--primary-navy); margin-bottom: 1.25rem;">Upload Event Photos</h2>
                    
                    <form action="manage_events.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.2rem;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action_create_event" value="1">

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;" for="title">Event Title *</label>
                            <input type="text" name="title" id="title" class="form-control" style="width: 100%; padding: 0.7rem; border-radius: 6px; border: 1px solid #cbd5e1; outline: none;" placeholder="e.g. Annual Inter-House Science Fair" required>
                        </div>

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;">Select Category *</label>
                            <div class="radio-group">
                                <label class="radio-label"><input type="radio" name="category_option" value="Academic Fairs" checked onclick="toggleCustomCat(false)"> Academic Fairs</label>
                                <label class="radio-label"><input type="radio" name="category_option" value="Sports Meet" onclick="toggleCustomCat(false)"> Sports Meet</label>
                                <label class="radio-label"><input type="radio" name="category_option" value="Cultural Programs" onclick="toggleCustomCat(false)"> Cultural Programs</label>
                                <label class="radio-label"><input type="radio" name="category_option" value="Exhibitions" onclick="toggleCustomCat(false)"> Exhibitions</label>
                                <label class="radio-label"><input type="radio" name="category_option" value="Other" id="radioOther" onclick="toggleCustomCat(true)"> Other (Custom)</label>
                            </div>
                            <input type="text" name="custom_category" id="custom_category" placeholder="Enter custom category name..." style="display: none; width: 100%; margin-top: 0.6rem; padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.9rem;">
                        </div>

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;" for="event_date_bs">Date (B.S.) *</label>
                            <input type="text" name="event_date_bs" id="event_date_bs" style="width: 100%; padding: 0.7rem; border-radius: 6px; border: 1px solid #cbd5e1; outline: none;" value="2083-05-15" required>
                        </div>

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;" for="description">Summary / Highlights *</label>
                            <textarea name="description" id="description" rows="4" style="width: 100%; padding: 0.7rem; border-radius: 6px; border: 1px solid #cbd5e1; outline: none;" placeholder="Write event summary or highlights..." required></textarea>
                        </div>

                        <div>
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;" for="event_images">Upload Multiple Event Photos (JPG, PNG)</label>
                            <input type="file" name="event_images[]" id="event_images" style="width: 100%; font-size: 0.85rem;" accept=".jpg,.jpeg,.png,.webp" multiple>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.25rem;">💡 You can select multiple photo files simultaneously!</span>
                        </div>

                        <button type="submit" class="btn btn-gold" style="width: 100%; height: 44px; margin-top: 0.5rem;">
                            Publish Gallery Event →
                        </button>
                    </form>
                </div>

                <!-- Events Table List -->
                <div style="background-color: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); padding: 1.75rem; box-shadow: var(--shadow-sm);">
                    <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--primary-navy); margin-bottom: 1.25rem;">Published Events Gallery</h2>

                    <div class="table-responsive">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                            <thead>
                                <tr style="background-color: var(--primary-navy); color: #ffffff; text-align: left;">
                                    <th style="padding: 0.75rem;">Photos</th>
                                    <th style="padding: 0.75rem;">Category</th>
                                    <th style="padding: 0.75rem;">Title</th>
                                    <th style="padding: 0.75rem;">Date B.S.</th>
                                    <th style="padding: 0.75rem;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $ev): ?>
                                    <?php 
                                        $imgs = json_decode($ev['image_paths'], true) ?? [$ev['image_paths']];
                                    ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 0.75rem;">
                                            <div style="display: flex; gap: 0.25rem; align-items: center;">
                                                <img src="<?= $base_path ?><?= htmlspecialchars($imgs[0]) ?>" alt="Thumb" style="width: 48px; height: 36px; object-fit: cover; border-radius: 4px;">
                                                <?php if (count($imgs) > 1): ?>
                                                    <span style="font-size: 0.7rem; background-color: var(--accent-gold); color: #fff; padding: 0.1rem 0.35rem; border-radius: 4px; font-weight: 800;">+<?= count($imgs) - 1 ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="padding: 0.75rem;"><span class="category-pill" style="position: static; font-size: 0.7rem; background-color: #0b1e38; color: #fff;"><?= htmlspecialchars($ev['category']) ?></span></td>
                                        <td style="padding: 0.75rem;"><strong><?= htmlspecialchars($ev['title']) ?></strong></td>
                                        <td style="padding: 0.75rem;"><?= htmlspecialchars($ev['event_date_bs']) ?></td>
                                        <td style="padding: 0.75rem;">
                                            <form action="manage_events.php" method="POST" onsubmit="return confirm('Delete event <?= htmlspecialchars($ev['title']) ?>?');" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="action_delete_event" value="1">
                                                <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">
                                                <button type="submit" class="btn btn-sm" style="background-color: #fee2e2; color: #991b1b; padding: 0.2rem 0.5rem; font-size: 0.75rem;">Delete</button>
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
                <a href="manage_notices.php">
                    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 11H7V9h2v2zm4 0h-2V9h2v2zm4 0h-2V9h2v2z"/></svg>
                    <span>Notices</span>
                </a>
            </li>
            <li class="admin-dock-item">
                <a href="manage_events.php" class="active">
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

</body>
</html>
