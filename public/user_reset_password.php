<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$admin = require_role('super_admin');
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) {
    http_response_code(404);
    echo 'User tidak ditemukan.';
    exit;
}

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');
    $force = isset($_POST['force_password_change']) ? 1 : 0;

    if (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        db()->prepare('UPDATE users SET password_hash = ?, force_password_change = ?, updated_at = NOW() WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $force, $id]);
        header('Location: users.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SISPREVIEW — Sistem Project View</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="edit-shell">
    <div class="edit-head">
      <div><div class="eyebrow">Super Admin</div><div class="title">Reset Password - <?= h($item['name']) ?></div></div>
      <a class="editlink" href="users.php">Kembali</a>
    </div>
    <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
      <label>Password Baru <input type="password" name="password" autocomplete="new-password" required></label>
      <label>Konfirmasi <input type="password" name="password_confirm" autocomplete="new-password" required></label>
      <div class="checks"><label><input type="checkbox" name="force_password_change" checked> Wajib ganti password saat login</label></div>
      <button class="primary">Reset Password</button>
    </form>
  </div>
</body>
</html>
