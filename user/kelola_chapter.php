<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$ceritaObject = new Cerita($databaseConnection);
$ceritaId = (int) ($_GET['id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

$cerita = $ceritaObject->findByIdForUser($ceritaId, $userId);
if (!$cerita) {
    header('Location: dashboard.php');
    exit;
}

$isNovel = ($cerita['tipe'] === 'novel');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

       $nomorChapter = (int) ($_POST['nomor_chapter'] ?? 0);
        $judulChapter = trim($_POST['judul_chapter'] ?? '');
        $spoiler = trim($_POST['spoiler'] ?? '');
        $harga = (int) ($_POST['harga'] ?? 0);

    if ($isNovel) {

        // bagian si novel simpen sebagai teks
        $kontenTeks = trim($_POST['konten_teks'] ?? '');

        if ($kontenTeks === '') {
            $errors[] = 'Isi chapter wajib diisi.';
        }

        if (!$errors) {
            $stmt = $databaseConnection->prepare("
                INSERT INTO chapter (cerita_id, nomor_chapter, judul_chapter, spoiler, tipe_konten, konten_teks, harga)
                VALUES (?, ?, ?, ?, 'teks', ?, ?)
            ");
            $stmt->execute([$ceritaId, $nomorChapter, $judulChapter, $spoiler, $kontenTeks, $harga]);

            header('Location: kelola_chapter.php?id=' . $ceritaId);
            exit;
        }

    } else {
        // ==== KOMIK: simpan sebagai banyak gambar ====
        $uploadedFiles = [];
        $fileCount = count($_FILES['gambar']['name'] ?? []);

        if ($fileCount === 0 || empty($_FILES['gambar']['name'][0])) {
            $errors[] = 'Minimal 1 gambar wajib diupload.';
        } else {
            $targetDir = __DIR__ . '/../assets/data/chapters';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['gambar']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $ext = strtolower(pathinfo($_FILES['gambar']['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $errors[] = 'File "' . escapeHtml($_FILES['gambar']['name'][$i]) . '" harus JPG, PNG, atau WEBP.';
                    continue;
                }

                if ($_FILES['gambar']['size'][$i] > 5 * 1024 * 1024) {
                    $errors[] = 'File "' . escapeHtml($_FILES['gambar']['name'][$i]) . '" maksimal 5MB.';
                    continue;
                }

                $fileName = 'chapter-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'][$i], $targetDir . '/' . $fileName);
                $uploadedFiles[] = $fileName;
            }
        }

        if (!$errors && $uploadedFiles) {
            $stmt = $databaseConnection->prepare("
                INSERT INTO chapter (cerita_id, nomor_chapter, judul_chapter, spoiler, tipe_konten, harga)
                VALUES (?, ?, ?, ?, 'gambar', ?)
            ");
            $stmt->execute([$ceritaId, $nomorChapter, $judulChapter, $spoiler, $harga]);
            $chapterId = $databaseConnection->lastInsertId();

            $stmtImg = $databaseConnection->prepare("
                INSERT INTO chapter_images (chapter_id, file_path) VALUES (?, ?)
            ");
            foreach ($uploadedFiles as $file) {
                $stmtImg->execute([$chapterId, $file]);
            }

            header('Location: kelola_chapter.php?id=' . $ceritaId);
            exit;
        }
    }
}

$stmtChapter = $databaseConnection->prepare("
    SELECT * FROM chapter WHERE cerita_id = ? ORDER BY nomor_chapter ASC
");
$stmtChapter->execute([$ceritaId]);
$chapters = $stmtChapter->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Chapter</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/editor.css">
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
                <li><a href="/katakita/user/dashboard.php">
                    <span class="material-symbols-rounded">dashboard</span>
                    Dashboard
                </a></li>
                <li><a href="/katakita/user/upload.php">
                    <span class="material-symbols-rounded">upload</span>
                    Upload Cerita
                </a></li>
                <li><a href="/katakita/profile.php">
                    <span class="material-symbols-rounded">account_circle</span>
                    Profile
                </a></li>
                <li><a href="/katakita/logout.php">
                    <span class="material-symbols-rounded">logout</span>
                    Logout
                </a></li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Kelola Chapter — <?= escapeHtml($cerita['judul']) ?> <span style="color:#94A3B8; font-size:14px;">(<?= strtoupper(escapeHtml($cerita['tipe'])) ?>)</span></h1>
            <a href="/katakita/user/dashboard.php" style="color:#94A3B8; font-size:14px;">← Kembali ke Dashboard</a>
        </div>

        <?php if ($errors): ?>
            <div style="background:#EF4444; padding:12px; border-radius:8px; margin-bottom:16px;">
                <?php foreach ($errors as $error): ?>
                    <p><?= escapeHtml($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="admin-form-wrapper" style="margin-bottom:24px;">
            <h3>Tambah Chapter Baru</h3>
            <form method="post" enctype="multipart/form-data" class="admin-form" style="flex-direction:column;">
                <div class="form-group">
                    <label>Nomor Chapter</label>
                    <input type="number" name="nomor_chapter" min="1" value="<?= count($chapters) + 1 ?>">
                </div>
                <div class="form-group">
                    <label>Judul Chapter (opsional)</label>
                    <input type="text" name="judul_chapter">
                </div>
                <div class="form-group">
                    <label>Spoiler / Cuplikan (opsional)</label>
                    <textarea name="spoiler" rows="2" style="padding:10px; border-radius:8px; border:1px solid #334155; background:#0F172A; color:#F8FAFC;"></textarea>
                </div>
                <div class="form-group">
                    <label>Harga (Rp, isi 0 kalau gratis)</label>
                    <input type="number" name="harga" min="0" value="0">
                </div>

                <?php if ($isNovel): ?>
                    <div class="form-group">
                    <label>Isi Chapter (teks)</label>
        
                    <div class="editor-toolbar">
                    <button type="button" onclick="formatText('bold')" title="Bold"><b>B</b></button>
                    <button type="button" onclick="formatText('italic')" title="Italic"><i>I</i></button>
                    <button type="button" onclick="formatText('underline')" title="Underline"><u>U</u></button>
                    <span class="toolbar-divider">|</span>
                    <select onchange="formatFont(this.value)" title="Font">

                    <option value="inherit">Font Default</option>
                    <option value="'Georgia', serif">Georgia</option>
                    <option value="'Times New Roman', serif">Times New Roman</option>
                    <option value="'Arial', sans-serif">Arial</option>
                    <option value="'Courier New', monospace">Courier New</option>
                </select>
                <select onchange="formatSize(this.value)" title="Ukuran">
                    <option value="16px">Normal</option>
                    <option value="12px">Kecil</option>
                    <option value="20px">Besar</option>
                    <option value="24px">Lebih Besar</option>
                </select>
                    <span class="toolbar-divider">|</span>
                    <button type="button" onclick="formatText('justifyLeft')" title="Rata Kiri">&#8676;</button>
                    <button type="button" onclick="formatText('justifyCenter')" title="Rata Tengah">&#8633;</button>
                    <button type="button" onclick="formatText('justifyRight')" title="Rata Kanan">&#8677;</button>
                    <button type="button" onclick="formatText('justifyFull')" title="Rata Kanan Kiri">&#8644;</button>
                    <span class="toolbar-divider">|</span>
                    <button type="button" onclick="insertHR()" title="Garis Pemisah">—</button>
                </div>

        <!-- Editor -->
        <div id="editor" contenteditable="true" class="rich-editor">
            <p>Tulis isi chapter di sini...</p>
        </div>

        <!-- Hidden textarea untuk submit -->
        <textarea name="konten_teks" id="konten_teks" style="display:none;"></textarea>
    </div>
<?php else: ?>
                    <div class="form-group">
                        <label>Gambar Chapter (bisa pilih banyak sekaligus, urutan sesuai urutan pilih)</label>
                        <input type="file" name="gambar[]" accept=".jpg,.jpeg,.png,.webp" multiple>
                        <p style="font-size:12px; color:#94A3B8;">Format JPG, PNG, atau WEBP. Maksimal 5MB per gambar. Tidak dibatasi jumlahnya.</p>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-approve" style="width:fit-content;">Tambah Chapter</button>
            </form>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Chapter</th>
                    <th>Judul</th>
                    <th>Tipe</th>
                    <th>Harga</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$chapters): ?>
                    <tr><td colspan="6" style="text-align:center; color:#94A3B8;">Belum ada chapter.</td></tr>
                <?php else: ?>
                    <?php foreach ($chapters as $ch): ?>
                    <tr>
                        <td>Chapter <?= $ch['nomor_chapter'] ?></td>
                        <td><?= escapeHtml($ch['judul_chapter'] ?? '-') ?></td>
                        <td><?= escapeHtml($ch['tipe_konten']) ?></td>
                        <td><?= $ch['harga'] > 0 ? 'Rp ' . number_format($ch['harga'], 0, ',', '.') : 'Gratis' ?></td>
                        <td><?= date('d M Y', strtotime($ch['created_at'])) ?></td>
                        <td>
                            <a href="edit_chapter.php?id=<?= $ch['id'] ?>&cerita_id=<?= $ceritaId ?>" class="btn-edit">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

   <script src="../assets/js/editor.js"></script>
</body>
</html>     