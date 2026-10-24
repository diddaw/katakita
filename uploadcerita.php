<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('/login.php');
}

$ceritaObject = new Cerita($databaseConnection);
$genreList = $databaseConnection->query("SELECT * FROM genre ORDER BY nama ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tipe = $_POST['tipe'] ?? 'novel';
    $harga = (int) ($_POST['harga'] ?? 0);
    $genreIds = $_POST['genre_id'] ?? [];

    if (!Validator::required($judul)) {
        $errors[] = 'Judul wajib diisi.';
    }
    if (!Validator::required($author)) {
        $errors[] = 'Author wajib diisi.';
    }

    $coverImage = uploadCoverImage($_FILES['cover_image'] ?? [], $errors);
    $pdfFile = null;

    if (!$errors) {
        if (!empty($_FILES['pdf_file']['name'])) {
            $pdfExt = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
            if ($pdfExt !== 'pdf') {
                $errors[] = 'File harus berformat PDF.';
            } elseif ($_FILES['pdf_file']['size'] > 20 * 1024 * 1024) {
                $errors[] = 'Ukuran PDF maksimal 20MB.';
            } else {
                $pdfFile = uniqid('cerita_') . '.pdf';
                move_uploaded_file($_FILES['pdf_file']['tmp_name'], __DIR__ . '/assets/data/pdf/' . $pdfFile);
            }
        } else {
            $errors[] = 'File PDF wajib diupload.';
        }
    }

    if (!$errors) {
        $ceritaObject->create([
            'user_id' => $_SESSION['user_id'],
            'judul' => $judul,
            'author' => $author,
            'deskripsi' => $deskripsi,
            'cover_image' => $coverImage,
            'pdf_file' => $pdfFile,
            'harga' => $harga,
            'tipe' => $tipe,
        ]);

        $ceritaId = $databaseConnection->lastInsertId();
        foreach ($genreIds as $genreId) {
            $stmtGenre = $databaseConnection->prepare("INSERT INTO cerita_genre (cerita_id, genre_id) VALUES (?, ?)");
            $stmtGenre->execute([$ceritaId, (int) $genreId]);
        }

        redirectTo('/profil.php');
    }
}

require 'includes/header.php';
?>
<body>
<?php require 'includes/navbar.php'; ?>

<section style="max-width:600px; margin:40px auto; padding:0 20px;">
    <h2>Upload Cerita</h2>

    <?php if ($errors): ?>
        <div style="background:#EF4444; padding:12px; border-radius:8px; margin-bottom:16px;">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:14px;">
        <div class="form-group">
            <label>Judul</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Author</label>
            <input type="text" name="author" value="<?= htmlspecialchars($_POST['author'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="4"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Tipe</label>
            <select name="tipe">
                <option value="novel">Novel</option>
                <option value="komik">Komik</option>
            </select>
        </div>
        <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" min="0" value="<?= htmlspecialchars($_POST['harga'] ?? '0') ?>">
        </div>
        <div class="form-group">
            <label>Genre</label>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <?php foreach ($genreList as $genre): ?>
                    <label style="display:flex; align-items:center; gap:6px;">
                        <input type="checkbox" name="genre_id[]" value="<?= $genre['id'] ?>">
                        <?= htmlspecialchars($genre['nama']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group">
            <label>Cover Image</label>
            <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="form-group">
            <label>File PDF</label>
            <input type="file" name="pdf_file" accept=".pdf">
        </div>
        <button type="submit" class="btn-approve">Upload</button>
    </form>
</section>

<?php require 'includes/footer.php'; ?>
</body>
</html>