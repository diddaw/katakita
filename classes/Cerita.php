<?php

class Cerita
{
    private PDO $databaseConnection;

    public function __construct(PDO $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    public function getLatestApproved(int $limit = 10): array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT cerita.*,
            MIN(chapter.harga) as harga_chapter_pertama,
            GROUP_CONCAT(genre.nama SEPARATOR ', ') as genre
            FROM cerita
            LEFT JOIN cerita_genre cg ON cg.cerita_id = cerita.id
            LEFT JOIN genre ON genre.id = cg.genre_id
            LEFT JOIN chapter ON chapter.cerita_id = cerita.id
            WHERE cerita.user_id IS NULL
            AND cerita.is_new_release = 1
            AND cerita.slide_only = 0
            GROUP BY cerita.id 
            ORDER BY cerita.created_at DESC 
            LIMIT ? 
        ");     
        
        $stmt->bindValue(1, $limit, PDO::PARAM_INT); 
        $stmt->execute(); 

        return $stmt->fetchAll(); 
    }

    public function findApprovedById(int $ceritaId): ?array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT cerita.*, users.username,
            GROUP_CONCAT(genre.nama SEPARATOR ', ') as genre
            FROM cerita
            LEFT JOIN users ON users.id = cerita.user_id
            LEFT JOIN cerita_genre cg ON cg.cerita_id = cerita.id
            LEFT JOIN genre ON genre.id = cg.genre_id
            WHERE cerita.id = ? AND cerita.approval_status = 'approved'
            GROUP BY cerita.id
        ");
        $stmt->execute([$ceritaId]);
        $cerita = $stmt->fetch();

