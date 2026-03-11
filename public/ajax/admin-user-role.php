<?php

declare(strict_types=1);

require __DIR__ . '/ajax.php';

use App\Classes\Ajax;
use App\Classes\Admin;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Ajax::error('Method not allowed', 405);
}

$user_id = $_POST['user_id'] ?? null;
$user_role = $_POST['user_role'] ?? null;
Admin::changeRole($user_role, $user_id);

Ajax::success([
    'user_id' => $user_id,
    'role' => $user_role
]);