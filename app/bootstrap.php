<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

if (strtolower((string) getenv('APP_ENV')) === 'production') {
    ini_set('display_errors', '0');
    set_exception_handler(function (Throwable $e): void {
        error_log((string) $e);
        http_response_code(500);
        echo 'Terjadi kesalahan server.';
        exit;
    });
}

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

const STAGES = ['Klarifikasi', 'Scope Fix', 'Development', 'QC/UAT', 'Ready', 'Released'];
const STAGE_INDEX = [
    'Klarifikasi' => 0,
    'Scope Fix' => 1,
    'Development' => 2,
    'QC/UAT' => 3,
    'Ready' => 4,
    'Released' => 6,
];
const ALLOWED_BLOCKERS = ['-', 'RS/User', 'Kontrak', 'Dev', 'QC', 'Desain'];
const ALLOWED_ROLES = ['super_admin', 'management_viewer', 'developer', 'client_viewer'];
const FORM_SUGGESTIONS = [
    'Flowsheet ICU',
    'Informed Consent',
    'Edukasi Pasien',
    'Cardex',
    'Pengajuan Pembedahan',
    'Perioperatif',
    'SBAR / Pemindahan Pasien',
    'Discharge Planning',
    'SSC Anestesi',
    'SKL (Ket. Lahir)',
    'Sertifikat Kematian',
    'Catatan Anestesi',
    'Form Gizi',
    'Monitoring Pengkajian Jatuh Ontario',
];
const FLOWSHEET_SUB_AREAS = [
    'TTV',
    'SSP & OBS',
    'Respiratory',
    'Hemodinamik',
    'Balance Cairan',
    'Hasil Penunjang',
    'Petunjuk',
    'CPPT',
    'Diagnosa Keperawatan ICU',
];

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = require __DIR__ . '/../config/database.php';
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $cfg['host'],
        $cfg['port'],
        $cfg['database'],
        $cfg['charset']
    );

    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        echo 'Sesi form tidak valid. Silakan kembali dan coba lagi.';
        exit;
    }
}

function forbid(string $message = 'Akses ditolak.'): void
{
    http_response_code(403);
    echo $message;
    exit;
}

function id_datetime_label(bool $withWeekday = true): string
{
    $now = new DateTimeImmutable('now');
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $date = (int) $now->format('j') . ' ' . $months[(int) $now->format('n')] . ' ' . $now->format('Y');
    $time = $now->format('H.i');
    return ($withWeekday ? $days[(int) $now->format('w')] . ', ' : '') . $date . ' - ' . $time;
}

function current_script(): string
{
    return basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $cached = null;
    if (is_array($cached) && (int) $cached['id'] === (int) $_SESSION['user_id']) {
        return $cached;
    }

    $stmt = db()->prepare(
        'SELECT id, name, username, role, is_active, force_password_change, last_login_at
         FROM users
         WHERE id = ?
         LIMIT 1'
    );
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || (int) $user['is_active'] !== 1) {
        $_SESSION = [];
        return null;
    }
    $cached = $user;
    return $cached;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: login.php');
        exit;
    }

    if ((int) $user['force_password_change'] === 1 && !in_array(current_script(), ['change_password.php', 'logout.php'], true)) {
        header('Location: change_password.php');
        exit;
    }

    return $user;
}

function require_role($roles): array
{
    $user = require_login();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $allowed, true)) {
        forbid('Role Anda tidak memiliki akses ke halaman ini.');
    }
    return $user;
}

function role_label(string $role): string
{
    $labels = [
        'super_admin' => 'Super Admin',
        'management_viewer' => 'Management Viewer',
        'developer' => 'Developer',
        'client_viewer' => 'Client Viewer',
    ];
    return $labels[$role] ?? $role;
}

function is_super_admin(?array $user = null): bool
{
    $user = $user ?: current_user();
    return is_array($user) && $user['role'] === 'super_admin';
}

function is_view_only(?array $user = null): bool
{
    $user = $user ?: current_user();
    return !is_array($user) || in_array($user['role'], ['management_viewer', 'client_viewer'], true);
}

function dev_stage_options(): array
{
    return STAGES;
}

function all_clients(bool $activeOnly = true): array
{
    $sql = 'SELECT * FROM clients';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY name ASC';
    return db()->query($sql)->fetchAll();
}

function client_by_id(int $clientId): ?array
{
    $stmt = db()->prepare('SELECT * FROM clients WHERE id = ? LIMIT 1');
    $stmt->execute([$clientId]);
    $client = $stmt->fetch();
    return $client ?: null;
}

function client_exists(int $clientId): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM clients WHERE id = ? AND is_active = 1');
    $stmt->execute([$clientId]);
    return (int) $stmt->fetchColumn() > 0;
}

