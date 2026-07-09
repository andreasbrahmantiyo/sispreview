<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$user = require_role('super_admin');
$clients = all_clients(true);
$defaultClientId = null;
if (isset($_GET['client_id']) && is_string($_GET['client_id']) && ctype_digit($_GET['client_id']) && client_exists((int) $_GET['client_id'])) {
    $defaultClientId = (int) $_GET['client_id'];
}
if ($defaultClientId === null && $clients) {
    $defaultClientId = (int) $clients[0]['id'];
}

$error = null;
$item = [
    'client_id' => $defaultClientId,
    'display_order' => next_display_order(),
    'form_name' => '',
    'sub_area' => '',
    'cr_title' => '',
    'stage' => 'Klarifikasi',
    'ticket_no' => '',
    'ticket_url' => '',
    'figma_url' => '',
    'pic' => '',
    'blocker' => '-',
    'blocker_type' => 'none',
    'target_date' => '',
    'target_label' => '',
    'today_update' => '',
    'last_update_at' => today_sql(),
    'is_hold_contract' => '0',
    'is_rework' => '0',
    'need_clarification' => '0',
    'need_decision' => '0',
    'is_priority' => '0',
    'is_active' => '1',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verify_csrf();

    foreach (['client_id', 'display_order', 'form_name', 'sub_area', 'cr_title', 'stage', 'ticket_no', 'ticket_url', 'figma_url', 'pic', 'blocker', 'target_date', 'target_label', 'today_update', 'last_update_at'] as $field) {
        $posted = $_POST[$field] ?? '';
        $item[$field] = is_array($posted) ? '' : trim((string) $posted);
    }
    foreach (['is_hold_contract', 'is_rework', 'need_clarification', 'need_decision', 'is_priority', 'is_active'] as $field) {
        $item[$field] = isset($_POST[$field]) ? '1' : '0';
    }

    $item['client_id'] = ctype_digit((string) $item['client_id']) ? (int) $item['client_id'] : 0;
    $item['display_order'] = (string) max(1, (int) $item['display_order']);
    foreach (['sub_area', 'target_date', 'target_label', 'ticket_no', 'ticket_url', 'figma_url', 'last_update_at'] as $nullable) {
        if ($item[$nullable] === '') {
            $item[$nullable] = null;
        }
    }
    if ($item['blocker'] === '') {
        $item['blocker'] = '-';
    }
    $item['blocker_type'] = blocker_type_for((string) $item['blocker']);

    $errors = [];
    if (!client_exists((int) $item['client_id'])) {
        $errors[] = 'Client tidak valid.';
    }
    if (!in_array($item['stage'], STAGES, true)) {
        $errors[] = 'Tahap tidak valid.';
    }
    if (!in_array($item['blocker'], ALLOWED_BLOCKERS, true)) {
        $errors[] = 'Penahan tidak valid.';
    }
    if (in_array($item['stage'], ['Development', 'QC/UAT', 'Ready', 'Released'], true) && trim((string) ($item['ticket_no'] ?? '')) === '') {
        $errors[] = 'Tiket wajib diisi sebelum tahap Development ke atas.';
    }
    foreach (['target_date', 'last_update_at'] as $dateField) {
        if (!empty($item[$dateField]) && !is_valid_sql_date((string) $item[$dateField])) {
            $errors[] = $dateField . ' harus tanggal valid dengan format YYYY-MM-DD.';
        }
    }
    foreach (['form_name' => 'Form', 'cr_title' => 'CR', 'pic' => 'PIC', 'today_update' => 'Today update'] as $field => $label) {
        if (trim((string) ($item[$field] ?? '')) === '') {
            $errors[] = $label . ' wajib diisi.';
        }
    }

    if ($errors) {
        $error = implode(' ', $errors);
    } else {
        $stmt = db()->prepare(
            'INSERT INTO cr_items
             (client_id, display_order, form_name, sub_area, cr_title, stage, ticket_no, ticket_url, figma_url, pic, blocker, blocker_type, target_date, target_label, today_update, last_update_at, is_hold_contract, is_rework, need_clarification, need_decision, is_priority, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $item['client_id'],
            $item['display_order'],
            $item['form_name'],
            $item['sub_area'],
            $item['cr_title'],
            $item['stage'],
            $item['ticket_no'],
            $item['ticket_url'],
            $item['figma_url'],
            $item['pic'],
            $item['blocker'],
            $item['blocker_type'],
            $item['target_date'],
            $item['target_label'],
            $item['today_update'],
            $item['last_update_at'],
            $item['is_hold_contract'],
            $item['is_rework'],
            $item['need_clarification'],
            $item['need_decision'],
            $item['is_priority'],
            $item['is_active'],
        ]);
        log_created_event((int) db()->lastInsertId());

        header('Location: index.php?client_id=' . (int) $item['client_id'] . '#detail');
        exit;
    }
}

require __DIR__ . '/../app/views_create.php';
