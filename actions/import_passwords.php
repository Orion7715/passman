<?php

require_once '../includes/protect.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

$importType = $_POST['import_type'] ?? '';
$file = $_FILES['csv_file'] ?? null;
if (!in_array($importType, ['plain', 'encrypted'], true)
    || !is_array($file)
    || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    || !is_uploaded_file($file['tmp_name'] ?? '')) {
    $_SESSION['flash_error'] = 'Choose a valid CSV file and import type.';
    header('Location: ../dashboard.php');
    exit;
}

$extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
if ($extension !== 'csv' || !in_array($mime, ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'], true)
    || (int) $file['size'] < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
    $_SESSION['flash_error'] = 'CSV imports must be a text CSV file no larger than 5 MiB.';
    header('Location: ../dashboard.php');
    exit;
}

$handle = fopen($file['tmp_name'], 'rb');
if ($handle === false) {
    $_SESSION['flash_error'] = 'The uploaded CSV could not be read.';
    header('Location: ../dashboard.php');
    exit;
}

try {
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }
    $header = fgetcsv($handle);
    if ($header === false || count($header) < 6) {
        throw new RuntimeException('The CSV header is missing or invalid.');
    }

    $pdo->beginTransaction();
    $insert = $pdo->prepare(
        'INSERT INTO passwords (user_id, category, domain, username, password, email, note, otp_secret) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $count = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if ($row === [null] || $row === []) {
            continue;
        }
        if (count($row) < 6 || count($row) > 7) {
            throw new RuntimeException('Every CSV row must contain 6 or 7 columns.');
        }
        if (++$count > 5000) {
            throw new RuntimeException('A maximum of 5000 credentials can be imported at once.');
        }

        $input = [
            'category' => $row[0] ?? 'General',
            'domain' => $row[1] ?? '',
            'username' => $row[2] ?? '',
            'password' => $row[3] ?? '',
            'email' => $row[4] ?? '',
            'note' => $row[5] ?? '',
            'otp_secret' => $row[6] ?? '',
        ];
        if ($importType === 'plain') {
            $error = null;
            $entry = normalize_credential_input($input, $error);
            if ($entry === null) {
                throw new RuntimeException('Invalid CSV row: ' . $error);
            }
            $values = [
                encrypt_with_master($entry['domain'], $_SESSION['master_key']),
                $entry['username'],
                encrypt_with_master($entry['password'], $_SESSION['master_key']),
                encrypt_with_master($entry['email'], $_SESSION['master_key']),
                encrypt_with_master($entry['note'], $_SESSION['master_key']),
                $entry['otp_secret'] === '' ? null : encrypt_with_master($entry['otp_secret'], $_SESSION['master_key']),
            ];
            $category = $entry['category'];
        } else {
            $category = trim((string) $input['category']);
            if (!in_array($category, PASSMAN_CATEGORIES, true) || strlen((string) $input['username']) > 100) {
                throw new RuntimeException('Invalid encrypted CSV row.');
            }
            foreach (['domain', 'password', 'email', 'note', 'otp_secret'] as $field) {
                if ($input[$field] !== '' && decrypt_with_master((string) $input[$field], $_SESSION['master_key']) === false) {
                    throw new RuntimeException('Encrypted CSV data belongs to another vault or is invalid.');
                }
            }
            $values = [$input['domain'], $input['username'], $input['password'], $input['email'], $input['note'], $input['otp_secret'] === '' ? null : $input['otp_secret']];
        }

        $insert->execute([(int) $_SESSION['user_id'], $category, ...$values]);
    }
    if ($count === 0) {
        throw new RuntimeException('The CSV contains no credential rows.');
    }
    $pdo->commit();
    $_SESSION['flash_success'] = $count . ' credential(s) imported successfully.';
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Passman import failed: ' . $exception->getMessage());
    $_SESSION['flash_error'] = $exception instanceof RuntimeException ? $exception->getMessage() : 'CSV import failed.';
} finally {
    fclose($handle);
}

header('Location: ../dashboard.php');
exit;
