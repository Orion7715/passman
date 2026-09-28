<?php

require_once '../includes/protect.php';
require_once '../includes/db.php';
require_once '../includes/totp.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);

    echo json_encode([
        'success' => false
    ]);

    exit;
}

$user_id = (int) $_SESSION['user_id'];
$master_key = $_SESSION['master_key'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false || $id === null) {
    echo json_encode([
        'success' => false
    ]);

    exit;
}

$stmt = $pdo->prepare("
    SELECT otp_secret
    FROM passwords
    WHERE id = ? AND user_id = ?
    LIMIT 1
");

$stmt->execute([$id, $user_id]);

$row = $stmt->fetch();

if (!$row || empty($row['otp_secret'])) {
    echo json_encode([
        'success' => false,
        'code' => null
    ]);

    exit;
}

$otp_secret = decrypt_with_master(
    $row['otp_secret'],
    $master_key
);

if (empty($otp_secret)) {
    echo json_encode([
        'success' => false,
        'code' => null
    ]);

    exit;
}

$code = generate_totp_code($otp_secret);

echo json_encode([
    'success' => !empty($code),
    'code' => $code
]);
