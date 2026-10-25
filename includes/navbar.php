<?php
$logoPath = 'assets/data/images/logo.png';
$hasLogo = file_exists(__DIR__ . '/../' . $logoPath);
?>

<nav class="navbar">
    <a href="index.php" class="logo">
        <?php if ($hasLogo): ?>
            <img src="<?= $logoPath ?>?v=<?= filemtime(__DIR__ . '/../' . $logoPath) ?>" alt="Logo Website" class="nav-logo-img">
        <?php else: ?>
            Novel<span>&</span>Kami
        <?php endif; ?>
    </a>

    <div class="navbar-actions">
        <div class="search-wrapper">
            <button class="search-toggle" type="button" id="searchToggleBtn">
                <span class="material-symbols-rounded">search</span>
            </button>
            <div class="search-box" id="searchBox">
                <form action="search.php" method="get">
                    <input type="text" name="q" placeholder="Cari judul..." required>
                    <button type="submit"><span class="material-symbols-rounded">search</span></button>
                </form>
            </div>
        </div>

        <button class="menu-toggler" type="button" id="menuToggleBtn">
            <span class="material-symbols-rounded">menu</span>
        </button>
    </div>
</nav>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="header-logo">
            <a href="index.php" class="logo" style="color:#111827; font-weight:700; text-decoration:none; display:flex; align-items:center;">
                <?php if ($hasLogo): ?>
                    <img src="<?= $logoPath ?>?v=<?= filemtime(__DIR__ . '/../' . $logoPath) ?>" alt="Logo" style="height:32px;">
                <?php else: ?>
                    Novel<span style="color:#FF544C;">&</span>Kami
                <?php endif; ?>
            </a>
        </div>
        <button class="sidebar-toggler" id="sidebarCloseBtn" type="button">
            <span class="material-symbols-rounded">close</span>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li>
                <a href="index.php" class="nav-link">
                    <span class="material-symbols-rounded nav-icon">grid_view</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="newest.php" class="nav-link">
                    <span class="material-symbols-rounded nav-icon">new_releases</span>
                    <span>Newest</span>
                </a>
            </li>
            <li>
                <a href="genres.php" class="nav-link">
                    <span class="material-symbols-rounded nav-icon">category</span>
                    <span>Genres</span>
                </a>
            </li>
            <li>
                <a href="bookmark.php" class="nav-link">
                    <span class="material-symbols-rounded nav-icon">bookmark</span>
                    <span>Bookmarks</span>
                </a>
            </li>
        </ul>
        <ul class="nav-list secondary-nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a href="profile.php" class="nav-link">
                        <span class="material-symbols-rounded nav-icon">person_outline</span>
                        <span><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="nav-link">
                        <span class="material-symbols-rounded nav-icon">logout</span>
                        <span>Logout</span>
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="login.php" class="nav-link">
                        <span class="material-symbols-rounded nav-icon">login</span>
                        <span>Login / Daftar</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchToggleBtn = document.getElementById('searchToggleBtn');
    const searchBox = document.getElementById('searchBox');
    const menuToggleBtn = document.getElementById('menuToggleBtn');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (searchToggleBtn && searchBox) {
        searchToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            searchBox.classList.toggle('active');
        });
        document.addEventListener('click', function(e) {
            if (!searchBox.contains(e.target) && !searchToggleBtn.contains(e.target)) {
                searchBox.classList.remove('active');
            }
        });
    }

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (sidebarOverlay) sidebarOverlay.classList.add('active');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
    }

    if (menuToggleBtn) menuToggleBtn.addEventListener('click', openSidebar);
    if (sidebarCloseBtn) sidebarCloseBtn.addEventListener('click', closeSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
});
</script>