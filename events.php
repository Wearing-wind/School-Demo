<?php
require_once __DIR__ . '/db_init.php';

// Fetch all events from database
$stmt = $pdo->query("SELECT * FROM events ORDER BY id DESC");
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Life & Events Gallery - Apex Model Secondary School</title>
    <meta name="description" content="Explore vibrant campus events, annual sports championships, STEM science exhibitions, and cultural functions at Apex Model Secondary School.">
    <meta name="theme-color" content="#0b1e38">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/icons/icon-192.png">
    <style>
        .search-bar-wrap {
            max-width: 600px;
            margin: 0 auto 3rem auto;
            position: relative;
        }
        .search-bar-input {
            width: 100%;
            padding: 0.85rem 1.25rem 0.85rem 2.85rem;
            border-radius: var(--radius-full);
            border: 1px solid var(--border-subtle);
            box-shadow: var(--shadow-sm);
            font-size: 1rem;
            outline: none;
            transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
        }
        .search-bar-input:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }
    </style>
</head>
<body>

    <!-- Floating Pill Navigation Header -->
    <header class="site-header">
        <div class="header-inner">
            <a href="index.php" class="brand-block">
                <img src="assets/images/logo.png" alt="Apex Logo" class="brand-crest">
                <div class="brand-text-block">
                    <div class="brand-title">Apex Model Secondary School</div>
                    <span class="brand-subtitle">Campus Life & Activities Gallery</span>
                </div>
            </a>
            <nav>
                <ul class="main-nav-list">
                    <li>
                        <a href="index.php" class="main-nav-link">
                            <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="about.php" class="main-nav-link">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                            <span>About Us</span>
                        </a>
                    </li>
                    <li>
                        <a href="events.php" class="main-nav-link active">
                            <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                            <span>Events & Life</span>
                        </a>
                    </li>
                    <li>
                        <a href="notices.php" class="main-nav-link">
                            <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                            <span>Notices</span>
                        </a>
                    </li>
                    <li>
                        <a href="contact.php" class="main-nav-link">
                            <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                            <span>Contact</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Page Header Hero Banner with Campus Event Background Image -->
    <section class="hero-section" style="padding: 4.5rem 0;">
        <div class="hero-video-bg" style="background-image: url('assets/images/events/science_fair.jpg'); background-size: cover; background-position: center; opacity: 0.65;"></div>
        <div class="hero-overlay" style="background: linear-gradient(180deg, rgba(11, 30, 56, 0.85) 0%, rgba(11, 30, 56, 0.7) 50%, rgba(11, 30, 56, 0.9) 100%);"></div>
        <div class="container">
            <div class="hero-content scroll-reveal" style="padding: 2rem 0; text-align: center;">
                <div class="hero-badge-pill">
                    📸 Campus Co-Curricular & Event Gallery
                </div>
                <h1 class="hero-h1" style="font-size: 2.85rem;">
                    Campus Life & Events Gallery
                </h1>
                <p class="hero-body-text prose-constrained" style="font-size: 1.15rem; margin-bottom: 0;">
                    A vibrant showcase of student talent, STEM science fairs, athletic championships, and annual cultural celebrations at Apex Model School.
                </p>
            </div>
        </div>
    </section>

    <!-- Events Grid Section -->
    <main class="section">
        <div class="container">

            <!-- Live Keyword Search Bar -->
            <div class="search-bar-wrap">
                <input type="text" id="eventSearchInput" class="search-bar-input" placeholder="Search event by name, keyword, or category (e.g. Science Fair, Sports)...">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            </div>

            <!-- Events Grid -->
            <div class="events-grid">
                <?php foreach ($events as $ev): ?>
                    <?php 
                        $imgs = json_decode($ev['image_paths'], true) ?? [$ev['image_paths']];
                        $imgCount = count($imgs);
                    ?>
                    <div class="event-card event-card-item" data-category="<?= htmlspecialchars($ev['category']) ?>" onclick='showEventDetails(<?= json_encode($ev) ?>)'>
                        <div class="event-img-wrap">
                            <span class="category-pill"><?= htmlspecialchars($ev['category']) ?></span>
                            <?php if ($imgCount > 1): ?>
                                <span class="multi-photo-badge">📷 <?= $imgCount ?> Photos</span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($imgs[0]) ?>" alt="<?= htmlspecialchars($ev['title']) ?>">
                        </div>
                        <div class="event-body">
                            <div class="event-date">📅 <?= htmlspecialchars($ev['event_date_bs']) ?> B.S.</div>
                            <h3><?= htmlspecialchars($ev['title']) ?></h3>
                            <p><?= htmlspecialchars(mb_strimwidth($ev['description'], 0, 110, "...")) ?></p>
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
                <div>© <?= date('Y') ?> Apex Model Secondary School. Campus Events Gallery.</div>
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
            <li class="dock-item"><a href="events.php" class="active">
                <svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                <span>Events</span>
            </a></li>
            <li class="dock-item"><a href="notices.php">
                <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                <span>Notices</span>
            </a></li>
        </ul>
    </div>

    <!-- Centered Animated Modal Window Container -->
    <div class="modal-backdrop" id="modalBackdrop" onclick="closeModal()"></div>
    <div class="modal-window" id="modalWindow">
        <div id="modalBody"></div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function showEventDetails(ev) {
            let images = [];
            try {
                images = JSON.parse(ev.image_paths);
            } catch (e) {
                images = [ev.image_paths];
            }

            let mainImgSrc = images[0] || 'assets/images/events/science_fair.jpg';
            
            let thumbsHtml = '';
            if (images.length > 1) {
                thumbsHtml = '<div class="modal-gallery-grid">';
                images.forEach((img, idx) => {
                    thumbsHtml += `
                        <img src="${img}" class="modal-gallery-thumb ${idx === 0 ? 'active' : ''}" onclick="switchModalImage('${img}', this)" alt="Thumbnail ${idx + 1}">
                    `;
                });
                thumbsHtml += '</div>';
            }

            const content = `
                <img id="mainModalImage" src="${mainImgSrc}" alt="${ev.title}" style="width: 100%; height: 280px; object-fit: cover; border-radius: 10px; margin-bottom: 0.85rem; box-shadow: var(--shadow-md);">
                ${thumbsHtml}
                <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1rem; margin-bottom: 0.75rem;">
                    <span class="category-pill" style="position: static; font-size: 0.75rem;">${ev.category}</span>
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--accent-gold);">📅 ${ev.event_date_bs} B.S.</span>
                </div>
                <p style="font-size: 0.95rem; color: var(--text-body); line-height: 1.65; margin-bottom: 1.5rem;">${ev.description}</p>
                <button onclick="closeModal()" class="btn btn-outline" style="width: 100%;">Close Gallery</button>
            `;
            openModal(ev.title, content);
        }

        function switchModalImage(src, thumbEl) {
            const mainImg = document.getElementById('mainModalImage');
            if (mainImg) {
                mainImg.src = src;
            }
            document.querySelectorAll('.modal-gallery-thumb').forEach(t => t.classList.remove('active'));
            if (thumbEl) {
                thumbEl.classList.add('active');
            }
        }
    </script>
</body>
</html>
