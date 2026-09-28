<?php
require_once '../includes/protect.php';
require_once '../includes/db.php';
require_once '../includes/totp.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = (int) $_SESSION['user_id'];
    $master_key = $_SESSION['master_key'];
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $error = null;
    $entry = normalize_credential_input($_POST, $error);

    if ($id !== false && $entry !== null && ($entry['otp_secret'] === '' || generate_totp_code($entry['otp_secret']) !== '')) {
        $encrypted_otp_secret = $entry['otp_secret'] !== ''
            ? encrypt_with_master($entry['otp_secret'], $master_key)
            : null;

        $stmt = $pdo->prepare("
            UPDATE passwords
            SET
                category = ?,
                domain = ?,
                username = ?,
                password = ?,
                email = ?,
                note = ?,
                otp_secret = ?
            WHERE id = ? AND user_id = ?
        ");

        $success = $stmt->execute([
            $entry['category'],
            encrypt_with_master($entry['domain'], $master_key),
            $entry['username'],
            encrypt_with_master($entry['password'], $master_key),
            encrypt_with_master($entry['email'], $master_key),
            encrypt_with_master($entry['note'], $master_key),
            $encrypted_otp_secret,
            $id,
            $user_id
        ]);

        $_SESSION[$success ? "flash_success" : "flash_error"] =
            $success
            ? "Credential updated successfully!"
            : "An error occurred while updating.";

    } else {
        $_SESSION['flash_error'] = $id === false
            ? 'Invalid vault item.'
            : ($entry !== null ? 'The TOTP secret is invalid.' : ($error ?? 'Please fill in all required fields.'));
    }
}

header("Location: ../dashboard.php");
exit;
