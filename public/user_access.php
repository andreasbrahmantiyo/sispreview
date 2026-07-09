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

$clients = all_clients(true);
$stmt = db()->prepare('SELECT client_id FROM user_clients WHERE user_id = ?');
$stmt->execute([$id]);
$assigned = array_map('intval', array_column($stmt->fetchAll(), 'client_id'));
$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $clientIds = $_POST['client_ids'] ?? [];
    $clientIds = is_array($clientIds) ? array_values(array_unique(array_filter(array_map('intval', $clientIds)))) : [];
    $errors = [];
    foreach ($clientIds as $clientId) {
        if (!client_exists($clientId)) {
            $errors[] = 'Ada client yang tidak valid.';
            break;
        }
    }

    if (!$errors) {
        db()->beginTransaction();
        db()->prepare('DELETE FROM user_clients WHERE user_id = ?')->execute([$id]);
        $insert = db()->prepare('INSERT INTO user_clients (user_id, client_id) VALUES (?, ?)');
        foreach ($clientIds as $clientId) {
            $insert->execute([$id, $clientId]);
        }
        db()->commit();
        header('Location: users.php');
        exit;
    }
    $error = implode(' ', $errors);
    $assigned = $clientIds;
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
      <div><div class="eyebrow">Super Admin</div><div class="title">Akses Client - <?= h($item['name']) ?></div></div>
      <a class="editlink" href="users.php">Kembali</a>
    </div>
    <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
      <div class="checks">
        <?php foreach ($clients as $client): ?>
          <label><input type="checkbox" name="client_ids[]" value="<?= (int) $client['id'] ?>" <?= in_array((int) $client['id'], $assigned, true) ? 'checked' : '' ?>> <?= h($client['name']) ?></label>
        <?php endforeach; ?>
      </div>
      <button class="primary">Simpan Akses</button>
    </form>
  </div>
</body>
</html>
