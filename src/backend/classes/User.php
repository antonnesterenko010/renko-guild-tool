<?php

declare(strict_types=1);

namespace App\Classes;

use App\Classes\Database;

class User
{
    public function save(string $login): void
    {
        Database::query(
            'INSERT INTO izhachok_users (login) VALUES (?)',
            [$login]
        );
    }
    public function signUp(string $login, string $password) : int|false
    {
        $exists = Database::query('SELECT id FROM izhachok_users WHERE login = ? LIMIT 1', [$login])->fetch();
        if ($exists) {
            return false;
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        Database::query('INSERT INTO izhachok_users (login, password_hash, role, is_approved) VALUES (?, ?, ?, ?)', [$login, $password_hash, 'user', 0]);
        return (int)Database::lastInsertId();
    }
    public function addField($fields, $user_id): void
    {
        foreach ($fields as $field_key => $field_value) {
            if ($field_value === 'none') {
                Database::delete('izhachok_users_fields', [
                    'user_id' => (int)$user_id,
                    'field_key' => $field_key
                ]);
                continue;
            }
            if ($field_value) {
                Database::query(
                    'INSERT INTO izhachok_users_fields (user_id, field_key, field_value) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE field_value = VALUES(field_value)',
                    [(int)$user_id, $field_key, $field_value]
                );
            }
        }
    }
    public function dungeonSignUp(int $user_id, int $dungeon_id, string $mode, string $character_name): void
    {
        Database::query(
            'INSERT INTO izhachok_users_signup (user_id, dungeon_id, mode, character_name)
            VALUES (?, ?, ?, ?)',
            [$user_id, $dungeon_id, $mode, $character_name]
        );
    }

    public function clearDungeonSignUps(int $user_id, string $mode, string $character_name): void
    {
        Database::query(
            'DELETE FROM izhachok_users_signup
            WHERE user_id = ? AND mode = ? AND character_name = ?',
            [$user_id, $mode, $character_name]
        );
    }
    
}