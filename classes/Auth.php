<?php

class Auth
{
    private PDO $databaseConnection;

    public function __construct(PDO $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    public function loginAdmin(string $username, string $password): bool
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT * FROM admins WHERE username = ? LIMIT 1
        ");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if (!$admin || $password !== $admin['password']) {
            return false;
        }

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['role'] = 'admin';

        return true;
    }

    public function login(string $username, string $password): bool
    {
        $stmt = $this->databaseConnection->prepare("
            SELECT * FROM users WHERE username = ? LIMIT 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        return true;
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function checkAdmin(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public function isAdmin(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public function requireLogin(): void
    {
        if (!$this->check()) {
            redirectTo('/login.php');
        }
    }

    public function requireAdmin(): void
    {
        if (!$this->checkAdmin()) {
            redirectTo('/admin/login.php');
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}