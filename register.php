<?php
require_once __DIR__ . '/includes/bootstrap.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (!Validator::required($username)) {
        $errors[] = 'Username wajib diisi.';
    }
    if (!Validator::email($email)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (!Validator::required($no_telepon)) {
        $errors[] = 'No. telepon wajib diisi.';
    }
    if (!Validator::minLength($password, 6)) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $konfirmasi) {
        $errors[] = 'Password tidak cocok!';
    }

    if (!$errors) {
        $userObject = new User($databaseConnection);
        $berhasil = $userObject->register($username, $email, $no_telepon, $password);

        if ($berhasil) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $errors[] = 'Username atau email sudah dipakai!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        <h2>Daftar Akun</h2>

        <?php if ($errors): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert error"><?= escapeHtml($error) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= escapeHtml($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= escapeHtml($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= escapeHtml($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="no_telepon" value="<?= escapeHtml($_POST['no_telepon'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="konfirmasi" required>
            </div>
            <button type="submit" class="btn-auth">Daftar</button>
        </form>

        <p class="auth-link">Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>