<?php

declare(strict_types=1);

require __DIR__ . '/ajax.php';

use App\Classes\Ajax;
use App\Classes\Admin;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Ajax::error('Method not allowed', 405);
}

$user_id = $_POST['user_id'] ?? null;
Admin::deleteUser($user_id);

Ajax::success([
    'user_id' => $user_id
]);