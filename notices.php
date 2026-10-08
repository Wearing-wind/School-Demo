<?php
require_once __DIR__ . '/db_init.php';

// Fetch notices ordered by pinned first, then newest
$stmt = $pdo->query("SELECT * FROM notices ORDER BY is_pinned DESC, id DESC");
$notices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Notices & Circulars - Apex Model Secondary School</title>
    <meta name="description" content="Official administrative circulars, exam routines, holiday announcements, and academic updates from Apex Model Secondary School.">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0b1e38">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/icons/icon-192.png">
    <style>
        .notice-filter-card {
            background-color: var(--surface-white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-subtle);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .filter-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .file-preview-thumb {
            width: 110px;
            height: 75px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-subtle);
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="brand-block">
                    <img src="assets/images/logo.png" alt="Apex Logo" class="brand-crest">
                    <div>
                        <div class="brand-title">Apex Model Secondary School</div>
                        <span class="brand-subtitle">Official Circulars & Notice Bulletin</span>
                    </div>
                </a>
                <nav>
                    <ul class="main-nav-list">
                        <li><a href="index.php" class="main-nav-link">Home</a></li>
                        <li><a href="about.php" class="main-nav-link">About Us</a></li>
                        <li><a href="events.php" class="main-nav-link">Events & Life</a></li>
                        <li><a href="notices.php" class="main-nav-link active">Notices</a></li>
                        <li><a href="contact.php" class="main-nav-link">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Page Header Hero Banner with Campus Background Image -->
    <section class="hero-section" style="padding: 4.5rem 0;">
        <div class="hero-video-bg" style="background-image: url('assets/images/facilities/computer_lab.jpg'); background-size: cover; background-position: center; opacity: 0.65;"></div>
        <div class="hero-overlay" style="background: linear-gradient(180deg, rgba(11, 30, 56, 0.85) 0%, rgba(11, 30, 56, 0.7) 50%, rgba(11, 30, 56, 0.9) 100%);"></div>
        <div class="container">
            <div class="hero-content scroll-reveal" style="padding: 2rem 0; text-align: center;">
                <div class="hero-badge-pill">
                    📢 Official Administrative Bulletin & Circulars
                </div>
                <h1 class="hero-h1" style="font-size: 2.85rem;">
                    Official Administrative Circulars
                </h1>
                <p class="hero-body-text prose-constrained" style="font-size: 1.15rem; margin-bottom: 0;">
                    Stay informed with official examination schedules, holiday circulars, admissions procedures, and school events.
                </p>
            </div>
        </div>
    </section>

    <!-- Main Notices Feed -->
    <main class="section">
        <div class="container">

            <!-- Filter Controls Bar -->
            <div class="notice-filter-card">
                <div class="filter-controls">
                    <div style="position: relative; min-width: 260px;">
                        <input type="text" id="noticeSearchInput" placeholder="Search notices by keyword..." style="width: 100%; padding: 0.6rem 0.85rem 0.6rem 2.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); outline: none; font-size: 0.9rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label for="noticeMonthSelect" style="font-size: 0.85rem; font-weight: 700; color: var(--primary-navy);">Filter by Month:</label>
                        <select id="noticeMonthSelect" style="padding: 0.6rem 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); font-size: 0.9rem; outline: none;">
                            <option value="">All Months</option>
                            <option value="-01-">Baisakh (Month 01)</option>
                            <option value="-02-">Jestha (Month 02)</option>
                            <option value="-03-">Asar (Month 03)</option>
                            <option value="-04-">Shrawan (Month 04)</option>
                            <option value="-05-">Bhadra (Month 05)</option>
                            <option value="-06-">Ashwin (Month 06)</option>
                        </select>
                    </div>
                </div>

                <div style="font-size: 0.825rem; font-weight: 600; color: var(--text-muted);">
                    Showing <?= count($notices) ?> Archive Circulars
                </div>
            </div>

            <!-- Notices Cards List -->
            <div class="notices-list">
                <?php foreach ($notices as $n): ?>
                    <?php 
                        $ext = !empty($n['file_path']) ? strtolower(pathinfo($n['file_path'], PATHINFO_EXTENSION)) : '';
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                    ?>
                    <div class="notice-item-card" data-date="<?= htmlspecialchars($n['published_date_bs']) ?>">
                        <div class="notice-item-main">
                            <div class="notice-meta-tags">
                                <span class="notice-cat-tag <?= htmlspecialchars($n['category']) ?>">
                                    <?= htmlspecialchars($n['category']) ?>
                                </span>
                                <?php if ($n['is_pinned']): ?>
                                    <span style="font-size: 0.725rem; font-weight: 700; background-color: #fef3c7; color: #92400e; padding: 0.15rem 0.5rem; border-radius: 4px;">📌 Pinned Notice</span>
                                <?php endif; ?>
                                <span style="font-size: 0.8125rem; color: var(--text-muted); font-weight: 600;">📅 Date: <?= htmlspecialchars($n['published_date_bs']) ?> B.S.</span>
                            </div>
                            <h3><?= htmlspecialchars($n['title']) ?></h3>
                            <p><?= htmlspecialchars($n['description']) ?></p>
                        </div>

                        <?php if ($isImage): ?>
                            <div style="flex-shrink: 0;" onclick='showNoticeModal(<?= json_encode($n) ?>)'>
                                <img src="<?= htmlspecialchars($n['file_path']) ?>" alt="Preview" class="file-preview-thumb">
                            </div>
                        <?php endif; ?>

                        <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end; justify-content: center; min-width: 160px;">
                            <button onclick='showNoticeModal(<?= json_encode($n) ?>)' class="btn btn-outline btn-sm" style="width: 100%;">
                                View Preview 👁️
                            </button>
                            <?php if (!empty($n['file_path'])): ?>
                                <a href="<?= htmlspecialchars($n['file_path']) ?>" download class="btn btn-gold btn-sm" style="width: 100%;">
                                    Download File 📄
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom" style="border-top: none; padding-top: 0;">
                <div>© <?= date('Y') ?> Apex Model Secondary School. Official Circulars Archive.</div>
                <div><a href="index.php" style="color: var(--accent-blue);">← Return to Main Website</a></div>
            </div>
        </div>
    </footer>

    <!-- Mobile Dock -->
    <div class="mobile-bottom-dock">
        <ul class="dock-items">
            <li class="dock-item"><a href="index.php">
                <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                <span>Home</span>
            </a></li>
            <li class="dock-item"><a href="about.php">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                <span>About</span>
            </a></li>
            <li class="dock-item"><a href="events.php">
                <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                <span>Events</span>
            </a></li>
            <li class="dock-item"><a href="notices.php" class="active">
                <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                <span>Notices</span>
            </a></li>
        </ul>
    </div>

    <!-- Centered Animated Modal Backdrop & Window -->
    <div class="modal-backdrop" id="modalBackdrop" onclick="closeModal()"></div>
    <div class="modal-window" id="modalWindow">
        <div id="modalBody"></div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function showNoticeModal(n) {
            let filePreviewHtml = '';
            if (n.file_path) {
                const ext = n.file_path.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                    filePreviewHtml = `<img src="${n.file_path}" alt="Notice Circular Preview" style="width: 100%; max-height: 320px; object-fit: contain; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #cbd5e1;">`;
                }
            }

            const content = `
                ${filePreviewHtml}
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.75rem;">
                    <span class="notice-cat-tag ${n.category}">${n.category}</span>
                    <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">📅 Date: ${n.published_date_bs} B.S.</span>
                </div>
                <p style="font-size: 1rem; color: var(--text-body); line-height: 1.65; margin-bottom: 1.5rem; background-color: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">${n.description}</p>
                <div style="display: flex; gap: 0.75rem;">
                    ${n.file_path ? `<a href="${n.file_path}" download class="btn btn-gold" style="flex: 1; text-align: center;">Download Attached Document</a>` : ''}
                    <button onclick="closeModal()" class="btn btn-outline" style="flex: 1;">Close Window</button>
                </div>
            `;
            openModal(n.title, content);
        }
    </script>
</body>
</html>
