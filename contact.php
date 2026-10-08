<?php
require_once __DIR__ . '/db_init.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $level = trim($_POST['academic_level'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($phone) && !empty($message)) {
        $success_msg = "Thank you, <strong>" . htmlspecialchars($name) . "</strong>. Your admissions inquiry for <em>" . htmlspecialchars($level) . "</em> has been received. Our administration office will contact you shortly.";
    } else {
        $error_msg = "Please fill in all required fields (Full Name, Contact Phone, and Message).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact & Admissions Desk - Apex Model Secondary School</title>
    <meta name="description" content="Contact Apex Model Secondary School admissions office. Enquire about admissions from Nursery to Grade 12 in Koshi Province, Nepal.">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0b1e38">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/icons/icon-192.png">
    <style>
        .contact-grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2.5rem;
        }
        @media (max-width: 900px) {
            .contact-grid-layout { grid-template-columns: 1fr; }
        }
        .contact-card-wrap {
            background-color: var(--surface-white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-subtle);
            padding: 2.25rem;
            box-shadow: var(--shadow-sm);
        }
        .form-label {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--primary-navy);
            display: block;
            margin-bottom: 0.35rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 0.9rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-subtle);
            font-size: 0.9375rem;
            outline: none;
            background-color: #fafafa;
            transition: border-color var(--transition-fast), background-color var(--transition-fast);
        }
        .form-control:focus {
            border-color: var(--accent-blue);
            background-color: #ffffff;
        }
        .info-item {
            padding-bottom: 1.2rem;
            border-bottom: 1px solid var(--border-subtle);
        }
        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
    </style>
</head>
<body>

    <!-- Header Navigation (Clean, Single Row) -->
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="brand-block">
                    <img src="assets/images/logo.png" alt="Apex Logo" class="brand-crest">
                    <div>
                        <div class="brand-title">Apex Model Secondary School</div>
                        <span class="brand-subtitle">Admissions Desk & General Inquiries</span>
                    </div>
                </a>
                <nav>
                    <ul class="main-nav-list">
                        <li><a href="index.php" class="main-nav-link">Home</a></li>
                        <li><a href="about.php" class="main-nav-link">About Us</a></li>
                        <li><a href="events.php" class="main-nav-link">Events & Life</a></li>
                        <li><a href="notices.php" class="main-nav-link">Notices</a></li>
                        <li><a href="contact.php" class="main-nav-link active">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Page Header Banner -->
    <section class="section section-alt" style="padding: 3.5rem 0 2rem 0;">
        <div class="container">
            <div style="text-align: center;">
                <span class="section-tag">Admissions Office</span>
                <h1 class="section-h2" style="margin-top: 0.25rem;">Contact & Admissions Desk</h1>
                <p class="section-desc prose-constrained">Reach out to our administration desk for inquiries regarding admissions, campus visits, or academic programs.</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="section">
        <div class="container">
            <div class="contact-grid-layout">
                
                <!-- Inquiry Form (Left Column) -->
                <div class="contact-card-wrap">
                    <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--primary-navy); margin-bottom: 0.4rem;">Send an Inquiry</h2>
                    <p style="font-size: 0.875rem; color: var(--text-body); margin-bottom: 1.5rem;">Submit your details below and our admissions desk will get back to you.</p>

                    <?php if (!empty($success_msg)): ?>
                        <div style="background-color: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.9rem;">
                            <?= $success_msg ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error_msg)): ?>
                        <div style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.9rem;">
                            <?= htmlspecialchars($error_msg) ?>
                        </div>
                    <?php endif; ?>

                    <form action="contact.php" method="POST" style="display: flex; flex-direction: column; gap: 1.1rem;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <div>
                            <label class="form-label" for="full_name">Full Name *</label>
                            <input type="text" name="full_name" id="full_name" class="form-control" placeholder="e.g. Ramesh Kumar Adhikari" required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="admin-grid-2col">
                            <div>
                                <label class="form-label" for="phone">Phone / Mobile *</label>
                                <input type="tel" name="phone" id="phone" class="form-control" placeholder="+977-9800000000" required>
                            </div>
                            <div>
                                <label class="form-label" for="email">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com">
                            </div>
                        </div>

                        <div>
                            <label class="form-label" for="academic_level">Seeking Admission Level</label>
                            <select name="academic_level" id="academic_level" class="form-control">
                                <option value="Pre-Primary & Primary (PG - Grade 5)">Pre-Primary & Primary (PG - Grade 5)</option>
                                <option value="Lower Secondary (Grades 6 - 8)">Lower Secondary (Grades 6 - 8)</option>
                                <option value="Secondary SEE (Grades 9 & 10)">Secondary SEE (Grades 9 & 10)</option>
                                <option value="Senior Secondary 10+2 Science">Senior Secondary 10+2 Science</option>
                                <option value="Senior Secondary 10+2 Management">Senior Secondary 10+2 Management</option>
                                <option value="Senior Secondary 10+2 Humanities">Senior Secondary 10+2 Humanities</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label" for="message">Inquiry Message *</label>
                            <textarea name="message" id="message" rows="4" class="form-control" placeholder="Write your inquiry or question here..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; height: 44px; margin-top: 0.25rem;">
                            Submit Inquiry →
                        </button>
                    </form>
                </div>

                <!-- Campus Details & Map (Right Column) -->
                <div class="contact-card-wrap" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h2 style="font-size: 1.3rem; font-weight: 800; color: var(--primary-navy); margin-bottom: 1.25rem;">Campus Information</h2>

                        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div class="info-item">
                                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Address & Location</span>
                                <div style="font-size: 0.95rem; font-weight: 700; color: var(--primary-navy); margin-top: 0.2rem;">Apex Model Secondary School</div>
                                <div style="font-size: 0.875rem; color: var(--text-body);">Ward No. 3, Main Highway Corridor, Koshi Province, Nepal</div>
                            </div>

                            <div class="info-item">
                                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Telephone & Email</span>
                                <div style="font-size: 0.875rem; color: var(--text-body); margin-top: 0.2rem;">Phone: <strong>+977-25-560000</strong></div>
                                <div style="font-size: 0.875rem; color: var(--text-body);">Email: <strong>info@apexschool.edu.np</strong></div>
                            </div>

                            <div class="info-item">
                                <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Office Operating Hours</span>
                                <div style="font-size: 0.875rem; color: var(--text-body); margin-top: 0.2rem;">Sunday – Friday: <strong>8:00 AM – 4:00 PM (B.S.)</strong></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">Closed on Saturdays and official Nepalese public holidays.</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; height: 220px; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border-subtle);">
                        <iframe src="https://maps.google.com/maps?q=Sunsari,%20Koshi%20Province,%20Nepal&t=&z=13&ie=UTF8&iwloc=&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom" style="border-top: none; padding-top: 0;">
                <div>© <?= date('Y') ?> Apex Model Secondary School. Admissions & Campus Contacts.</div>
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
            <li class="dock-item"><a href="notices.php">
                <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                <span>Notices</span>
            </a></li>
        </ul>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
