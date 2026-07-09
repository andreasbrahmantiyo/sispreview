<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$loginError = null;

if (current_user()) {
    header('Location: index.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare(
        'SELECT id, name, username, password_hash, role, is_active, force_password_change
         FROM users
         WHERE username = ?
         LIMIT 1'
    );
    $stmt->execute([$username]);
    $userRow = $stmt->fetch();

    if ($userRow && (int) $userRow['is_active'] === 1 && password_verify($password, $userRow['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $userRow['id'];
        db()->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?')->execute([(int) $userRow['id']]);
        header('Location: ' . ((int) $userRow['force_password_change'] === 1 ? 'change_password.php' : 'index.php'));
        exit;
    }

    $loginError = 'Username atau password tidak cocok, atau user tidak aktif.';
}

require __DIR__ . '/../app/views_login.php';