        return $cerita ?: null;
    }

    public function findByIdForUser(int $ceritaId, int $userId): ?array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT * FROM cerita WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$ceritaId, $userId]);
        $cerita = $stmt->fetch();

        return $cerita ?: null;
    }

    public function updateByUser(int $ceritaId, int $userId, array $data): bool
    {
        // Mengubah data cerita tanpa mengubah/mereset approval_status
        $stmt = $this->databaseConnection->prepare("
            UPDATE cerita
            SET judul = ?, author = ?, deskripsi = ?, cover_image = ?, tipe = ?
            WHERE id = ? AND user_id = ?
        ");

        return $stmt->execute([
            $data['judul'],
            $data['author'],
            $data['deskripsi'],
            $data['cover_image'],
            $data['tipe'],
            $ceritaId,
            $userId,
        ]);
    }

    public function updateGenres(int $ceritaId, array $genreIds): void
    {
        // 1. Hapus genre lama cerita ini agar tidak duplikat
        $stmtDelete = $this->databaseConnection->prepare("DELETE FROM cerita_genre WHERE cerita_id = ?");
        $stmtDelete->execute([$ceritaId]);

        // 2. Simpan pilihan genre baru
        if (!empty($genreIds)) {
            $genreIdsUnique = array_unique($genreIds);
            $stmtInsert = $this->databaseConnection->prepare("INSERT IGNORE INTO cerita_genre (cerita_id, genre_id) VALUES (?, ?)");
            foreach ($genreIdsUnique as $genreId) {
                $stmtInsert->execute([$ceritaId, (int) $genreId]);
            }
        }
    }

    public function updateStatus(int $ceritaId, string $status): bool
    {
        $stmt = $this->databaseConnection->prepare("UPDATE cerita SET approval_status = ? WHERE id = ?");
        return $stmt->execute([$status, $ceritaId]);
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT * FROM cerita WHERE user_id = ? ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function getAllForAdmin(): array
    {
        return $this->databaseConnection->query("
            SELECT * FROM cerita
            WHERE user_id IS NOT NULL
            ORDER BY created_at DESC
        ")->fetchAll();
    }

    public function delete(int $ceritaId): bool
    {
        // Hapus dulu relasi genre
        $stmt = $this->databaseConnection->prepare("DELETE FROM cerita_genre WHERE cerita_id = ?");
        $stmt->execute([$ceritaId]);

        // Baru hapus cerita
        $stmt = $this->databaseConnection->prepare("DELETE FROM cerita WHERE id = ?");
        return $stmt->execute([$ceritaId]);
    }

    public function getRandom(int $limit = 8): array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT * FROM cerita WHERE is_recommended = 1 ORDER BY RAND() LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function search(string $keyword): array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT cerita.*,
                   GROUP_CONCAT(genre.nama SEPARATOR ', ') AS genre
            FROM cerita
            LEFT JOIN cerita_genre cg ON cg.cerita_id = cerita.id
            LEFT JOIN genre ON genre.id = cg.genre_id
            WHERE cerita.approval_status = 'approved'
            AND cerita.slide_only = 0
            AND cerita.judul LIKE ?
            GROUP BY cerita.id
            ORDER BY cerita.judul ASC
        ");

        $stmt->execute(['%' . $keyword . '%']);

        return $stmt->fetchAll();
    }
public function create(array $data): bool
{
    $stmt = $this->databaseConnection->prepare("
        INSERT INTO cerita
        (user_id, judul, author, deskripsi, cover_image, tipe, harga, approval_status, is_new_release, slide_only, is_recommended)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 0, 0, 0)
    ");

    return $stmt->execute([
        $data['user_id'],
        $data['judul'],
        $data['author'],
        $data['deskripsi'],
        $data['cover_image'],
        $data['tipe'],
        $data['harga'], 
    ]);
}

    public function hasPurchased(int $chapterId, int $userId): bool
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT id FROM transaksi
            WHERE chapter_id = ? AND user_id = ? AND status = 'approved'
            LIMIT 1
        ");
        $stmt->execute([$chapterId, $userId]);

        return (bool) $stmt->fetch();
    }

    public function recordPurchase(int $chapterId, int $ceritaId, int $userId, int $harga, string $buktiTransfer): bool
    { 
        $stmt = $this->databaseConnection->prepare("
            INSERT INTO transaksi (user_id, cerita_id, chapter_id, harga, bukti_transfer, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");

        return $stmt->execute([$userId, $ceritaId, $chapterId, $harga, $buktiTransfer]);
    }

    public function findChapter(int $ceritaId, int $nomorChapter): ?array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT * FROM chapter WHERE cerita_id = ? AND nomor_chapter = ?
        ");
        $stmt->execute([$ceritaId, $nomorChapter]);
        $chapter = $stmt->fetch();

        return $chapter ?: null;
    }

    public function getTransaksiBySeller(int $sellerId): array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT transaksi.*, users.username AS pembeli, cerita.judul AS judul_cerita, chapter.nomor_chapter
            FROM transaksi
            JOIN users ON users.id = transaksi.user_id
            JOIN cerita ON cerita.id = transaksi.cerita_id
            JOIN chapter ON chapter.id = transaksi.chapter_id
            WHERE cerita.user_id = ?
            ORDER BY transaksi.created_at DESC
        ");
        $stmt->execute([$sellerId]);

        return $stmt->fetchAll();
    }

    public function getChapterImages(int $chapterId): array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT * FROM chapter_images WHERE chapter_id = ? ORDER BY id ASC
        ");
        $stmt->execute([$chapterId]);

        return $stmt->fetchAll();
    }

    public function deleteChapter(int $chapterId, int $ceritaId, int $userId): bool
    {
        $cerita = $this->findByIdForUser($ceritaId, $userId);
        if (!$cerita) {
            return false;
        }

        $stmt = $this->databaseConnection->prepare("DELETE FROM chapter_images WHERE chapter_id = ?");
        $stmt->execute([$chapterId]);

        $stmt = $this->databaseConnection->prepare("DELETE FROM chapter WHERE id = ? AND cerita_id = ?");
        return $stmt->execute([$chapterId, $ceritaId]);
    }

    public function getPendingTransaksiBySeller(int $sellerId): array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT transaksi.*, users.username AS pembeli, cerita.judul AS judul_cerita, chapter.nomor_chapter
            FROM transaksi
            JOIN users ON users.id = transaksi.user_id
            JOIN cerita ON cerita.id = transaksi.cerita_id
            JOIN chapter ON chapter.id = transaksi.chapter_id
            WHERE cerita.user_id = ? AND transaksi.status = 'pending'
            ORDER BY transaksi.created_at ASC
        ");
        $stmt->execute([$sellerId]);

        return $stmt->fetchAll();
    }

    public function updateTransaksiStatusBySeller(int $transaksiId, int $sellerId, string $status): bool
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT transaksi.id
            FROM transaksi
            JOIN cerita ON cerita.id = transaksi.cerita_id
            WHERE transaksi.id = ? AND cerita.user_id = ?
        ");
        $stmt->execute([$transaksiId, $sellerId]);

        if (!$stmt->fetch()) {
            return false;
        }

        $stmt = $this->databaseConnection->prepare("UPDATE transaksi SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $transaksiId]);
    }

    public function getChapterById(int $chapterId, int $ceritaId, int $userId): ?array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT chapter.* FROM chapter
            JOIN cerita ON cerita.id = chapter.cerita_id
            WHERE chapter.id = ? AND chapter.cerita_id = ? AND cerita.user_id = ?
        ");
        $stmt->execute([$chapterId, $ceritaId, $userId]);
        $chapter = $stmt->fetch();

        return $chapter ?: null;
    }

    public function updateChapterText(int $chapterId, string $judulChapter, string $spoiler, string $kontenTeks, int $harga): bool
    {
        $stmt = $this->databaseConnection->prepare("
            UPDATE chapter SET judul_chapter = ?, spoiler = ?, konten_teks = ?, harga = ? WHERE id = ?
        ");
        return $stmt->execute([$judulChapter, $spoiler, $kontenTeks, $harga, $chapterId]);
    }

    public function updateChapterMeta(int $chapterId, string $judulChapter, string $spoiler, int $harga): bool
    {
        $stmt = $this->databaseConnection->prepare("
            UPDATE chapter SET judul_chapter = ?, spoiler = ?, harga = ? WHERE id = ?
        ");
        return $stmt->execute([$judulChapter, $spoiler, $harga, $chapterId]);
    }

    public function addChapterImages(int $chapterId, array $filePaths): void
    {
        $stmt = $this->databaseConnection->prepare("
            INSERT INTO chapter_images (chapter_id, file_path) VALUES (?, ?)
        ");
        foreach ($filePaths as $file) {
            $stmt->execute([$chapterId, $file]);
        }
    }

    public function getNewReleasesAdmin(): array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT c.*, GROUP_CONCAT(g.nama SEPARATOR ', ') as genre 
            FROM cerita c 
            LEFT JOIN cerita_genre cg ON c.id = cg.cerita_id 
            LEFT JOIN genre g ON cg.genre_id = g.id 
            WHERE c.is_new_release = 1 AND c.slide_only = 0 AND c.approval_status = 'approved'
            GROUP BY c.id 
            ORDER BY c.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTransaksiByBuyer(int $buyerId): array
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT transaksi.*, cerita.judul AS judul_cerita, chapter.nomor_chapter
            FROM transaksi
            JOIN cerita ON cerita.id = transaksi.cerita_id
            JOIN chapter ON chapter.id = transaksi.chapter_id
            WHERE transaksi.user_id = ?
            ORDER BY transaksi.created_at DESC
        ");
        $stmt->execute([$buyerId]);

        return $stmt->fetchAll();
    }
}