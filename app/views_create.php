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
      <div><div class="eyebrow">Tambah Item</div><div class="title">SISPREVIEW — Sistem Project View</div></div>
      <a class="editlink" href="index.php#detail">Kembali</a>
    </div>
    <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="form grid-form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <label>Client
        <select name="client_id" required>
          <?php foreach ($clients as $client): ?>
            <option value="<?= (int) $client['id'] ?>" <?= (int) $item['client_id'] === (int) $client['id'] ? 'selected' : '' ?>><?= h($client['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Urutan <input type="number" min="1" name="display_order" value="<?= (int) $item['display_order'] ?>"></label>
      <label>Form <input name="form_name" list="formSuggestions" value="<?= h($item['form_name']) ?>" required></label>
      <label>Sub Area / Tab <input name="sub_area" list="subAreaSuggestions" value="<?= h((string) $item['sub_area']) ?>"></label>
      <label>CR <input name="cr_title" value="<?= h($item['cr_title']) ?>" required></label>
      <label>Tahap
        <select name="stage">
          <?php foreach (STAGES as $stage): ?><option <?= $item['stage'] === $stage ? 'selected' : '' ?>><?= h($stage) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Tiket <input name="ticket_no" value="<?= h((string) $item['ticket_no']) ?>"></label>
      <label>Link Tiket <input name="ticket_url" value="<?= h((string) $item['ticket_url']) ?>"></label>
      <label>Link Figma <input name="figma_url" value="<?= h((string) $item['figma_url']) ?>"></label>
      <label>PIC <input name="pic" value="<?= h($item['pic']) ?>" required></label>
      <label>Penahan
        <select name="blocker">
          <?php foreach (ALLOWED_BLOCKERS as $blocker): ?><option value="<?= h($blocker) ?>" <?= $item['blocker'] === $blocker ? 'selected' : '' ?>><?= h($blocker) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Target Date <input type="date" name="target_date" value="<?= h((string) $item['target_date']) ?>"></label>
      <label>Target Label <input name="target_label" value="<?= h((string) $item['target_label']) ?>"></label>
      <label>Last Update <input type="date" name="last_update_at" value="<?= h((string) $item['last_update_at']) ?>"></label>
      <label class="wide">Today Update <textarea name="today_update" required><?= h($item['today_update']) ?></textarea></label>
      <div class="checks wide">
        <?php foreach ([['is_hold_contract','Hold Contract'],['is_rework','Rework'],['need_clarification','Perlu klarifikasi'],['need_decision','Perlu keputusan'],['is_priority','Prioritas'],['is_active','Aktif']] as $chk): ?>
          <label><input type="checkbox" name="<?= $chk[0] ?>" <?= $item[$chk[0]] ? 'checked' : '' ?>> <?= h($chk[1]) ?></label>
        <?php endforeach; ?>
      </div>
      <button class="primary wide">Simpan</button>
      <datalist id="formSuggestions">
        <?php foreach (FORM_SUGGESTIONS as $suggestion): ?><option value="<?= h($suggestion) ?>"></option><?php endforeach; ?>
      </datalist>
      <datalist id="subAreaSuggestions">
        <?php foreach (FLOWSHEET_SUB_AREAS as $suggestion): ?><option value="<?= h($suggestion) ?>"></option><?php endforeach; ?>
      </datalist>
    </form>
  </div>
</body>
</html>
