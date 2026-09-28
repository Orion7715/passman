<?php
require_once '../includes/protect.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int) $_SESSION['user_id'];
    if (!hash_equals((string) $_SESSION['username'], trim((string) ($_POST['username_confirm'] ?? '')))) {
        $_SESSION['flash_error'] = 'Type your username exactly to confirm account deletion.';
        header('Location: ../dashboard.php');
        exit;
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Account was not found.');
        }

        $pdo->commit();

        // Best-effort removal of file and note records not represented in SQL.
        $masterKey = $_SESSION['master_key'];
        $filesDirectory = dirname(__DIR__) . '/vault/files';
        if (is_dir($filesDirectory)) {
            foreach (scandir($filesDirectory) ?: [] as $filename) {
                if (!preg_match('/\A[a-f0-9]{32}\.enc\z/', $filename)) {
                    continue;
                }
                $path = $filesDirectory . '/' . $filename;
                $packet = !is_link($path) ? json_decode((string) file_get_contents($path), true) : null;
                if (is_array($packet) && (int) ($packet['user_id'] ?? 0) === $user_id) {
                    @unlink($path);
                }
            }
        }
        $notesPath = dirname(__DIR__) . '/vault/secure_notes.bin';
        if (is_file($notesPath)) {
            $lines = file($notesPath, FILE_IGNORE_NEW_LINES);
            if (is_array($lines)) {
                $remaining = [];
                foreach ($lines as $line) {
                    $decoded = ($plain = decrypt_with_master($line, $masterKey)) === false ? null : json_decode($plain, true);
                    if (!is_array($decoded) || (int) ($decoded['user_id'] ?? 0) !== $user_id) {
                        $remaining[] = $line;
                    }
                }
                atomic_write($notesPath, empty($remaining) ? '' : implode(PHP_EOL, $remaining) . PHP_EOL);
            }
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, [
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Strict',
            ]);
        }
        session_destroy();
        header('Location: ../login.php');
        exit();

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Passman account deletion failed: ' . $e->getMessage());
        $_SESSION['flash_error'] = "An error occurred while trying to delete your account.";
        header("Location: ../dashboard.php");
        exit();
    }
}
