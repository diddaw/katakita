<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$ceritaObject = new Cerita($databaseConnection);
$userId = (int) $_SESSION['user_id'];

// Mengambil riwayat transaksi yang dilakukan oleh user yang sedang login sebagai pembeli
$riwayatTransaksi = $ceritaObject->getTransaksiByBuyer($userId);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pembelian</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>

<div class="admin-container">
    <aside class="admin-sidebar">
        <div class="admin-logo"><h2>Novel<span>&</span>Komik</h2></div>
        <nav>
            <ul>
                <li><a href="/codingpenyet/user/dashboard.php">
                    <span class="material-symbols-rounded">dashboard</span>
                    Dashboard
                </a></li>
                <li><a href="/codingpenyet/user/upload.php">
                    <span class="material-symbols-rounded">upload</span>
                    Upload Cerita
                </a></li>
                <li class="active"><a href="/codingpenyet/user/riwayat_transaksi.php">
                    <span class="material-symbols-rounded">receipt_long</span>
                    Riwayat Pembelian
                </a></li>
                <li><a href="/codingpenyet/user/penjualan.php">
                    <span class="material-symbols-rounded">payments</span>
                    Dashboard Penjualan
                </a></li>
                <li><a href="/codingpenyet/profile.php">
                    <span class="material-symbols-rounded">account_circle</span>
                    Profile
                </a></li>
                <li><a href="/codingpenyet/logout.php">
                    <span class="material-symbols-rounded">logout</span>
                    Logout
                </a></li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Riwayat Pembelian</h1>
            <p style="color:#6B7280;">Daftar chapter atau novel yang pernah kamu beli.</p>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cerita</th>
                    <th>Chapter</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$riwayatTransaksi): ?>
                    <tr><td colspan="5" style="text-align:center; color:#6B7280;">Belum ada riwayat pembelian.</td></tr>
                <?php else: ?>
                    <?php foreach ($riwayatTransaksi as $t): ?>
                    <tr>
                        <td><?= escapeHtml($t['judul_cerita']) ?></td>
                        <td>Chapter <?= $t['nomor_chapter'] ?></td>
                        <td>Rp <?= number_format($t['harga'], 0, ',', '.') ?></td>
                        <td>
                            <span class="status-<?= escapeHtml($t['status']) ?>" style="padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #E5E7EB; color: #374151;">
                                <?= escapeHtml($t['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>

</body>
</html>