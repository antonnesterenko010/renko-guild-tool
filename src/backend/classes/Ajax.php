<?php

declare(strict_types=1);

namespace App\Classes;

class Ajax {
    public static function init(): void
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    public static function requireAuthorized(): void
    {
        if (!isset($_SESSION['user_id'])) {
            self::error('Unauthorized', 401);
        }

        if (($_SESSION['role'] ?? '') !== 'admin' && ($_SESSION['role'] ?? '') !== 'user') {
            self::error('Forbidden', 403);
        }
    }

    public static function success(array $data = []): void
    {
        echo json_encode(['ok' => true] + $data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}