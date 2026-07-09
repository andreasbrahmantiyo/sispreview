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
    <div class="eyebrow">Internal</div>
    <div class="title">SISPREVIEW</div>
    <div class="app-subtitle">Sistem Project View</div>
    <p class="login-note">Masuk untuk melihat progress project dan delivery client.</p>
    <?php if ($loginError): ?><div class="alert"><?= h($loginError) ?></div><?php endif; ?>
    <form method="post" class="form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <label>Username <input name="username" autocomplete="username" required></label>
      <label>Password <input name="password" type="password" autocomplete="current-password" required></label>
      <button class="primary">Masuk</button>
    </form>
  </div>
</body>
</html>
