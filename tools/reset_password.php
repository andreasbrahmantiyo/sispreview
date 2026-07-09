<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Script ini hanya untuk CLI.\n");
    exit(1);
}

require __DIR__ . '/../app/bootstrap.php';

$username = $argv[1] ?? '';
$force = in_array('--force', $argv, true);

if ($username === '') {
    fwrite(STDERR, "Usage: php tools/reset_password.php <username> [--force]\n");
    exit(1);
}

$stmt = db()->prepare('SELECT id, username FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();
if (!$user) {
    fwrite(STDERR, "User tidak ditemukan.\n");
    exit(1);
}

fwrite(STDOUT, "Password baru untuk {$username}: ");
$password = trim((string) fgets(STDIN));
if (strlen($password) < 8) {
    fwrite(STDERR, "Password minimal 8 karakter.\n");
    exit(1);
}

$stmt = db()->prepare('UPDATE users SET password_hash = ?, force_password_change = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([password_hash($password, PASSWORD_DEFAULT), $force ? 1 : 0, (int) $user['id']]);

fwrite(STDOUT, "Password berhasil direset.\n");
