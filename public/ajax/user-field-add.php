<?php

declare(strict_types=1);

require __DIR__ . '/ajax.php';

use App\Classes\Ajax;
use App\Classes\User;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Ajax::error('Method not allowed', 405);
}

$user_id = $_POST['user_id'] ?? null;
$fields = [
    'discord_nickname' => $_POST['discord_nickname'] ?? null,
    'main_nickname' => $_POST['main_nickname'] ?? null,
    'twink_nickname' => $_POST['twink_nickname'] ?? null,
    'spec_main' => $_POST['spec_main'] ?? null,
    'spec_twink' => $_POST['spec_twink'] ?? null,
];

$user = new User();
$user->addField($fields, $user_id);
Ajax::success([
    'user_id' => $user_id,
    'fields' => $fields
]);