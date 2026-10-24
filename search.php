<?php
require_once __DIR__ . '/includes/bootstrap.php';

$ceritaObject = new Cerita($databaseConnection);

$keyword = trim($_GET['q'] ?? '');
$results = [];

if ($keyword !== '') {
    $results = $ceritaObject->search($keyword);
}

require 'includes/header.php';
?>

<body>
<?php require 'includes/navbar.php'; ?>

<section class="new-releases search-results">
    <div class="section-header">
        <h2>
            <?php if ($keyword === ''): ?>
                Cari Novel
            <?php else: ?>
                Hasil pencarian untuk "<?php echo htmlspecialchars($keyword); ?>"
            <?php endif; ?>
        </h2>
    </div>

    <?php if ($keyword === ''): ?>
        <p>Silakan ketik judul cerita yang ingin kamu cari.</p>

    <?php elseif (empty($results)): ?>
        <p>Tidak ada cerita yang cocok dengan "<?php echo htmlspecialchars($keyword); ?>".</p>

    <?php else: ?>
        <div class="releases-grid">
            <?php foreach ($results as $item): ?>
            <div class="release-card" onclick="window.location.href='detail.php?id=<?php echo $item['id']; ?>'">
                <div class="card-img-wrapper">
                    <img src="assets/data/images/<?php echo $item['cover_image']; ?>"
                         alt="<?php echo htmlspecialchars($item['judul']); ?>">
                </div>
                <div class="card-info">
                    <p class="card-title"><?php echo htmlspecialchars($item['judul']); ?></p>
                    <p class="card-genre"><?php echo htmlspecialchars($item['genre'] ?? ''); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require 'includes/footer.php'; ?>

<script src="script.js"></script>

</body>
</html>