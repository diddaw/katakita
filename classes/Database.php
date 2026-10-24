<?php

class Database
{
    private PDO $connection;

    public function __construct()
    {
        $dataSourceName = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $this->connection = new PDO($dataSourceName, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $databaseException) {
            http_response_code(500);
            die('Database belum siap. Detail: ' . htmlspecialchars($databaseException->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}