<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$admin = require_role('super_admin');
$clients = all_clients(true);
$error = null;
$item = [
    'name' => '',
    'username' => '',
    'role' => 'client_viewer',
    'is_active' => '1',
    'force_password_change' => '1',
    'client_ids' => [],
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $item['name'] = trim((string) ($_POST['name'] ?? ''));
    $item['username'] = trim((string) ($_POST['username'] ?? ''));
    $item['role'] = trim((string) ($_POST['role'] ?? ''));
    $item['is_active'] = isset($_POST['is_active']) ? '1' : '0';
    $item['force_password_change'] = isset($_POST['force_password_change']) ? '1' : '0';
    $password = (string) ($_POST['password'] ?? '');
    $clientIds = $_POST['client_ids'] ?? [];
    $item['client_ids'] = is_array($clientIds) ? array_values(array_filter(array_map('intval', $clientIds))) : [];

    $errors = [];
    if ($item['name'] === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,80}$/', $item['username'])) {
        $errors[] = 'Username minimal 3 karakter dan hanya huruf, angka, titik, underscore, atau strip.';
    }
    if (!in_array($item['role'], ALLOWED_ROLES, true)) {
        $errors[] = 'Role tidak valid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }
    foreach ($item['client_ids'] as $clientId) {
        if (!client_exists($clientId)) {
            $errors[] = 'Ada client yang tidak valid.';
            break;
        }
    }

    if (!$errors) {
        try {
            db()->beginTransaction();
            $stmt = db()->prepare(
                'INSERT INTO users (name, username, password_hash, role, is_active, force_password_change)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $item['name'],
                $item['username'],
                password_hash($password, PASSWORD_DEFAULT),
                $item['role'],
                $item['is_active'],
                $item['force_password_change'],
            ]);
            $newUserId = (int) db()->lastInsertId();
            $insert = db()->prepare('INSERT INTO user_clients (user_id, client_id) VALUES (?, ?)');
            foreach ($item['client_ids'] as $clientId) {
                $insert->execute([$newUserId, $clientId]);
            }
            db()->commit();
            header('Location: users.php');
            exit;
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $errors[] = 'User gagal disimpan. Pastikan username belum dipakai.';
        }
    }
    $error = implode(' ', $errors);
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
      <div><div class="eyebrow">Super Admin</div><div class="title">Tambah User</div></div>
      <a class="editlink" href="users.php">Kembali</a>
    </div>
    <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="form grid-form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <label>Nama <input name="name" value="<?= h($item['name']) ?>" required></label>
      <label>Username <input name="username" value="<?= h($item['username']) ?>" required></label>
      <label>Role
        <select name="role">
          <?php foreach (ALLOWED_ROLES as $role): ?><option value="<?= h($role) ?>" <?= $item['role'] === $role ? 'selected' : '' ?>><?= h(role_label($role)) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Password Awal <input type="password" name="password" autocomplete="new-password" required></label>
      <div class="checks wide">
        <label><input type="checkbox" name="is_active" <?= $item['is_active'] ? 'checked' : '' ?>> Aktif</label>
        <label><input type="checkbox" name="force_password_change" <?= $item['force_password_change'] ? 'checked' : '' ?>> Wajib ganti password</label>
      </div>
      <div class="checks wide">
        <?php foreach ($clients as $client): ?>
          <label><input type="checkbox" name="client_ids[]" value="<?= (int) $client['id'] ?>" <?= in_array((int) $client['id'], $item['client_ids'], true) ? 'checked' : '' ?>> <?= h($client['name']) ?></label>
        <?php endforeach; ?>
      </div>
      <button class="primary wide">Simpan</button>
    </form>
  </div>
</body>
</html>
