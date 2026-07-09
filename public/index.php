<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$user = require_login();
$clients = available_clients_for_user($user);
$selectedClientId = selected_client_id($user, $clients);
$selectedClient = $selectedClientId ? client_by_id($selectedClientId) : null;
$clientLabel = $selectedClient ? $selectedClient['name'] : (count($clients) === 1 ? $clients[0]['name'] : 'SEMUA CLIENT');
$clientContext = strtoupper($clientLabel);

$data = dashboard_data($selectedClientId);
$items = $data['items'];
$counts = $data['counts'];
$lists = $data['lists'];
$blockerGroups = $data['blocker_groups'];
$attention = $data['attention'];
$movement = $data['movement'];

require __DIR__ . '/../app/views_dashboard.php';
