<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OTPHP\TOTP;

function generate_totp_code(string $secret): string
{
    $secret = strtoupper(
        preg_replace('/[\s-]+/', '', $secret)
    );
    
    if ($secret === '') {
        return '';
    }

    try {
        $totp = TOTP::create($secret);
        return $totp->now();
    } catch (\Throwable $e) {
        return '';
    }
}