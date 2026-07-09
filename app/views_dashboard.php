<?php
$canCreate = $user['role'] === 'super_admin';
$canShowActions = !is_view_only($user);
$tileConfig = [
    ['total', 'Total Item', ''],
    ['fixuser', 'Fix dgn user', ''],
    ['tiket', 'Sudah tiket', ''],
    ['dev', 'Development', ''],
    ['qc', 'QC / UAT', ''],
    ['ready', 'Ready release', ''],
    ['released', 'Released', ''],
    ['overdue', 'Overdue', 'od'],
    ['hold', 'Hold kontrak', 'hold'],
];
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
  <div class="topbar">
    <div class="toggle">
      <button id="btnExec" class="on" onclick="setMode('exec')">Ringkas - Pak Dir</button>
      <button id="btnDetail" onclick="setMode('detail')">Detail - Curator</button>
    </div>
    <div class="userbar">
      Login sebagai: <b><?= h($user['name']) ?></b> (<?= h(role_label($user['role'])) ?>)
      <?php if ($user['role'] === 'super_admin'): ?><a href="users.php">Users</a><?php endif; ?>
      <a href="change_password.php">Password</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <div class="clientbar">
    <?php if (count($clients) > 1): ?>
      <form method="get" class="client-filter">
        <label>Client
          <select name="client_id" onchange="this.form.submit()">
            <option value="all" <?= $selectedClientId === null ? 'selected' : '' ?>>Semua client</option>
            <?php foreach ($clients as $client): ?>
              <option value="<?= (int) $client['id'] ?>" <?= $selectedClientId === (int) $client['id'] ? 'selected' : '' ?>><?= h($client['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </form>
    <?php elseif ($clients): ?>
      <div class="client-lock">Client: <b><?= h($clients[0]['name']) ?></b></div>
    <?php endif; ?>
  </div>

  <div class="card exec" id="viewExec">
    <div class="hd">
      <div class="eyebrow">SISMEDIKA × <?= h($clientContext) ?> - EXECUTIVE VIEW</div>
      <div class="title">SISPREVIEW — Sistem Project View</div>
      <div class="app-subtitle">Kontrak 2026</div>
      <div class="upd">Update <b><?= h(id_datetime_label(true)) ?> WIB</b> - Curator: Andreas B.</div>
    </div>
    <div class="band"><span class="dot"></span><div><div class="lbl">Status Target</div><div class="val"><?= $counts['overdue'] > 0 ? 'PERLU PERHATIAN' : 'TERKENDALI' ?></div></div></div>

    <div class="counts">
      <?php foreach ($tileConfig as $tile): ?>
        <button class="c <?= h($tile[2]) ?>" onclick="drill('<?= h($tile[0]) ?>')"><span class="n"><?= (int) $counts[$tile[0]] ?></span><span class="k"><?= h($tile[1]) ?></span></button>
      <?php endforeach; ?>
    </div>
    <div class="taphint">Ketuk angka untuk lihat daftar CR di tahap itu</div>

    <div class="pen-strip">
      <span class="plabel">Penahan overdue</span>
      <?php foreach ($blockerGroups as $group): ?>
        <button class="pchip <?= $group['type'] === 'external' ? 'ex' : 'in' ?>" onclick="drill('<?= h($group['key']) ?>')"><span class="d"></span><?= h($group['name']) ?> <b><?= (int) $group['count'] ?></b></button>
      <?php endforeach; ?>
      <?php if (!$blockerGroups): ?><span class="pchip muted-chip"><span class="d"></span>- <b>0</b></span><?php endif; ?>
    </div>

    <div class="sec">
      <div class="h">Bergerak hari ini</div>
      <?php if (!$movement): ?>
        <div class="li"><span class="mk hold"></span><span class="tx">Belum ada movement tercatat hari ini.</span></div>
      <?php endif; ?>
      <?php foreach ($movement as $ev): ?>
        <div class="li"><span class="mk mv"></span><span class="tx"><b><?= h(item_label($ev)) ?></b> - <?= h($ev['note'] ?: (($ev['field_name'] ?? 'update') . ' diperbarui')) ?>.</span></div>
      <?php endforeach; ?>
    </div>
    <div class="sec">
      <div class="h">Perlu perhatian / keputusan</div>
      <?php foreach ($attention as $a): ?>
        <div class="li">
          <span class="mk" style="background:<?= $a['overdue'] ? 'var(--red)' : 'var(--orange)' ?>"></span>
          <span class="tx"><b><?= h(item_label($a)) ?></b> - <?= h($a['today_update']) ?>.
            <?php if ($a['blocker'] !== '-'): ?><span class="tag <?= $a['blocker_type'] === 'external' ? 'ex' : 'in' ?>"><?= h($a['blocker']) ?></span><?php endif; ?>
            <?php if ($a['overdue']): ?><span class="tag over">telat <?= (int) $a['late_days'] ?> hari</span><?php endif; ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="ready-line"><b>Siap release:</b> <?= (int) $counts['ready'] ?> CR<?= $counts['hold'] ? ' - ada item hold contract.' : '.' ?></div>
    <div class="note"><b>Catatan target:</b> Bottleneck utama berada pada konfirmasi RS/User dan administrasi kontrak. Mayoritas pekerjaan pengembangan berjalan sesuai jalur; item dev yang overdue sedang ditindaklanjuti.</div>
    <div class="foot"><b>LAPORAN KOORDINASI PROGRES</b> - bukan BAST, bukan dasar penagihan.<br>Detail <?= (int) $counts['total'] ?> item tersedia di board internal curator.</div>
  </div>

  <div class="card hidden" id="viewDetail">
    <div class="hd">
      <div class="eyebrow">SISMEDIKA × <?= h($clientContext) ?> - DETAIL CURATOR</div>
      <div class="title">SISPREVIEW — Sistem Project View</div>
      <div class="app-subtitle">Kontrak 2026</div>
      <div class="upd">Update <b><?= h(id_datetime_label(false)) ?> WIB</b> - oleh Andreas B. - <b><?= (int) $counts['total'] ?> item</b></div>
    </div>
    <div class="summary">
      <?php foreach ($tileConfig as $tile): ?>
        <button class="c <?= h($tile[2]) ?>" onclick="drill('<?= h($tile[0]) ?>')"><span class="n"><?= (int) $counts[$tile[0]] ?></span><span class="k"><?= h($tile[1]) ?></span></button>
      <?php endforeach; ?>
    </div>
    <?php if ($canCreate): ?><div class="detail-actions"><a class="editlink" href="create.php<?= $selectedClientId ? '?client_id=' . (int) $selectedClientId : '' ?>">Tambah Item</a></div><?php endif; ?>
    <div class="wrap">
      <table>
        <thead><tr><th>#</th><th>Form</th><th>Change Request &amp; Update</th><th>Tahap (Fix -> Release)</th><th>Penahan</th><th>Target</th><?php if ($canShowActions): ?><th>Aksi</th><?php endif; ?></tr></thead>
        <tbody>
          <?php foreach ($items as $c): ?>
            <tr>
              <td class="num"><?= (int) $c['display_order'] ?></td>
              <td class="form">
                <span class="fname"><?= h($c['form_name']) ?></span>
                <?php if (count($clients) > 1): ?><span class="subbadge"><?= h($c['client_code']) ?></span><?php endif; ?>
                <?php if (!empty($c['sub_area'])): ?><span class="subbadge"><?= h($c['sub_area']) ?></span><?php endif; ?>
              </td>
              <td class="cr">
                <div class="ctxt"><?= h($c['cr_title']) ?></div>
                <div class="meta"><?= $c['ticket_no'] ? '<span class="tk">' . h($c['ticket_no']) . '</span>' : 'Belum tiket' ?> - <?= h($c['pic']) ?> - <?= $c['stale'] ? '<span class="stale">stale</span>' : 'upd ' . h((string) $c['last_update_at']) ?></div>
                <?= item_links_html($c) ?>
                <div class="flags">
                  <?php if ($c['is_rework']): ?><span class="pill sm fl-rework">Rework</span><?php endif; ?>
                  <?php if ($c['is_priority']): ?><span class="pill sm fl-prio">Prioritas</span><?php endif; ?>
                  <?php if ($c['need_clarification']): ?><span class="pill sm fl-clar">Perlu klarifikasi</span><?php endif; ?>
                  <?php if ($c['need_decision']): ?><span class="pill sm fl-clar">Perlu keputusan</span><?php endif; ?>
                  <?php if ($c['is_hold_contract']): ?><span class="pill sm fl-hold">Hold Contract</span><?php endif; ?>
                  <?php if ($c['overdue']): ?><span class="pill sm fl-over">Overdue</span><?php endif; ?>
                </div>
                <div class="today"><b>Hari ini:</b> <?= h($c['today_update']) ?></div>
              </td>
              <td><?= tracker_html($c) ?></td>
              <td><?= blocker_pill($c) ?></td>
              <td class="tgt"><span class="<?= $c['overdue'] ? 'tgt-late' : 'tgt-ok' ?>"><?= h($c['target_text']) ?></span><span class="day"><?= h($c['day_text']) ?></span></td>
              <?php if ($canShowActions): ?><td><?php if (can_edit_cr((int) $c['id'], $user)): ?><a class="editlink" href="edit.php?id=<?= (int) $c['id'] ?>">Edit</a><?php endif; ?></td><?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="note"><b>Bottleneck hari ini:</b> ranking otomatis dari overdue, need decision, stale, dan blocker.</div>
    <div class="foot"><b>LAPORAN KOORDINASI PROGRES</b> - bukan BAST, bukan dasar penagihan.<br>Scope Fix ke atas = sudah fix dgn user - Sumber data: database internal.</div>
  </div>

  <div class="overlay" id="overlay" onclick="closeSheet()">
    <div class="sheet" onclick="event.stopPropagation()">
      <div class="sh-hd"><div><div class="st" id="shTitle"></div><div class="sc" id="shCount"></div></div><button class="x" onclick="closeSheet()">x</button></div>
      <div class="sh-body" id="shBody"></div>
    </div>
  </div>

  <script>
    window.CR_DATA = <?= json_encode($lists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  </script>
  <script src="assets/app.js"></script>
</body>
</html>
<?php
function tracker_html(array $c): string
{
    $labels = ['Fix', 'Tiket', 'Dev', 'QC', 'Ready', 'Rel'];
    $act = (int) $c['stage_rank'];
    $active = $c['stage'] === 'Released' ? -1 : $act;
    $done = [
        0 => $act >= 1,
        1 => !empty($c['ticket_no']),
        2 => $act >= 2,
        3 => $act >= 3,
        4 => $act >= 4,
        5 => $c['stage'] === 'Released',
    ];
    $segs = '';
    $lbls = '';
    for ($i = 0; $i < 6; $i++) {
        $seg = 'seg';
        if ($done[$i]) {
            $seg .= ' done';
        }
        if ($i === $active) {
            $seg .= $c['overdue'] ? ' act-over' : ((int) $c['is_hold_contract'] === 1 ? ' act-hold' : ' act');
        }
        $cur = '';
        if ($i === $active) {
            $cur = 'cur' . ($c['overdue'] ? ' over' : ((int) $c['is_hold_contract'] === 1 ? ' hold' : ''));
        }
        $segs .= '<div class="' . $seg . '"></div>';
        $lbls .= '<span class="' . $cur . '">' . h($labels[$i]) . '</span>';
    }
    return '<div class="trk"><div class="segs">' . $segs . '</div><div class="lbls">' . $lbls . '</div></div>';
}

function blocker_pill(array $c): string
{
    if ($c['blocker'] === '-' || $c['blocker_type'] === 'none') {
        return '<span class="pill pen pen-none">-</span>';
    }
    $cls = $c['blocker_type'] === 'external' ? 'pen-ex' : 'pen-in';
    return '<span class="pill pen ' . $cls . '"><span class="pdot"></span>' . h($c['blocker']) . '</span>';
}

function item_links_html(array $c): string
{
    $links = [];
    if (!empty($c['ticket_url'])) {
        $links[] = '<a href="' . h((string) $c['ticket_url']) . '" target="_blank" rel="noopener noreferrer">Tiket</a>';
    }
    if (!empty($c['figma_url'])) {
        $links[] = '<a href="' . h((string) $c['figma_url']) . '" target="_blank" rel="noopener noreferrer">Figma</a>';
    }
    if (!$links) {
        return '';
    }
    return '<div class="item-links">[' . implode('] [', $links) . ']</div>';
}
