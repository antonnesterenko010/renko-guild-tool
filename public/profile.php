<?php

declare(strict_types=1);

require __DIR__ . '/../config/init.php';

use App\Classes\App;
use App\Classes\Database;
use App\Classes\Content;

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
if ($_SESSION['role'] === 'admin') {
    Content::push('is_admin', true);
} else {
    Content::push('is_admin', false);
}
$current_user_id = $_SESSION['user_id'];
$current_user_query = Database::query(
    'SELECT u.id, u.login, uf.field_key, uf.field_value FROM izhachok_users AS u 
    LEFT JOIN izhachok_users_fields AS uf ON u.id = uf.user_id WHERE u.id = ?', [$current_user_id]);

$rows = $current_user_query->fetchAll();
$current_user = [
    'id' => $rows[0]['id'],
    'login' => $rows[0]['login'],
    'fields' => []
];
foreach ($rows as $row) {
    if ($row['field_key']) {
        $current_user['fields'][$row['field_key']] = $row['field_value'];
    }
}
$dungeon_query = Database::query('SELECT id, dungeon_key, dungeon_name, location FROM izhachok_dungeons ORDER BY location');
$dungeons = $dungeon_query->fetchAll();
$grouped_dungeons = [];
foreach ($dungeons as $dungeon) {
    $grouped_dungeons[$dungeon['location']][] = $dungeon;
}
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'user') {
    $users = Database::query('SELECT id, login, role, is_approved FROM izhachok_users ORDER BY id ASC')->fetchAll();
    Content::push('users', $users);
    $user = Database::query('SELECT login, role, is_approved FROM izhachok_users WHERE id = ?', [$current_user_id])->fetch();
    Content::push('is_approved', $user['is_approved']);
}
Content::push('dungeons', $grouped_dungeons);
Content::push('current_user', $current_user);
$current_user_signups_rows = Database::query(
    'SELECT dungeon_id, mode, character_name
     FROM izhachok_users_signup
     WHERE user_id = ?',
    [$current_user_id]
)->fetchAll();

$current_user_signups = [];

foreach ($current_user_signups_rows as $row) {
    $current_user_signups[$row['mode']][$row['character_name']][$row['dungeon_id']] = true;
}

Content::push('current_user_signups', $current_user_signups);
App::render('profile.twig', Content::get());