function accessible_client_ids(?array $user = null): array
{
    $user = $user ?: current_user();
    if (!$user) {
        return [];
    }
    if (is_super_admin($user)) {
        return array_map('intval', array_column(all_clients(true), 'id'));
    }

    $stmt = db()->prepare(
        'SELECT c.id
         FROM clients c
         JOIN user_clients uc ON uc.client_id = c.id
         WHERE uc.user_id = ?
           AND c.is_active = 1
         ORDER BY c.name ASC'
    );
    $stmt->execute([(int) $user['id']]);
    return array_map('intval', array_column($stmt->fetchAll(), 'id'));
}

function available_clients_for_user(?array $user = null): array
{
    $user = $user ?: current_user();
    if (!$user) {
        return [];
    }
    if (is_super_admin($user)) {
        return all_clients(true);
    }

    $stmt = db()->prepare(
        'SELECT c.*
         FROM clients c
         JOIN user_clients uc ON uc.client_id = c.id
         WHERE uc.user_id = ?
           AND c.is_active = 1
         ORDER BY c.name ASC'
    );
    $stmt->execute([(int) $user['id']]);
    return $stmt->fetchAll();
}

function can_access_client(int $clientId, ?array $user = null): bool
{
    $user = $user ?: current_user();
    if (!$user || $clientId < 1) {
        return false;
    }
    if (is_super_admin($user)) {
        return client_exists($clientId);
    }
    return in_array($clientId, accessible_client_ids($user), true);
}

function selected_client_id(array $user, array $clients): ?int
{
    $raw = $_GET['client_id'] ?? '';
    if ($raw === '' || $raw === 'all') {
        return null;
    }
    if (!is_string($raw) || !ctype_digit($raw)) {
        forbid('Client tidak valid.');
    }
    $clientId = (int) $raw;
    $allowed = array_map('intval', array_column($clients, 'id'));
    if (!in_array($clientId, $allowed, true)) {
        forbid('Anda tidak memiliki akses ke client ini.');
    }
    return $clientId;
}

function default_client_id(array $user): ?int
{
    $clients = available_clients_for_user($user);
    if (!$clients) {
        return null;
    }
    return (int) $clients[0]['id'];
}

function client_scope_clause(array &$params, ?int $selectedClientId = null, string $alias = 'c'): string
{
    $user = require_login();
    $prefix = $alias !== '' ? $alias . '.' : '';
    if ($selectedClientId !== null) {
        if (!can_access_client($selectedClientId, $user)) {
            forbid('Anda tidak memiliki akses ke client ini.');
        }
        $params[] = $selectedClientId;
        return ' AND ' . $prefix . 'client_id = ?';
    }

    if (is_super_admin($user)) {
        return '';
    }

    $ids = accessible_client_ids($user);
    if (!$ids) {
        return ' AND 1 = 0';
    }
    $params = array_merge($params, $ids);
    return ' AND ' . $prefix . 'client_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
}

function blocker_type_for(string $blocker): string
{
    $map = [
        '-' => 'none',
        'RS/User' => 'external',
        'Kontrak' => 'external',
        'Dev' => 'internal',
        'QC' => 'internal',
        'Desain' => 'internal',
    ];
    return $map[$blocker] ?? 'none';
}

function item_label(array $item, string $separator = ' / '): string
{
    return !empty($item['sub_area'])
        ? $item['form_name'] . $separator . $item['sub_area']
        : $item['form_name'];
}

function can_save_field(array $user, string $field, array $item): bool
{
    if (is_super_admin($user)) {
        return true;
    }
    if ($user['role'] !== 'developer') {
        return false;
    }
    if ((int) ($item['is_active'] ?? 1) !== 1 || !can_access_client((int) $item['client_id'], $user)) {
        return false;
    }
    return in_array($field, ['stage', 'ticket_no', 'ticket_url', 'figma_url', 'today_update'], true);
}

function can_edit_cr(int $crId, ?array $user = null): bool
{
    $user = $user ?: current_user();
    if (!$user || is_view_only($user)) {
        return false;
    }
    $item = item_by_id($crId);
    if (!$item || !can_access_client((int) $item['client_id'], $user)) {
        return false;
    }
    if (is_super_admin($user)) {
        return true;
    }
    return $user['role'] === 'developer' && (int) $item['is_active'] === 1;
}

