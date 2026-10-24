<?php
require_once __DIR__ . '/includes/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $databaseConnection->prepare("SELECT * FROM cerita WHERE id = ?");
$stmt->execute([$id]);
$cerita = $stmt->fetch();

if (!$cerita) {
    header('Location: index.php');
    exit;
}

$stmtGenre = $databaseConnection->prepare("
    SELECT g.nama FROM genre g 
    JOIN cerita_genre cg ON g.id = cg.genre_id 
    WHERE cg.cerita_id = ?
");
$stmtGenre->execute([$id]);
$genres = array_column($stmtGenre->fetchAll(), 'nama');

$stmtChapter = $databaseConnection->prepare("
    SELECT * FROM chapter WHERE cerita_id = ? ORDER BY nomor_chapter ASC
");
$stmtChapter->execute([$id]);
$chapters = $stmtChapter->fetchAll();

$sudahBookmark = false;
if (isset($_SESSION['user_id'])) {
    $stmtBookmark = $databaseConnection->prepare("
        SELECT id FROM bookmarks WHERE user_id = ? AND cerita_id = ?
    ");
    $stmtBookmark->execute([$_SESSION['user_id'], $id]);
    $sudahBookmark = (bool) $stmtBookmark->fetch();
}
$deskripsiLengkap = $cerita['deskripsi'] ?? '';
$maxChar = 320;
$isLong = mb_strlen($deskripsiLengkap) > $maxChar;
$deskripsiPendek = $isLong ? mb_substr($deskripsiLengkap, 0, $maxChar) . '...' : $deskripsiLengkap;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapeHtml($cerita['judul']) ?></title>
    <link rel="stylesheet" href="assets/css/detail.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="detail-wrapper">
    <div class="detail-top">
        <div class="detail-cover">
            <img src="assets/data/images/<?= escapeHtml($cerita['cover_image']) ?>" 
                 alt="<?= escapeHtml($cerita['judul']) ?>">
        </div>
        <div class="detail-info">
            <span class="detail-tipe"><?= strtoupper(escapeHtml($cerita['tipe'])) ?></span>
            <h1 class="detail-judul"><?= escapeHtml($cerita['judul']) ?></h1>
            <p class="detail-author"><?= escapeHtml($cerita['author']) ?></p>
            
            <div class="deskripsi-wrapper" style="margin-top: 12px; margin-bottom: 16px;">
                <p id="text-deskripsi" class="detail-deskripsi" style="margin: 0; line-height: 1.6; color: #4B5563;">
                    <?= nl2br(escapeHtml($deskripsiPendek)) ?>
                </p>

                <?php if ($isLong): ?>
                    <button type="button" id="btn-toggle-desc" onclick="toggleDeskripsi()" 
                        style="background: none; border: none; color: #1B7B73; font-weight: 600; cursor: pointer; padding: 0; font-size: 13px; margin-top: 6px; outline: none;">
                        Selengkapnya 
                    </button>
                    <script>
                        const descFull = <?= json_encode(nl2br(escapeHtml($deskripsiLengkap))) ?>;
                        const descShort = <?= json_encode(nl2br(escapeHtml($deskripsiPendek))) ?>;
                        let isExpanded = false;

                        function toggleDeskripsi() {
                            const descElement = document.getElementById('text-deskripsi');
                            const btnElement = document.getElementById('btn-toggle-desc');
                            
                            isExpanded = !isExpanded;
                            
                            if (isExpanded) {
                                descElement.innerHTML = descFull;
                                btnElement.innerText = 'Sembunyikan';
                            } else {
                                descElement.innerHTML = descShort;
                                btnElement.innerText = 'Selengkapnya';
                            }
                        }
                    </script>
                <?php endif; ?>
            </div>

            <div class="detail-genres">
                <?php foreach ($genres as $genre): ?>
                <span class="genre-tag"><?= escapeHtml($genre) ?></span>
                <?php endforeach; ?>
            </div>

            <div style="display:flex; gap:12px; align-items:center; margin-top:8px;">
                <?php if (!empty($chapters)): ?>
                <a href="read.php?cerita_id=<?= $id ?>&chapter=1" class="btn-start">
                    Start Reading
                </a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                <form method="post" action="bookmark.php">
                    <input type="hidden" name="cerita_id" value="<?= $id ?>">
                    <?php if ($sudahBookmark): ?>
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="btn-bookmark active">
                            <span class="material-symbols-rounded">bookmark</span>
                        </button>
                    <?php else: ?>
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="btn-bookmark">
                            <span class="material-symbols-rounded">bookmark_border</span>
                        </button>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="detail-tabs">
        <button class="tab-btn active" onclick="showTab('episode')">Episode</button>
    </div>

    <div id="tab-episode" class="tab-content active">
        <?php if (empty($chapters)): ?>
        <p class="empty-msg">Belum ada chapter tersedia.</p>
        <?php else: ?>
        <?php foreach ($chapters as $ch): ?>
        <a href="read.php?cerita_id=<?= $id ?>&chapter=<?= $ch['nomor_chapter'] ?>" 
           class="episode-item">
            <div class="episode-cover">
                <img src="assets/data/images/<?= escapeHtml($cerita['cover_image']) ?>" alt="">
            </div>
            <div class="episode-info">
                <h3>Chapter <?= $ch['nomor_chapter'] ?> 
                    <?php if ($ch['judul_chapter']) echo '- ' . escapeHtml($ch['judul_chapter']); ?>
                </h3>
                <p class="episode-spoiler"><?= escapeHtml($ch['spoiler']) ?></p>
                <span class="episode-date"><?= date('d M Y', strtotime($ch['created_at'])) ?></span>
            </div>
            <span class="btn-baca">Baca</span>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    event.target.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.add('open');
    if (overlay) overlay.classList.add('active');
});
</script>
</body>
</html>