<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $ceritaId = (int) $_POST['cerita_id'];
        $urutan = (int) $_POST['urutan'];
        $stmt = $databaseConnection->prepare("INSERT INTO recommended (cerita_id, urutan, aktif) VALUES (?, ?, 1)");
        $stmt->execute([$ceritaId, $urutan]);
    }
    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        $stmt = $databaseConnection->prepare("DELETE FROM recommended WHERE id = ?");
        $stmt->execute([$id]);
    }
    if ($action === 'toggle') {
        $id = (int) $_POST['id'];
        $aktif = (int) $_POST['aktif'];
        $stmt = $databaseConnection->prepare("UPDATE recommended SET aktif = ? WHERE id = ?");
        $stmt->execute([$aktif, $id]);
    }

    redirectTo('/admin/recommended.php');
}
$recommended = $databaseConnection->query("
    SELECT recommended.*, cerita.judul, cerita.cover_image
    FROM recommended
    JOIN cerita ON cerita.id = recommended.cerita_id
    ORDER BY recommended.urutan ASC
")->fetchAll();

$ceritaList = $databaseConnection->query("
    SELECT id, judul FROM cerita ORDER BY judul ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Recommended</title>
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
                <li>
                    <a href="cerita.php">
                        <span class="material-symbols-rounded">menu_book</span>
                        Kelola Cerita
                    </a>
                </li>
                <li>
                    <a href="slides.php">
                        <span class="material-symbols-rounded">slideshow</span>
                        Kelola Slides
                    </a>
                </li>
                <li class="active">
                    <a href="recommended.php">
                        <span class="material-symbols-rounded">recommend</span>
                        Kelola Recommended
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
        </div>
        <div class="admin-form-wrapper">
            <h3>Tambah Recommended Baru</h3>
            <form method="post" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Pilih Cerita</label>
                    <select name="cerita_id">
                        <?php foreach ($ceritaList as $cerita): ?>
                            <option value="<?= $cerita['id'] ?>"><?= escapeHtml($cerita['judul']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="urutan" value="0" min="0">
                </div>
                <button type="submit" class="btn-approve">Tambah</button>
            </form>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recommended as $item): ?>
                <tr>
                    <td>
                        <img src="../assets/data/images/<?= escapeHtml($item['cover_image']) ?>" 
                             width="50" style="border-radius:4px;">
                    </td>
                    <td><?= escapeHtml($item['judul']) ?></td>
                    <td><?= $item['urutan'] ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="aktif" value="<?= $item['aktif'] ? 0 : 1 ?>">
                            <button type="submit" class="<?= $item['aktif'] ? 'btn-approve' : 'btn-reject' ?>">
                                <?= $item['aktif'] ? 'Aktif' : 'Nonaktif' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn-delete"
                                onclick="return confirm('Hapus recommended ini?')">Hapus</button>
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