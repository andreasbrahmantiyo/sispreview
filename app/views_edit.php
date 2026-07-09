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
      <div><div class="eyebrow">Edit CR</div><div class="title"><?= h($item['form_name']) ?></div></div>
      <a class="editlink" href="index.php?client_id=<?= (int) $item['client_id'] ?>#detail">Kembali</a>
    </div>
    <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="form grid-form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
      <?php $super = $user['role'] === 'super_admin'; ?>
      <?php if ($super): ?>
        <label>Client
          <select name="client_id">
            <?php foreach ($clients as $client): ?>
              <option value="<?= (int) $client['id'] ?>" <?= (int) $item['client_id'] === (int) $client['id'] ? 'selected' : '' ?>><?= h($client['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Urutan <input type="number" min="1" name="display_order" value="<?= (int) $item['display_order'] ?>"></label>
      <?php else: ?>
        <label>Client <input value="<?= h($item['client_name']) ?>" disabled></label>
      <?php endif; ?>
      <label>Form <input name="form_name" list="formSuggestions" value="<?= h($item['form_name']) ?>" <?= can_save_field($user, 'form_name', $item) ? '' : 'disabled' ?>></label>
      <label>Sub Area / Tab <input name="sub_area" list="subAreaSuggestions" value="<?= h((string) $item['sub_area']) ?>" <?= can_save_field($user, 'sub_area', $item) ? '' : 'disabled' ?>></label>
      <label>CR <input name="cr_title" value="<?= h($item['cr_title']) ?>" <?= can_save_field($user, 'cr_title', $item) ? '' : 'disabled' ?>></label>
      <label>Tahap
        <?php $stageEditable = can_save_field($user, 'stage', $item); ?>
        <?php $stageOptions = $stageEditable ? ($super ? STAGES : dev_stage_options()) : [$item['stage']]; ?>
        <select name="stage" <?= $stageEditable ? '' : 'disabled' ?>>
          <?php foreach ($stageOptions as $stage): ?><option <?= $item['stage'] === $stage ? 'selected' : '' ?>><?= h($stage) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Tiket <input name="ticket_no" value="<?= h((string) $item['ticket_no']) ?>" <?= can_save_field($user, 'ticket_no', $item) ? '' : 'disabled' ?>></label>
      <label>Link Tiket <input name="ticket_url" value="<?= h((string) $item['ticket_url']) ?>" <?= can_save_field($user, 'ticket_url', $item) ? '' : 'disabled' ?>></label>
      <label>Link Figma <input name="figma_url" value="<?= h((string) $item['figma_url']) ?>" <?= can_save_field($user, 'figma_url', $item) ? '' : 'disabled' ?>></label>
      <label>PIC <input name="pic" value="<?= h($item['pic']) ?>" <?= can_save_field($user, 'pic', $item) ? '' : 'disabled' ?>></label>
      <label>Penahan
        <select name="blocker" <?= can_save_field($user, 'blocker', $item) ? '' : 'disabled' ?>>
          <?php foreach (ALLOWED_BLOCKERS as $blocker): ?><option value="<?= h($blocker) ?>" <?= $item['blocker'] === $blocker ? 'selected' : '' ?>><?= h($blocker) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Target Date <input type="date" name="target_date" value="<?= h((string) $item['target_date']) ?>" <?= can_save_field($user, 'target_date', $item) ? '' : 'disabled' ?>></label>
      <label>Target Label <input name="target_label" value="<?= h((string) $item['target_label']) ?>" <?= can_save_field($user, 'target_label', $item) ? '' : 'disabled' ?>></label>
      <label>Last Update <input type="date" name="last_update_at" value="<?= h((string) $item['last_update_at']) ?>" <?= can_save_field($user, 'last_update_at', $item) ? '' : 'disabled' ?>></label>
      <label class="wide">Today Update <textarea name="today_update" <?= can_save_field($user, 'today_update', $item) ? '' : 'disabled' ?>><?= h($item['today_update']) ?></textarea></label>
      <div class="checks wide">
        <?php foreach ([['is_hold_contract','Hold Contract'],['is_rework','Rework'],['need_clarification','Perlu klarifikasi'],['need_decision','Perlu keputusan'],['is_priority','Prioritas']] as $chk): ?>
          <label><input type="checkbox" name="<?= $chk[0] ?>" <?= $item[$chk[0]] ? 'checked' : '' ?> <?= can_save_field($user, $chk[0], $item) ? '' : 'disabled' ?>> <?= h($chk[1]) ?></label>
        <?php endforeach; ?>
        <?php if ($super): ?><label><input type="checkbox" name="is_active" <?= $item['is_active'] ? 'checked' : '' ?>> Aktif</label><?php endif; ?>
      </div>
      <button class="primary wide">Simpan</button>
      <datalist id="formSuggestions">
        <?php foreach (FORM_SUGGESTIONS as $suggestion): ?><option value="<?= h($suggestion) ?>"></option><?php endforeach; ?>
      </datalist>
      <datalist id="subAreaSuggestions">
        <?php foreach (FLOWSHEET_SUB_AREAS as $suggestion): ?><option value="<?= h($suggestion) ?>"></option><?php endforeach; ?>
      </datalist>
    </form>
    <div class="events">
      <div class="h">Event log</div>
      <?php foreach ($events as $event): ?>
        <div class="event"><b><?= h($event['created_at']) ?></b> - <?= h($event['user_name'] ?: $event['created_by']) ?> mengubah <?= h($event['field_name']) ?>: <?= h($event['before_value'] ?? $event['old_value']) ?> -> <?= h($event['after_value'] ?? $event['new_value']) ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
