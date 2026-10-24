<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$ceritaObject = new Cerita($databaseConnection);
$userId = (int) $_SESSION['user_id'];
$ceritaId = (int) ($_GET['cerita_id'] ?? 0);
$chapterId = (int) ($_GET['id'] ?? 0);

$cerita = $ceritaObject->findByIdForUser($ceritaId, $userId);
if (!$cerita) {
    header('Location: dashboard.php');
    exit;
}

$chapter = $ceritaObject->getChapterById($chapterId, $ceritaId, $userId);
if (!$chapter) {
    header('Location: kelola_chapter.php?id=' . $ceritaId);
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judulChapter = trim($_POST['judul_chapter'] ?? '');
    $spoiler = trim($_POST['spoiler'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);

    if ($chapter['tipe_konten'] === 'teks') {
        $kontenTeks = trim($_POST['konten_teks'] ?? '');
        if ($kontenTeks === '' || $kontenTeks === '<p></p>') {
            $errors[] = 'Isi chapter wajib diisi.';
        } else {
            $ceritaObject->updateChapterText($chapterId, $judulChapter, $spoiler, $kontenTeks, $harga);
            $success = true;
        }
    } else {
        $ceritaObject->updateChapterMeta($chapterId, $judulChapter, $spoiler, $harga);

        $fileCount = count($_FILES['gambar_tambahan']['name'] ?? []);
        if ($fileCount > 0 && !empty($_FILES['gambar_tambahan']['name'][0])) {
            $uploadedFiles = [];
            $targetDir = __DIR__ . '/../assets/data/chapters';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['gambar_tambahan']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $ext = strtolower(pathinfo($_FILES['gambar_tambahan']['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    continue;
                }
                if ($_FILES['gambar_tambahan']['size'][$i] > 5 * 1024 * 1024) {
                    continue;
                }
                $fileName = 'chapter-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                move_uploaded_file($_FILES['gambar_tambahan']['tmp_name'][$i], $targetDir . '/' . $fileName);
                $uploadedFiles[] = $fileName;
            }

            if ($uploadedFiles) {
                $ceritaObject->addChapterImages($chapterId, $uploadedFiles);
            }
        }

        $success = true;
    }

    if ($success) {
        $chapter = $ceritaObject->getChapterById($chapterId, $ceritaId, $userId);
    }
}

$existingImages = $chapter['tipe_konten'] === 'gambar' ? $ceritaObject->getChapterImages($chapterId) : [];
$kontenUntukEditor = $chapter['konten_teks'] ?? '';
if ($kontenUntukEditor !== '' && $kontenUntukEditor === strip_tags($kontenUntukEditor)) {
    $kontenUntukEditor = nl2br(escapeHtml($kontenUntukEditor));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Chapter</title>
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
                <li><a href="dashboard.php">
                    <span class="material-symbols-rounded">dashboard</span>
                    Dashboard
                </a></li>
                <li><a href="upload.php">
                    <span class="material-symbols-rounded">upload</span>
                    Upload Cerita
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
            <h1>Edit Chapter <?= $chapter['nomor_chapter'] ?> — <?= escapeHtml($cerita['judul']) ?></h1>
            <a href="kelola_chapter.php?id=<?= $ceritaId ?>" style="color:#94A3B8; font-size:14px;">← Kembali ke Kelola Chapter</a>
        </div>

        <?php if ($success): ?>
            <div style="background:#22C55E; padding:12px; border-radius:8px; margin-bottom:16px;">
                Chapter berhasil diperbarui.
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
            <form method="post" enctype="multipart/form-data" class="admin-form" style="flex-direction:column;">
                <div class="form-group">
                    <label>Judul Chapter (opsional)</label>
                    <input type="text" name="judul_chapter" value="<?= escapeHtml($chapter['judul_chapter'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Spoiler / Cuplikan (opsional)</label>
                    <textarea name="spoiler" rows="2" style="padding:10px; border-radius:8px; border:1px solid #334155; background:#0F172A; color:#F8FAFC;"><?= escapeHtml($chapter['spoiler'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Harga (Rp, isi 0 kalau gratis)</label>
                    <input type="number" name="harga" min="0" value="<?= (int) $chapter['harga'] ?>">
                </div>

                <?php if ($chapter['tipe_konten'] === 'teks'): ?>
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

                        <div id="editor" contenteditable="true" class="rich-editor"><?= $kontenUntukEditor ?: '<p>Tulis isi chapter di sini...</p>' ?></div>

                        <textarea name="konten_teks" id="konten_teks" style="display:none;"></textarea>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label>Gambar yang Sudah Ada (<?= count($existingImages) ?> gambar)</label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                            <?php foreach ($existingImages as $img): ?>
                                <img src="../assets/data/chapters/<?= escapeHtml($img['file_path']) ?>" style="width:80px; border-radius:4px;">
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tambah Gambar Baru (opsional, akan ditambahkan di akhir)</label>
                        <input type="file" name="gambar_tambahan[]" accept=".jpg,.jpeg,.png,.webp" multiple>
                        <p style="font-size:12px; color:#94A3B8;">Gambar yang sudah ada tidak bisa dihapus, hanya bisa ditambah.</p>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-approve" style="width:fit-content;">Simpan Perubahan</button>
            </form>
        </div>
    </main>

</div>

<script src="../assets/js/editor.js"></script>
</body>
</html>