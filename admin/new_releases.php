<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'toggle') {
        $id = (int) $_POST['id'];
        $isNewRelease = (int) $_POST['is_new_release'];
        $stmt = $databaseConnection->prepare("UPDATE cerita SET is_new_release = ? WHERE id = ?");
        $stmt->execute([$isNewRelease, $id]);
        header('Location: new_releases.php');
        exit;
    }
        
    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        $stmt = $databaseConnection->prepare("UPDATE cerita SET is_new_release = 0 WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: new_releases.php');
        exit;
    }

    if ($action === 'add_existing') {
        $ceritaId = (int) $_POST['cerita_id'];
        $stmt = $databaseConnection->prepare("UPDATE cerita SET is_new_release = 1 WHERE id = ?");
        $stmt->execute([$ceritaId]);
        header('Location: new_releases.php');
        exit;
    }
}

$newReleases = $databaseConnection->query("
    SELECT * FROM cerita WHERE is_new_release = 1 AND slide_only = 0 ORDER BY created_at DESC
")->fetchAll();

$ceritaList = $databaseConnection->query("
    SELECT id, judul, cover_image FROM cerita 
    WHERE is_new_release = 0 AND approval_status = 'approved'
    ORDER BY judul ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola New Releases</title>
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
                <li class="active">
                    <a href="new_releases.php">
                        <span class="material-symbols-rounded">new_releases</span>
                        Kelola New Releases
                    </a>
                </li>
                <li>
                    <a href="slides.php">
                        <span class="material-symbols-rounded">slideshow</span>
                        Kelola Slides
                    </a>
                </li>
                <li>
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
            <h1>Kelola New Releases</h1>
        </div>
        <div class="admin-form-wrapper" style="margin-bottom:24px;">
            <h3>Tambahkan Cerita yang Sudah Ada (User)</h3>
            <form method="post" style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">
                <input type="hidden" name="action" value="add_existing">
                
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label>Ketik atau Pilih Judul Cerita</label>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <img id="coverPreview" src="" alt="Preview" 
                             style="width: 45px; height: 65px; object-fit: cover; border-radius: 6px; display: none; border: 1px solid #334155;">
                    
                        <div style="flex: 1; width: 100%;">
                            <input type="text" id="ceritaInput" list="daftarCerita" placeholder="Ketik atau pilih judul cerita..." 
                                   style="padding:10px; border-radius:8px; border:1px solid #334155; background:#0F172A; color:#F8FAFC; width: 100%;" required>
                            
                            <datalist id="daftarCerita">
                                <?php foreach ($ceritaList as $cerita): ?>
                                    <option data-id="<?= $cerita['id'] ?>" data-cover="<?= escapeHtml($cerita['cover_image']) ?>" value="<?= escapeHtml($cerita['judul']) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                            <input type="hidden" name="cerita_id" id="ceritaIdHidden" required>
                        </div>
                        
                    </div>
                </div>
                
                <button type="submit" class="btn-approve" style="height: 42px;">Tambah ke New Releases</button>
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
                <?php foreach ($newReleases as $item): ?>
                <tr>
                    <td>
                        <img src="../assets/data/images/<?= escapeHtml($item['cover_image']) ?>" 
                             width="50" style="border-radius:4px;">
                    </td>
                    <td><?= escapeHtml($item['judul']) ?></td>
                    <td><?= escapeHtml($item['author']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                             <button type="submit" class="btn-delete"
                                onclick="return confirm('Hapus dari New Releases?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

</div>

<script>
document.getElementById('ceritaInput').addEventListener('input', function() {
    var inputValue = this.value;
    var options = document.getElementById('daftarCerita').options;
    var hiddenInput = document.getElementById('ceritaIdHidden');
    var previewImg = document.getElementById('coverPreview');
    
    hiddenInput.value = '';
    previewImg.style.display = 'none';

    for (var i = 0; i < options.length; i++) {
        if (options[i].value === inputValue) {
            hiddenInput.value = options[i].getAttribute('data-id');
            var coverImage = options[i].getAttribute('data-cover');
            
            if (coverImage) {
                previewImg.src = '../assets/data/images/' + coverImage;
                previewImg.style.display = 'block';
            }
            break;
        }
    }
});
</script>

</body>
</html>