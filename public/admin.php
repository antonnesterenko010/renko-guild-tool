<?php

declare(strict_types=1);

require __DIR__ . '/../config/init.php';

use App\Classes\App;
use App\Classes\Database;
use App\Classes\Content;

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: /login');
    exit;
}
if ($_SESSION['role'] === 'admin') {
    Content::push('is_admin', true);
} else {
    Content::push('is_admin', false);
}
$current_user = $_SESSION['user_id'];
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'user') {
    $users = Database::query('SELECT id, login, role, is_approved FROM izhachok_users ORDER BY id ASC')->fetchAll();
    $$current_user = $_SESSION['user_id'];
    $user = Database::query('SELECT login, role, is_approved FROM izhachok_users WHERE id = ?', [$$current_user])->fetch();
    Content::push('users', $users);
    Content::push('is_approved', $user['is_approved']);
}

App::render('admin.twig', Content::get());