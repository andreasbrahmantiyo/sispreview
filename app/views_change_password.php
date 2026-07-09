<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SISPREVIEW — Sistem Project View</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="login-card">
    <div class="eyebrow">SISPREVIEW</div>
    <div class="title">Ganti Password</div>
    <p class="login-note">Login sebagai <?= h($user['name']) ?>. Buat password baru sebelum melanjutkan.</p>
    <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <label>Password Baru <input type="password" name="password" autocomplete="new-password" required></label>
      <label>Konfirmasi <input type="password" name="password_confirm" autocomplete="new-password" required></label>
      <button class="primary">Simpan Password</button>
    </form>
    <p class="login-note"><a href="logout.php">Keluar</a></p>
  </div>
</body>
</html>
