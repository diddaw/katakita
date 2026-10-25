<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'user'; 
    if ($role === 'admin') {
        header("Location: admin/index.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identitas = trim($_POST['identitas'] ?? '');
    $password = $_POST['password'] ?? '';
    $loginSuccess = false;

    $stmtUser = $databaseConnection->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmtUser->execute([$identitas, $identitas]);
    $user = $stmtUser->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role']; 
        $_SESSION['username'] = $user['username'];
        
        $loginSuccess = true;
        if ($user['role'] === 'admin') {
            header("Location: admin/index.php");
            exit;
        } else {
            header("Location: index.php");
            exit;
        }
    }

    if (!$loginSuccess) {
        $stmtAdmin = $databaseConnection->prepare("SELECT * FROM admins WHERE email = ? OR username = ?");
        $stmtAdmin->execute([$identitas, $identitas]);
        $admin = $stmtAdmin->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = (int) $admin['id'];
            $_SESSION['role'] = 'admin'; 
            $_SESSION['username'] = $admin['username'];
            
            $loginSuccess = true;
            header("Location: admin/index.php");
            exit;
        }
    }

    if (!$loginSuccess) {
        $errors[] = 'Username/Email atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Akun</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #F9FAFB; color: #111827; }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="auth-wrapper">
    <div class="auth-box">
        <h2>Login Akun</h2>

        <?php if ($errors): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Email atau Username</label>
                <input type="text" name="identitas" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-auth">Login</button>
        </form>

        <p class="auth-link">Belum punya akun? <a href="register.php">Daftar</a></p>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>