<?php
require_once __DIR__ . '/../database/db_init.php';

if (!isset($_SESSION['exam_admin_logged']) || $_SESSION['exam_admin_logged'] !== true) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'info';

// Auto-grade calculation helper
function calculate_grade_and_gp($marks, $full_marks = 100) {
    $marks = (float)$marks;
    $full_marks = (float)$full_marks > 0 ? (float)$full_marks : 100;
    $percentage = ($marks / $full_marks) * 100;

    if ($percentage >= 90) return ['grade' => 'A+', 'gp' => 4.0];
    if ($percentage >= 80) return ['grade' => 'A',  'gp' => 3.6];
    if ($percentage >= 70) return ['grade' => 'B+', 'gp' => 3.2];
    if ($percentage >= 60) return ['grade' => 'B',  'gp' => 2.8];
    if ($percentage >= 50) return ['grade' => 'C+', 'gp' => 2.4];
    if ($percentage >= 40) return ['grade' => 'C',  'gp' => 2.0];
    if ($percentage >= 35) return ['grade' => 'D',  'gp' => 1.6];
    return ['grade' => 'NG', 'gp' => 0.0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_upload_csv'])) {
    verify_results_csrf();

    $batch_year  = trim($_POST['batch_year'] ?? '2083 B.S.');
    $exam_name   = trim($_POST['exam_name'] ?? '');
    $class_name  = trim($_POST['class_name'] ?? '');
    $section_name= trim($_POST['section_name'] ?? 'ANNAPURNA');

    if (empty($exam_name) || empty($class_name)) {
        $msg = 'Please select or enter both Examination Name and Grade/Class.';
        $msg_type = 'error';
    } elseif (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $msg = 'Please choose a valid CSV file to upload.';
        $msg_type = 'error';
    } else {
        $tmp_file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($tmp_file, 'r');

        if (!$handle) {
            $msg = 'Could not read the uploaded CSV file.';
            $msg_type = 'error';
        } else {
            // Read first header line
            $raw_headers = fgetcsv($handle);
            if (!$raw_headers) {
                $msg = 'The uploaded CSV file appears to be empty.';
                $msg_type = 'error';
                fclose($handle);
            } else {
                // Remove UTF-8 BOM if present
                if (isset($raw_headers[0])) {
                    $raw_headers[0] = preg_replace('/\x{FEFF}/u', '', $raw_headers[0]);
                }

                $headers = array_map(function($h) {
                    return strtolower(trim($h));
                }, $raw_headers);

                // Find indices of standard columns
                $col_symbol     = array_search('symbol_no', $headers);
                $col_roll       = array_search('roll_no', $headers);
                $col_name       = array_search('student_name', $headers);
                $col_attendance = array_search('attendance', $headers);
                $col_remarks    = array_search('remarks', $headers);
                $col_gpa        = array_search('gpa', $headers);

                // Fallbacks if named slightly differently
                if ($col_symbol === false) $col_symbol = array_search('symbol', $headers);
                if ($col_roll === false) $col_roll = array_search('roll', $headers);
                if ($col_name === false) $col_name = array_search('name', $headers);

                if ($col_symbol === false || $col_name === false) {
                    $msg = 'CSV must contain at least "symbol_no" and "student_name" column headers.';
                    $msg_type = 'error';
                    fclose($handle);
                } else {
                    // Determine subject columns (all headers that are not standard metadata)
                    $reserved_indices = array_filter([
                        $col_symbol, $col_roll, $col_name, $col_attendance, $col_remarks, $col_gpa
                    ], function($v) { return $v !== false; });

                    $subject_columns = [];
                    foreach ($raw_headers as $idx => $orig_header) {
                        if (!in_array($idx, $reserved_indices)) {
                            $subject_columns[$idx] = trim($orig_header);
                        }
                    }

                    // Process rows inside PDO Transaction
                    $imported = 0;
                    $pdo_results->beginTransaction();

                    try {
                        $stmt_insert = $pdo_results->prepare("INSERT INTO student_results 
                            (batch_year, exam_name, class_name, section_name, symbol_no, roll_no, student_name, attendance, gpa, remarks, marks_json) 
                            VALUES (:batch, :exam, :class, :sec, :sym, :roll, :name, :att, :gpa, :rem, :marks)");

                        while (($row = fgetcsv($handle)) !== false) {
                            if (empty($row) || (count($row) === 1 && trim($row[0]) === '')) {
                                continue;
                            }

                            $sym_val  = trim($row[$col_symbol] ?? '');
                            $name_val = trim($row[$col_name] ?? '');
                            $roll_val = ($col_roll !== false) ? trim($row[$col_roll] ?? '') : 'N/A';
                            $att_val  = ($col_attendance !== false) ? trim($row[$col_attendance] ?? '') : '100/100';
                            $rem_val  = ($col_remarks !== false) ? trim($row[$col_remarks] ?? '') : 'PASSED';

                            if (empty($sym_val) || empty($name_val)) {
                                continue; // Skip blank student records
                            }

                            // Build subjects array
                            $marks_list = [];
                            $sn = 1;
                            $total_gp = 0;
                            $subject_count = 0;

                            foreach ($subject_columns as $s_idx => $s_name) {
                                $cell_val = trim($row[$s_idx] ?? '');

                                // Parse marks or numeric
                                $marks_val = is_numeric($cell_val) ? (float)$cell_val : 75;
                                $full_marks = 100;
                                
                                // Determine full marks if specified in name e.g. "ENGLISH (50)"
                                if (preg_match('/\((\d+)\)/', $s_name, $fm_matches)) {
                                    $full_marks = (int)$fm_matches[1];
                                }

                                $grade_info = calculate_grade_and_gp($marks_val, $full_marks);

                                $marks_list[] = [
                                    'sn'         => $sn++,
                                    'subject'    => strtoupper($s_name),
                                    'full_marks' => $full_marks,
                                    'grade'      => $grade_info['grade'],
                                    'gp'         => $grade_info['gp']
                                ];

                                $total_gp += $grade_info['gp'];
                                $subject_count++;
                            }

                            // Calculate overall student GPA
                            if ($col_gpa !== false && !empty($row[$col_gpa]) && is_numeric($row[$col_gpa])) {
                                $final_gpa = (float)$row[$col_gpa];
                            } else {
                                $final_gpa = $subject_count > 0 ? round($total_gp / $subject_count, 2) : 3.0;
                            }

                            $stmt_insert->execute([
                                ':batch' => $batch_year,
                                ':exam'  => $exam_name,
                                ':class' => $class_name,
                                ':sec'   => $section_name,
                                ':sym'   => $sym_val,
                                ':roll'  => $roll_val,
                                ':name'  => $name_val,
                                ':att'   => $att_val,
                                ':gpa'   => $final_gpa,
                                ':rem'   => $rem_val,
                                ':marks' => json_encode($marks_list)
                            ]);

                            $imported++;
                        }

                        $pdo_results->commit();
                        fclose($handle);

                        $msg = "Success! Successfully imported <strong>{$imported}</strong> student result records for \"{$exam_name}\" ({$class_name}).";
                        $msg_type = 'success';

                    } catch (Exception $ex) {
                        $pdo_results->rollBack();
                        fclose($handle);
                        $msg = 'Database batch processing failed: ' . htmlspecialchars($ex->getMessage());
                        $msg_type = 'error';
                    }
                }
            }
        }
    }
}

$csrf_token = generate_results_csrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Upload CSV Results - Apex Result Management</title>
    <meta name="theme-color" content="#091b33">
    <link rel="manifest" href="../manifest.json">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 800: '#091b33', 900: '#051124' },
                        amber: { 500: '#f59e0b', 600: '#d97706' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col justify-between">

    <!-- Navbar -->
    <header class="bg-navy-800 text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="../../assets/images/logo.png" alt="Apex Logo" class="w-10 h-10 object-contain bg-white/10 rounded-full p-1 border border-white/20">
                <div>
                    <h1 class="text-lg font-bold">Bulk CSV Result Import Engine</h1>
                    <p class="text-xs text-amber-300">Apex Model Secondary School</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="dashboard.php" class="text-xs font-semibold bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg border border-white/20 transition-all">
                    ← Dashboard
                </a>
                <a href="logout.php" class="text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg transition-all">
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-4xl w-full mx-auto px-4 py-8">
        
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 sm:p-8">
            <div class="flex items-center justify-between flex-wrap gap-4 pb-6 mb-6 border-b border-slate-200">
                <div>
                    <h2 class="text-xl font-extrabold text-navy-800">Upload Student Examination CSV</h2>
                    <p class="text-xs text-slate-600 mt-1">Import class marksheets dynamically. Grade points are auto-calculated from marks.</p>
                </div>
                <a href="sample_template.csv" download class="inline-flex items-center gap-2 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 font-bold px-4 py-2 rounded-lg text-xs transition-all shadow-sm">
                    <svg class="w-4 h-4 text-amber-700 fill-current" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                    Download Sample CSV Template
                </a>
            </div>

            <?php if ($msg): ?>
                <div class="mb-6 p-4 rounded-xl text-sm font-semibold flex items-center gap-3 <?= $msg_type === 'success' ? 'bg-emerald-50 border border-emerald-300 text-emerald-900' : 'bg-rose-50 border border-rose-300 text-rose-900' ?>">
                    <span class="text-lg"><?= $msg_type === 'success' ? '✅' : '⚠️' ?></span>
                    <div><?= $msg ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action_upload_csv" value="1">

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Academic Year Batch <span class="text-rose-600">*</span></label>
                        <input type="text" name="batch_year" required value="2083 B.S." placeholder="e.g. 2083 B.S." class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-medium focus:ring-2 focus:ring-navy-800 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Examination Session Name <span class="text-rose-600">*</span></label>
                        <input type="text" name="exam_name" required value="Second Mid-Terminal Examination 2083" placeholder="e.g. Second Mid-Terminal Examination 2083" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-medium focus:ring-2 focus:ring-navy-800 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Grade / Class Name <span class="text-rose-600">*</span></label>
                        <input type="text" name="class_name" required value="Grade: 10" placeholder="e.g. Grade: 10" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-medium focus:ring-2 focus:ring-navy-800 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Section Name</label>
                        <input type="text" name="section_name" value="ANNAPURNA" placeholder="e.g. ANNAPURNA" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-medium focus:ring-2 focus:ring-navy-800 focus:bg-white transition-all">
                    </div>
                </div>

                <!-- CSV Dropzone Box -->
                <div class="border-2 border-dashed border-slate-300 hover:border-navy-800 rounded-2xl p-8 text-center bg-slate-50 transition-all cursor-pointer relative">
                    <input type="file" name="csv_file" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="flex flex-col items-center justify-center pointer-events-none">
                        <svg class="w-12 h-12 text-slate-400 mb-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                        <p class="text-sm font-bold text-slate-700">Click or drag & drop `.csv` file here</p>
                        <p class="text-xs text-slate-500 mt-1">Supports standard CSV with headers: symbol_no, roll_no, student_name, attendance, remarks, and subject columns.</p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="dashboard.php" class="text-xs font-bold text-slate-600 hover:text-slate-900">Cancel & Return</a>
                    <button type="submit" class="bg-navy-800 hover:bg-navy-900 text-white font-bold py-3 px-8 rounded-lg shadow-md transition-all text-sm tracking-wide">
                        🚀 Start Bulk CSV Import →
                    </button>
                </div>
            </form>
        </div>

        <!-- Format Guide -->
        <div class="mt-8 bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-sm font-bold text-navy-800 uppercase tracking-wider mb-3">CSV Column Header Specification</h3>
            <div class="text-xs text-slate-600 space-y-2">
                <p>• <strong>Required Columns:</strong> <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">symbol_no</code>, <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">student_name</code></p>
                <p>• <strong>Optional Student Metadata:</strong> <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">roll_no</code>, <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">attendance</code>, <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">remarks</code>, <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">gpa</code></p>
                <p>• <strong>Subject Mark Columns:</strong> Any extra header (e.g., <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">NEPALI</code>, <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">ENGLISH</code>, <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">MATHEMATICS</code>) is automatically detected as a subject! Marks are converted to official Nepal Grade and Grade Points instantly.</p>
            </div>
        </div>

    </main>

    <footer class="bg-slate-200 text-slate-600 py-4 text-center text-xs border-t border-slate-300">
        &copy; <?= date('Y') ?> Apex Model Secondary School. Result Management Subsystem.
    </footer>

</body>
</html>
