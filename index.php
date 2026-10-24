<?php
require_once __DIR__ . '/includes/bootstrap.php';

$ceritaObject = new Cerita($databaseConnection);
$new_releases = $databaseConnection->query("
    SELECT * FROM cerita 
    WHERE is_new_release = 1 AND slide_only = 0 
    ORDER BY created_at DESC 
")->fetchAll();

$slidesData = $databaseConnection->query("
    SELECT slides.*, cerita.judul, cerita.author, cerita.deskripsi, cerita.cover_image
    FROM slides
    JOIN cerita ON cerita.id = slides.cerita_id
    WHERE slides.aktif = 1
    ORDER BY slides.urutan ASC
")->fetchAll();

require 'includes/header.php';
require 'data/featured.php';
?>

<style>
    .card-genres {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 6px;
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

    .card-info {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>

<body>
<?php require 'includes/navbar.php'; ?>

<div class="banner-wrapper">
  <?php foreach($slidesData as $index => $slide): ?>
    <div class="slide <?php echo $index === 0 ? 'active' : '' ?>">
        <img src="assets/data/images/<?php echo $slide['cover_image']; ?>" alt="<?php echo $slide['judul']; ?>">
        <div class="overlay"></div>
        <div class="content">
            <span class="tag">NOVEL</span>
            <h1><?php echo $slide['judul']; ?></h1>
            <h2><?php echo $slide['author']; ?></h2>
            <p><?php echo $slide['deskripsi']; ?></p>
            <div class="buttons">
                <a href="#" class="btn-primary">Explore Story</a>
                <a href="#" class="btn-secondary">Read More</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="navigation">
        <?php foreach($slidesData as $index => $slide): ?>
            <div class="dot <?php echo $index === 0 ? 'active' : '' ?>"></div>
        <?php endforeach; ?>
    </div>
</div>
<section class="new-releases">
    <div class="section-header">
        <h2>New Releases</h2>
        <a href="#" class="more-link">More ›</a>
    </div>
    <div class="releases-grid">
        <?php foreach($new_releases as $item): ?>
        <div class="release-card" onclick="window.location.href='detail.php?id=<?php echo $item['id']; ?>'" style="cursor:pointer;">
            <div class="card-img-wrapper">
              <img src="assets/data/images/<?php echo $item['cover_image']; ?>" loading="lazy" alt="<?php echo escapeHtml($item['judul']); ?>">
            </div>
            <div class="card-info">
                <div>
                    <p class="card-title"><?php echo escapeHtml($item['judul']); ?></p>
                    <p class="card-author" style="font-size: 12px; color: #6B7280; margin-top: 2px;"><?php echo escapeHtml($item['author'] ?? ''); ?></p>
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
                </div>
            </div>
        </div> 
        <?php endforeach; ?>
    </div>
</section>
<section class="featured-section">
    <div class="featured-header">
        <h2>Koleksi Spesial</h2>
    </div>

    <div class="featured-box">
    
        <button class="nav-btn-left">&lt;</button>
        <div class="featured-main" style="background-image: url('<?php echo $featured['banner_image'] ?? 'assets/data/images/default_banner.jpg'; ?>')">
            <div class="featured-main-overlay">
                <p class="featured-label">KOLEKSI SPESIAL</p>
                <h2><?php echo $featured['banner_subtitle'] ?? 'Omniscient & The Dark Lab'; ?></h2>
                <p><?php echo $featured['banner_desc'] ?? 'Karena ada cerita yang selalu membekas...'; ?></p>
                <a href="#" class="btn-lihat-semua">Lihat Semua</a>
            </div>
        </div>
        <div class="featured-grid">
        <?php foreach($featured['cards'] as $card): ?>
            <div class="special-card" onclick="window.location.href='detail.php?id=<?= $card['id'] ?>'" style="cursor:pointer;">
                <img src="assets/data/images/<?= escapeHtml($card['cover_image']) ?>" loading="lazy" alt="<?= escapeHtml($card['judul']) ?>" class="special-cover">

                <div class="special-info">
                    <div>
                        <h3><?= escapeHtml($card['judul']) ?></h3>
                        <p class="special-author"><?= escapeHtml($card['author'] ?? 'Penulis') ?></p>
                        
                        <p class="special-desc">
                            <?= escapeHtml($card['deskripsi'] ?? 'Deskripsi cerita belum tersedia.') ?>
                        </p>
                    </div>
        
                    <div class="special-meta" style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                    <span class="badge-status <?= ($card['status'] ?? '') === 'completed' ? 'completed' : 'ongoing' ?>">
                        <?= escapeHtml($card['status'] ?? 'ongoing') ?>
                    </span>
                        <a href="detail.php?id=<?= $card['id'] ?>" class="btn-baca" style="background:#1B7B73; color:#FFFFFF; padding:4px 12px; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600;">Baca</a>
                </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="nav-btn-right">&gt;</button>
    </div> 
</section>
<?php require 'data/recommended.php'; ?>
<?php require 'includes/footer.php'; ?>
<script>
const slideItems = document.querySelectorAll('.slide');
const dotItems = document.querySelectorAll('.dot');
let currentSlide = 0;

if (slideItems.length > 0) {
    function gantiSlide(index) {
        slideItems.forEach(s => s.classList.remove('active'));
        dotItems.forEach(d => d.classList.remove('active'));
        if (slideItems[index]) slideItems[index].classList.add('active');
        if (dotItems[index]) dotItems[index].classList.add('active');
    }

    dotItems.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            gantiSlide(currentSlide);
        });
    });

    setInterval(() => {
        currentSlide++;
        if (currentSlide >= slideItems.length) currentSlide = 0;
        gantiSlide(currentSlide);
    }, 5000);
}
</script>

<script src="script.js"></script>

</body>
</html>