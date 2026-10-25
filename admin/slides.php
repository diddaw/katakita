<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload') {
        $judul = trim($_POST['judul'] ?? '');

        if ($judul === '') {
            $errors[] = 'Judul wajib diisi.';
        }

        // Upload gambar
        $coverImage = uploadCoverImage($_FILES['cover_image'] ?? [], $errors);

        if (empty($errors)) {
            try {
                $stmt = $databaseConnection->prepare("
                    INSERT INTO cerita 
                    (judul, author, deskripsi, cover_image, tipe, status, is_recommended, is_new_release, slide_only)
                    VALUES (?, ?, NULL, ?, ?, 'ongoing', 0, 0, 1)
                ");

                $stmt->execute([
                    $judul,
                    'Admin',
                    $coverImage,
                    'novel'
                ]);
                $ceritaId = $databaseConnection->lastInsertId();
                $stmtSlide = $databaseConnection->prepare("
                    INSERT INTO slides
                    (cerita_id, urutan, aktif)
                    VALUES (?, 0, 1)
                ");
                $stmtSlide->execute([$ceritaId]);
                redirectTo('/admin/slides.php');
            } catch (PDOException $e) {
                $errors[] = "Error Database: " . $e->getMessage();
            }
        }
    } 
    elseif ($action === 'add') {
        $ceritaId = (int) $_POST['cerita_id'];
        $urutan = (int) $_POST['urutan']; 
        $stmt = $databaseConnection->prepare("INSERT INTO slides (cerita_id, urutan, aktif) VALUES (?, ?, 1)");
        $stmt->execute([$ceritaId, $urutan]);
        redirectTo('/admin/slides.php');
    } 
    elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        $stmt = $databaseConnection->prepare("DELETE FROM slides WHERE id = ?");
        $stmt->execute([$id]);
        redirectTo('/admin/slides.php');
    } 
    elseif ($action === 'toggle') {
        $id = (int) $_POST['id'];
        $aktif = (int) $_POST['aktif'];
        $stmt = $databaseConnection->prepare("UPDATE slides SET aktif = ? WHERE id = ?");
        $stmt->execute([$aktif, $id]);
        redirectTo('/admin/slides.php');
    }
}
$slides = $databaseConnection->query("
    SELECT slides.*, cerita.judul, cerita.cover_image
    FROM slides
    JOIN cerita ON cerita.id = slides.cerita_id
    ORDER BY slides.urutan ASC
")->fetchAll();
$ceritaList = $databaseConnection->query("
    SELECT id, judul, cover_image FROM cerita ORDER BY judul ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Slides</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>
<div class="admin-container">
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2>Novel<span>&</span>Komik</h2>
        </div>
        <?php if (!empty($errors)): ?>
         <div style="background:#EF4444; padding:12px; border-radius:8px; margin-bottom:16px; color:#fff;">
        <?php foreach ($errors as $error): ?>
            <p style="margin:0; font-weight:bold;">⚠️ <?= escapeHtml($error) ?></p>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
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
                <li class="active">
                    <a href="slides.php">
                        <span class="material-symbols-rounded">slideshow</span>
                        Kelola Slides
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
            <h1>Kelola Slides</h1>
        </div>
        <div class="admin-form-wrapper" style="margin-bottom:24px;">
            <h3>Upload Slides Baru</h3>
            <form method="post" enctype="multipart/form-data" class="admin-form" style="flex-direction:column;">
                <input type="hidden" name="action" value="upload">
                <div class="form-group">
                    <label>Judul</label>
                    <input type="text" name="judul">
                </div>
                <div class="form-group">
                    <label>Cover Image</label>
                    <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
                    <p style="font-size:12px; color:#94A3B8;">Format JPG, PNG, atau WEBP. Maksimal 2MB.</p>
                </div>
                <button type="submit" class="btn-approve" style="width:fit-content;">Upload</button>
            </form>
        </div>
        <div class="admin-form-wrapper">
            <h3>Tambah Slide Baru</h3>
            <form method="post" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Pilih Cerita</label>
                    <select name="cerita_id" id="pilih-cerita" style="width: 100%;">
                    <option value="">-- Ketik atau Pilih Cerita --</option>
           <?php 
                foreach ($ceritaList as $cerita): 
            ?>
        <option value="<?= $cerita['id'] ?>" 
                data-image="../assets/data/images/<?= escapeHtml($cerita['cover_image']) ?>">
            <?= escapeHtml($cerita['judul']) ?>
        </option>
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
                <?php foreach ($slides as $slide): ?>
                <tr>
                    <td>
                        <img src="../assets/data/images/<?= escapeHtml($slide['cover_image']) ?>" 
                             width="50" style="border-radius:4px;">
                    </td>
                    <td><?= escapeHtml($slide['judul']) ?></td>
                    <td><?= $slide['urutan'] ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                            <input type="hidden" name="aktif" value="<?= $slide['aktif'] ? 0 : 1 ?>">
                            <button type="submit" class="<?= $slide['aktif'] ? 'btn-approve' : 'btn-reject' ?>">
                                <?= $slide['aktif'] ? 'Aktif' : 'Nonaktif' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                            <button type="submit" class="btn-delete" 
                                onclick="return confirm('Hapus slide ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
.select2-container--default .select2-selection--single {
    background-color: #0F172A;
    border: 1px solid #334155;
    height: 42px;
    border-radius: 8px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #F8FAFC;
    line-height: 42px;
}
.select2-dropdown {
    background-color: #1E293B;
    border: 1px solid #334155;
}
.select2-container--default .select2-results__option {
    color: #F8FAFC;
}
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
    background-color: #3B82F6;
}
.select2-search--dropdown .select2-search__field {
    background-color: #0F172A;
    color: #F8FAFC;
    border: 1px solid #334155;
    border-radius: 4px;
}
</style>
<script>
$(document).ready(function() {
    function formatCerita(cerita) {
        if (!cerita.id) { return cerita.text; }
        var urlGambar = $(cerita.element).data('image');
        return $(
            '<div style="display: flex; align-items: center; gap: 10px;">' +
                '<img src="' + urlGambar + '" style="width: 30px; height: 40px; object-fit: cover; border-radius: 4px;" />' +
                '<span>' + cerita.text + '</span>' +
            '</div>'
        );
    }
    $('#pilih-cerita').select2({
        templateResult: formatCerita,
        templateSelection: formatCerita
    });
});
</script>
</body> 
</html>