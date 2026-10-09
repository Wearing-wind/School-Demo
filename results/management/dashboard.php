<?php
require_once __DIR__ . '/../database/db_init.php';

if (!isset($_SESSION['exam_admin_logged']) || $_SESSION['exam_admin_logged'] !== true) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'info';

// Handle Delete Record Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete_record'])) {
    verify_results_csrf();
    $delete_id = (int)($_POST['student_id'] ?? 0);
    if ($delete_id > 0) {
        try {
            $stmt = $pdo_results->prepare("DELETE FROM student_results WHERE id = :id");
            $stmt->execute([':id' => $delete_id]);
            $msg = 'Student result record deleted successfully.';
            $msg_type = 'success';
        } catch (PDOException $e) {
            $msg = 'Delete failed: ' . htmlspecialchars($e->getMessage());
            $msg_type = 'error';
        }
    }
}

// Fetch Metrics
$total_students = $pdo_results->query("SELECT COUNT(*) FROM student_results")->fetchColumn();
$total_batches  = $pdo_results->query("SELECT COUNT(DISTINCT batch_year) FROM student_results")->fetchColumn();
$total_exams    = $pdo_results->query("SELECT COUNT(DISTINCT exam_name) FROM student_results")->fetchColumn();
$total_classes  = $pdo_results->query("SELECT COUNT(DISTINCT class_name) FROM student_results")->fetchColumn();

$db_file = __DIR__ . '/../database/results.sqlite';
$db_size = file_exists($db_file) ? round(filesize($db_file) / 1024, 2) . ' KB' : 'N/A';

