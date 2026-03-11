<?php

declare(strict_types=1);

namespace App\Classes;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

class App{
    private static ?Environment $twig = null;

    public static function debug(bool $enabled = true): void
    {
        if($enabled) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
            error_reporting(0);
        }
    }

    private static function twig(): Environment
    {
        if (self::$twig) {
            return self::$twig;
        }

        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');

        self::$twig = new Environment($loader, [
            'cache' => __DIR__ . '/../../../var/cache/twig',
            'auto_reload' => true,
            'debug' => true
        ]);

        self::$twig->addExtension(new DebugExtension());
        return self::$twig;
    }

    public static function render(string $template, array $data = []): void
    {
        $data['page'] = pathinfo($template, PATHINFO_FILENAME);
        echo self::twig()->render($template, $data);
    }

    public static function getUserIp(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    public static function isIpBanned(string $event): bool
    {
        $ip = self::getUserIp();
        $query = Database::query('SELECT id FROM izhachok_users_banlist WHERE event_name = ? AND ip_address = ? AND banned_until > NOW() LIMIT 1', [$event, $ip]);
        return (bool)$query->fetch();
    }
    public static function addAttempt(string $event): void
    {
        $ip = self::getUserIp();
        Database::query('INSERT INTO izhachok_users_attempts (event_name, ip_address, created_at) VALUES (?, ?, NOW())', [$event, $ip]);
    }
    public static function countAttempts(string $event, int $seconds): int
    {
        $ip = self::getUserIp();
        $query = Database::query('SELECT COUNT(*) AS total FROM izhachok_users_attempts WHERE event_name = ? AND ip_address = ? AND created_at >= (NOW() - INTERVAL ? SECOND)', [$event, $ip, $seconds]);
        $result = $query->fetch();
        return (int)($result['total'] ?? 0);
    }
    public static function banIp(string $event, int $minutes): void
    {
        $ip = self::getUserIp();
        Database::query('INSERT INTO izhachok_users_banlist (event_name, ip_address, banned_at, banned_until) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? MINUTE))', [$event, $ip, $minutes]);
    }
    public static function securityEvent( string $event, int $max_attempts = 5, int $period_seconds = 10, int $ban_minutes = 5): bool 
    {
        if (self::isIpBanned($event)) {
            return false;
        }
        self::addAttempt($event);
        $attempts = self::countAttempts($event, $period_seconds);
        if ($attempts > $max_attempts) {
            self::banIp($event, $ban_minutes);
            return false;
        }
        return true;
    }
}