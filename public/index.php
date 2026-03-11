<?php

declare(strict_types=1);

require __DIR__ . '/../config/init.php';

use App\Classes\App;
use App\Classes\Database;
use App\Classes\Content;
if (!isset($_SESSION['role'])) {
    header('Location: /login');
    exit;
}
if ($_SESSION['role'] === 'guest') {
    Content::push('is_guest', true);
} else {
    Content::push('is_guest', false);
}

if ($_SESSION['role'] === 'user') {
    Content::push('is_user', true);
} else {
    Content::push('is_user', false);
}
if ($_SESSION['role'] === 'admin') {
    Content::push('is_admin', true);
} else {
    Content::push('is_admin', false);
}
$dungeon_query = Database::query('SELECT id, dungeon_key, dungeon_name, location FROM izhachok_dungeons ORDER BY id');
$dungeons = $dungeon_query->fetchAll();
$grouped_dungeons = [];
foreach ($dungeons as $dungeon) {
    $grouped_dungeons[$dungeon['location']][] = $dungeon;
}
Content::push('dungeons', $grouped_dungeons);

$signup_rows = Database::query(
    "SELECT 
        s.dungeon_id,
        s.mode,
        s.character_name,
        COALESCE(
            MAX(CASE WHEN uf.field_key = 'main_nickname' AND s.character_name = 'main' THEN uf.field_value END),
            MAX(CASE WHEN uf.field_key = 'twink_nickname' AND s.character_name = 'twink' THEN uf.field_value END),
            u.login
        ) AS display_name,
        MAX(CASE WHEN uf.field_key = 'discord_nickname' THEN uf.field_value END) AS discord_nickname,
        MAX(CASE WHEN uf.field_key = 'spec_main' THEN uf.field_value END) AS spec_main,
        MAX(CASE WHEN uf.field_key = 'spec_twink' THEN uf.field_value END) AS spec_twink
    FROM izhachok_users_signup s
    JOIN izhachok_users u ON u.id = s.user_id
    LEFT JOIN izhachok_users_fields uf ON uf.user_id = s.user_id
    GROUP BY s.id, s.dungeon_id, s.mode, s.character_name, u.login
")->fetchAll();

$signups = [];

$signups = [];

foreach ($signup_rows as $row) {
    $spec = $row['character_name'] === 'main'
        ? $row['spec_main']
        : $row['spec_twink'];

    $signups[$row['mode']][$row['dungeon_id']][] = [
        'name' => $row['display_name'],
        'discord_nickname' => $row['discord_nickname'],
        'spec' => $spec
    ];
}

Content::push('signups', $signups);

if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'user') {
    $id = $_SESSION['user_id'];
    $user = Database::query('SELECT login, role, is_approved FROM izhachok_users WHERE id = ?', [$id])->fetch();
    Content::push('is_approved', $user['is_approved']);
}

App::render('home.twig', Content::get());