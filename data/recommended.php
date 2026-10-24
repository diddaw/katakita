<?php
$stmt = $databaseConnection->prepare("
    SELECT cerita.*, recommended.urutan 
    FROM recommended 
    JOIN cerita ON cerita.id = recommended.cerita_id 
    WHERE recommended.aktif = 1 
    AND cerita.approval_status = 'approved' 
    ORDER BY recommended.urutan ASC, recommended.id DESC
");
$stmt->execute();
$recommendedList = $stmt->fetchAll();
?>

<section class="recommended-wrapper">
    <div class="recommended-header">
        <h2>Yang Mungkin Anda Suka</h2>
    </div>

    <?php if (empty($recommendedList)): ?>
        <p style="color: #6B7280; font-size: 14px; margin-top: 10px;">Belum ada cerita rekomendasi.</p>
    <?php else: ?>
        <div class="recommended-list">
            <?php foreach ($recommendedList as $row): ?>
                <a href="detail.php?id=<?= (int)$row['id'] ?>" class="recommended-card">
                    <img src="assets/data/images/<?= escapeHtml($row['cover_image']) ?>" loading="lazy" alt="<?= escapeHtml($row['judul']) ?>">
                    <h3><?= escapeHtml($row['judul']) ?></h3>
                    <p><?= escapeHtml($row['author']) ?></p>
                    <span class="tipe-badge"><?= escapeHtml(ucfirst($row['tipe'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>