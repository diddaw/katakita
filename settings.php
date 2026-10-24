<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$errors = [];
$success = '';

$stmt = $databaseConnection->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $fotoProfil = $user['foto_profil'] ?? null;

    if (empty($newUsername) || empty($newEmail)) {
        $errors[] = 'Username dan Email wajib diisi.';
    }

    if (!empty($_FILES['foto_profil']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $errors[] = 'Format foto harus JPG, PNG, atau WEBP.';
        } elseif ($_FILES['foto_profil']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran foto maksimal 2MB.';
        } else {
            $fileName = 'profil-' . $userId . '-' . time() . '.' . $ext;
            $targetDir = __DIR__ . '/assets/data/images/profil';
            
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $targetDir . '/' . $fileName)) {
                $fotoProfil = $fileName;
            } else {
                $errors[] = 'Gagal mengupload foto profil.';
            }
        }
    }

    if (!$errors) {
        $updateStmt = $databaseConnection->prepare("UPDATE users SET username = ?, email = ?, foto_profil = ? WHERE id = ?");
        if ($updateStmt->execute([$newUsername, $newEmail, $fotoProfil, $userId])) {
            $success = 'Pengaturan akun berhasil diperbarui!';
            $_SESSION['username'] = $newUsername; 
            
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan data.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/settings.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="settings-container">
    <div class="settings-card">
        <h2>Pengaturan Akun</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div>• <?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            
            <div class="foto-profil-wrapper">
                <div class="foto-preview">
                    <?php if (!empty($user['foto_profil'])): ?>
                        <img id="preview-img" src="assets/data/images/profil/<?= htmlspecialchars($user['foto_profil']) ?>" alt="Foto Profil">
                    <?php else: ?>
                        <div id="preview-placeholder" class="placeholder"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                
                <input type="file" name="foto_profil" id="foto_upload" accept=".jpg,.jpeg,.png,.webp" onchange="previewImage(event)">
                <label for="foto_upload" class="ganti-teks">ganti profil</label>
            </div>

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <button type="submit" class="btn-save">Simpan Perubahan</button>
        </form>
        <a href="profile.php" class="btn-back">← Kembali ke Profil</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('preview-img');
        if (!output) {
            var placeholder = document.getElementById('preview-placeholder');
            var newImg = document.createElement('img');
            newImg.id = 'preview-img';
            newImg.src = reader.result;
            placeholder.parentNode.replaceChild(newImg, placeholder);
        } else {
            output.src = reader.result;
        }
    }
    if(event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>
</body>
</html>