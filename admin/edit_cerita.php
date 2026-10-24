<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
$ceritaObject = new Cerita($databaseConnection);
$errors = [];

$ceritaId = (int) ($_GET['id'] ?? 0);
$stmt = $databaseConnection->prepare("SELECT * FROM cerita WHERE id = ?");
$stmt->execute([$ceritaId]);
$cerita = $stmt->fetch();

if (!$cerita) {
    redirectTo('/admin/cerita.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tipe = $_POST['tipe'] ?? 'novel';
    $status = $_POST['status'] ?? 'ongoing';
    $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;
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
        $stmt = $databaseConnection->prepare("
            UPDATE cerita SET judul = ?, author = ?, deskripsi = ?, tipe = ?, status = ?, cover_image = ?, is_recommended = ?
            WHERE id = ?
            ");
            $stmt->execute([$judul, $author, $deskripsi, $tipe, $status, $coverImage, $is_recommended, $ceritaId]);
            redirectTo('/admin/cerita.php');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Cerita</title>
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
                    <a href="index.php">
                        <span class="material-symbols-rounded">dashboard</span>
                        Dashboard
                    </a>
                </li>
                <li class="active">
                    <a href="cerita.php">
                        <span class="material-symbols-rounded">menu_book</span>
                        Kelola Cerita
                    </a>
                </li>
                <li>
                    <a href="recommended.php">
                        <span class="material-symbols-rounded">recommend</span>
                        Recommended
                    </a>
                </li>
                <li>
                    <a href="new-releases.php">
                        <span class="material-symbols-rounded">perm_media</span>
                        New Releases
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
            <h1>Kelola Cerita</h1>
            <a href="cerita.php" style="color:#94A3B8; font-size:14px;">← Kembali</a>
        </div>

        <?php if ($errors): ?>
            <div style="background:#EF4444; padding:12px; border-radius:8px; margin-bottom:16px;">
                <?php foreach ($errors as $error): ?>
                    <p><?= escapeHtml($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="admin-form" style="flex-direction:column;">
            <div class="form-group">
                <label>Judul</label>
                <input type="text" name="judul" value="<?= escapeHtml($_POST['judul'] ?? $cerita['judul']) ?>">
            </div>
            <div class="form-group">
                <label>Author</label>
                <input type="text" name="author" value="<?= escapeHtml($_POST['author'] ?? $cerita['author']) ?>">
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="4" style="padding:10px; border-radius:8px; border:1px solid #334155; background:#0F172A; color:#F8FAFC;"><?= escapeHtml($_POST['deskripsi'] ?? $cerita['deskripsi']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Tipe</label>
                <select name="tipe">
                    <option value="novel" <?= ($cerita['tipe'] === 'novel') ? 'selected' : '' ?>>Novel</option>
                    <option value="komik" <?= ($cerita['tipe'] === 'komik') ? 'selected' : '' ?>>Komik</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="ongoing" <?= ($cerita['status'] === 'ongoing') ? 'selected' : '' ?>>Ongoing</option>
                    <option value="completed" <?= ($cerita['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
                 <div class="form-group">
                    <label>Rekomendasi</label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; background:#1E293B; padding:10px; border-radius:8px; border:1px solid #334155;">
                    <input type="checkbox" name="is_recommended" <?= ($cerita['is_recommended'] == 1) ? 'checked' : '' ?>>
                        Tampilkan di bagian "Yang Mungkin Anda Suka"
            </label>
            </div>
            <div class="form-group">
                <label>Cover Image</label>
                <?php if ($cerita['cover_image']): ?>
                    <img src="../assets/data/images/<?= escapeHtml($cerita['cover_image']) ?>" 
                         width="80" style="border-radius:8px; margin-bottom:8px; display:block;">
                <?php endif; ?>
                <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
                <p style="font-size:12px; color:#94A3B8;">Kosongkan jika tidak ingin ganti gambar. Maks 2MB.</p>
            </div>
            <button type="submit" class="btn-approve" style="width:fit-content;">Simpan</button>
        </form>
    </main>

</div>

</body>
</html>