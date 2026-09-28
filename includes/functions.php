<?php

const PASSMAN_CATEGORIES = [
    'General', 'Work', 'Education', 'Social', 'Finance', 'Personal', 'Shopping',
];

function is_strong_password(string $password): array
{
    $requirements = [];
    if (strlen($password) < 12) $requirements[] = '12 characters';
    if (!preg_match('/[A-Z]/', $password)) $requirements[] = 'an uppercase letter';
    if (!preg_match('/[a-z]/', $password)) $requirements[] = 'a lowercase letter';
    if (!preg_match('/[0-9]/', $password)) $requirements[] = 'a number';
    if (!preg_match('/[!@#$%^&*()\-_=+\[\]{}<>?]/', $password)) $requirements[] = 'a special character';

    return empty($requirements) ? [] : ['Password must include ' . implode(', ', $requirements) . '.'];
}

function is_valid_username(string $username): bool
{
    return (bool) preg_match('/\A[A-Za-z0-9_.-]{3,50}\z/', $username);
}

function new_encryption_salt(): string
{
    return bin2hex(random_bytes(32));
}

/** Existing accounts without key_salt retain their legacy key until rotation. */
function derive_encryption_key(string $password, ?string $keySalt): string
{
    if (is_string($keySalt) && preg_match('/\A[0-9a-f]{64}\z/i', $keySalt)) {
        return hash_pbkdf2('sha256', $password, hex2bin($keySalt), 600000, 32, true);
    }

    return hash('sha256', $password, true);
}

/** Encrypts new data using authenticated AES-256-GCM. */
function encrypt_with_master(string $data, string $key): string
{
    if ($data === '') {
        return '';
    }

    $nonce = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt($data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
    if ($ciphertext === false || strlen($tag) !== 16) {
        throw new RuntimeException('Encryption failed.');
    }

    return 'v2:' . base64_encode($nonce . $tag . $ciphertext);
}

/** Decrypts v2 values and legacy AES-CBC values for migration compatibility. */
function decrypt_with_master(?string $data, string $key): string|false
{
    if ($data === null || $data === '') {
        return '';
    }

    if (str_starts_with($data, 'v2:')) {
        $decoded = base64_decode(substr($data, 3), true);
        if ($decoded === false || strlen($decoded) < 29) {
            return false;
        }

        return openssl_decrypt(
            substr($decoded, 28),
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            substr($decoded, 0, 12),
            substr($decoded, 12, 16)
        );
    }

    // Backward compatibility for data written by the original AES-CBC release.
    $decoded = base64_decode($data, true);
    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    if ($decoded === false || strlen($decoded) <= $ivLength) {
        return false;
    }

    return openssl_decrypt(
        substr($decoded, $ivLength),
        'AES-256-CBC',
        $key,
        0,
        substr($decoded, 0, $ivLength)
    );
}

function csrf_token(string $action): string
{
    if (empty($_SESSION['csrf_tokens'][$action])) {
        $_SESSION['csrf_tokens'][$action] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_tokens'][$action];
}

function validate_csrf(string $action, ?string $token): bool
{
    if (empty($token) || empty($_SESSION['csrf_tokens'][$action])) {
        return false;
    }
    if (!hash_equals($_SESSION['csrf_tokens'][$action], $token)) {
        return false;
    }

    unset($_SESSION['csrf_tokens'][$action]);
    return true;
}

function string_sanitize(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** @return array{category:string,domain:string,username:string,password:string,email:string,note:string,otp_secret:string}|null */
function normalize_credential_input(array $input, ?string &$error = null): ?array
{
    $category = trim((string) ($input['category'] ?? 'General'));
    $domain = trim((string) ($input['domain'] ?? ''));
    $username = trim((string) ($input['username'] ?? ''));
    $password = (string) ($input['password'] ?? '');
    $email = trim((string) ($input['email'] ?? ''));
    $note = trim((string) ($input['note'] ?? ''));
    $otpSecret = strtoupper(preg_replace('/[\s-]+/', '', (string) ($input['otp_secret'] ?? '')));

    if (!in_array($category, PASSMAN_CATEGORIES, true)) {
        $error = 'Invalid category.';
    } elseif ($domain === '' || strlen($domain) > 255) {
        $error = 'A domain or service name of 255 characters or fewer is required.';
    } elseif (strlen($username) > 100) {
        $error = 'Username must be 100 characters or fewer.';
    } elseif ($password === '' || strlen($password) > 4096) {
        $error = 'Password is required and must be 4096 characters or fewer.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $error = 'A valid email address of 254 characters or fewer is required.';
    } elseif (strlen($note) > 12000) {
        $error = 'Note must be 12000 characters or fewer.';
    } elseif ($otpSecret !== '' && (!preg_match('/\A[A-Z2-7]+=*\z/', $otpSecret) || strlen($otpSecret) > 128)) {
        $error = 'The TOTP secret format is invalid.';
    }

    if ($error !== null) {
        return null;
    }

    return [
        'category' => $category,
        'domain' => $domain,
        'username' => $username,
        'password' => $password,
        'email' => $email,
        'note' => $note,
        'otp_secret' => $otpSecret,
    ];
}

function safe_download_filename(string $filename): string
{
    $filename = basename(str_replace('\\', '/', $filename));
    $filename = preg_replace('/[\x00-\x1F\x7F"\\\\]/', '_', $filename) ?? '';
    $filename = trim($filename, '. ');

    return $filename === '' ? 'download.bin' : substr($filename, 0, 150);
}

function atomic_write(string $path, string $contents): bool
{
    $temporary = $path . '.tmp-' . bin2hex(random_bytes(8));
    if (file_put_contents($temporary, $contents, LOCK_EX) === false) {
        return false;
    }
    chmod($temporary, 0640);
    if (!rename($temporary, $path)) {
        @unlink($temporary);
        return false;
    }

    return true;
}
