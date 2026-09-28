<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$isInsideActions = basename(dirname($_SERVER['SCRIPT_NAME'] ?? '')) === 'actions';
$loginPath = $isInsideActions ? '../login.php' : 'login.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['master_key'])) {
    header('Location: ' . $loginPath);
    exit;
}

if (isset($_SESSION['last_activity']) && (time() - (int) $_SESSION['last_activity']) > 900) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . $loginPath);
    exit;
}
$_SESSION['last_activity'] = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = basename($_SERVER['SCRIPT_FILENAME'] ?? '', '.php');
    if (!validate_csrf($action, $_POST['csrf_token'] ?? null)) {
        $_SESSION['flash_error'] = 'Security validation failed. Please try again.';
        header('Location: ' . ($isInsideActions ? '../dashboard.php' : 'dashboard.php'));
        exit;
    }
}
