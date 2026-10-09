<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/database/db_init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. POST required.'
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    $data = $_POST;
}

$batch_year         = trim($data['batch_year'] ?? '');
$exam_name          = trim($data['exam_name'] ?? '');
$class_name         = trim($data['class_name'] ?? '');
$symbol_no          = trim($data['symbol_no'] ?? '');
$verification_query = trim($data['verification_query'] ?? '');

if (empty($exam_name) || empty($class_name) || empty($symbol_no) || empty($verification_query)) {
    echo json_encode([
        'success' => false,
        'message' => 'All search fields (Exam, Grade, Symbol No, and Name/Roll No verification) are required.'
    ]);
    exit;
}

try {
    $sql = "SELECT * FROM student_results 
        WHERE LOWER(TRIM(exam_name)) = LOWER(TRIM(:exam))
          AND LOWER(TRIM(class_name)) = LOWER(TRIM(:class))
          AND LOWER(TRIM(symbol_no)) = LOWER(TRIM(:symbol))";
    
    $params = [
        ':exam'   => $exam_name,
        ':class'  => $class_name,
        ':symbol' => $symbol_no
    ];

    if (!empty($batch_year)) {
        $sql .= " AND LOWER(TRIM(batch_year)) = LOWER(TRIM(:batch))";
        $params[':batch'] = $batch_year;
    }

    $sql .= " LIMIT 1";

    $stmt = $pdo_results->prepare($sql);
    $stmt->execute($params);

    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid Symbol Number or no record found for the selected Examination and Grade.'
        ]);
        exit;
    }

    // Security Verification Check: Verification query must match Roll No OR Student Name
    $v_clean = strtolower($verification_query);
    $roll_clean = strtolower(trim($student['roll_no']));
    $name_clean = strtolower(trim($student['student_name']));

    $match_roll = ($v_clean === $roll_clean);
    $match_name = (strpos($name_clean, $v_clean) !== false || $v_clean === $name_clean);

    if (!$match_roll && !$match_name) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid Symbol Number or Student verification details (Name/Roll No) do not match our official records.'
        ]);
        exit;
    }

    // Parse marks_json and round GP
    $marks = json_decode($student['marks_json'], true) ?: [];
    foreach ($marks as &$m) {
        if (isset($m['gp'])) {
            $m['gp'] = (float)round((float)$m['gp'], 2);
        }
    }
    unset($m);

    echo json_encode([
        'success' => true,
        'data' => [
            'id'           => $student['id'],
            'batch_year'   => $student['batch_year'] ?? '2083 B.S.',
            'exam_name'    => $student['exam_name'],
            'class_name'   => $student['class_name'],
            'section_name' => $student['section_name'],
            'symbol_no'    => $student['symbol_no'],
            'roll_no'      => $student['roll_no'],
            'student_name' => $student['student_name'],
            'attendance'   => $student['attendance'],
            'gpa'          => sprintf("%.2f", (float)$student['gpa']),
            'remarks'      => $student['remarks'],
            'marks'        => $marks,
            'created_at'   => $student['created_at']
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error while retrieving results: ' . htmlspecialchars($e->getMessage())
    ]);
}
