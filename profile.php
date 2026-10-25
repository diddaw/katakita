<?php
require_once __DIR__ . '/includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$userObject = new User($databaseConnection);
$user = $userObject->findById((int) $_SESSION['user_id']);

$ceritaObject = new Cerita($databaseConnection);
$ceritaList = $ceritaObject->getByUser((int) $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="profile-wrapper">
    <div class="profile-card">
        <div class="profile-avatar">
            <?php if (!empty($user['foto_profil'])): ?>
                <img src="assets/data/images/profil/<?= htmlspecialchars($user['foto_profil']) ?>" alt="Foto Profil">
            <?php else: ?>
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <h2><?= htmlspecialchars($user['username']) ?></h2>
        <p><?= htmlspecialchars($user['email']) ?></p>
        <p style="margin-top:6px; color:#FF544C; font-weight:600; text-transform:capitalize;"><?= htmlspecialchars($user['role']) ?></p>
    </div>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <div class="profile-menu" style="border: 2px solid #FF544C;">
        <div class="profile-menu-title" style="color: #FF544C;">Admin Only</div>
        <a href="admin/index.php">
            <span class="material-symbols-rounded">admin_panel_settings</span>
            <div class="menu-info">
                Dashboard Admin
                <p>Masuk ke panel kontrol web</p>
            </div>
            <span class="material-symbols-rounded menu-arrow">chevron_right</span>
        </a>
    </div>
    <?php endif; ?>

    <div class="profile-menu">
        <div class="profile-menu-title">Aktivitas</div>
        <a href="user/dashboard.php">
            <span class="material-symbols-rounded">menu_book</span>
            <div class="menu-info">
                Cerita Saya
                <p>Kelola cerita yang sudah diupload</p>
            </div>
            <span class="cerita-count"><?= count($ceritaList) ?></span>
            <span class="material-symbols-rounded menu-arrow">chevron_right</span>
        </a>
        <a href="user/upload.php">
            <span class="material-symbols-rounded">upload</span>
            <div class="menu-info">
                Upload Cerita Baru
                <p>Tambah novel atau komik baru</p>
            </div>
            <span class="material-symbols-rounded menu-arrow">chevron_right</span>
        </a>
    </div>

    <div class="profile-menu">
        <div class="profile-menu-title">Akun dan Privasi</div>
        <a href="settings.php">
            <span class="material-symbols-rounded">settings</span>
            <div class="menu-info">
                Pengaturan
                <p>Edit username, email, foto profil</p>
            </div>
            <span class="material-symbols-rounded menu-arrow">chevron_right</span>
        </a>
    </div>

    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<script src="script.js"></script>
</body>
</html>