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
    $name = trim((string) ($_POST['name'] ?? ''));
    $role = trim((string) ($_POST['role'] ?? ''));
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $force = isset($_POST['force_password_change']) ? 1 : 0;
    if ((int) $item['id'] === (int) $admin['id']) {
        $isActive = 1;
    }

    $errors = [];
    if ($name === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if (!in_array($role, ALLOWED_ROLES, true)) {
        $errors[] = 'Role tidak valid.';
    }
    if (!$errors) {
        db()->prepare('UPDATE users SET name = ?, role = ?, is_active = ?, force_password_change = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$name, $role, $isActive, $force, $id]);
        header('Location: users.php');
        exit;
    }
    $error = implode(' ', $errors);
    $item['name'] = $name;
    $item['role'] = $role;
    $item['is_active'] = $isActive;
    $item['force_password_change'] = $force;
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
      <div><div class="eyebrow">Super Admin</div><div class="title">Edit User</div></div>
      <a class="editlink" href="users.php">Kembali</a>
    </div>
    <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="form grid-form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
      <label>Nama <input name="name" value="<?= h($item['name']) ?>" required></label>
      <label>Username <input value="<?= h($item['username']) ?>" disabled></label>
      <label>Role
        <select name="role">
          <?php foreach (ALLOWED_ROLES as $role): ?><option value="<?= h($role) ?>" <?= $item['role'] === $role ? 'selected' : '' ?>><?= h(role_label($role)) ?></option><?php endforeach; ?>
        </select>
      </label>
      <div class="checks wide">
        <label><input type="checkbox" name="is_active" <?= $item['is_active'] ? 'checked' : '' ?> <?= (int) $item['id'] === (int) $admin['id'] ? 'disabled' : '' ?>> Aktif</label>
        <label><input type="checkbox" name="force_password_change" <?= $item['force_password_change'] ? 'checked' : '' ?>> Wajib ganti password</label>
      </div>
      <button class="primary wide">Simpan</button>
    </form>
  </div>
</body>
</html>
