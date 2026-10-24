<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('/login.php');
}

$ceritaObject = new Cerita($databaseConnection);
$chapterId = (int) ($_GET['chapter_id'] ?? $_POST['chapter_id'] ?? 0);
$ceritaId = (int) ($_GET['cerita_id'] ?? $_POST['cerita_id'] ?? 0);

$stmt = $databaseConnection->prepare("SELECT * FROM chapter WHERE id = ?");
$stmt->execute([$chapterId]);
$chapter = $stmt->fetch();

if (!$chapter) {
    redirectTo('/index.php');
}

$stmtCerita = $databaseConnection->prepare("
    SELECT cerita.*, users.bank_nama, users.bank_norek, users.bank_pemilik
    FROM cerita
    JOIN users ON users.id = cerita.user_id
    WHERE cerita.id = ?
");
$stmtCerita->execute([$ceritaId]);
$ceritaInfo = $stmtCerita->fetch();

if (!$ceritaInfo || !$ceritaInfo['bank_norek']) {
    die('Penjual cerita ini belum mengatur rekening pembayaran. Silakan hubungi admin.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['bukti_transfer']['name'])) {
        $errors[] = 'Bukti transfer wajib diupload.';
    } else {
        $ext = strtolower(pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $errors[] = 'Format bukti transfer harus JPG, PNG, atau WEBP.';
        } elseif ($_FILES['bukti_transfer']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran file maksimal 2MB.';
        } else {
            $fileName = 'bukti-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetDir = __DIR__ . '/assets/data/bukti_transfer';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $targetDir . '/' . $fileName);

            $ceritaObject->recordPurchase($chapterId, $ceritaId, (int) $_SESSION['user_id'], (int) $chapter['harga'], $fileName);

            redirectTo('/read.php?cerita_id=' . $ceritaId . '&chapter=' . $chapter['nomor_chapter'] . '&menunggu=1');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beli Chapter <?= $chapter['nomor_chapter'] ?></title>
    <!-- Link CSS Navbar dan Ikon Material -->
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

        .page-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .card-buy {
            background: #FFFFFF !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
            width: 100%;
            max-width: 420px;
            padding: 32px;
            text-align: center;
        }

        .card-buy .title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .card-buy .price {
            font-size: 26px;
            font-weight: 800;
            color: #1B7B73;
            margin-bottom: 24px;
        }

        .bank-box {
            background: #ECFDF5 !important;
            border: 1px solid #A7F3D0 !important;
            border-radius: 12px !important;
            padding: 16px !important;
            margin-bottom: 20px !important;
            text-align: left !important;
        }

        .bank-box .label {
            font-size: 11px;
            font-weight: 700;
            color: #047857 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bank-box .info {
            font-size: 15px;
            font-weight: 700;
            color: #065F46 !important;
            margin-top: 4px;
        }

        .bank-box .owner {
            font-size: 12px;
            color: #047857 !important;
            margin-top: 2px;
        }

        .upload-group {
            text-align: left !important;
            background: #F9FAFB !important;
            border: 1px dashed #D1D5DB !important;
            border-radius: 10px !important;
            padding: 16px !important;
            margin-bottom: 20px !important;
        }

        .upload-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151 !important;
            margin-bottom: 8px;
        }

        .upload-group input[type="file"] {
            width: 100%;
            font-size: 12px;
            color: #4B5563 !important;
            cursor: pointer;
        }

        .btn-submit-proof {
            width: 100%;
            background: #1B7B73 !important;
            color: #FFFFFF !important;
            border: none !important;
            padding: 12px !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-submit-proof:hover {
            background: #145E58 !important;
        }

        .btn-cancel {
            display: inline-block;
            margin-top: 16px;
            font-size: 13px;
            color: #6B7280 !important;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-cancel:hover {
            color: #111827 !important;
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="page-container">
    <div class="card-buy">
        <h2 class="title">Beli Chapter <?= $chapter['nomor_chapter'] ?></h2>
        <div class="price">Rp <?= number_format($chapter['harga'], 0, ',', '.') ?></div>

        <?php if ($errors): ?>
            <div style="background:#FEE2E2; color:#991B1B; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px; text-align:left;">
                <?php foreach ($errors as $error): ?>
                    <p><?= escapeHtml($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="bank-box">
            <div class="label">Transfer ke</div>
            <div class="info"><?= escapeHtml($ceritaInfo['bank_nama']) ?> — <?= escapeHtml($ceritaInfo['bank_norek']) ?></div>
            <div class="owner">a.n. <?= escapeHtml($ceritaInfo['bank_pemilik']) ?></div>
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="chapter_id" value="<?= $chapter['id'] ?>">
            <input type="hidden" name="cerita_id" value="<?= $ceritaId ?>">

            <div class="upload-group">
                <label>Upload Bukti Transfer</label>
                <input type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png,.webp" required>
            </div>

            <button type="submit" class="btn-submit-proof">Kirim Bukti Transfer</button>
        </form>

        <a href="read.php?cerita_id=<?= $ceritaId ?>&chapter=<?= $chapter['nomor_chapter'] ?>" class="btn-cancel">← Batal dan Kembali</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>         