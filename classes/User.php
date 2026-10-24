<?php

class User
{
    private PDO $databaseConnection;

    public function __construct(PDO $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->databaseConnection->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $userId): ?array
    {
        $stmt = $this->databaseConnection->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

 public function register(string $username, string $email, string $noTelepon, string $password): bool
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $this->databaseConnection->prepare("
            INSERT INTO users (username, email, no_telepon, password_hash, role)
            VALUES (?, ?, ?, ?, 'user')
        ");
        return $stmt->execute([$username, $email, $noTelepon, $passwordHash]);
    } catch (PDOException $e) {
        return false;
    }
}

    public function getAll(): array
    {
        return $this->databaseConnection
            ->query("SELECT * FROM users ORDER BY created_at DESC")
            ->fetchAll();
    }

    public function updateProfile(int $userId, string $username, string $email): bool
    {
        $stmt = $this->databaseConnection->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $userId]);
    }

    public function updateRekening(int $userId, string $bankNama, string $bankNorek, string $bankPemilik): bool
{
    $stmt = $this->databaseConnection->prepare("
        UPDATE users SET bank_nama = ?, bank_norek = ?, bank_pemilik = ? WHERE id = ?
    ");
    return $stmt->execute([$bankNama, $bankNorek, $bankPemilik, $userId]);
}

    public function delete(int $userId): bool
    {
        $stmt = $this->databaseConnection->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}