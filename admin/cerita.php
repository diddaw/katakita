<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
$ceritaObject = new Cerita($databaseConnection);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ceritaId = (int) $_POST['id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $ceritaObject->updateStatus($ceritaId, 'approved');
    }
    if ($action === 'reject') {
        $ceritaObject->updateStatus($ceritaId, 'rejected');
    }
    if ($action === 'delete') {
        $ceritaObject->delete($ceritaId);
    }

    redirectTo('/admin/cerita.php');
}

$ceritaList = $ceritaObject->getAllForAdmin();
$pageTitle = 'Kelola Cerita';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>

<div class="admin-container">
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2>Novel<span>&</span>Komik</h2>
        </div>
        <nav>
            <ul>
                <li>
                    <a href="index.php">
                        <span class="material-symbols-rounded">dashboard</span>
                        Dashboard
                    </a>
                </li>
                <li class="active">
                    <a href="cerita.php">
                        <span class="material-symbols-rounded">menu_book</span>
                        Kelola Cerita
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="material-symbols-rounded">logout</span>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Kelola Cerita</h1>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Author</th>
                    <th>Tipe</th>
                    <th>Status Approval</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ceritaList as $cerita): ?>
                <?php 
                    $statusApproval = $cerita['approval_status'] ?? 'pending';
                    $bgApprove = ($statusApproval === 'approved') ? '#1B7B73' : '#EF4444'; 
                    $bgReject = ($statusApproval === 'rejected') ? '#DC2626' : '#94A3B8'; 
                ?>
                <tr>
                    <td>
                        <img src="../assets/data/images/<?= escapeHtml($cerita['cover_image']) ?>" 
                             width="50" style="border-radius:4px;">
                    </td>
                    <td><?= escapeHtml($cerita['judul']) ?></td>
                    <td><?= escapeHtml($cerita['author']) ?></td>
                    <td><?= escapeHtml($cerita['tipe']) ?></td>
                    <td>
                        <span style="padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #E5E7EB; color: #374151; text-transform: capitalize;">
                            <?= escapeHtml($statusApproval) ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" style="display:flex; gap:6px; align-items: center;">
                            <a href="edit_cerita.php?id=<?= $cerita['id'] ?>" class="btn-edit" style="background:#4B5563; padding:8px 14px; color:white; border-radius:6px; font-size:12px; text-decoration:none;">Edit</a>
                            
                            <input type="hidden" name="id" value="<?= (int) $cerita['id'] ?>">
                            
                            <button name="action" value="approve" style="background-color: <?= $bgApprove ?> !important; color: white !important; padding: 8px 14px; border-radius: 6px; font-size: 12px; border: none; cursor: pointer;">
                                <?= ($statusApproval === 'approved') ? 'Approved' : 'Approve' ?>
                            </button>
                            
                            <button name="action" value="reject" style="background-color: <?= $bgReject ?> !important; color: white !important; padding: 8px 14px; border-radius: 6px; font-size: 12px; border: none; cursor: pointer;">
                                <?= ($statusApproval === 'rejected') ? 'Rejected' : 'Reject' ?>
                            </button>
                            
                            <button name="action" value="delete" style="background-color: #EF4444 !important; color: white !important; padding: 8px 14px; border-radius: 6px; font-size: 12px; border: none; cursor: pointer;"
                                onclick="return confirm('Hapus cerita ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

</div>

</body>
</html>