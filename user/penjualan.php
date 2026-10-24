<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$ceritaObject = new Cerita($databaseConnection);
$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaksiId = (int) $_POST['id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $ceritaObject->updateTransaksiStatusBySeller($transaksiId, $userId, 'approved');
    }
    if ($action === 'reject') {
        $ceritaObject->updateTransaksiStatusBySeller($transaksiId, $userId, 'rejected');
    }

    header('Location: penjualan.php');
    exit;
}

$pendingList = $ceritaObject->getPendingTransaksiBySeller($userId);
$semuaTransaksi = $ceritaObject->getTransaksiBySeller($userId);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Penjualan</title>
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
                <li><a href="/codingpenyet/user/riwayat_transaksi.php">
                    <span class="material-symbols-rounded">receipt_long</span>
                    Riwayat Pembelian
                </a></li>
                <li class="active"><a href="/codingpenyet/user/penjualan.php">
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
            <h1>Dashboard Penjualan</h1>
            <p style="color:#94A3B8;">Konfirmasi pembayaran untuk cerita yang kamu upload.</p>
        </div>

        <h3 style="margin-bottom:12px;">Menunggu Konfirmasi</h3>
        <table class="admin-table" style="margin-bottom:32px;">
            <thead>
                <tr>
                    <th>Pembeli</th>
                    <th>Cerita</th>
                    <th>Chapter</th>
                    <th>Harga</th>
                    <th>Bukti</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$pendingList): ?>
                    <tr><td colspan="6" style="text-align:center; color:#94A3B8;">Tidak ada transaksi pending.</td></tr>
                <?php else: ?>
                    <?php foreach ($pendingList as $t): ?>
                    <tr>
                        <td><?= escapeHtml($t['pembeli']) ?></td>
                        <td><?= escapeHtml($t['judul_cerita']) ?></td>
                        <td>Chapter <?= $t['nomor_chapter'] ?></td>
                        <td>Rp <?= number_format($t['harga'], 0, ',', '.') ?></td>
                        <td>
                            <a href="../assets/data/bukti_transfer/<?= escapeHtml($t['bukti_transfer']) ?>" target="_blank">
                                <img src="../assets/data/bukti_transfer/<?= escapeHtml($t['bukti_transfer']) ?>" width="60" style="border-radius:4px;">
                            </a>
                        </td>
                        <td>
                            <form method="post" style="display:flex; gap:6px;">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button name="action" value="approve" class="btn-approve">Approve</button>
                                <button name="action" value="reject" class="btn-reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h3 style="margin-bottom:12px;">Semua Transaksi</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Pembeli</th>
                    <th>Cerita</th>
                    <th>Chapter</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$semuaTransaksi): ?>
                    <tr><td colspan="6" style="text-align:center; color:#94A3B8;">Belum ada transaksi masuk.</td></tr>
                <?php else: ?>
                    <?php foreach ($semuaTransaksi as $t): ?>
                    <tr>
                        <td><?= escapeHtml($t['pembeli']) ?></td>
                        <td><?= escapeHtml($t['judul_cerita']) ?></td>
                        <td>Chapter <?= $t['nomor_chapter'] ?></td>
                        <td>Rp <?= number_format($t['harga'], 0, ',', '.') ?></td>
                        <td><span class="status-<?= escapeHtml($t['status']) ?>"><?= escapeHtml($t['status']) ?></span></td>
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