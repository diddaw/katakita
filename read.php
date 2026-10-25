<?php
require_once __DIR__ . '/includes/bootstrap.php';

$ceritaObject = new Cerita($databaseConnection);

$ceritaId = (int) ($_GET['cerita_id'] ?? 0);
$nomorChapter = (int) ($_GET['chapter'] ?? 0);

$cerita = $ceritaObject->findApprovedById($ceritaId);
if (!$cerita) {
    redirectTo('/index.php');
}

$chapter = $ceritaObject->findChapter($ceritaId, $nomorChapter);
if (!$chapter) {
    redirectTo('/detail.php?id=' . $ceritaId);
}

$gratis = ((int) $chapter['harga'] === 0);
$sudahBeli = false;

if (!$gratis && isset($_SESSION['user_id'])) {
    $sudahBeli = $ceritaObject->hasPurchased($chapter['id'], $_SESSION['user_id']);
}

$bolehBaca = $gratis || $sudahBeli;

$images = ($bolehBaca && $chapter['tipe_konten'] === 'gambar') ? $ceritaObject->getChapterImages($chapter['id']) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escapeHtml($cerita['judul']) ?> - Chapter <?= $chapter['nomor_chapter'] ?></title>
    <link rel="stylesheet" href="assets/css/detail.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif; }
        body { 
            background: #F9FAFB !important; 
            color: #111827 !important; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
        }
        .read-container {
            flex: 1;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .read-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .read-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }
        .read-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: #FF544C;
        }
        .paywall-wrapper {
            display: flex;
            justify-content: center;
            margin: 40px 0;
        }
        .paywall-card {
            background: #FFFFFF !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
            padding: 32px !important;
            text-align: center !important;
            max-width: 420px !important;
            width: 100% !important;
        }
        .paywall-card .lock-icon {
            width: 64px !important;
            height: 64px !important;
            border-radius: 50% !important;
            background: #ECFDF5 !important;
            color: #FF544C !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 auto 16px auto !important;
            font-size: 32px !important;
        }
        .paywall-card .price-label {
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #6B7280 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            margin-bottom: 4px !important;
        }
        .paywall-card .price-value {
            font-size: 32px !important;
            font-weight: 800 !important;
            color: #111827 !important;
            margin-bottom: 24px !important;
        }
        .btn-beli {
            display: block !important;
            width: 100% !important;
            background: #FF544C !important;
            color: #FFFFFF !important;
            text-decoration: none !important;
            padding: 14px 20px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            font-size: 15px !important;
            transition: background 0.2s !important;
        }
        .btn-beli:hover {
            background: #FF544C !important;
        }
        .paywall-login {
            color: #4B5563 !important;
            font-size: 14px !important;
        }
        .paywall-login a {
            color: #FF544C !important;
            font-weight: 600 !important;
            text-decoration: none !important;
        }
        .chapter-content {
            background: #FFFFFF;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 32px;
            line-height: 1.8;
            font-size: 16px;
            color: #374151;
            margin-bottom: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }
        .chapter-content img {
            max-width: 100%;
            display: block;
            margin: 0 auto 16px auto;
            border-radius: 8px;
        }
        .chapter-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 24px;
            gap: 12px;
        }
        .chapter-nav a {
            background: #FFFFFF !important;
            border: 1px solid #D1D5DB !important;
            color: #FF544C !important;
            padding: 10px 18px !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            transition: 0.2s !important;
        }
        .chapter-nav a:hover {
            background: #F3F4F6 !important;
            border-color: #FF544C !important;
        }
        .nav-placeholder {
            visibility: hidden;
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="read-container">
    <div class="read-header">
        <h1><?= escapeHtml($cerita['judul']) ?></h1>
        <h2>Chapter <?= $chapter['nomor_chapter'] ?><?php if ($chapter['judul_chapter']) echo ' - ' . escapeHtml($chapter['judul_chapter']); ?></h2>
    </div>

    <?php if (!$bolehBaca): ?>

        <div class="paywall-wrapper">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="paywall-card">
                    <div class="lock-icon">
                        <span class="material-symbols-rounded" style="font-size: 32px;">lock</span>
                    </div>
                    <p class="paywall-login">Silakan <a href="login.php">login</a> untuk membeli chapter ini.</p>
                </div>
            <?php else: ?>
                <div class="paywall-card">
                    <div class="lock-icon">
                        <span class="material-symbols-rounded" style="font-size: 32px;">lock</span>
                    </div>
                    <div class="price-label">Harga Chapter Ini</div>
                    <div class="price-value">Rp <?= number_format($chapter['harga'], 0, ',', '.') ?></div>
                    <a href="beli.php?chapter_id=<?= $chapter['id'] ?>&cerita_id=<?= $ceritaId ?>" class="btn-beli">Beli Chapter Ini</a>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <?php if ($chapter['tipe_konten'] === 'teks' && $chapter['konten_teks']): ?>
            <div class="chapter-content">
                <?php
                $konten = $chapter['konten_teks'];
                if ($konten === strip_tags($konten)) {
                    echo nl2br(escapeHtml($konten));
                } else {
                    echo $konten;
                }
                ?>
            </div>
        <?php elseif ($images): ?>
            <div class="chapter-content" style="padding: 16px;">
                <?php foreach ($images as $img): ?>
                    <img src="assets/data/chapters/<?= escapeHtml($img['file_path']) ?>">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="chapter-content" style="text-align: center; color: #6B7280;">
                <p>Konten chapter belum tersedia.</p>
            </div>
        <?php endif; ?>

        <div class="chapter-nav">
            <?php if ($nomorChapter > 1): ?>
                <a href="read.php?cerita_id=<?= $ceritaId ?>&chapter=<?= $nomorChapter - 1 ?>">← Sebelumnya</a>
            <?php else: ?>
                <span class="nav-placeholder"></span>
            <?php endif; ?>
            <a href="read.php?cerita_id=<?= $ceritaId ?>&chapter=<?= $nomorChapter + 1 ?>">Selanjutnya →</a>
        </div>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>