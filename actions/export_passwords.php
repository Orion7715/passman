<?php

require_once '../includes/protect.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

$exportType = $_POST['export_type'] ?? '';
if (!in_array($exportType, ['plain', 'encrypted'], true)) {
    $_SESSION['flash_error'] = 'Invalid export type.';
    header('Location: ../dashboard.php');
    exit;
}

$statement = $pdo->prepare(
    'SELECT category, domain, username, password, email, note, otp_secret FROM passwords WHERE user_id = ? ORDER BY id DESC'
);
$statement->execute([(int) $_SESSION['user_id']]);
$rows = $statement->fetchAll();
if (empty($rows)) {
    $_SESSION['flash_error'] = 'No passwords to export.';
    header('Location: ../dashboard.php');
    exit;
}

try {
    $exportRows = [];
    foreach ($rows as $row) {
        $values = [
            $row['category'] ?: 'General',
            $row['domain'],
            $row['username'],
            $row['password'],
            $row['email'],
            $row['note'],
            $row['otp_secret'],
        ];
        if ($exportType === 'plain') {
            foreach ([1, 3, 4, 5, 6] as $index) {
                $values[$index] = decrypt_with_master($values[$index], $_SESSION['master_key']);
                if ($values[$index] === false) {
                    throw new RuntimeException('A vault record could not be decrypted.');
                }
            }
        }
        $exportRows[] = $values;
    }
} catch (Throwable $exception) {
    error_log('Passman export failed: ' . $exception->getMessage());
    $_SESSION['flash_error'] = 'Export failed because a vault record could not be decrypted.';
    header('Location: ../dashboard.php');
    exit;
}

$csvCell = static function (?string $value): string {
    $value = (string) $value;
    return preg_match('/\A[=+\-@]/', $value) ? "'" . $value : $value;
};

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="passman_vault_' . $exportType . '.csv"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

$output = fopen('php://output', 'wb');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['Category', 'Domain', 'Username', 'Password', 'Email', 'Note', 'TOTP Secret']);
foreach ($exportRows as $row) {
    fputcsv($output, array_map($csvCell, $row));
}
fclose($output);
exit;
