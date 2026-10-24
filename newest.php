<?php
require_once __DIR__ . '/includes/bootstrap.php';

$stmt = $databaseConnection->prepare("
    SELECT cerita.*,
    GROUP_CONCAT(genre.nama SEPARATOR ', ') as genre
    FROM cerita
    LEFT JOIN cerita_genre cg ON cg.cerita_id = cerita.id
    LEFT JOIN genre ON genre.id = cg.genre_id
    WHERE cerita.approval_status = 'approved'
    GROUP BY cerita.id
    ORDER BY cerita.created_at DESC
");
$stmt->execute();
$newestList = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Newest</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/new_releases.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
       * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif; }
        body { background:#F9FAFB; color:#111827; }
        .newest-wrapper {
            padding: 30px 8%;
        }
        .newest-header {
            margin-bottom: 24px;
        }
        .newest-header h1 {
            font-size: 22px;
            margin-bottom: 4px;
            color: #111827;
        }
        .newest-header p {
            color: #6B7280;
            font-size: 14px;
        }
        .newest-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 20px;
        }
        .newest-card {
            text-decoration: none;
            color: #111827;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: 0.3s;
        }
        .newest-card img {
            width: 100%;
            aspect-ratio: 2/3;
            object-fit: cover;
            border-radius: 8px;
            transition: 0.3s;
        }
        .newest-card img:hover {
            transform: scale(1.03);
        }
        .newest-card h3 {
            font-size: 13px;
            line-height: 1.3;
            overflow: hidden;
            max-height: 2.6em;
            color: #111827;
        }
        .newest-card p {
            font-size: 12px;
            color: #6B7280;
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            color: #6B7280;
        }
        .card-genres {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 2px;
        }
        .genre-badge {
            background-color: #F3F4F6;
            color: #1B7B73;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
            line-height: 1.4;
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="newest-wrapper">
    <div class="newest-header">
        <h1>Newest</h1>
        <p><?= count($newestList) ?> cerita tersedia</p>
    </div>

    <?php if (!$newestList): ?>
        <div class="empty-state">
            <p>Belum ada cerita yang tersedia.</p>
        </div>
    <?php else: ?>
        <div class="newest-grid">
            <?php foreach ($newestList as $item): ?>
                <a href="detail.php?id=<?= $item['id'] ?>" class="newest-card">
                    <img src="assets/data/images/<?= escapeHtml($item['cover_image']) ?>" 
                         alt="<?= escapeHtml($item['judul']) ?>">
                    <h3><?= escapeHtml($item['judul']) ?></h3>
                    <p><?= escapeHtml($item['author']) ?></p>
                    <div class="card-genres">
                        <?php 
                        if (!empty($item['genre'])) {
                            $genres = explode(',', $item['genre']);
                            $genresLimited = array_slice($genres, 0, 2);
                            foreach ($genresLimited as $g) {
                                $gTrim = trim($g);
                                if ($gTrim !== '') {
                                    echo '<span class="genre-badge">' . escapeHtml($gTrim) . '</span>';
                                }
                            }
                        }
                        ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script src="script.js"></script>
</body>
</html>