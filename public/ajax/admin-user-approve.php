<?php

declare(strict_types=1);

require __DIR__ . '/ajax.php';

use App\Classes\Ajax;
use App\Classes\Admin;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Ajax::error('Method not allowed', 405);
}

$user_id = $_POST['user_id'] ?? null;
$is_approved = $_POST['user_approved'] ?? null;
if ($is_approved === null || $user_id === null) {
    Ajax::error('Missing parameters', 400);
}

Admin::approveUser($is_approved, $user_id);

Ajax::success([
    'user_id' => $user_id,
    'approved' => $is_approved
]);