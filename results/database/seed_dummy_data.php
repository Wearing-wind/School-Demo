<?php
/**
 * Multi-Batch Dummy Data Seeder for Examination & Result Subsystem
 * Location: results/database/seed_dummy_data.php
 * Generates realistic student result records for Academic Year Batches (2083 B.S. and 2082 B.S.) across Class 1 to 10
 */

require_once __DIR__ . '/db_init.php';

echo "Starting bulk dummy data generation for Batches (2083 B.S. & 2082 B.S.) Grade 1 to 10...\n";

// Clear existing student_results table for clean multi-batch seed
$pdo_results->exec("DELETE FROM student_results");
$pdo_results->exec("DELETE FROM sqlite_sequence WHERE name='student_results'");

$firstNames = [
    "Aarav", "Aayush", "Bibek", "Bishal", "Dipesh", "Kiran", "Nabin", "Prashant", "Rohan", "Sujan",
    "Suman", "Waibhav", "Anushka", "Bipana", "Dikshya", "Kripa", "Pooja", "Riya", "Sneha", "Subiksha",
    "Aashish", "Anuj", "Bikram", "Gaurav", "Manish", "Nirajan", "Prajwal", "Roshan", "Sabin", "Suraj",
    "Aakriti", "Anjali", "Drishti", "Karuna", "Nisha", "Prativa", "Samyukta", "Shristi", "Sunita", "Swastika"
];

$lastNames = [
    "Adhikari", "Aryal", "Bhattarai", "Bhandari", "Dahal", "Giri", "Jha", "Karki", "Khatiwada", "Maharjan",
    "Mehta", "Nepal", "Neupane", "Pokharel", "Rai", "Rizal", "Sharma", "Shrestha", "Tamang", "Thapa"
];

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

function getGradeAndGP($marks, $fullMarks) {
    $perc = ($marks / $fullMarks) * 100;
    if ($perc >= 90) return ["grade" => "A+", "gp" => 4.0];
    if ($perc >= 80) return ["grade" => "A",  "gp" => 3.6];
    if ($perc >= 70) return ["grade" => "B+", "gp" => 3.2];
    if ($perc >= 60) return ["grade" => "B",  "gp" => 2.8];
    if ($perc >= 50) return ["grade" => "C+", "gp" => 2.4];
    if ($perc >= 40) return ["grade" => "C",  "gp" => 2.0];
    if ($perc >= 35) return ["grade" => "D",  "gp" => 1.6];
    return ["grade" => "NG", "gp" => 0.0];
}

$batchesConfig = [
    [
        "batch_year"  => "2083 B.S.",
        "exam_name"   => "Second Mid-Terminal Examination 2083",
        "symbol_start"=> 1001,
        "min_students"=> 15,
        "max_students"=> 30
    ],
    [
        "batch_year"  => "2082 B.S.",
        "exam_name"   => "Annual Examination 2082",
        "symbol_start"=> 2001,
        "min_students"=> 12,
        "max_students"=> 25
    ]
];

$stmt_insert = $pdo_results->prepare("INSERT INTO student_results 
    (batch_year, exam_name, class_name, section_name, symbol_no, roll_no, student_name, attendance, gpa, remarks, marks_json) 
    VALUES (:batch, :exam, :class, :sec, :sym, :roll, :name, :att, :gpa, :rem, :marks)");

$pdo_results->beginTransaction();
$totalRecordsInserted = 0;

foreach ($batchesConfig as $batch) {
    $batchYear   = $batch['batch_year'];
    $examSession = $batch['exam_name'];
    $symbolCounter = $batch['symbol_start'];

    for ($grade = 1; $grade <= 10; $grade++) {
        $className = "Grade: " . $grade;
        $studentCount = rand($batch['min_students'], $batch['max_students']);

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
                $gInfo = getGradeAndGP($obtainedMarks, $fm);

                $marksList[] = [
                    "sn" => $sn++,
                    "subject" => $subDef['subject'],
                    "full_marks" => $fm,
                    "grade" => $gInfo['grade'],
                    "gp" => $gInfo['gp']
                ];

                $totalGP += $gInfo['gp'];
            }

            $overallGPA = round($totalGP / count($subjectDefs), 2);

            if ($overallGPA >= 3.6) {
                $remarks = "PASSED WITH DISTINCTION";
            } elseif ($overallGPA >= 3.0) {
                $remarks = "PROMOTED TO NEXT GRADE";
            } elseif ($overallGPA >= 2.0) {
                $remarks = "PASSED";
            } else {
                $remarks = "ELIGIBLE FOR RETAKE";
            }

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

            $totalRecordsInserted++;
        }
    }
}

$pdo_results->commit();

echo "Successfully populated {$totalRecordsInserted} student result records across 2 Academic Batches (2083 B.S. & 2082 B.S.) for Grade 1 to Grade 10!\n";
