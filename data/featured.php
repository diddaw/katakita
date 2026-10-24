<?php
$stmtFeatured = $databaseConnection->prepare("
    SELECT * FROM cerita WHERE koleksi_spesial = 1 ORDER BY created_at DESC
");
$stmtFeatured->execute();
$featuredCards = $stmtFeatured->fetchAll();

$featured = [
    'banner_title' => 'Koleksi Spesial',
    'banner_subtitle' => 'Omniscient & The Dark Lab',
    'banner_desc' => 'Karena ada cerita yang selalu membekas, meski sudah tamat',
    'banner_image' => 'assets/data/images/kimdokja.png',
    'cards' => $featuredCards
];
?>