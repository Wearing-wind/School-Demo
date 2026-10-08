<?php
require_once __DIR__ . '/db_init.php';

$admissions_open = get_setting('admissions_open', '1');

// Fetch latest 3 events for homepage
$eventsStmt = $pdo->query("SELECT * FROM events ORDER BY id DESC LIMIT 3");
$recentEvents = $eventsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apex Model Secondary School - Excellence in Academics, Leadership & Integrity</title>
    <meta name="description" content="Apex Model Secondary School established 2052 B.S. in Koshi Province, Nepal. Premier academic institution offering Early Childhood to Senior Secondary 10+2 programs.">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0b1e38">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/icons/icon-192.png">
</head>
<body>

    <!-- Single-Row Main Header (No navbar button, Clean & Interactive) -->
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="index.php" class="brand-block">
                    <img src="assets/images/logo.png" alt="Apex Model Secondary School Crest" class="brand-crest">
                    <div>
                        <div class="brand-title">Apex Model Secondary School</div>
                        <span class="brand-subtitle">Estd. 2052 B.S. (1995 A.D.) • Ward No. 3, Koshi Province</span>
                    </div>
                </a>

                <nav>
                    <ul class="main-nav-list">
                        <li><a href="index.php" class="main-nav-link active">Home</a></li>
                        <li><a href="about.php" class="main-nav-link">About Us</a></li>
                        <li><a href="events.php" class="main-nav-link">Events & Life</a></li>
                        <li><a href="notices.php" class="main-nav-link">Notices</a></li>
                        <li><a href="contact.php" class="main-nav-link">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Dynamic Hero Section -->
    <section class="hero-section">
        <video class="hero-video-bg" autoplay loop muted playsinline poster="assets/images/facilities/robotics_lab.jpg">
            <source src="assets/videos/hero-drone.mp4?v=<?= file_exists(__DIR__ . '/assets/videos/hero-drone.mp4') ? filemtime(__DIR__ . '/assets/videos/hero-drone.mp4') : time() ?>" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content scroll-reveal">
                <?php if ($admissions_open === '1'): ?>
                    <div class="hero-badge-pill">
                        ✨ Admissions Open for Academic Session 2083/84 B.S.
                    </div>
                <?php endif; ?>

                <h1 class="hero-h1">
                    Inspiring Academic Excellence,<br><span>Leadership & Character.</span>
                </h1>

                <p class="hero-body-text prose-constrained">
                    Nurturing young minds from Early Childhood through Grade 12 with forward-thinking STEM laboratories, comprehensive humanities, and dedicated character discipline in Koshi Province.
                </p>

                <div class="hero-cta-group">
                    <a href="events.php" class="btn btn-primary">
                        Explore Campus Life & Events →
                    </a>
                    <a href="contact.php" class="btn btn-gold">
                        Admissions Inquiry
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Rolling Metrics Counter Strip -->
    <div class="container">
        <div class="metrics-strip scroll-reveal">
            <div class="metrics-grid">
                <div class="metric-item">
                    <div class="metric-number"><span class="counter-val" data-target="30">0</span><span>+</span></div>
                    <div class="metric-label">Years of Academic Heritage</div>
                </div>
                <div class="metric-item">
                    <div class="metric-number"><span class="counter-val" data-target="1400">0</span><span>+</span></div>
                    <div class="metric-label">Active Enrolled Scholars</div>
                </div>
                <div class="metric-item">
                    <div class="metric-number"><span class="counter-val" data-target="100">0</span><span>%</span></div>
                    <div class="metric-label">Board Examination Success</div>
                </div>
                <div class="metric-item">
                    <div class="metric-number"><span class="counter-val" data-target="65">0</span><span>+</span></div>
                    <div class="metric-label">Dedicated Qualified Faculty</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Streams & Levels Section with Real Image Cards -->
    <section class="section scroll-reveal">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Academic Pathways</span>
                <h2 class="section-h2">Comprehensive Academic Programs</h2>
                <p class="section-desc prose-constrained">Structured curriculum approved by the Ministry of Education & National Examination Board (NEB) fostering intellectual curiosity at every age.</p>
            </div>

            <div class="streams-grid">
                <div class="stream-card">
                    <img src="assets/images/events/assembly.jpg" alt="Pre-Primary & Primary" class="stream-card-img">
                    <div class="stream-card-body">
                        <h3>Pre-Primary & Primary</h3>
                        <p>Playgroup to Grade 5 focusing on foundational literacy, numeracy, creative arts, and joyful inquiry-based learning environments.</p>
                        <span style="font-size: 0.8rem; font-weight: 800; color: var(--accent-blue);">Grades PG – 5</span>
                    </div>
                </div>

                <div class="stream-card">
                    <img src="assets/images/facilities/computer_lab.jpg" alt="Lower Secondary" class="stream-card-img">
                    <div class="stream-card-body">
                        <h3>Lower Secondary (BLE)</h3>
                        <p>Grades 6 to 8 building strong analytical skills in mathematics, environmental science, digital literacy, and languages.</p>
                        <span style="font-size: 0.8rem; font-weight: 800; color: var(--accent-gold);">Grades 6 – 8</span>
                    </div>
                </div>

                <div class="stream-card">
                    <img src="assets/images/facilities/robotics_lab.jpg" alt="Secondary SEE Prep" class="stream-card-img">
                    <div class="stream-card-body">
                        <h3>Secondary (SEE Prep)</h3>
                        <p>Grades 9 & 10 preparing scholars for Secondary Education Examination (SEE) with intensive laboratory experiments and tutorials.</p>
                        <span style="font-size: 0.8rem; font-weight: 800; color: #166534;">Grades 9 – 10</span>
                    </div>
                </div>

                <div class="stream-card">
                    <img src="assets/images/facilities/library.jpg" alt="Senior Secondary 10+2" class="stream-card-img">
                    <div class="stream-card-body">
                        <h3>Senior Secondary 10+2</h3>
                        <p>NEB affiliated streams in Science, Management, and Humanities equipping students for university admissions and career leadership.</p>
                        <span style="font-size: 0.8rem; font-weight: 800; color: #7e22ce;">Grades 11 – 12</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Facilities Showcase Section -->
    <section class="section section-alt scroll-reveal">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Campus Infrastructure</span>
                <h2 class="section-h2">World-Class Learning Facilities</h2>
                <p class="section-desc prose-constrained">State-of-the-art infrastructure designed to foster hands-on exploration, physical health, and technological mastery.</p>
            </div>

            <div class="facilities-grid">
                <div class="facility-card">
                    <div class="facility-img-wrap">
                        <img src="assets/images/facilities/robotics_lab.jpg" alt="Robotics & STEM Lab">
                    </div>
                    <div class="facility-body">
                        <h3>Advanced STEM & Robotics Lab</h3>
                        <p>Equipped with 3D printers, microcontroller kits, and robotic assembly stations empowering practical engineering mindset.</p>
                    </div>
                </div>

                <div class="facility-card">
                    <div class="facility-img-wrap">
                        <img src="assets/images/facilities/computer_lab.jpg" alt="Computer Laboratory">
                    </div>
                    <div class="facility-body">
                        <h3>High-Speed Computer Lab</h3>
                        <p>Fully networked workstations with high-speed fiber connectivity, programming IDEs, and multimedia software.</p>
                    </div>
                </div>

                <div class="facility-card">
                    <div class="facility-img-wrap">
                        <img src="assets/images/facilities/library.jpg" alt="Resource Center">
                    </div>
                    <div class="facility-body">
                        <h3>Knowledge Resource Center</h3>
                        <p>Comprehensive physical and digital library featuring over 10,000 academic titles, periodicals, and quiet study zones.</p>
                    </div>
                </div>

                <div class="facility-card">
                    <div class="facility-img-wrap">
                        <img src="assets/images/facilities/sports_ground.jpg" alt="Sports Grounds">
                    </div>
                    <div class="facility-body">
                        <h3>Sports Complex & Futsal Ground</h3>
                        <p>Dedicated basketball courts, badminton courts, track grounds, and futsal arena supporting house championships.</p>
                    </div>
                </div>

                <div class="facility-card">
                    <div class="facility-img-wrap">
                        <img src="assets/images/facilities/school_bus.jpg" alt="School Transport">
                    </div>
                    <div class="facility-body">
                        <h3>Safe Student Transport Fleets</h3>
                        <p>Modern bus fleet covering major transportation corridors across Koshi Province with trained safety attendants.</p>
                    </div>
                </div>

                <div class="facility-card">
                    <div class="facility-img-wrap">
                        <img src="assets/images/events/science_fair.jpg" alt="Science Laboratories">
                    </div>
                    <div class="facility-body">
                        <h3>Integrated Science Laboratories</h3>
                        <p>Individual Physics, Chemistry, and Biology laboratories enabling real-world scientific experimentation.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- School Leadership Vision Section (Redesigned with Prominent Photos) -->
    <section class="section scroll-reveal">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Leadership Vision</span>
                <h2 class="section-h2">Guided by Educational Pioneers</h2>
                <p class="section-desc prose-constrained">Meet the visionary leaders steering Apex Model Secondary School towards regional and national academic distinction.</p>
            </div>

            <div class="leadership-grid">
                <div class="leader-card">
                    <div class="leader-header-block">
                        <img src="assets/images/principal.jpg" alt="Prof. Dr. K. P. Adhikari" class="leader-avatar-lg">
                        <div>
                            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--primary-navy);">Prof. Dr. K. P. Adhikari</h3>
                            <p style="font-size: 0.85rem; color: var(--accent-gold); font-weight: 700; margin-top: 0.2rem;">Principal (Ph.D. in Education Administration)</p>
                            <span style="font-size: 0.75rem; background-color: #dbeafe; color: #1e40af; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 4px; display: inline-block; margin-top: 0.4rem;">30+ Years Leadership</span>
                        </div>
                    </div>
                    <div class="leader-quote">
                        "For three decades, Apex Model Secondary School has stood as a beacon of academic excellence and holistic student formation. Our mission is to inspire critical thinking, foster ethical leadership, and equip scholars with 21st-century competencies."
                    </div>
                </div>

                <div class="leader-card">
                    <div class="leader-header-block">
                        <img src="assets/images/director.jpg" alt="Mrs. Sunita Sharma" class="leader-avatar-lg">
                        <div>
                            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--primary-navy);">Mrs. Sunita Sharma</h3>
                            <p style="font-size: 0.85rem; color: var(--accent-gold); font-weight: 700; margin-top: 0.2rem;">Academic Director (M.Sc., 20+ Yrs Curriculum)</p>
                            <span style="font-size: 0.75rem; background-color: #dcfce7; color: #166534; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 4px; display: inline-block; margin-top: 0.4rem;">Curriculum Directorate</span>
                        </div>
                    </div>
                    <div class="leader-quote">
                        "Our forward-thinking curriculum integrates rigorous STEM inquiry, modern robotics laboratories, and rich co-curricular engagement, ensuring every student discovers their potential and leads with integrity."
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent School Life & Events Strip -->
    <section class="section section-alt scroll-reveal">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span class="section-tag">Campus Life</span>
                    <h2 class="section-h2" style="margin-bottom: 0;">Recent Happenings & Events</h2>
                </div>
                <a href="events.php" class="btn btn-outline btn-sm">View All Gallery Events →</a>
            </div>

            <div class="events-grid">
                <?php foreach ($recentEvents as $event): ?>
                    <?php 
                        $imgs = json_decode($event['image_paths'], true) ?? [$event['image_paths']];
                        $imgCount = count($imgs);
                    ?>
                    <div class="event-card" onclick="location.href='events.php'">
                        <div class="event-img-wrap">
                            <span class="category-pill"><?= htmlspecialchars($event['category']) ?></span>
                            <?php if ($imgCount > 1): ?>
                                <span class="multi-photo-badge">📷 <?= $imgCount ?> Photos</span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($imgs[0]) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                        </div>
                        <div class="event-body">
                            <div class="event-date">📅 <?= htmlspecialchars($event['event_date_bs']) ?> B.S.</div>
                            <h3><?= htmlspecialchars($event['title']) ?></h3>
                            <p><?= htmlspecialchars(mb_strimwidth($event['description'], 0, 110, "...")) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <img src="assets/images/logo.png" alt="Apex Logo">
                        <h2>Apex Model School</h2>
                    </div>
                    <p class="footer-p prose-constrained-sm">Apex Model Secondary School (Estd. 2052 B.S.) is committed to academic rigor, leadership formation, and character integrity approved by NEB and Ministry of Education, Nepal.</p>
                </div>

                <div>
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home Page</a></li>
                        <li><a href="about.php">About Institution</a></li>
                        <li><a href="events.php">Events & Gallery</a></li>
                        <li><a href="notices.php">Official Notices</a></li>
                        <li><a href="contact.php">Admissions Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h3>Contact Information</h3>
                    <p class="footer-p">Ward No. 3, Main Highway Corridor, Koshi Province, Nepal</p>
                    <p class="footer-p">Phone: +977-25-560000<br>WhatsApp: +977-9800000000</p>
                    <p class="footer-p">Email: info@apexschool.edu.np</p>
                </div>

                <div>
                    <h3>Campus Hours</h3>
                    <p class="footer-p">Sunday – Thursday: 9:00 AM – 4:00 PM</p>
                    <p class="footer-p">Friday: 9:00 AM – 1:30 PM</p>
                    <p class="footer-p" style="color: #ef4444;">Saturday: Closed</p>
                </div>
            </div>

            <div class="footer-bottom">
                <div>© <?= date('Y') ?> Apex Model Secondary School. All Rights Reserved.</div>
                <div>Excellence in Academics, Leadership & Integrity.</div>
            </div>
        </div>
    </footer>

    <!-- Minimalist Mobile Bottom Dock -->
    <div class="mobile-bottom-dock">
        <ul class="dock-items">
            <li class="dock-item"><a href="index.php" class="active">
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
