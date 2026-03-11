<?php

declare(strict_types=1);

require __DIR__ . '/ajax.php';

use App\Classes\App;
use App\Classes\Ajax;
use App\Classes\User;

Ajax::requireAuthorized();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Ajax::error('Method not allowed', 405);
}
if (!App::securityEvent('user_dungeon_signup', 10, 10, 5)) {
    Ajax::error('Too many attempts. Try again later.', 429);
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$mode_id = (int)($_POST['mode_id'] ?? 0);
$character_id = (int)($_POST['character_id'] ?? 0);
$dungeons = $_POST['dungeons'] ?? [];

$modes = [
    1 => 'heroic',
    2 => 'normal',
    3 => 'other',
];

$characters = [
    1 => 'main',
    2 => 'twink',
];

$mode = $modes[$mode_id] ?? null;
$character_name = $characters[$character_id] ?? null;

if (!$user_id || !$mode || !$character_name) {
    Ajax::error('Invalid data', 400);
}

$user = new User();
$user->clearDungeonSignUps($user_id, $mode, $character_name);

foreach ($dungeons as $dungeon_id) {
    $user->dungeonSignUp($user_id, (int)$dungeon_id, $mode, $character_name);
}

Ajax::success([
    'user_id' => $user_id,
    'mode' => $mode,
    'character_name' => $character_name,
    'dungeons' => $dungeons
]);