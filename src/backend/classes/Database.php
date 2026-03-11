<?php

declare(strict_types=1);

namespace App\Classes;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        $config = require __DIR__ . '/../../../config/db.php';

        self::$pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
            $config['user'],
            $config['password']
        );

        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public static function update(string $table, array $data, array $where): int
    {
        $set = [];
        $params = [];

        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
            $params[] = $value;
        }

        $conditions = [];
        foreach ($where as $column => $value) {
            $conditions[] = "$column = ?";
            $params[] = $value;
        }

        $sql = "UPDATE $table SET " . implode(', ', $set)
            . " WHERE " . implode(' AND ', $conditions);

        $stmt = self::query($sql, $params);

        return $stmt->rowCount();
    }
    public static function lastInsertId(): string
    {
        return self::get()->lastInsertId();
    }
    public static function delete(string $table, array $where): int
    {
        $conditions = [];
        $params = [];

        foreach ($where as $column => $value) {
            $conditions[] = "$column = ?";
            $params[] = $value;
        }

        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $conditions);

        $stmt = self::query($sql, $params);

        return $stmt->rowCount();
    }
}