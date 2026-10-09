<?php
require_once __DIR__ . '/../database/db_init.php';

if (isset($_SESSION['exam_admin_logged']) && $_SESSION['exam_admin_logged'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo_results->prepare("SELECT * FROM exam_admins WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['exam_admin_logged'] = true;
                $_SESSION['exam_admin_id'] = $user['id'];
                $_SESSION['exam_admin_user'] = $user['username'];
                $_SESSION['exam_admin_name'] = $user['full_name'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid administrative credentials.';
            }
        } catch (PDOException $e) {
            $error = 'Authentication error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Management Portal Login - Apex Model Secondary School</title>
    <meta name="theme-color" content="#091b33">
    <link rel="manifest" href="../manifest.json">
    <link rel="icon" type="image/png" href="../../assets/icons/icon-192.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 800: '#091b33', 900: '#051124' },
                        gold: { 500: '#d97706', 600: '#b45309' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between text-slate-800">

    <!-- Header -->
    <header class="bg-navy-800 text-white py-4 px-6 shadow-md">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="../../assets/images/logo.png" alt="Apex Logo" class="w-10 h-10 object-contain bg-white/10 rounded-full p-1 border border-white/20">
                <div>
                    <h1 class="text-lg font-bold leading-tight">Apex Examination Subsystem</h1>
                    <p class="text-xs text-amber-300">Management & Marksheet Control Panel</p>
                </div>
            </div>
            <a href="../index.php" class="text-xs font-semibold bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg border border-white/20 transition-all">
                ← Public Portal
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            
            <!-- PWA Install Banner -->
            <div id="pwaBanner" class="hidden mb-4 bg-amber-500 text-navy-900 p-3.5 rounded-xl shadow-md border border-amber-400 flex items-center justify-between gap-3 text-xs font-bold">
                <div class="flex items-center gap-2">
                    <span class="text-base">📱</span>
                    <span>Install Result Management App on your device for quick access.</span>
                </div>
                <button id="pwaInstallBtn" type="button" class="bg-navy-900 hover:bg-navy-800 text-white px-3 py-1.5 rounded-lg font-bold flex-shrink-0">
                    Install
                </button>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-navy-900 p-6 text-center text-white relative">
                    <div class="inline-flex p-3 bg-white/10 rounded-full mb-3 border border-white/20">
                        <svg class="w-8 h-8 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a5 5 0 00-5 5v2H4a2 2 0 00-2 2v5a2 2 0 002 2h12a2 2 0 002-2v-5a2 2 0 00-2-2h-1V7a5 5 0 00-5-5zm3 7V7a3 3 0 00-6 0v2h6z" clip-rule="evenodd"/></svg>
                    </div>
                    <h2 class="text-xl font-extrabold tracking-tight">Controller Authentication</h2>
                    <p class="text-xs text-slate-300 mt-1">Enter authorized credentials to manage examination results</p>
                </div>

                <form method="POST" action="" class="p-6 sm:p-8 space-y-5">
                    <?php if ($error): ?>
                        <div class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Username</label>
                        <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? 'examadmin') ?>" placeholder="e.g. examadmin" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-medium focus:ring-2 focus:ring-navy-800 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700 mb-1.5">Password</label>
                        <input type="password" name="password" required value="admin123" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-medium focus:ring-2 focus:ring-navy-800 focus:bg-white transition-all">
                        <p class="text-[11px] text-slate-500 mt-1">Default Seed: <code class="bg-slate-200 px-1 py-0.5 rounded text-slate-800 font-bold">examadmin</code> / <code class="bg-slate-200 px-1 py-0.5 rounded text-slate-800 font-bold">admin123</code></p>
                    </div>

                    <button type="submit" class="w-full bg-navy-800 hover:bg-navy-900 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-all text-sm tracking-wide">
                        Sign In to Controller Dashboard →
                    </button>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-slate-200 text-slate-600 py-4 text-center text-xs border-t border-slate-300">
        &copy; <?= date('Y') ?> Apex Model Secondary School. Isolated Result Subsystem.
    </footer>

    <script>
        // Service worker & PWA banner
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('../sw.js', { scope: '/results/' });
        }

        let deferredPrompt;
        const banner = document.getElementById('pwaBanner');
        const installBtn = document.getElementById('pwaInstallBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (banner) banner.classList.remove('hidden');
        });

        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted' && banner) {
                        banner.classList.add('hidden');
                    }
                    deferredPrompt = null;
                }
            });
        }
    </script>
</body>
</html>
