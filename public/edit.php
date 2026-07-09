<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$user = require_login();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$item = item_by_id($id);
if (!$item) {
    http_response_code(404);
    echo 'CR tidak ditemukan.';
    exit;
}
if (!can_access_client((int) $item['client_id'], $user)) {
    forbid('Anda tidak memiliki akses ke CR ini.');
}
if (!can_edit_cr($id, $user)) {
    forbid('Role Anda tidak boleh mengubah CR.');
}

$error = null;
$clients = all_clients(true);
$fields = [
    'client_id', 'display_order', 'form_name', 'sub_area', 'cr_title', 'stage', 'ticket_no', 'ticket_url', 'figma_url', 'pic', 'blocker', 'blocker_type',
    'target_date', 'target_label', 'today_update', 'last_update_at',
    'is_hold_contract', 'is_rework', 'need_clarification', 'need_decision', 'is_priority', 'is_active',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();
    $old = $item;
    $new = $old;
    $errors = [];
    $autoSaveFields = [];

    foreach ($fields as $field) {
        if ($field === 'blocker_type') {
            continue;
        }
        if (!can_save_field($user, $field, $old)) {
            continue;
        }
        if (strpos($field, 'is_') === 0 || strpos($field, 'need_') === 0) {
            $new[$field] = isset($_POST[$field]) ? '1' : '0';
            continue;
        }
        $posted = $_POST[$field] ?? '';
        $posted = is_array($posted) ? '' : trim((string) $posted);
        $new[$field] = $posted;
        if ($field === 'client_id') {
            $new[$field] = ctype_digit($posted) ? (string) (int) $posted : '0';
        }
        if ($field === 'display_order') {
            $new[$field] = (string) max(1, (int) $new[$field]);
        }
        if (in_array($field, ['target_date', 'last_update_at', 'target_label', 'ticket_no', 'ticket_url', 'figma_url', 'sub_area'], true) && $new[$field] === '') {
            $new[$field] = null;
        }
    }

    if (can_save_field($user, 'blocker', $old) && trim((string) $new['blocker']) === '') {
        $new['blocker'] = '-';
    }
    if (can_save_field($user, 'blocker', $old) && in_array($new['blocker'], ALLOWED_BLOCKERS, true)) {
        $new['blocker_type'] = blocker_type_for((string) $new['blocker']);
        $autoSaveFields[] = 'blocker_type';
    }

    $todayChanged = can_save_field($user, 'today_update', $old)
        && (string) ($old['today_update'] ?? '') !== (string) ($new['today_update'] ?? '');
    if ($todayChanged) {
        $postedLastUpdate = $_POST['last_update_at'] ?? '';
        $postedLastUpdate = is_array($postedLastUpdate) ? '' : trim((string) $postedLastUpdate);
        if (!is_super_admin($user) || $postedLastUpdate === '' || $postedLastUpdate === (string) ($old['last_update_at'] ?? '')) {
            $new['last_update_at'] = today_sql();
            $autoSaveFields[] = 'last_update_at';
        }
    }

    if (can_save_field($user, 'client_id', $old) && !client_exists((int) $new['client_id'])) {
        $errors[] = 'Client tidak valid.';
    }
    if (!in_array($new['stage'], STAGES, true)) {
        $errors[] = 'Tahap tidak valid.';
    }
    if (!in_array($new['blocker'], ALLOWED_BLOCKERS, true)) {
        $errors[] = 'Penahan tidak valid.';
    }
    if (!in_array($new['blocker_type'], ['none', 'internal', 'external'], true)) {
        $errors[] = 'Tipe penahan tidak valid.';
    }
    if (in_array($new['stage'], ['Development', 'QC/UAT', 'Ready', 'Released'], true) && trim((string) ($new['ticket_no'] ?? '')) === '') {
        $errors[] = 'Tiket wajib diisi sebelum tahap Development ke atas.';
    }
    foreach (['target_date', 'last_update_at'] as $dateField) {
        if (!empty($new[$dateField]) && !is_valid_sql_date((string) $new[$dateField])) {
            $errors[] = $dateField . ' harus tanggal valid dengan format YYYY-MM-DD.';
        }
    }
    if (can_save_field($user, 'display_order', $old) && (int) $new['display_order'] < 1) {
        $errors[] = 'Urutan harus minimal 1.';
    }
    foreach (['form_name' => 'Form', 'cr_title' => 'CR', 'pic' => 'PIC', 'today_update' => 'Today update'] as $field => $label) {
        if (can_save_field($user, $field, $old) && trim((string) ($new[$field] ?? '')) === '') {
            $errors[] = $label . ' wajib diisi.';
        }
    }

    if ($errors) {
        $error = implode(' ', $errors);
    } else {
        $important = ['client_id', 'display_order', 'stage', 'ticket_no', 'ticket_url', 'figma_url', 'blocker', 'blocker_type', 'target_date', 'target_label', 'today_update', 'last_update_at', 'is_hold_contract', 'is_rework', 'need_clarification', 'need_decision', 'is_priority', 'is_active'];
        $sets = [];
        $params = [];
        foreach ($fields as $field) {
            if (!can_save_field($user, $field, $old) && !in_array($field, $autoSaveFields, true)) {
                continue;
            }
            $sets[] = "$field = ?";
            $params[] = $new[$field];
        }
        if ($sets) {
            $sets[] = 'updated_at = NOW()';
            $params[] = $id;
            $stmt = db()->prepare('UPDATE cr_items SET ' . implode(', ', $sets) . ' WHERE id = ?');
            $stmt->execute($params);

            foreach ($important as $field) {
                if ((can_save_field($user, $field, $old) || in_array($field, $autoSaveFields, true)) && (string) ($old[$field] ?? '') !== (string) ($new[$field] ?? '')) {
                    log_event($id, $field, $old[$field] ?? null, $new[$field] ?? null);
                }
            }
        }

        header('Location: index.php?client_id=' . (int) $new['client_id'] . '#detail');
        exit;
    }
}

$item = with_derived($item);
$events = event_log($id);
require __DIR__ . '/../app/views_edit.php';
