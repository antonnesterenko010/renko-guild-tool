<?php

declare(strict_types=1);

namespace App\Classes;

use App\Classes\Database;

class Admin
{
    public static function approveUser($approveValue, $userId): void
    {
        Database::update(
            'izhachok_users',
            ['is_approved' => (int)$approveValue],
            ['id' => (int)$userId]
        );
    }
    public static function changeRole($role, $userId): void
    {
        Database::update(
            'izhachok_users',
            ['role' => (string)$role],
            ['id' => (int)$userId]
        );
    }
    public static function deleteUser($userId):void
    {
        $userId = (int)$userId;
        Database::delete('izhachok_users_fields', ['user_id' => $userId]);
        Database::delete('izhachok_users_signup', ['user_id' => $userId]);
        Database::delete('izhachok_users', ['id' => $userId]);
    }
}