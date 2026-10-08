<?php
require_once __DIR__ . '/db_init.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Page Not Found - Apex Model Secondary School</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/icons/icon-192.png">
    <style>
        body {
            background-color: var(--body-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .error-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 1.25rem;
        }
        .error-card-body {
            background-color: var(--surface-white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-subtle);
            box-shadow: var(--shadow-lg);
            padding: 3.5rem 2.5rem;
            max-width: 580px;
            width: 100%;
            text-align: center;
        }
        .error-code-num {
            font-size: 5rem;
            font-weight: 800;
            color: var(--primary-navy);
            line-height: 1;
            margin-bottom: 0.75rem;
        }
        .error-code-num span { color: var(--accent-gold); }
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
                        <span class="brand-subtitle">Academic Portal</span>
                    </div>
                </a>
                <nav>
                    <ul class="main-nav-list">
                        <li><a href="index.php" class="main-nav-link">Home</a></li>
                        <li><a href="about.php" class="main-nav-link">About Us</a></li>
                        <li><a href="events.php" class="main-nav-link">Events & Life</a></li>
                        <li><a href="notices.php" class="main-nav-link">Notices</a></li>
                        <li><a href="contact.php" class="main-nav-link">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- 404 Error Body -->
    <div class="error-wrapper">
        <div class="error-card-body">
            <img src="assets/images/logo.png" alt="Apex Seal" style="width: 72px; height: 72px; margin: 0 auto 1.25rem auto;">
            <div class="error-code-num">4<span>0</span>4</div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--primary-navy); margin-bottom: 0.75rem;">Page Not Found</h1>
            <p style="color: var(--text-body); font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;" class="prose-constrained-sm">
                The requested page or resource could not be found on the Apex Model Secondary School server. It may have been moved or is temporarily unavailable.
            </p>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="index.php" class="btn btn-primary">
                    ← Return to Homepage
                </a>
                <a href="events.php" class="btn btn-gold">
                    View Campus Events
                </a>
                <a href="notices.php" class="btn btn-outline">
                    View Notices Archive
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom" style="border-top: none; padding-top: 0;">
                <div>© <?= date('Y') ?> Apex Model Secondary School. All Rights Reserved.</div>
                <div><a href="index.php" style="color: var(--accent-blue);">Main Website</a></div>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