function all_items(?int $selectedClientId = null): array
{
    $params = [];
    $scope = client_scope_clause($params, $selectedClientId, 'c');
    $stmt = db()->prepare(
        'SELECT c.*, cl.code AS client_code, cl.name AS client_name
         FROM cr_items c
         JOIN clients cl ON cl.id = c.client_id
         WHERE c.is_active = 1' . $scope . '
         ORDER BY cl.name ASC, c.display_order ASC'
    );
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function item_by_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT c.*, cl.code AS client_code, cl.name AS client_name
         FROM cr_items c
         JOIN clients cl ON cl.id = c.client_id
         WHERE c.id = ?
         LIMIT 1'
    );
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    return $item ?: null;
}

function next_display_order(): int
{
    $stmt = db()->query('SELECT COALESCE(MAX(display_order), 0) + 1 FROM cr_items');
    return (int) $stmt->fetchColumn();
}

function today_sql(): string
{
    return date('Y-m-d');
}

function is_valid_sql_date(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
}

function is_overdue(array $item): bool
{
    return !empty($item['target_date'])
        && !in_array($item['stage'], ['Ready', 'Released'], true)
        && $item['target_date'] < today_sql();
}

function overdue_days(array $item): int
{
    if (!is_overdue($item)) {
        return 0;
    }
    $target = new DateTimeImmutable($item['target_date']);
    $today = new DateTimeImmutable(today_sql());
    return (int) $target->diff($today)->format('%a');
}

function is_stale(array $item): bool
{
    if (empty($item['last_update_at'])) {
        return true;
    }
    $last = new DateTimeImmutable($item['last_update_at']);
    $today = new DateTimeImmutable(today_sql());
    return ((int) $last->diff($today)->format('%a')) >= 2;
}

function day_label(array $item): string
{
    if (empty($item['target_date'])) {
        return $item['target_label'] ?: '';
    }
    $target = new DateTimeImmutable($item['target_date']);
    $today = new DateTimeImmutable(today_sql());
    $days = (int) $today->diff($target)->format('%r%a');
    if ($days < 0) {
        return 'telat ' . abs($days) . ' hari';
    }
    if ($days === 0) {
        return 'hari ini';
    }
    return $days . ' hari lagi';
}

function target_label(array $item): string
{
    if (!empty($item['target_label'])) {
        return $item['target_label'];
    }
    if (empty($item['target_date'])) {
        return '-';
    }
    return (new DateTimeImmutable($item['target_date']))->format('j M');
}

function stage_rank(string $stage): int
{
    return STAGE_INDEX[$stage] ?? 0;
}

function with_derived(array $item): array
{
    $item['overdue'] = is_overdue($item);
    $item['late_days'] = overdue_days($item);
    $item['stale'] = is_stale($item);
    $item['target_text'] = target_label($item);
    $item['day_text'] = day_label($item);
    $item['stage_rank'] = stage_rank($item['stage']);
    return $item;
}

function dashboard_data(?int $selectedClientId = null): array
{
    $items = array_map('with_derived', all_items($selectedClientId));

    $filter = [
        'total' => fn($i) => true,
        'fixuser' => fn($i) => $i['stage_rank'] >= 1,
        'tiket' => fn($i) => !empty($i['ticket_no']),
        'dev' => fn($i) => $i['stage'] === 'Development',
        'qc' => fn($i) => $i['stage'] === 'QC/UAT',
        'ready' => fn($i) => $i['stage'] === 'Ready',
        'released' => fn($i) => $i['stage'] === 'Released',
        'overdue' => fn($i) => $i['overdue'],
        'hold' => fn($i) => (int) $i['is_hold_contract'] === 1,
    ];

    $counts = [];
    $lists = [];
    foreach ($filter as $key => $fn) {
        $lists[$key] = array_values(array_filter($items, $fn));
        $counts[$key] = count($lists[$key]);
    }

    $blockerGroups = [];
    foreach ($items as $item) {
        if (!$item['overdue'] || $item['blocker'] === '-') {
            continue;
        }
        $key = 'blocker:' . $item['blocker'];
        if (!isset($blockerGroups[$key])) {
            $blockerGroups[$key] = [
                'key' => $key,
                'name' => $item['blocker'],
                'type' => $item['blocker_type'],
                'items' => [],
            ];
        }
        if ($item['blocker_type'] === 'external') {
            $blockerGroups[$key]['type'] = 'external';
        }
        $blockerGroups[$key]['items'][] = $item;
    }

    usort($blockerGroups, function ($a, $b) {
        $typeRank = ['external' => 0, 'internal' => 1, 'none' => 2];
        return ($typeRank[$a['type']] ?? 2) <=> ($typeRank[$b['type']] ?? 2)
            ?: strcasecmp($a['name'], $b['name']);
    });

    foreach ($blockerGroups as $idx => $group) {
        $blockerGroups[$idx]['count'] = count($group['items']);
        $lists[$group['key']] = $group['items'];
        $counts[$group['key']] = count($group['items']);
    }

    $attention = $items;
    usort($attention, function ($a, $b) {
        $score = function ($i) {
            return ($i['late_days'] * 10)
                + ((int) $i['need_decision'] * 8)
                + ((int) $i['need_clarification'] * 6)
                + ($i['stale'] ? 5 : 0)
                + ($i['blocker_type'] !== 'none' ? 3 : 0)
                + ((int) $i['is_priority'] * 2);
        };
        return $score($b) <=> $score($a);
    });

    return [
        'items' => $items,
        'counts' => $counts,
        'lists' => $lists,
        'blocker_groups' => $blockerGroups,
        'attention' => array_slice(array_filter($attention, fn($i) => $i['overdue'] || $i['need_decision'] || $i['need_clarification'] || $i['stale'] || $i['blocker_type'] !== 'none'), 0, 4),
        'movement' => todays_movement($selectedClientId),
    ];
}

