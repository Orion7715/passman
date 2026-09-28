<?php

require_once '../includes/protect.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

$oldMaster = (string) ($_POST['old_master'] ?? '');
$newMaster = (string) ($_POST['new_master'] ?? '');
$confirmMaster = (string) ($_POST['confirm_master'] ?? '');
$userId = (int) $_SESSION['user_id'];
$currentKey = $_SESSION['master_key'];

if ($oldMaster === $newMaster) {
    $_SESSION['flash_error'] = 'New master password cannot be the same as the old one.';
} elseif ($newMaster !== $confirmMaster) {
    $_SESSION['flash_error'] = 'New passwords do not match.';
} elseif ($errors = is_strong_password($newMaster)) {
    $_SESSION['flash_error'] = implode(' ', $errors);
} else {
    try {
        $userStatement = $pdo->prepare('SELECT master_password FROM users WHERE id = ?');
        $userStatement->execute([$userId]);
        $user = $userStatement->fetch();
        if (!$user || !password_verify($oldMaster, $user['master_password'])) {
            throw new RuntimeException('The current master password is incorrect.');
        }

        $newSalt = new_encryption_salt();
        $newKey = derive_encryption_key($newMaster, $newSalt);
        $rowsStatement = $pdo->prepare(
            'SELECT id, domain, password, email, note, otp_secret FROM passwords WHERE user_id = ?'
        );
        $rowsStatement->execute([$userId]);

        $encryptedRows = [];
        foreach ($rowsStatement->fetchAll() as $row) {
            $plaintext = [];
            foreach (['domain', 'password', 'email', 'note', 'otp_secret'] as $field) {
                if ($row[$field] === null) {
                    $plaintext[$field] = null;
                    continue;
                }
                $plaintext[$field] = decrypt_with_master($row[$field], $currentKey);
                if ($plaintext[$field] === false) {
                    throw new RuntimeException('A vault record could not be decrypted.');
                }
            }

            $encryptedRows[] = [
                'id' => $row['id'],
                'domain' => encrypt_with_master($plaintext['domain'] ?? '', $newKey),
                'password' => encrypt_with_master($plaintext['password'] ?? '', $newKey),
                'email' => encrypt_with_master($plaintext['email'] ?? '', $newKey),
                'note' => encrypt_with_master($plaintext['note'] ?? '', $newKey),
                'otp_secret' => $plaintext['otp_secret'] === null ? null : encrypt_with_master($plaintext['otp_secret'], $newKey),
            ];
        }

        // Secure notes are shared in one protected file. Only lines decryptable
        // as this user are rotated; other users' ciphertext remains untouched.
        $notesPath = dirname(__DIR__) . '/vault/secure_notes.bin';
        $notesOriginal = null;
        $notesUpdated = null;
        if (is_file($notesPath)) {
            $notesOriginal = file_get_contents($notesPath);
            if ($notesOriginal === false) {
                throw new RuntimeException('Secure notes could not be read.');
            }
            $lines = file($notesPath, FILE_IGNORE_NEW_LINES);
            if ($lines === false) {
                throw new RuntimeException('Secure notes could not be read.');
            }
            $changed = false;
            foreach ($lines as $index => $line) {
                if ($line === '') {
                    continue;
                }
                $decrypted = decrypt_with_master($line, $currentKey);
                $decoded = $decrypted === false ? null : json_decode($decrypted, true);
                if (is_array($decoded) && (int) ($decoded['user_id'] ?? 0) === $userId) {
                    $lines[$index] = encrypt_with_master(
                        json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        $newKey
                    );
                    $changed = true;
                }
            }
            if ($changed) {
                $notesUpdated = implode(PHP_EOL, $lines) . PHP_EOL;
            }
        }

        $fileChanges = [];
        $filesDirectory = dirname(__DIR__) . '/vault/files';
        if (is_dir($filesDirectory)) {
            foreach (scandir($filesDirectory) ?: [] as $filename) {
                if (!preg_match('/\A[a-f0-9]{32}\.enc\z/', $filename) || is_link($filesDirectory . '/' . $filename)) {
                    continue;
                }
                $path = $filesDirectory . '/' . $filename;
                $original = file_get_contents($path);
                $packet = $original === false ? null : json_decode($original, true);
                if (!is_array($packet) || (int) ($packet['user_id'] ?? 0) !== $userId) {
                    continue;
                }
                $contents = decrypt_with_master($packet['data'] ?? null, $currentKey);
                if ($contents === false) {
                    throw new RuntimeException('A vault file could not be decrypted.');
                }
                $packet['data'] = encrypt_with_master($contents, $newKey);
                $fileChanges[] = [
                    'path' => $path,
                    'original' => $original,
                    'updated' => json_encode($packet, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                ];
            }
        }

        $writtenFiles = [];
        $notesWritten = false;
        $pdo->beginTransaction();
        try {
            $updateRecord = $pdo->prepare(
                'UPDATE passwords SET domain = ?, password = ?, email = ?, note = ?, otp_secret = ? WHERE id = ? AND user_id = ?'
            );
            foreach ($encryptedRows as $row) {
                $updateRecord->execute([
                    $row['domain'], $row['password'], $row['email'], $row['note'], $row['otp_secret'], $row['id'], $userId,
                ]);
            }

            if ($notesUpdated !== null && !atomic_write($notesPath, $notesUpdated)) {
                throw new RuntimeException('Secure notes could not be updated.');
            }
            $notesWritten = $notesUpdated !== null;
            foreach ($fileChanges as $change) {
                if (!atomic_write($change['path'], $change['updated'])) {
                    throw new RuntimeException('A vault file could not be updated.');
                }
                $writtenFiles[] = $change;
            }

            $newHash = password_hash($newMaster, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET master_password = ?, key_salt = ? WHERE id = ?')
                ->execute([$newHash, $newSalt, $userId]);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            foreach ($writtenFiles as $change) {
                atomic_write($change['path'], $change['original']);
            }
            if ($notesWritten && $notesOriginal !== null) {
                atomic_write($notesPath, $notesOriginal);
            }
            throw $exception;
        }

        session_regenerate_id(true);
        $_SESSION['master_key'] = $newKey;
        $_SESSION['last_activity'] = time();
        $_SESSION['flash_success'] = 'Master password updated successfully.';
    } catch (Throwable $exception) {
        error_log('Passman master-password change failed: ' . $exception->getMessage());
        $_SESSION['flash_error'] = $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'The master password could not be updated.';
    }
}

header('Location: ../dashboard.php');
exit;
