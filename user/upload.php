<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$ceritaObject = new Cerita($databaseConnection);
$errors = [];

$genreList = $databaseConnection->query("SELECT * FROM genre ORDER BY nama ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tipe = $_POST['tipe'] ?? 'novel';
    $harga = (int)($_POST['harga'] ?? 0);

    if (!Validator::required($judul)) {
        $errors[] = 'Judul wajib diisi.';
    }
    if (!Validator::required($author)) {
        $errors[] = 'Author wajib diisi.';
    }

    if (!$errors) {
        $coverImage = uploadCoverImage($_FILES['cover_image'] ?? [], $errors);
    }

    $genreIds = $_POST['genre_id'] ?? [];

    if (!$errors) {
        $ceritaObject->create([
            'user_id' => (int) $_SESSION['user_id'],
            'judul' => $judul,
            'author' => $author,
            'deskripsi' => $deskripsi,
            'cover_image' => $coverImage,
            'tipe' => $tipe,
            'harga' => $harga,
        ]);

        $ceritaId = $databaseConnection->lastInsertId();
       $genreIdsUnique = array_unique($genreIds);
       foreach ($genreIdsUnique as $genreId) {
            $stmtGenre = $databaseConnection->prepare("INSERT IGNORE INTO cerita_genre (cerita_id, genre_id) VALUES (?, ?)");
            $stmtGenre->execute([$ceritaId, (int) $genreId]);
        }

        redirectTo('/user/dashboard.php?uploaded=1');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Cerita</title>
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
                    <a href="/codingpenyet/user/dashboard.php">
                        <span class="material-symbols-rounded">dashboard</span>
                        Dashboard
                    </a>
                </li>
                <li class="active">
                    <a href="/codingpenyet/user/upload.php">
                        <span class="material-symbols-rounded">upload</span>
                        Upload Cerita
                    </a>
                </li>
                <li>
                    <a href="/codingpenyet/user/penjualan.php">
                        <span class="material-symbols-rounded">payments</span>
                        Dashboard Penjualan
                    </a>
                </li>
                <li>
                    <a href="/codingpenyet/user/rekening.php">
                        <span class="material-symbols-rounded">account_balance</span>
                        Rekening Pembayaran
                    </a>
                </li>
               <li>
                    <a href="/codingpenyet/profile.php">
                     <span class="material-symbols-rounded">account_circle</span>
                     Profile
                     </a>
                </li>
                <li>
                    <a href="/codingpenyet/logout.php">
                        <span class="material-symbols-rounded">logout</span>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <div>
                <h1>Upload Cerita Baru</h1>
                <p>Cerita akan berstatus pending sampai admin approve.</p>
            </div>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= escapeHtml($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="admin-form-wrapper">
            <form method="post" enctype="multipart/form-data" class="admin-form" style="flex-direction:column;">
                <div class="form-group">
                    <label>Judul</label>
                    <input type="text" name="judul" value="<?= escapeHtml($_POST['judul'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" name="author" value="<?= escapeHtml($_POST['author'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="4"><?= escapeHtml($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Tipe</label>
                    <select name="tipe">
                        <option value="novel">Novel</option>
                        <option value="komik">Komik</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Genre</label>
                    <button type="button" onclick="document.getElementById('genre-list-user').style.display = document.getElementById('genre-list-user').style.display === 'none' ? 'flex' : 'none'" 
                        class="btn-pilih-genre-custom">
                        Pilih Genre 
                    </button>
                    <div id="genre-list-user" style="display:none; flex-wrap:wrap; gap:8px; margin-top:8px;">
                        <?php foreach ($genreList as $genre): ?>
                            <label class="genre-checkbox-item">
                                <input type="checkbox" name="genre_id[]" value="<?= $genre['id'] ?>">
                                <?= escapeHtml($genre['nama']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p style="font-size:12px; color:#6B7280; margin-top:6px;">Bisa pilih lebih dari 1 genre.</p>
                </div> 

                <div class="form-group">
                    <label>Cover Image</label>
                    <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
                    <p class="form-hint">Format JPG, PNG, atau WEBP. Maksimal 2MB.</p>
                </div>
                <button type="submit" class="btn-approve" style="width:fit-content;">Upload</button>
            </form>
        </div>
    </main>

</div>

</body>
</html>