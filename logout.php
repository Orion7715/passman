<?php

require_once 'includes/session.php';
require_once 'includes/functions.php';

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !validate_csrf('logout', $_POST['csrf_token'] ?? null)
) {
    header('Location: login.php');
    exit;
}

// Clear all session data
$_SESSION = [];

// Remove the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $params['path'] ?? '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => $params['secure'] ?? false,
            'httponly' => $params['httponly'] ?? true,
            'samesite' => $params['samesite'] ?? 'Lax',
        ]
    );
}

// Destroy the session
session_destroy();

header('Location: login.php');
exit;
