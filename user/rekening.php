<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$userObject = new User($databaseConnection);
$user = $userObject->findById((int) $_SESSION['user_id']);

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bankNama = trim($_POST['bank_nama'] ?? '');
    $bankNorek = trim($_POST['bank_norek'] ?? '');
    $bankPemilik = trim($_POST['bank_pemilik'] ?? '');

    if ($bankNama === '' || $bankNorek === '' || $bankPemilik === '') {
        $errors[] = 'Semua field wajib diisi.';
    } else {
        $userObject->updateRekening((int) $_SESSION['user_id'], $bankNama, $bankNorek, $bankPemilik);
        $user = $userObject->findById((int) $_SESSION['user_id']);
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekening Pembayaran</title>
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
                <li><a href="dashboard.php">
                    <span class="material-symbols-rounded">dashboard</span>
                    Dashboard
                </a></li>
                <li><a href="upload.php">
                    <span class="material-symbols-rounded">upload</span>
                    Upload Cerita
                </a></li>
                <li><a href="riwayat_transaksi.php">
                    <span class="material-symbols-rounded">receipt_long</span>
                    Riwayat Pembelian
                </a></li>
                <li><a href="penjualan.php">
                    <span class="material-symbols-rounded">payments</span>
                    Dashboard Penjualan
                </a></li>
                <li class="active"><a href="rekening.php">
                    <span class="material-symbols-rounded">account_balance</span>
                    Rekening Pembayaran
                </a></li>
                <li><a href="../profile.php">
                    <span class="material-symbols-rounded">account_circle</span>
                    Profile
                </a></li>
                <li><a href="../logout.php">
                    <span class="material-symbols-rounded">logout</span>
                    Logout
                </a></li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Rekening Pembayaran</h1>
            <p style="color:#94A3B8;">Rekening ini ditampilkan ke pembeli saat mereka membeli chapter dari cerita yang kamu upload.</p>
        </div>

        <?php if ($success): ?>
            <div style="background:#22C55E; padding:12px; border-radius:8px; margin-bottom:16px;">
                Rekening berhasil disimpan.
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div style="background:#EF4444; padding:12px; border-radius:8px; margin-bottom:16px;">
                <?php foreach ($errors as $error): ?>
                    <p><?= escapeHtml($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="admin-form-wrapper">
            <form method="post" class="admin-form" style="flex-direction:column;">
                <div class="form-group">
                    <label>Nama Bank / E-wallet</label>
                    <input type="text" name="bank_nama" placeholder="contoh: BCA, Dana, OVO" value="<?= escapeHtml($user['bank_nama'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Nomor Rekening / Nomor HP</label>
                    <input type="text" name="bank_norek" value="<?= escapeHtml($user['bank_norek'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Nama Pemilik Rekening</label>
                    <input type="text" name="bank_pemilik" value="<?= escapeHtml($user['bank_pemilik'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-approve" style="width:fit-content;">Simpan Rekening</button>
            </form>
        </div>
    </main>

</div>

</body>
</html>