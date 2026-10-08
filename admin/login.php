<?php
require_once __DIR__ . '/../db_init.php';

// Dynamic base URL for asset loading so styles never break
$base_path = '../';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_full_name'] = $admin['full_name'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid administrator username or password!";
        }
    } else {
        $error = "Please fill in both username and password fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Private Staff Portal - Apex Model Secondary School</title>
    <meta name="theme-color" content="#091b33">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
    <link rel="icon" type="image/png" href="<?= $base_path ?>assets/icons/icon-192.png">
    <style>
        body {
            background-color: var(--primary-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }
        .login-card-wrap {
            background-color: var(--surface-white);
            border-radius: var(--radius-lg);
            padding: 2.75rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.15);
        }
        .login-card-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-card-header img {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem auto;
        }
        .login-card-header h1 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--primary-navy);
        }
        .login-card-header p {
            font-size: 0.825rem;
            color: var(--text-muted);
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="login-card-wrap">
        <div class="login-card-header">
            <img src="<?= $base_path ?>assets/images/logo.png" alt="Apex Logo">
            <h1>Apex Staff Control Panel</h1>
            <p>Apex Model Secondary School Executive Administration</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 0.85rem; border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.875rem; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div>
                <label for="username" style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;">Admin Username</label>
                <input type="text" name="username" id="username" class="form-control" style="width: 100%; padding: 0.7rem 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); outline: none;" placeholder="admin" required autofocus>
            </div>

            <div>
                <label for="password" style="font-size: 0.8125rem; font-weight: 700; color: var(--primary-navy); display: block; margin-bottom: 0.35rem;">Security Password</label>
                <input type="password" name="password" id="password" class="form-control" style="width: 100%; padding: 0.7rem 0.9rem; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); outline: none;" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem; height: 46px;">
                Authenticate & Login →
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.75rem; font-size: 0.8rem; color: var(--text-muted);">
            <span>Default Credential: <strong>admin</strong> / <strong>admin123</strong></span>
            <div style="margin-top: 0.5rem;"><a href="<?= $base_path ?>index.php" style="color: var(--accent-blue);">← Return to Public Website</a></div>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js').catch(err => console.log('Admin SW Registration:', err));
            });
        }
    </script>
</body>
</html>
