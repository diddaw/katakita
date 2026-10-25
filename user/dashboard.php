<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$ceritaObject = new Cerita($databaseConnection);
$ceritaList = $ceritaObject->getByUser((int) $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
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
                <li class="active">
                    <a href="dashboard.php">
                        <span class="material-symbols-rounded">dashboard</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="upload.php">
                        <span class="material-symbols-rounded">upload</span>
                        Upload Cerita
                    </a>
                </li>

                <li>
                    <a href="penjualan.php">
                        <span class="material-symbols-rounded">payments</span>
                        Dashboard Penjualan
                    </a>
                </li>
              <li>
                     <a href="/katakita/profile.php">
                      <span class="material-symbols-rounded">account_circle</span>
                     Profile
                  </a>
            </li>
                <li>
                    <a href="../logout.php">
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
            <p style="color:#94A3B8;">Halo, <strong><?= escapeHtml($_SESSION['username']) ?></strong>!</p>
        </div>

        <?php if (isset($_GET['uploaded'])): ?>
            <div style="background:#22C55E; padding:12px; border-radius:8px; margin-bottom:16px;">
                Cerita berhasil diupload! Menunggu approval admin.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div style="background:#22C55E; padding:12px; border-radius:8px; margin-bottom:16px;">
                Cerita berhasil diupdate!
            </div>
        <?php endif; ?>

        <div style="margin-bottom:20px;">
            <a href="upload.php" class="btn-approve" style="text-decoration:none; padding:10px 20px;">
                + Upload Cerita Baru
            </a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$ceritaList): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#94A3B8;">
                            Belum ada cerita yang diupload.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ceritaList as $cerita): ?>
                    <tr>
                        <td>
                            <img src="../assets/data/images/<?= escapeHtml($cerita['cover_image']) ?>" 
                                 width="50" style="border-radius:4px;">
                        </td>
                        <td><?= escapeHtml($cerita['judul']) ?></td>
                        <td><?= escapeHtml($cerita['tipe']) ?></td>
                                                <td>
                            <span class="status-<?= escapeHtml($cerita['approval_status']) ?>">
                                <?= escapeHtml($cerita['approval_status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $cerita['id'] ?>" class="btn-edit">Edit</a>
                            <a href="kelola_chapter.php?id=<?= $cerita['id'] ?>" class="btn-edit">Kelola Chapter</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

</div>

</body>
</html>