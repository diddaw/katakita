<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'upload') {
        $judul = trim($_POST['judul'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tipe = $_POST['tipe'] ?? 'novel';
        $status = $_POST['status'] ?? 'ongoing';
        $genreIds = $_POST['genre_id'] ?? [];
        $errors = [];

        $coverImage = uploadCoverImage($_FILES['cover_image'] ?? [], $errors);
        if (!$errors) {
            $stmt = $databaseConnection->prepare("
                INSERT INTO cerita (judul, author, deskripsi, cover_image, tipe, status, koleksi_spesial)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$judul, $author, $deskripsi, $coverImage, $tipe, $status]);

            $ceritaId = $databaseConnection->lastInsertId();
            foreach ($genreIds as $genreId) {
                $stmtGenre = $databaseConnection->prepare("INSERT INTO cerita_genre (cerita_id, genre_id) VALUES (?, ?)");
                $stmtGenre->execute([$ceritaId, (int) $genreId]);
            }
        }
        header('Location: koleksi_spesial.php');
        exit;
    }
    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        $stmt = $databaseConnection->prepare("UPDATE cerita SET koleksi_spesial = 0 WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: koleksi_spesial.php');
        exit;
    }
    if ($action === 'add_existing') {
        $id = (int) $_POST['cerita_id'];
        $stmt = $databaseConnection->prepare("UPDATE cerita SET koleksi_spesial = 1 WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: koleksi_spesial.php');
        exit;
    }
}
$featuredList = $databaseConnection->query("
    SELECT * FROM cerita WHERE koleksi_spesial = 1 ORDER BY created_at DESC
")->fetchAll();

$genreList = $databaseConnection->query("SELECT * FROM genre ORDER BY nama ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Koleksi Spesial</title>
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
                <li><a href="index.php"><span class="material-symbols-rounded">dashboard</span> Dashboard</a></li>
                <li><a href="cerita.php"><span class="material-symbols-rounded">menu_book</span> Kelola Cerita</a></li>
                <li><a href="new_releases.php"><span class="material-symbols-rounded">new_releases</span> New Releases</a></li>
                <li><a href="slides.php"><span class="material-symbols-rounded">slideshow</span> Kelola Slides</a></li>
                <li><a href="recommended.php"><span class="material-symbols-rounded">recommend</span> Recommended</a></li>
                <li class="active"><a href="koleksi_spesial.php"><span class="material-symbols-rounded">star</span> Koleksi Spesial</a></li>
                <li><a href="logout.php"><span class="material-symbols-rounded">logout</span> Logout</a></li>
            </ul>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Kelola Koleksi Spesial</h1>
        </div>
         <div class="admin-form-wrapper" style="margin-bottom:24px;">
         <h3>Tambah dari Cerita yang Sudah Ada</h3>
         <form method="post" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
         <input type="hidden" name="action" value="add_existing">
         <div class="form-group">
            <label>Pilih Cerita</label>
            <select name="cerita_id">
                <?php 
                $allCerita = $databaseConnection->query("SELECT id, judul FROM cerita WHERE koleksi_spesial = 0 ORDER BY judul ASC")->fetchAll();
                foreach ($allCerita as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= escapeHtml($c['judul']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-approve">Tambah</button>
    </form>
</div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($featuredList as $item): ?>
                <tr>
                    <td><img src="../assets/data/images/<?= escapeHtml($item['cover_image']) ?>" width="50" style="border-radius:4px;"></td>
                    <td><?= escapeHtml($item['judul']) ?></td>
                    <td><?= escapeHtml($item['author']) ?></td>
                    <td><?= $item['status'] === 'ongoing' ? 'Berlanjut' : 'Tamat' ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn-delete" onclick="return confirm('Hapus dari Koleksi Spesial?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>