function todays_movement(?int $selectedClientId = null): array
{
    $params = [];
    $scope = client_scope_clause($params, $selectedClientId, 'c');
    $sql = "SELECT e.*, c.form_name, c.sub_area, c.cr_title, c.stage, c.blocker, c.blocker_type, c.client_id, cl.code AS client_code, cl.name AS client_name
         FROM cr_events e
         JOIN cr_items c ON c.id = e.cr_item_id
         JOIN clients cl ON cl.id = c.client_id
         WHERE e.field_name IN ('stage', 'ticket_no', 'ticket_url', 'figma_url', 'blocker', 'target_date', 'today_update', 'is_hold_contract', 'need_decision', 'need_clarification')
           AND c.is_active = 1" . $scope . "
           AND DATE(e.created_at) = CURDATE()
           AND e.id = (
             SELECT e2.id
             FROM cr_events e2
             WHERE e2.cr_item_id = e.cr_item_id
               AND e2.field_name IN ('stage', 'ticket_no', 'ticket_url', 'figma_url', 'blocker', 'target_date', 'today_update', 'is_hold_contract', 'need_decision', 'need_clarification')
               AND DATE(e2.created_at) = CURDATE()
             ORDER BY e2.created_at DESC, e2.id DESC
             LIMIT 1
           )
         ORDER BY e.created_at DESC, e.id DESC
         LIMIT 8";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        if ($row['field_name'] === 'stage') {
            $row['note'] = 'masuk tahap ' . $row['new_value'];
        }
    }
    unset($row);
    return $rows;
}

function event_value($value): ?string
{
    if ($value === null) {
        return null;
    }
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }
    return (string) $value;
}

function request_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

function request_user_agent(): string
{
    return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

function log_event(int $itemId, string $field, $old, $new): void
{
    $actor = current_user();
    $oldValue = event_value($old);
    $newValue = event_value($new);
    $type = $field === 'stage' ? 'stage' : 'field_change';
    if ($field === 'today_update') {
        $note = $newValue;
    } elseif ($field === 'stage') {
        $note = 'Tahap berubah dari ' . ($oldValue ?: '-') . ' ke ' . ($newValue ?: '-');
    } else {
        $note = $field . ' diperbarui';
    }

    $stmt = db()->prepare(
        'INSERT INTO cr_events
         (cr_item_id, event_type, field_name, old_value, new_value, note, created_by, user_id, user_name, action, entity_type, entity_id, before_value, after_value, ip_address, user_agent)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $itemId,
        $type,
        $field,
        $oldValue,
        $newValue,
        $note,
        $actor['username'] ?? 'system',
        $actor['id'] ?? null,
        $actor['name'] ?? 'System',
        $type === 'stage' ? 'stage_changed' : 'field_changed',
        'cr_item',
        $itemId,
        $oldValue,
        $newValue,
        request_ip(),
        request_user_agent(),
    ]);
}

function log_created_event(int $itemId): void
{
    $actor = current_user();
    $stmt = db()->prepare(
        'INSERT INTO cr_events
         (cr_item_id, event_type, field_name, old_value, new_value, note, created_by, user_id, user_name, action, entity_type, entity_id, before_value, after_value, ip_address, user_agent)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $itemId,
        'created',
        'created',
        null,
        '1',
        'Item dibuat',
        $actor['username'] ?? 'system',
        $actor['id'] ?? null,
        $actor['name'] ?? 'System',
        'created',
        'cr_item',
        $itemId,
        null,
        '1',
        request_ip(),
        request_user_agent(),
    ]);
}

function event_log(int $itemId): array
{
    $stmt = db()->prepare('SELECT * FROM cr_events WHERE cr_item_id = ? ORDER BY created_at DESC, id DESC LIMIT 30');
    $stmt->execute([$itemId]);
    return $stmt->fetchAll();
}
