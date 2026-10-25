<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$genreList = $databaseConnection->query("SELECT * FROM genre ORDER BY nama ASC")->fetchAll();
$ceritaObject = new Cerita($databaseConnection);
$errors = [];

$ceritaId = (int) ($_GET['id'] ?? 0);
$userId = (int) $_SESSION['user_id'];
$cerita = $ceritaObject->findByIdForUser($ceritaId, $userId);

if (!$cerita) {
    redirectTo('/user/dashboard.php');
}
$stmtSelectedGenres = $databaseConnection->prepare("SELECT genre_id FROM cerita_genre WHERE cerita_id = ?");
$stmtSelectedGenres->execute([$ceritaId]);
$selectedGenres = $stmtSelectedGenres->fetchAll(PDO::FETCH_COLUMN) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tipe = $_POST['tipe'] ?? 'novel';
    $genreIds = $_POST['genre_id'] ?? [];
    $coverImage = $cerita['cover_image'];

    if (!Validator::required($judul)) {
        $errors[] = 'Judul wajib diisi.';
    }
    if (!Validator::required($author)) {
        $errors[] = 'Author wajib diisi.';
    }

    if (!$errors) {
        $uploadedImage = uploadCoverImage($_FILES['cover_image'] ?? [], $errors);
        if ($uploadedImage) {
            $coverImage = $uploadedImage;
        }
    }

    if (!$errors) {
        $ceritaObject->updateByUser($ceritaId, $userId, [
            'judul' => $judul,
            'author' => $author,
            'deskripsi' => $deskripsi,
            'cover_image' => $coverImage,
            'tipe' => $tipe,
        ]);

        // Update relasi genre cerita
        $ceritaObject->updateGenres($ceritaId, $genreIds);

        redirectTo('/user/dashboard.php?updated=1');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Cerita</title>
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
                    <a href="/katakita/user/dashboard.php">
                        <span class="material-symbols-rounded">dashboard</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="/katakita/user/upload.php">
                        <span class="material-symbols-rounded">upload</span>
                        Upload Cerita
                    </a>
                </li>
                <li>
                    <a href="/katakita/user/riwayat_transaksi.php">
                        <span class="material-symbols-rounded">receipt_long</span>
                        Riwayat Pembelian
                    </a>
                </li>
                <li>
                    <a href="/katakita/user/penjualan.php">
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
                    <a href="/katakita/logout.php">
                        <span class="material-symbols-rounded">logout</span>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Edit Cerita</h1>
            <a href="/katakita/user/dashboard.php" style="color:#6B7280; font-size:14px; text-decoration:none;">← Kembali</a>
        </div>

        <?php if ($errors): ?>
            <div style="background:#FEE2E2; color:#991B1B; padding:12px; border-radius:8px; margin-bottom:16px; border:1px solid #FCA5A5;">
                <?php foreach ($errors as $error): ?>
                    <p><?= escapeHtml($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="admin-form" style="flex-direction:column;">
            <div class="form-group">
                <label>Judul</label>
                <input type="text" name="judul" value="<?= escapeHtml($_POST['judul'] ?? $cerita['judul']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Author</label>
                <input type="text" name="author" value="<?= escapeHtml($_POST['author'] ?? $cerita['author']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="4" style="padding:10px 14px; border-radius:8px; border:1px solid #D1D5DB; background:#FFFFFF; color:#111827; width:100%; outline:none;"><?= escapeHtml($_POST['deskripsi'] ?? $cerita['deskripsi']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Tipe</label>
                <select name="tipe">
                    <option value="novel" <?= ($cerita['tipe'] === 'novel') ? 'selected' : '' ?>>Novel</option>
                    <option value="komik" <?= ($cerita['tipe'] === 'komik') ? 'selected' : '' ?>>Komik</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Cover Image</label>
                <?php if ($cerita['cover_image']): ?>
                    <img src="/katakita/assets/data/images/<?= escapeHtml($cerita['cover_image']) ?>" 
                         width="80" style="border-radius:8px; margin-bottom:8px; display:block;">
                <?php endif; ?>
                <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
                <p style="font-size:12px; color:#6B7280; margin-top:4px;">Kosongkan jika tidak ingin mengganti gambar. Maks 2MB.</p>
            </div>
            
            <div class="form-group">
                <label>Genre</label>
                <button type="button" onclick="document.getElementById('genre-list').style.display = document.getElementById('genre-list').style.display === 'none' ? 'flex' : 'none'" 
                        style="background:#FFFFFF; color:#111827; border:1px solid #D1D5DB; padding:8px 16px; border-radius:8px; cursor:pointer; margin-top:6px; font-weight:600;">
                    Pilih Genre ▼
                </button>
                <div id="genre-list" style="display:none; flex-wrap:wrap; gap:8px; margin-top:8px;">
                    <?php foreach ($genreList as $genre): ?>
                        <?php 
                            // Tentukan apakah genre ini sebelumnya sudah dipilih
                            $isCheck = in_array($genre['id'], $_POST['genre_id'] ?? $selectedGenres); 
                        ?>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; background:#FFFFFF; color:#374151; padding:6px 12px; border-radius:8px; border:1px solid #D1D5DB; transition: 0.2s;">
                            <input type="checkbox" name="genre_id[]" value="<?= $genre['id'] ?>" style="accent-color: #FF544C;" <?= $isCheck ? 'checked' : '' ?>>
                            <?= escapeHtml($genre['nama']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p style="font-size:12px; color:#6B7280; margin-top:6px;">Bisa pilih lebih dari 1 genre.</p>
            </div>
            
            <button type="submit" class="btn-approve" style="width:fit-content; background:#FF544C; color:#FFFFFF; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-weight:600; margin-top:12px;">Simpan Perubahan</button>
        </form>
    </main>

</div>

</body>
</html>