// Fetch Storage Breakdown by Academic Year Batch, Exam Session & Class
$storage_breakdown_raw = $pdo_results->query("
    SELECT 
        batch_year,
        exam_name, 
        class_name, 
        COUNT(*) as student_count,
        SUM(LENGTH(marks_json) + LENGTH(student_name) + LENGTH(remarks) + LENGTH(symbol_no) + LENGTH(roll_no)) as approx_data_bytes
    FROM student_results 
    GROUP BY batch_year, exam_name, class_name 
    ORDER BY batch_year DESC, exam_name DESC, class_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$total_raw_bytes = array_sum(array_column($storage_breakdown_raw, 'approx_data_bytes')) ?: 1;
$db_file_bytes = file_exists($db_file) ? filesize($db_file) : 0;

$storage_breakdown = [];
foreach ($storage_breakdown_raw as $item) {
    $ratio = $item['approx_data_bytes'] / $total_raw_bytes;
    $est_bytes = $db_file_bytes > 0 ? round($db_file_bytes * $ratio) : $item['approx_data_bytes'];
    $est_kb = round($est_bytes / 1024, 2);
    $percent = round($ratio * 100, 1);

    $storage_breakdown[] = [
        'batch_year'    => $item['batch_year'],
        'exam_name'     => $item['exam_name'],
        'class_name'    => $item['class_name'],
        'student_count' => (int)$item['student_count'],
        'est_kb'        => $est_kb,
        'percent'       => $percent
    ];
}

// Search & Filter parameters
$search_query = trim($_GET['q'] ?? '');
$filter_batch = trim($_GET['batch'] ?? '');
$filter_exam  = trim($_GET['exam'] ?? '');
$filter_class = trim($_GET['class'] ?? '');

$sql = "SELECT * FROM student_results WHERE 1=1";
$params = [];

if ($search_query !== '') {
    $sql .= " AND (symbol_no LIKE :q OR student_name LIKE :q OR roll_no LIKE :q)";
    $params[':q'] = "%{$search_query}%";
}
if ($filter_batch !== '') {
    $sql .= " AND batch_year = :batch";
    $params[':batch'] = $filter_batch;
}
if ($filter_exam !== '') {
    $sql .= " AND exam_name = :exam";
    $params[':exam'] = $filter_exam;
}
if ($filter_class !== '') {
    $sql .= " AND class_name = :class";
    $params[':class'] = $filter_class;
}

$sql .= " ORDER BY id DESC LIMIT 100";
$stmt_list = $pdo_results->prepare($sql);
$stmt_list->execute($params);
$students = $stmt_list->fetchAll();

// Dropdown lists
$batches_list = $pdo_results->query("SELECT DISTINCT batch_year FROM student_results ORDER BY batch_year DESC")->fetchAll(PDO::FETCH_COLUMN);
$exams_list   = $pdo_results->query("SELECT DISTINCT exam_name FROM student_results ORDER BY exam_name DESC")->fetchAll(PDO::FETCH_COLUMN);
$classes_list = $pdo_results->query("SELECT DISTINCT class_name FROM student_results ORDER BY class_name ASC")->fetchAll(PDO::FETCH_COLUMN);

$csrf_token = generate_results_csrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controller Dashboard - Result Subsystem</title>
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
    <style>
        @media (max-width: 640px) {
            .res-table table,
            .res-table thead,
            .res-table tbody,
            .res-table th,
            .res-table td,
            .res-table tr {
                display: block;
            }
            .res-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            .res-table tr {
                background: #ffffff;
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.85rem;
                margin-bottom: 1rem;
                padding: 0.85rem 1rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }
            .res-table td {
                border: none !important;
                padding: 0.45rem 0 !important;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
                word-break: break-word;
                font-size: 0.85rem !important;
            }
            .res-table td::before {
                content: attr(data-label);
                font-weight: 800;
                font-size: 0.725rem;
                text-transform: uppercase;
                color: #64748b;
                letter-spacing: 0.04em;
                flex-shrink: 0;
            }
            .res-table td:last-child {
                margin-top: 0.75rem;
                padding-top: 0.75rem !important;
                border-top: 1px solid #f1f5f9 !important;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col justify-between">

    <!-- Navbar Header -->
    <header class="bg-navy-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="../../assets/images/logo.png" alt="Apex Logo" class="w-10 h-10 object-contain bg-white/10 rounded-full p-1 border border-white/20">
                <div>
                    <h1 class="text-lg font-bold">Apex Examination Management Dashboard</h1>
                    <p class="text-xs text-amber-300">Logged in as: <strong><?= htmlspecialchars($_SESSION['exam_admin_name'] ?? 'Controller') ?></strong></p>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="upload_csv.php" class="bg-amber-500 hover:bg-amber-600 text-navy-900 font-bold text-xs px-4 py-2 rounded-lg shadow transition-all flex items-center gap-1.5">
                    <span>📤</span> Upload CSV Batch
                </a>
                <a href="../index.php" target="_blank" class="bg-white/10 hover:bg-white/20 text-white font-semibold text-xs px-3.5 py-2 rounded-lg border border-white/20 transition-all">
                    Public Portal ↗
                </a>
                <a href="logout.php" class="bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs px-3.5 py-2 rounded-lg transition-all">
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 py-8">
        
        <?php if ($msg): ?>
            <div class="mb-6 p-4 rounded-xl text-sm font-semibold flex items-center justify-between <?= $msg_type === 'success' ? 'bg-emerald-50 border border-emerald-300 text-emerald-900' : 'bg-rose-50 border border-rose-300 text-rose-900' ?>">
                <div><?= $msg ?></div>
                <button onclick="this.parentElement.remove()" class="text-xs opacity-60 hover:opacity-100">✕</button>
            </div>
        <?php endif; ?>

        <!-- Summary Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <div class="text-xs font-bold uppercase text-slate-500 tracking-wider">Total Records Stored</div>
                <div class="text-3xl font-extrabold text-navy-800 mt-2"><?= number_format($total_students) ?></div>
                <div class="text-[11px] text-slate-400 mt-1">Verified marksheets in database</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <div class="text-xs font-bold uppercase text-slate-500 tracking-wider">Academic Year Batches</div>
                <div class="text-3xl font-extrabold text-amber-600 mt-2"><?= number_format($total_batches) ?></div>
                <div class="text-[11px] text-slate-400 mt-1">Distinct years (e.g. 2083 B.S., 2082 B.S.)</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <div class="text-xs font-bold uppercase text-slate-500 tracking-wider">Classes Covered</div>
                <div class="text-3xl font-extrabold text-emerald-700 mt-2"><?= number_format($total_classes) ?></div>
                <div class="text-[11px] text-slate-400 mt-1">Grades 1 to 10</div>
            </div>

            <div onclick="openStorageModal()" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-md transition-all cursor-pointer group">
                <div class="flex items-center justify-between">
                    <div class="text-xs font-bold uppercase text-slate-500 tracking-wider">SQLite DB Storage</div>
                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200 group-hover:bg-indigo-600 group-hover:text-white transition-all">Breakdown 📊</span>
                </div>
                <div class="text-3xl font-extrabold text-indigo-700 mt-2"><?= $db_size ?></div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1 font-medium">
                    <span>Click to view batch & class storage share →</span>
                </div>
            </div>
        </div>

        <!-- Filter & Data Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
            
            <div class="p-6 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-extrabold text-navy-800">Student Examination Records</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Filter, search, review or delete uploaded student marksheets.</p>
                </div>
                <a href="upload_csv.php" class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 text-white font-bold text-xs px-4 py-2.5 rounded-lg transition-all shadow">
                    <span>+ Import New CSV</span>
                </a>
            </div>

            <!-- Filter Controls -->
            <form method="GET" action="" class="p-4 bg-slate-50 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search Symbol No, Name or Roll..." class="bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium">
                
                <select name="batch" class="bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium">
                    <option value="">-- All Batches (Years) --</option>
                    <?php foreach ($batches_list as $bt): ?>
                        <option value="<?= htmlspecialchars($bt) ?>" <?= $filter_batch === $bt ? 'selected' : '' ?>><?= htmlspecialchars($bt) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="exam" class="bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium">
                    <option value="">-- All Exam Sessions --</option>
                    <?php foreach ($exams_list as $ex): ?>
                        <option value="<?= htmlspecialchars($ex) ?>" <?= $filter_exam === $ex ? 'selected' : '' ?>><?= htmlspecialchars($ex) ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="flex gap-2">
                    <select name="class" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium">
                        <option value="">-- All Grades --</option>
                        <?php foreach ($classes_list as $cl): ?>
                            <option value="<?= htmlspecialchars($cl) ?>" <?= $filter_class === $cl ? 'selected' : '' ?>><?= htmlspecialchars($cl) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-4 py-2 rounded-lg text-xs">Filter</button>
                    <?php if ($search_query !== '' || $filter_batch !== '' || $filter_exam !== '' || $filter_class !== ''): ?>
                        <a href="dashboard.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold px-3 py-2 rounded-lg text-xs flex items-center justify-center">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-x-auto res-table">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                            <th class="px-4 py-3">Batch</th>
                            <th class="px-4 py-3">Symbol</th>
                            <th class="px-4 py-3">Roll</th>
                            <th class="px-4 py-3">Student Name</th>
                            <th class="px-4 py-3">Examination Session</th>
                            <th class="px-4 py-3">Grade</th>
                            <th class="px-4 py-3 text-center">GPA</th>
                            <th class="px-4 py-3">Remarks</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-slate-500">
                                    No student records found matching your filters.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $st): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td data-label="Batch Year" class="px-4 py-3 text-xs font-bold text-amber-800 bg-amber-50/50"><?= htmlspecialchars($st['batch_year'] ?? '2083 B.S.') ?></td>
                                    <td data-label="Symbol No" class="px-4 py-3 font-mono font-bold text-slate-900"><?= htmlspecialchars($st['symbol_no']) ?></td>
                                    <td data-label="Roll No" class="px-4 py-3 font-mono text-slate-600"><?= htmlspecialchars($st['roll_no']) ?></td>
                                    <td data-label="Student Name" class="px-4 py-3 font-bold text-navy-800"><?= htmlspecialchars($st['student_name']) ?></td>
                                    <td data-label="Examination" class="px-4 py-3 text-xs text-slate-600"><?= htmlspecialchars($st['exam_name']) ?></td>
                                    <td data-label="Grade" class="px-4 py-3 text-xs font-bold text-slate-700"><?= htmlspecialchars($st['class_name']) ?></td>
                                    <td data-label="GPA" class="px-4 py-3 text-center font-bold">
                                        <span class="inline-block px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800 font-black">
                                            <?= sprintf("%.2f", (float)$st['gpa']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Remarks" class="px-4 py-3 text-xs font-semibold text-slate-600"><?= htmlspecialchars($st['remarks']) ?></td>
                                    <td data-label="Actions" class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="previewMarksheet(<?= htmlspecialchars(json_encode($st)) ?>)" class="text-xs font-bold bg-navy-800 hover:bg-navy-900 text-white px-2.5 py-1 rounded transition-all">
                                                View
                                            </button>
                                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this student result record?');" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                <input type="hidden" name="action_delete_record" value="1">
                                                <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                                                <button type="submit" class="text-xs font-bold bg-rose-100 hover:bg-rose-200 text-rose-800 px-2.5 py-1 rounded transition-all">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </main>

    <!-- Modal for Storage Breakdown -->
    <div id="storageModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm hidden items-center justify-center p-4 z-50 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-4xl w-full p-6 relative max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-300">
            <button onclick="closeStorageModal()" class="absolute top-4 right-4 bg-slate-100 hover:bg-slate-200 text-slate-700 w-8 h-8 rounded-full font-bold text-sm">✕</button>
            
            <div class="text-center border-b border-slate-200 pb-4 mb-4">
                <span class="inline-block text-xs font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-200 mb-1">
                    Database Storage Analytics
                </span>
                <h3 class="text-xl font-extrabold text-navy-800">Academic Year Batches & Class Storage Breakdown</h3>
                <p class="text-xs text-slate-500 mt-0.5">Database File: <code class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded font-mono font-bold">results/database/results.sqlite</code> (Total: <?= $db_size ?>)</p>
            </div>

            <div class="grid grid-cols-4 gap-3 mb-6 text-center">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Total Students</div>
                    <div class="text-lg font-black text-navy-800"><?= number_format($total_students) ?></div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Academic Batches</div>
                    <div class="text-lg font-black text-amber-600"><?= number_format($total_batches) ?> Years</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Classes Covered</div>
                    <div class="text-lg font-black text-emerald-700"><?= number_format($total_classes) ?> Grades</div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-500 uppercase">File Size</div>
                    <div class="text-lg font-black text-indigo-700"><?= $db_size ?></div>
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-xl res-table">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px] border-b border-slate-200">
                            <th class="px-4 py-2.5">Academic Batch (Year)</th>
                            <th class="px-4 py-2.5">Exam Session</th>
                            <th class="px-4 py-2.5">Grade / Class</th>
                            <th class="px-4 py-2.5 text-center">Students</th>
                            <th class="px-4 py-2.5 text-right">Est. Size</th>
                            <th class="px-4 py-2.5">Storage Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        <?php if (empty($storage_breakdown)): ?>
                            <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No batch records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($storage_breakdown as $sb): ?>
                                <tr class="hover:bg-slate-50">
                                    <td data-label="Academic Batch" class="px-4 py-2.5 text-xs text-amber-800 font-extrabold bg-amber-50/50"><?= htmlspecialchars($sb['batch_year']) ?></td>
                                    <td data-label="Exam Session" class="px-4 py-2.5 text-xs text-slate-700 font-bold"><?= htmlspecialchars($sb['exam_name']) ?></td>
                                    <td data-label="Grade" class="px-4 py-2.5 text-xs font-bold text-navy-800"><?= htmlspecialchars($sb['class_name']) ?></td>
                                    <td data-label="Students" class="px-4 py-2.5 text-center font-mono font-bold text-slate-800"><?= number_format($sb['student_count']) ?></td>
                                    <td data-label="Est. Size" class="px-4 py-2.5 text-right font-mono font-bold text-indigo-700"><?= $sb['est_kb'] ?> KB</td>
                                    <td data-label="Share %" class="px-4 py-2.5">
                                        <div class="flex items-center gap-2 w-full">
                                            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                                <div class="bg-indigo-600 h-2 rounded-full" style="width: <?= min(100, max(5, $sb['percent'])) ?>%"></div>
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-600 w-10 text-right"><?= $sb['percent'] ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Previewing Marksheet -->
    <div id="previewModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm hidden items-center justify-center p-4 z-50 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 relative max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-300">
            <button onclick="closeModal()" class="absolute top-4 right-4 bg-slate-100 hover:bg-slate-200 text-slate-700 w-8 h-8 rounded-full font-bold text-sm">✕</button>
            <div id="modalBody"></div>
        </div>
    </div>

    <footer class="bg-slate-200 text-slate-600 py-4 text-center text-xs border-t border-slate-300">
        &copy; <?= date('Y') ?> Apex Model Secondary School. Isolated Result Subsystem.
    </footer>

    <script>
        function openStorageModal() {
            const modal = document.getElementById('storageModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeStorageModal() {
            const modal = document.getElementById('storageModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function previewMarksheet(st) {
            const modal = document.getElementById('previewModal');
            const modalBody = document.getElementById('modalBody');
            
            const marks = JSON.parse(st.marks_json || '[]');
            let rowsHtml = '';
            let totalFM = 0;

            marks.forEach((m, i) => {
                totalFM += parseFloat(m.full_marks || 0);
                rowsHtml += `
                    <tr class="${i % 2 === 0 ? 'bg-white' : 'bg-slate-50'}">
                        <td class="border px-2 py-1 text-center font-bold text-slate-600">${m.sn || (i+1)}</td>
                        <td class="border px-3 py-1 font-bold text-slate-800">${m.subject}</td>
                        <td class="border px-2 py-1 text-center">${m.full_marks}</td>
                        <td class="border px-2 py-1 text-center font-black text-emerald-700">${m.grade}</td>
                        <td class="border px-2 py-1 text-center font-mono font-bold">${parseFloat(m.gp).toFixed(2)}</td>
                    </tr>
                `;
            });

            modalBody.innerHTML = `
                <div class="text-center border-b-2 border-slate-900 pb-3 mb-4">
                    <h3 class="text-lg font-black text-navy-800 uppercase">Apex Model Secondary School</h3>
                    <p class="text-xs text-rose-800 font-bold">Official Student Result Record</p>
                    <span class="inline-block mt-1 bg-navy-800 text-amber-300 font-bold px-3 py-0.5 rounded-full text-[11px]">
                        ${st.exam_name}
                    </span>
                </div>

                <div class="bg-slate-50 p-3 rounded-lg text-xs grid grid-cols-2 gap-2 mb-4 font-semibold">
                    <div>Student Name: <strong class="text-slate-900 uppercase">${st.student_name}</strong></div>
                    <div>Grade: <strong>${st.class_name}</strong></div>
                    <div>Symbol No: <strong>${st.symbol_no}</strong></div>
                    <div>Roll No: <strong>${st.roll_no}</strong></div>
                    <div>Section: <strong>${st.section_name}</strong></div>
                    <div>Attendance: <strong>${st.attendance}</strong></div>
                </div>

                <table class="w-full text-xs border border-slate-300 text-left mb-4">
                    <thead>
                        <tr class="bg-navy-800 text-white font-bold uppercase">
                            <th class="border px-2 py-1.5 text-center">S.N.</th>
                            <th class="border px-3 py-1.5">Subject</th>
                            <th class="border px-2 py-1.5 text-center">FM</th>
                            <th class="border px-2 py-1.5 text-center">Grade</th>
                            <th class="border px-2 py-1.5 text-center">GP</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>

                <div class="flex items-center justify-between text-xs font-bold bg-amber-50 p-2.5 rounded border border-amber-300">
                    <div>Final GPA: <span class="text-navy-900 text-sm font-black">${parseFloat(st.gpa).toFixed(2)}</span> / 4.0</div>
                    <div>Remarks: <span class="text-rose-900 uppercase font-extrabold">${st.remarks}</span></div>
                </div>
            `;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('previewModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</body>
</html>
