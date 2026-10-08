<?php
require_once __DIR__ . '/../db_init.php';

$base_path = '../';

// Auth Guard
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$msg = '';

// Handle Admissions Open/Closed Toggle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_toggle_admissions'])) {
    verify_csrf();
    $raw_val = $_POST['admissions_open'] ?? '0';
    $new_status = ($raw_val === '1') ? '1' : '0';
    try {
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO settings (key_name, key_value) VALUES ('admissions_open', :val)");
        $stmt->execute([':val' => $new_status]);
        $msg = "Admissions banner status updated to: <strong>" . ($new_status === '1' ? 'OPEN (Banner Visible)' : 'CLOSED (Banner Hidden)') . "</strong>";
    } catch (PDOException $e) {
        $msg = "<span style='color: #991b1b;'>Database Update Warning: " . htmlspecialchars($e->getMessage()) . "</span>";
    }
}

$admissions_open = get_setting('admissions_open', '1');
$totalNotices = $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();
$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();

$dbFile = __DIR__ . '/../database/school.sqlite';
$dbSize = file_exists($dbFile) ? round(filesize($dbFile) / 1024, 2) . ' KB' : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Control Dashboard - Apex Model Secondary School</title>
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
    <link rel="icon" type="image/png" href="<?= $base_path ?>assets/icons/icon-192.png">
    <style>
        .admin-header-nav {
            background-color: var(--primary-navy);
            color: var(--text-white);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }
        .admin-subnav {
            background-color: var(--secondary-navy);
            color: var(--text-white);
            padding: 0.75rem 0;
        }
        .admin-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .admin-card-stat {
            background-color: var(--surface-white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-subtle);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        .admin-stat-num {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary-navy);
            margin-top: 0.35rem;
            line-height: 1;
        }
        @media (max-width: 900px) {
            .admin-grid-4 { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body class="admin-body">

    <!-- Admin Header -->
    <div class="admin-header-nav">
        <div class="container">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <img src="<?= $base_path ?>assets/images/logo.png" alt="Apex Logo" style="width: 44px; height: 44px;">
                    <div>
                        <div style="font-size: 1.1rem; font-weight: 800; color: #ffffff;">Apex Executive Administration Panel</div>
                        <div style="font-size: 0.8rem; color: #f1f5f9;">User: <?= htmlspecialchars($_SESSION['admin_full_name']) ?></div>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <a href="<?= $base_path ?>index.php" target="_blank" class="btn btn-outline btn-sm" style="color: #ffffff; border-color: rgba(255,255,255,0.4);">View Public Site ↗</a>
                    <a href="logout.php" class="btn btn-sm" style="background-color: #ef4444; color: #ffffff;">Logout Session</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Navigation Links -->
    <div class="admin-subnav">
        <div class="container">
            <div style="display: flex; gap: 1.75rem; font-size: 0.9375rem; font-weight: 700; flex-wrap: wrap;">
                <a href="dashboard.php" style="color: #38bdf8; text-decoration: underline;">📊 Control Dashboard</a>
                <a href="manage_notices.php" style="color: #ffffff;">📢 Notice Circulars Manager</a>
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

            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--primary-navy);">System Overview & Admissions Controls</h1>
                <p style="color: var(--text-body); font-size: 0.95rem;" class="prose-constrained-sm">Control public admissions banner status, publish official notice circulars, and manage campus event photo galleries.</p>
            </div>

            <!-- Stats Grid -->
            <div class="admin-grid-4">
                <div class="admin-card-stat">
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Admissions Banner</div>
                    <div class="admin-stat-num" style="font-size: 1.6rem; color: <?= $admissions_open === '1' ? '#166534' : '#ef4444' ?>;">
                        <?= $admissions_open === '1' ? '🟢 OPEN' : '🔴 CLOSED' ?>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-body); margin-top: 0.4rem; font-weight: 600;">Public Hero Banner</div>
                </div>

                <div class="admin-card-stat">
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Published Notices</div>
                    <div class="admin-stat-num"><?= $totalNotices ?></div>
                    <div style="font-size: 0.8rem; color: var(--accent-blue); margin-top: 0.4rem; font-weight: 600;">Circular Records</div>
                </div>

                <div class="admin-card-stat">
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Campus Events</div>
                    <div class="admin-stat-num"><?= $totalEvents ?></div>
                    <div style="font-size: 0.8rem; color: var(--accent-gold); margin-top: 0.4rem; font-weight: 600;">Gallery Photo Records</div>
                </div>

                <div class="admin-card-stat">
                    <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">SQLite Storage</div>
                    <div class="admin-stat-num" style="font-size: 1.6rem;"><?= $dbSize ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-body); margin-top: 0.4rem; font-weight: 600;">school.sqlite DB file</div>
                </div>
            </div>

            <!-- Admissions Toggle Form & Quick Actions -->
            <div class="admin-dashboard-grid">
                
                <div style="background-color: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); padding: 1.75rem; box-shadow: var(--shadow-sm);">
                    <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-navy); margin-bottom: 0.5rem;">Admissions Open/Closed Control</h2>
                    <p style="font-size: 0.9rem; color: var(--text-body); margin-bottom: 1.25rem;">Toggle whether the "Admissions Open for Academic Session 2083/84 B.S." banner is displayed on the homepage hero section.</p>

                    <form action="dashboard.php" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="action_toggle_admissions" value="1">

                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <label class="radio-label" style="padding: 0.85rem 1rem; cursor: pointer; justify-content: space-between; border-color: <?= $admissions_open === '1' ? 'var(--accent-gold)' : '#cbd5e1' ?>; background-color: <?= $admissions_open === '1' ? '#f0fdf4' : '#f8fafc' ?>;">
                                <span style="font-weight: 700; color: #166534;">🟢 Admissions OPEN (Show Banner on Homepage)</span>
                                <input type="radio" name="admissions_open" value="1" <?= $admissions_open === '1' ? 'checked' : '' ?>>
                            </label>
                            
                            <label class="radio-label" style="padding: 0.85rem 1rem; cursor: pointer; justify-content: space-between; border-color: <?= $admissions_open === '0' ? '#ef4444' : '#cbd5e1' ?>; background-color: <?= $admissions_open === '0' ? '#fef2f2' : '#f8fafc' ?>;">
                                <span style="font-weight: 700; color: #991b1b;">🔴 Admissions CLOSED (Hide Banner on Homepage)</span>
                                <input type="radio" name="admissions_open" value="0" <?= $admissions_open === '0' ? 'checked' : '' ?>>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-gold" style="width: 100%; height: 46px; font-weight: 800;">
                            Save Admissions Status →
                        </button>
                    </form>
                </div>

                <div style="background-color: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); padding: 1.75rem; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: center; gap: 1.25rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--primary-navy);">Quick Module Management</h2>
                    <p style="font-size: 0.9rem; color: var(--text-body);">Manage published circular notices or campus event photo galleries with instant update controls.</p>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="manage_notices.php" class="btn btn-primary" style="flex: 1; min-width: 200px; text-align: center;">
                            📢 Manage Notices
                        </a>
                        <a href="manage_events.php" class="btn btn-gold" style="flex: 1; min-width: 200px; text-align: center;">
                            🖼️ Manage Events & Gallery
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Admin Floating Mobile Dock -->
    <div class="admin-mobile-dock">
        <ul class="admin-dock-items">
            <li class="admin-dock-item">
                <a href="dashboard.php" class="active">
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

</body>
</html>
