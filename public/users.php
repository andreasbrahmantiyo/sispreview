<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$user = require_role('super_admin');
$rows = db()->query(
    "SELECT u.*,
            (
              SELECT GROUP_CONCAT(c.code ORDER BY c.code SEPARATOR ', ')
              FROM user_clients uc
              JOIN clients c ON c.id = uc.client_id
              WHERE uc.user_id = u.id
            ) AS client_codes
     FROM users u
     ORDER BY u.name ASC"
)->fetchAll();
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
  <div class="edit-shell admin-shell">
    <div class="edit-head">
      <div><div class="eyebrow">Super Admin</div><div class="title">User Management</div></div>
      <div class="admin-actions"><a class="editlink" href="user_create.php">Tambah User</a><a class="editlink" href="index.php">Dashboard</a></div>
    </div>
    <div class="wrap admin-table">
      <table>
        <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Client</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= h($row['name']) ?></td>
              <td><?= h($row['username']) ?></td>
              <td><?= h(role_label($row['role'])) ?></td>
              <td><?= h($row['client_codes'] ?: '-') ?></td>
              <td><?= (int) $row['is_active'] === 1 ? 'Aktif' : 'Nonaktif' ?><?= (int) $row['force_password_change'] === 1 ? ' / wajib ganti password' : '' ?></td>
              <td>
                <a class="editlink" href="user_edit.php?id=<?= (int) $row['id'] ?>">Edit</a>
                <a class="editlink" href="user_access.php?id=<?= (int) $row['id'] ?>">Client</a>
                <a class="editlink" href="user_reset_password.php?id=<?= (int) $row['id'] ?>">Reset</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
