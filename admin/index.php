<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>

<div class="admin-container">
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2>Novel<span>&</span>Komik</h2>
        </div>
        <nav>
            <ul>
                <li>
                    <a href="../index.php">
                        <span class="material-symbols-rounded">home</span>
                        Halaman Utama
                    </a>
                </li>
                <li class="active">
                    <a href="index.php">
                        <span class="material-symbols-rounded">dashboard</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="cerita.php">
                        <span class="material-symbols-rounded">menu_book</span>
                         Kelola Cerita
                    </a>
                </li>
                 <li>
                    <a href="new_releases.php">
                        <span class="material-symbols-rounded">perm_media</span>
                         New Releases
                    </a>
                </li>
                <li>
                    <a href="recommended.php">
                        <span class="material-symbols-rounded">recommend</span>
                         Recommended
                    </a>
                </li>
                   <li>
                    <a href="koleksi_spesial.php">
                        <span class="material-symbols-rounded">star</span>
                        Koleksi Spesial
                    </a>
                </li>
                <li>
                    <a href="slides.php">
                        <span class="material-symbols-rounded">slideshow</span>
                        Kelola Slides
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="material-symbols-rounded">logout</span>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Selamat datang, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong>!</p>
        </div>

        <div class="admin-cards">
            <a href="cerita.php" class="admin-card">
                <span class="material-symbols-rounded">menu_book</span>
                <h3>Kelola Cerita</h3>
                <p>Approve, reject, atau hapus cerita</p>
            </a>
            <a href="recommended.php" class="admin-card">
                <span class="material-symbols-rounded">recommend</span>
                <h3>Kelola Recommended</h3>
                <p>Atur cerita yang muncul di "Yang Mungkin Anda Suka"</p>
            </a>
            <a href="new_releases.php" class="admin-card">
                <span class="material-symbols-rounded">perm_media</span>
                <h3>Kelola New Releases</h3>
                <p>Atur cerita yang muncul di "New Releases"</p>
            </a>
            <a href="slides.php" class="admin-card">
                <span class="material-symbols-rounded">slideshow</span>
                <h3>Kelola Slides</h3>
                <p>Atur banner di halaman utama</p>
            </a>
        </div>
    </main>
</div>

</body>
</html>