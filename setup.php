<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Setup is available only from the command line.');
}

require_once __DIR__ . '/vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $variable) {
        if (!isset($_ENV[$variable]) || $_ENV[$variable] === '') {
            throw new RuntimeException('Required database environment variables are missing.');
        }
    }

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

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        master_password VARCHAR(255) NOT NULL,
        key_salt CHAR(64) NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS passwords (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        category VARCHAR(50) NOT NULL DEFAULT 'General',
        domain TEXT NULL,
        username VARCHAR(100) NULL,
        password MEDIUMTEXT NULL,
        email TEXT NULL,
        note MEDIUMTEXT NULL,
        otp_secret TEXT NULL,
        INDEX idx_passwords_user_id (user_id),
        CONSTRAINT fk_passwords_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$columnExists = static function (string $table, string $column) use ($pdo): bool {
    $statement = $pdo->prepare(
        'SELECT 1
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?
         LIMIT 1'
    );

    $statement->execute([$table, $column]);

    return (bool) $statement->fetchColumn();
};

    if (!$columnExists('users', 'key_salt')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN key_salt CHAR(64) NULL DEFAULT NULL AFTER master_password');
    }
    if (!$columnExists('passwords', 'otp_secret')) {
        $pdo->exec('ALTER TABLE passwords ADD COLUMN otp_secret TEXT NULL AFTER note');
    }

    // Ciphertext expands beyond the original VARCHAR(100) columns.
    $pdo->exec("ALTER TABLE passwords
        MODIFY category VARCHAR(50) NOT NULL DEFAULT 'General',
        MODIFY domain TEXT NULL,
        MODIFY username VARCHAR(100) NULL,
        MODIFY password MEDIUMTEXT NULL,
        MODIFY email TEXT NULL,
        MODIFY note MEDIUMTEXT NULL,
        MODIFY otp_secret TEXT NULL");
    $pdo->exec("UPDATE passwords SET category = 'General' WHERE category IS NULL OR category = ''");

    $hasUserIndex = false;
    foreach ($pdo->query('SHOW INDEX FROM passwords')->fetchAll() as $index) {
        if (($index['Column_name'] ?? null) === 'user_id') {
            $hasUserIndex = true;
            break;
        }
    }
    if (!$hasUserIndex) {
        $pdo->exec('CREATE INDEX idx_passwords_user_id ON passwords (user_id)');
    }

    $foreignKeyStatement = $pdo->prepare(
        'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
           AND REFERENCED_TABLE_NAME = ?'
    );
    $foreignKeyStatement->execute(['passwords', 'user_id', 'users']);
    if (!$foreignKeyStatement->fetch()) {
        $pdo->exec('ALTER TABLE passwords ADD CONSTRAINT fk_passwords_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
    }

    fwrite(STDOUT, "Passman schema is ready. setup.php is CLI-only and web access is blocked.\n");
} catch (Throwable $exception) {
    error_log('Passman setup failed: ' . $exception->getMessage());
    fwrite(STDERR, "Setup failed. Check .env, database existence, and database-user privileges.\n");
    exit(1);
}
