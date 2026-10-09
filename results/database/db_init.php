<?php
/**
 * Dedicated Database Initializer for Examination & Result Subsystem
 * Location: results/database/db_init.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_dir = __DIR__;
$db_file = $db_dir . '/results.sqlite';

// Ensure database directory exists
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0777, true);
}

try {
    $pdo_results = new PDO('sqlite:' . $db_file);
    $pdo_results->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_results->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Set file permissions if newly created
    if (file_exists($db_file)) {
        @chmod($db_file, 0666);
    }

    // 1. Create exam_admins table
    $pdo_results->exec("CREATE TABLE IF NOT EXISTS exam_admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        full_name TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Create student_results table
    $pdo_results->exec("CREATE TABLE IF NOT EXISTS student_results (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        batch_year TEXT NOT NULL DEFAULT '2083 B.S.',
        exam_name TEXT NOT NULL,
        class_name TEXT NOT NULL,
        section_name TEXT NOT NULL,
        symbol_no TEXT NOT NULL,
        roll_no TEXT NOT NULL,
        student_name TEXT NOT NULL,
        attendance TEXT DEFAULT 'N/A',
        gpa REAL NOT NULL,
        remarks TEXT NOT NULL,
        marks_json TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Migration check: ensure batch_year column exists if table was previously created
    try {
        $pdo_results->exec("ALTER TABLE student_results ADD COLUMN batch_year TEXT NOT NULL DEFAULT '2083 B.S.'");
    } catch (PDOException $ex_col) {
        // Column already exists
    }

    // 3. Create Index for fast lookups
    $pdo_results->exec("CREATE INDEX IF NOT EXISTS idx_batch_exam_class_symbol ON student_results(batch_year, exam_name, class_name, symbol_no)");

    // 4. Seed Default Admin if empty
    $admin_count = $pdo_results->query("SELECT COUNT(*) FROM exam_admins")->fetchColumn();
    if ($admin_count == 0) {
        $default_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo_results->prepare("INSERT INTO exam_admins (username, password_hash, full_name) VALUES (:u, :p, :f)");
        $stmt->execute([
            ':u' => 'examadmin',
            ':p' => $default_hash,
            ':f' => 'System Examination Controller'
        ]);
    }

    // 5. Seed Default Student Results across Academic Year Batches (2083 B.S. & 2082 B.S.) if empty
    $results_count = $pdo_results->query("SELECT COUNT(*) FROM student_results")->fetchColumn();
    if ($results_count == 0) {
        $firstNames = ["Aarav", "Aayush", "Bibek", "Bishal", "Dipesh", "Kiran", "Nabin", "Prashant", "Rohan", "Sujan", "Suman", "Waibhav", "Anushka", "Bipana", "Dikshya", "Kripa", "Pooja", "Riya", "Sneha", "Subiksha"];
        $lastNames = ["Adhikari", "Aryal", "Bhattarai", "Bhandari", "Dahal", "Giri", "Jha", "Karki", "Khatiwada", "Maharjan", "Mehta", "Nepal", "Neupane", "Pokharel", "Rai", "Rizal", "Sharma", "Shrestha", "Tamang", "Thapa"];
        $sections = ["ANNAPURNA", "LHOTSE", "KANCHENJUNGA", "EVEREST"];

        $primarySubjects = [
            ["subject" => "NEPALI", "fm" => 100],
            ["subject" => "ENGLISH", "fm" => 100],
            ["subject" => "MATHEMATICS", "fm" => 100],
            ["subject" => "SCIENCE & ENVIRONMENT", "fm" => 100],
            ["subject" => "SOCIAL STUDIES & CREATIVE ARTS", "fm" => 100]
        ];

        $secondarySubjects = [
            ["subject" => "NEPALI", "fm" => 100],
            ["subject" => "ENGLISH", "fm" => 100],
            ["subject" => "COMPULSORY MATHEMATICS", "fm" => 100],
            ["subject" => "SCIENCE & TECHNOLOGY", "fm" => 100],
            ["subject" => "SOCIAL STUDIES", "fm" => 100],
            ["subject" => "OPTIONAL MATHEMATICS", "fm" => 100],
            ["subject" => "COMPUTER SCIENCE", "fm" => 50]
        ];

        $batchesConfig = [
            ["batch_year" => "2083 B.S.", "exam_name" => "Second Mid-Terminal Examination 2083", "symbol_start" => 1001, "min_s" => 15, "max_s" => 30],
            ["batch_year" => "2082 B.S.", "exam_name" => "Annual Examination 2082", "symbol_start" => 2001, "min_s" => 12, "max_s" => 25]
        ];

        $pdo_results->beginTransaction();

        $stmt_insert = $pdo_results->prepare("INSERT INTO student_results 
            (batch_year, exam_name, class_name, section_name, symbol_no, roll_no, student_name, attendance, gpa, remarks, marks_json) 
            VALUES (:batch, :exam, :class, :sec, :sym, :roll, :name, :att, :gpa, :rem, :marks)");

        foreach ($batchesConfig as $batch) {
            $batchYear   = $batch['batch_year'];
            $examSession = $batch['exam_name'];
            $symbolCounter = $batch['symbol_start'];

            for ($grade = 1; $grade <= 10; $grade++) {
                $className = "Grade: " . $grade;
                $studentCount = rand($batch['min_s'], $batch['max_s']);

                for ($roll = 1; $roll <= $studentCount; $roll++) {
                    $fn = $firstNames[array_rand($firstNames)];
                    $ln = $lastNames[array_rand($lastNames)];
                    $studentName = $fn . " " . $ln;
                    $section = $sections[array_rand($sections)];
                    $symbolNo = (string)$symbolCounter++;

                    $totalDays = 110;
                    $presentDays = rand(85, 110);
                    $attendance = "{$presentDays}/{$totalDays}";

                    $subjectDefs = ($grade <= 5) ? $primarySubjects : $secondarySubjects;
                    $marksList = [];
                    $totalGP = 0;
                    $sn = 1;

                    foreach ($subjectDefs as $subDef) {
                        $fm = $subDef['fm'];
                        $obtainedMarks = rand(floor($fm * 0.45), $fm);
                        
                        $perc = ($obtainedMarks / $fm) * 100;
                        if ($perc >= 90) { $g = "A+"; $gp = 4.0; }
                        elseif ($perc >= 80) { $g = "A"; $gp = 3.6; }
                        elseif ($perc >= 70) { $g = "B+"; $gp = 3.2; }
                        elseif ($perc >= 60) { $g = "B"; $gp = 2.8; }
                        elseif ($perc >= 50) { $g = "C+"; $gp = 2.4; }
                        elseif ($perc >= 40) { $g = "C"; $gp = 2.0; }
                        elseif ($perc >= 35) { $g = "D"; $gp = 1.6; }
                        else { $g = "NG"; $gp = 0.0; }

                        $marksList[] = [
                            "sn" => $sn++,
                            "subject" => $subDef['subject'],
                            "full_marks" => $fm,
                            "grade" => $g,
                            "gp" => $gp
                        ];

                        $totalGP += $gp;
                    }

                    $overallGPA = round($totalGP / count($subjectDefs), 2);
                    $remarks = ($overallGPA >= 3.0) ? "PROMOTED TO NEXT GRADE" : "PASSED";

                    $stmt_insert->execute([
                        ':batch' => $batchYear,
                        ':exam'  => $examSession,
                        ':class' => $className,
                        ':sec'   => $section,
                        ':sym'   => $symbolNo,
                        ':roll'  => (string)$roll,
                        ':name'  => $studentName,
                        ':att'   => $attendance,
                        ':gpa'   => $overallGPA,
                        ':rem'   => $remarks,
                        ':marks' => json_encode($marksList)
                    ]);
                }
            }
        }

        $pdo_results->commit();
    }

} catch (PDOException $e) {
    if (isset($pdo_results) && $pdo_results->inTransaction()) {
        $pdo_results->rollBack();
    }
    die("Database Connection Error (Results Subsystem): " . htmlspecialchars($e->getMessage()));
}

// CSRF helper functions if needed in results subsystem
if (!function_exists('generate_results_csrf')) {
    function generate_results_csrf() {
        if (empty($_SESSION['results_csrf_token'])) {
            $_SESSION['results_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['results_csrf_token'];
    }
}

if (!function_exists('verify_results_csrf')) {
    function verify_results_csrf() {
        $token = $_POST['csrf_token'] ?? '';
        if (!$token || !isset($_SESSION['results_csrf_token']) || !hash_equals($_SESSION['results_csrf_token'], $token)) {
            die("CSRF Security Validation Failed.");
        }
    }
}
