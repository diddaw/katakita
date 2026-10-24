<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Cerita.php';
require_once __DIR__ . '/../classes/Validator.php';

session_start();

$database = new Database();
$databaseConnection = $database->getConnection();

function redirectTo(string $path): void
{
    header('Location: ' . APP_URL . $path);
    exit;
}

function escapeHtml($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
function uploadCoverImage(array $uploadedFile, array &$errors): ?string
{
    if (($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload gambar gagal.';
        return null;
    }

    if ($uploadedFile['size'] > 100 * 1024 * 1024) {
    $errors[] = 'Ukuran gambar maksimal 100MB.';
    return null;
}

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $originalFileName = $uploadedFile['name'] ?? '';
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions, true)) {
        $errors[] = 'Format gambar harus JPG, PNG, atau WEBP.';
        return null;
    }

    $newFileName = 'cover-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
    $uploadDirectory = __DIR__ . '/../assets/data/images';

    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    $targetPath = $uploadDirectory . '/' . $newFileName;

    if (!move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
        $errors[] = 'Gambar tidak bisa disimpan.';
        return null;
    }

    return $newFileName;
}