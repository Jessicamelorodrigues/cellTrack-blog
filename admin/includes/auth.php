<?php
// Every admin page except login/instalar/verificar requires this file first.
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/layout.php';

if (empty(load_users())) {
    header('Location: instalar.php');
    exit;
}

$me = current_user();
if (!$me) {
    $_SESSION = [];
    header('Location: login.php');
    exit;
}
// Keep role/name in sync with users.json (e.g. after being changed by the owner)
$_SESSION['admin_role'] = $me['role'];
$_SESSION['admin_name'] = $me['name'];
