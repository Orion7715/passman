<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $variable) {
    if (!isset($_ENV[$variable]) || $_ENV[$variable] === '') {
        error_log('Passman database configuration is incomplete.');
        http_response_code(500);
        exit('Application configuration error.');
    }
}

if (($_ENV['APP_ENV'] ?? 'production') !== 'development') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}
error_reporting(E_ALL);

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $_ENV['DB_HOST'], $_ENV['DB_NAME']),
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    error_log('Passman database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Database connection failed.');
}
