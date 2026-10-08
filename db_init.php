<?php
/**
 * Apex Model Secondary School (Estd. 2052 B.S.)
 * Database Auto-Initialization & Connection Module
 * PHP 8.x + SQLite 3 (PDO)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_dir = __DIR__ . '/database';
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0777, true);
}
@chmod($db_dir, 0777);

$db_path = $db_dir . '/school.sqlite';
if (file_exists($db_path)) {
    @chmod($db_path, 0666);
}

try {
    $pdo = new PDO("sqlite:" . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create Tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            key_name TEXT PRIMARY KEY,
            key_value TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            full_name TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS notices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            category TEXT NOT NULL,
            published_date_bs TEXT NOT NULL,
            description TEXT NOT NULL,
            file_path TEXT,
            is_pinned INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            category TEXT NOT NULL,
            event_date_bs TEXT NOT NULL,
            description TEXT NOT NULL,
            image_paths TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Seed Settings if empty
    $settingCount = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($settingCount == 0) {
        $pdo->exec("INSERT INTO settings (key_name, key_value) VALUES ('admissions_open', '1')");
    }

    // Seed Admin Account if empty
    $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    if ($adminCount == 0) {
        $adminStmt = $pdo->prepare("INSERT INTO admins (username, password_hash, full_name) VALUES (:username, :password_hash, :full_name)");
        $adminStmt->execute([
            ':username' => 'admin',
            ':password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            ':full_name' => 'System Administrator'
        ]);
    }

    // Seed Notices if empty
    $noticeCount = $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();
    if ($noticeCount == 0) {
        $noticeStmt = $pdo->prepare("
            INSERT INTO notices (title, category, published_date_bs, description, file_path, is_pinned)
            VALUES (:title, :category, :published_date_bs, :description, :file_path, :is_pinned)
        ");

        $seedNotices = [
            [
                ':title' => 'Admissions Open for Academic Session 2083/84 B.S.',
                ':category' => 'Academic',
                ':published_date_bs' => '2083-01-10',
                ':description' => 'Apex Model Secondary School announces admissions open for Nursery through Grade 9 and Senior Secondary 10+2 (Science, Management, Humanities). Entrance assessment schedules are available at the administration desk.',
                ':file_path' => 'uploads/notices/routine-first-term-2083.pdf',
                ':is_pinned' => 1
            ],
            [
                ':title' => 'Annual Inter-House STEM & Robotics Exhibition 2083',
                ':category' => 'General',
                ':published_date_bs' => '2083-03-15',
                ':description' => 'Students from Grades 6 to 12 will showcase innovative robotics models, IoT devices, and environmental science projects in the school main assembly quadrangle.',
                ':file_path' => 'uploads/notices/inter-school-sports-meet.pdf',
                ':is_pinned' => 1
            ],
            [
                ':title' => 'First Terminal Examination Schedule & Academic Calendar',
                ':category' => 'Examinations',
                ':published_date_bs' => '2083-04-05',
                ':description' => 'The First Terminal Examination for Grades 1 to 10 will commence from Jestha 12, 2083 B.S. All students must obtain admit cards after clearing school dues.',
                ':file_path' => 'uploads/notices/routine-first-term-2083.pdf',
                ':is_pinned' => 0
            ],
            [
                ':title' => 'Festive Vacation Circular: Dashain & Tihar 2083',
                ':category' => 'Holidays',
                ':published_date_bs' => '2083-06-20',
                ':description' => 'The school will remain closed for Dashain and Tihar holidays from Ashwin 28 to Kartik 18, 2083 B.S. Classes resume promptly on Kartik 21, 2083 B.S.',
                ':file_path' => 'uploads/notices/dashain-tihar-vacation-2083.pdf',
                ':is_pinned' => 0
            ]
        ];

        foreach ($seedNotices as $n) {
            $noticeStmt->execute($n);
        }
    }

    // Seed Events if empty (using JSON array for image_paths)
    $eventCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    if ($eventCount == 0) {
        $eventStmt = $pdo->prepare("
            INSERT INTO events (title, category, event_date_bs, description, image_paths)
            VALUES (:title, :category, :event_date_bs, :description, :image_paths)
        ");

        $seedEvents = [
            [
                ':title' => 'Annual Inter-School Science & Technology Fair',
                ':category' => 'Academic Fairs',
                ':event_date_bs' => '2083-02-18',
                ':description' => 'Over 40 student projects ranging from automated solar irrigation systems to AI drone prototypes were displayed before regional educational inspectors.',
                ':image_paths' => json_encode(['assets/images/events/science_fair.jpg', 'assets/images/facilities/robotics_lab.jpg', 'assets/images/facilities/computer_lab.jpg'])
            ],
            [
                ':title' => 'Annual Athletic Championship & Sports Meet',
                ':category' => 'Sports Meet',
                ':event_date_bs' => '2083-03-02',
                ':description' => 'Inter-house competitions in track sprints, high jump, basketball, and futsal championship finals at the Apex Sports Complex.',
                ':image_paths' => json_encode(['assets/images/events/sports_day.jpg', 'assets/images/facilities/sports_ground.jpg'])
            ],
            [
                ':title' => 'Grand Cultural Pageant & Annual Day Celebrations',
                ':category' => 'Cultural Programs',
                ':event_date_bs' => '2083-04-12',
                ':description' => 'A grand celebration featuring traditional Nepalese folk dances, orchestral musical recitals, and academic award honors.',
                ':image_paths' => json_encode(['assets/images/events/annual_function.jpg', 'assets/images/events/assembly.jpg'])
            ],
            [
                ':title' => 'Student Art & Heritage Creative Exhibition',
                ':category' => 'Exhibitions',
                ':event_date_bs' => '2083-05-08',
                ':description' => 'Exhibition showcasing student canvas paintings, clay sculptures, and traditional Nepalese architectural miniature models.',
                ':image_paths' => json_encode(['assets/images/events/art_exhibition.jpg', 'assets/images/facilities/library.jpg'])
            ],
            [
                ':title' => 'Inter-House Oratory & Public Speaking Tournament',
                ':category' => 'Academic Fairs',
                ':event_date_bs' => '2083-05-22',
                ':description' => 'Debating contemporary global issues, ethics in artificial intelligence, and environmental conservation in Koshi Province.',
                ':image_paths' => json_encode(['assets/images/events/debate.jpg'])
            ],
            [
                ':title' => 'Weekly Leadership & Moral Ethics Assembly',
                ':category' => 'Cultural Programs',
                ':event_date_bs' => '2083-06-04',
                ':description' => 'Morning assembly focusing on character discipline, community service, and student council announcements.',
                ':image_paths' => json_encode(['assets/images/events/assembly.jpg', 'assets/images/principal.jpg'])
            ]
        ];

        foreach ($seedEvents as $e) {
            $eventStmt->execute($e);
        }
    }

} catch (PDOException $e) {
    die("Database Connection / Initialization Error: " . $e->getMessage());
}

// CSRF Utility
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Invalid CSRF Token! Access blocked.");
        }
    }
}

// Helper to get site setting
function get_setting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = :key LIMIT 1");
    $stmt->execute([':key' => $key]);
    $res = $stmt->fetch();
    return $res ? $res['key_value'] : $default;
}
