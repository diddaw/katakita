<?php
require_once __DIR__ . '/includes/bootstrap.php';

$auth = new Auth($databaseConnection);
$auth->requireLogin();

$userId = (int) $_SESSION['user_id'];

// Tambah bookmark
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ceritaId = (int) ($_POST['cerita_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $stmt = $databaseConnection->prepare("
            INSERT IGNORE INTO bookmarks (user_id, cerita_id) VALUES (?, ?)
        ");
        $stmt->execute([$userId, $ceritaId]);
    }

    if ($action === 'remove') {
        $stmt = $databaseConnection->prepare("
            DELETE FROM bookmarks WHERE user_id = ? AND cerita_id = ?
        ");
        $stmt->execute([$userId, $ceritaId]);
    }

    $kembaliKe = $_SERVER['HTTP_REFERER'] ?? 'bookmark.php';
        if (strpos($kembaliKe, 'bookmark.php') !== false) {
        header('Location: bookmark.php?noauto=1');
        } else {
        header('Location: ' . $kembaliKe);
}
exit;
}

// Ambil semua bookmark user
$stmt = $databaseConnection->prepare("
    SELECT cerita.* FROM bookmarks
    JOIN cerita ON cerita.id = bookmarks.cerita_id
    WHERE bookmarks.user_id = ?
    ORDER BY bookmarks.created_at DESC
");
$stmt->execute([$userId]);
$bookmarks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bookmarks</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/new_releases.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif; }
    body { 
        background: #FFFFFF !important; 
        color: #4B5563 !important; 
    }
    .bookmark-wrapper {
        padding: 30px 8%;
        background: #FFFFFF;
    }
    .bookmark-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .bookmark-header h1 {
        font-size: 22px;
        color: #111827; 
    }
    .bookmark-header span, .bookmark-header p {
        color: #6B7280;
    }

    .bookmark-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 20px;
    }
    .bookmark-card {
        text-decoration: none;
        display: flex;
        flex-direction: column;
        position: relative;
        background: #FFFFFF;
        border-radius: 10px;
        border: 1px solid #F3F4F6;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .bookmark-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 24px -8px rgba(27, 123, 115, 0.25); 
    }
    .bookmark-card img {
        width: 100%;
        aspect-ratio: 2/3;
        object-fit: cover;
        border-radius: 8px 8px 0 0; 
    }
    .bookmark-card h3 {
        font-size: 13px;
        line-height: 1.3;
        color: #111827; 
        margin: 10px 8px 4px 8px;
    }
    .bookmark-card p {
        font-size: 12px;
        color: #6B7280; 
        margin: 0 8px 12px 8px;
    }
    .btn-remove-bookmark {
        position: absolute;
        top: 6px;
        right: 6px;
        background: rgba(239, 68, 68, 0.9);
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        transition: 0.2s;
    }
    .btn-remove-bookmark:hover {
        background: #DC2626; 
        transform: scale(1.1);
    }
    .empty-state {
        text-align: center;
        padding: 60px;
        color: #6B7280;
    }
    .empty-state span {
        font-size: 48px;
        display: block;
        margin-bottom: 16px;
        color: #9CA3AF;
    }
</style>
<?php include 'includes/navbar.php'; ?>

<div class="bookmark-wrapper">
    <div class="bookmark-header">
        <h1>Bookmarks Saya</h1>
        <p style="color:#94A3B8;"><?= count($bookmarks) ?> cerita tersimpan</p>
    </div>

    <?php if (!$bookmarks): ?>
        <div class="empty-state">
            <span class="material-symbols-rounded">bookmark</span>
            <p>Belum ada cerita yang di-bookmark.</p>
            <a href="index.php" style="color:#3B82F6; margin-top:12px; display:inline-block;">Jelajahi Cerita</a>
        </div>
    <?php else: ?>
        <div class="bookmark-grid">
            <?php foreach ($bookmarks as $item): ?>
                <div class="bookmark-card">
                    <a href="detail.php?id=<?= $item['id'] ?>">
                        <img src="assets/data/images/<?= escapeHtml($item['cover_image']) ?>" 
                             alt="<?= escapeHtml($item['judul']) ?>">
                    </a>
                    <!-- Tombol hapus bookmark -->
                    <form method="post" action="bookmark.php" style="display:inline;">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="cerita_id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn-remove-bookmark" title="Hapus Bookmark">✕</button>
                    </form>
                    <a href="detail.php?id=<?= $item['id'] ?>" style="text-decoration:none; color:#F8FAFC;">
                        <h3><?= escapeHtml($item['judul']) ?></h3>
                        <p><?= escapeHtml($item['author']) ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('noauto')) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('active');
    }
});
</script>
</body>
</html> 