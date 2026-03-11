<?php

declare(strict_types=1);

require __DIR__ . '/../config/init.php';

use App\Classes\App;
use App\Classes\Database;
use App\Classes\User;

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header('Location: /');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'guest') {
        if(!App::securityEvent('guest_login', 5, 5, 5)) {
            $error = 'Too many attempts. Try again later.';
        } else {
            $_SESSION['role'] = 'guest';
            header('Location: /');
            exit;
        }
    }
    if ($action === 'login') {
        if(!App::securityEvent('login', 5, 5, 5)) {
            $error = 'Too many attempts for login in a short period. Try again later.';
        } else {
            $login = trim((string)($_POST['login'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            $query = Database::query('SELECT id, password_hash, role, is_approved FROM izhachok_users WHERE login = ? LIMIT 1', [$login]);
            $user = $query->fetch();
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Invalid login or password';
            } elseif ((int)$user['is_approved'] !== 1) {
                $error = 'Account not approved yet';
            } else {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['role'] = $user['role'];
                header('Location: /');
                exit;
            }
        }
    }
    if ($action === 'signup') {

        if(!App::securityEvent('signup', 5, 5, 5)) {
            $error = 'Too many attempts for signup in a short period. Try again later.';
        } else {
            $login = trim((string)($_POST['login'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            $user = new User();
            $user_id = $user->signup($login, $password);
            if (!$user_id) {
                $error = 'Login already exists';
            } else {
                $error = 'Account created. Waiting for approval.';
            }
        }
    }
}

App::render('login.twig', ['error' => $error]);

