# 🔐 Passman

Passman is a self-hosted PHP password and private-data vault designed to keep sensitive account information under the control of the person running the application.

It provides a web interface for managing credentials, TOTP secrets, secure notes, and private files, with authentication, CSRF protection, user-level authorization, session handling, database-backed storage, and encryption for sensitive vault data.

---

## Features

### 🔑 Credential Vault

Store and manage:

- Website/domain
- Username
- Password
- Email
- Category
- Secure note
- TOTP secret

Password entries can be created, viewed, edited, and deleted through the authenticated application.

### 🔐 Encryption and Master-Key Handling

Sensitive vault fields are protected using the application's master-key based encryption flow.

The current hardened code supports:

- Per-user `key_salt` values for the newer key-derivation flow.
- Authenticated encryption for newly written encrypted vault data.
- Legacy encrypted records remaining readable through the compatibility path.
- Key/data rotation when the master password is changed, including protected vault data such as TOTP secrets, secure notes, and file-vault contents where supported by the current implementation.

Existing accounts created before the newer key-salt migration may have a `NULL` `key_salt`. Do not manually populate or modify this value. Run the supplied setup migration and use the application's master-password rotation flow to migrate protected data.

### 🔢 TOTP / 2FA

Passman can store TOTP secrets and generate time-based verification codes for vault entries.

The project uses the Composer package:

- [`spomky-labs/otphp`](https://github.com/Spomky-Labs/OTPHP)

### 📝 Secure Notes

Passman includes a notes feature for storing private text and supports authenticated access, input handling, and protected storage.

### 📁 Private File Vault

Passman supports private file storage through the file-vault functionality.

The application is designed so that private data directories are not intended to be directly downloadable through normal browser requests. Apache rules and application-level authorization work together to protect stored files.

### 🛡️ Authentication and Session Security

The application includes:

- User registration
- Password hashing
- Login/logout
- Session regeneration on authentication
- CSRF protection for state-changing operations
- Authentication/authorization checks
- Session activity handling
- CAPTCHA protection for the login flow

### 📥 Import / 📤 Export

Credential data can be imported and exported through the corresponding application actions.

Always treat exported vault data as sensitive.

---

# Requirements

The following environment is recommended.

## Server

- Linux
- Apache HTTP Server 2.4+
- HTTPS/TLS for production deployments

Nginx can be used only if the required equivalent access controls and PHP configuration are implemented. The instructions below are written for Apache.

## PHP

- PHP 8.1 or newer
- PDO
- PDO MySQL
- OpenSSL
- GD for the CAPTCHA functionality

Check your PHP version with:

```bash
php -v
```

Check installed modules with:

```bash
php -m
```

On Ubuntu/Debian, the common packages are:

```bash
sudo apt update
sudo apt install -y php php-pdo php-mysql php-gd
```

OpenSSL support is normally included with standard PHP packages. If your installation is custom, verify it with:

```bash
php -m | grep -i openssl
```

## Database

- MySQL 8.0+ or a compatible MariaDB release

The current development environment has been tested against MySQL 8.x.

## Composer

Composer is required to install the project's PHP dependencies.

Check:

```bash
composer --version
```

---

# Project Dependencies

The application uses Composer.

The main direct packages are:

- `vlucas/phpdotenv` — loads environment variables from `.env`
- `spomky-labs/otphp` — TOTP generation and verification

Install all dependencies with:

```bash
composer install
```

---

# Installation

## 1. Clone the repository

Example:

```bash
cd /var/www/html
git clone https://github.com/Orion7715/passman.git
cd passman
```

---

## 2. Install Composer dependencies

Inside the project directory:

```bash
composer install
```

Verify that Composer generated:

```text
vendor/autoload.php
```

---

## 3. Create the database

Log into MySQL as an administrative user:

```bash
mysql -u root -p
```

Create the database:

```sql
CREATE DATABASE mypassman
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Create a dedicated application user.

Use a new, strong password. Do **not** copy a password from this README into production:

```sql
CREATE USER 'passman_user'@'localhost'
IDENTIFIED BY 'REPLACE_WITH_A_STRONG_DATABASE_PASSWORD';
```

Grant privileges:

```sql
GRANT ALL PRIVILEGES ON mypassman.* TO 'passman_user'@'localhost';
FLUSH PRIVILEGES;
```

Exit:

```sql
EXIT;
```

---

# 4. Configure the environment

Create the real `.env` file:

```bash
cp .env.example .env
```

Edit it:

```bash
nano .env
```

Set the correct values:

```env
DB_HOST=localhost
DB_NAME=mypassman
DB_USER=passman_user
DB_PASS=REPLACE_WITH_YOUR_REAL_DATABASE_PASSWORD
```

### Important

The repository contains `.env.example` only so that a new installation knows which variables are required.

---

# 5. Run the database setup/migration

The current `setup.php` is intentionally **CLI-only**.

Run:

```bash
php setup.php
```

Expected result:

```text
Passman schema is ready. setup.php is CLI-only and web access is blocked.
```

The setup process creates or migrates the current schema.

The migration includes the current fields required by the application, including:

### `users`

- `id`
- `username`
- `master_password`
- `key_salt`
- `created_at`

### `passwords`

- `id`
- `user_id`
- `category`
- `domain`
- `username`
- `password`
- `email`
- `note`
- `otp_secret`

It also creates/maintains the user relationship and required index.

The migration is designed to be repeatable.

---

# 6. Verify the database schema

You can verify the result with:

```bash
mysql -u passman_user -p mypassman
```

Then:

```sql
DESCRIBE users;
DESCRIBE passwords;
```

The `users` table should contain `key_salt`.

The `passwords` table should contain `otp_secret`.

Exit:

```sql
EXIT;
```

---

# 7. Apache configuration

Passman uses `.htaccess`, so Apache must allow directory-level overrides.

Open the Apache virtual-host configuration:

```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```

Add a directory block appropriate for your deployment:

```apache
<Directory /var/www/html/passman>
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

Then enable the rewrite module if needed:

```bash
sudo a2enmod rewrite
```

Restart Apache:

```bash
sudo systemctl restart apache2
```

Verify Apache:

```bash
sudo systemctl status apache2
```

---

# 8. HTTPS

For a production deployment, use HTTPS.

Do not expose a password manager over plain HTTP on an untrusted network.

For a local development installation, `https://localhost/...` may use a locally generated certificate. A browser warning about a self-signed or mismatched development certificate is separate from the Passman application itself.

---

# 9. File permissions

Use the minimum permissions required for the application.

Do not use:

```bash
chmod -R 777
```

Do not make `.env` world-writable.

If Apache needs to read the application, make sure the Apache account can traverse the required directories and read the application files.

For example, application files are commonly:

```text
Directories: 755
Files:       644
```

Adjust writable directories only where the application genuinely needs to write data.

The exact ownership model depends on the server layout.

---

# Directory and Data Protection

Passman contains private data directories such as:

```text
vault/
vault/files/
```

These directories should not be directly downloadable through the browser.

The project includes Apache configuration intended to deny direct HTTP access to protected data while allowing the PHP application to access the files through the server filesystem.

If the project contains:

```text
vault/.htaccess
vault/files/.htaccess
```

do not remove those files without replacing their protection with an equivalent server configuration.

A rule such as:

```apache
Require all denied
```

prevents direct web access to the protected directory on Apache 2.4.

This protection is not encryption. It is an additional boundary that prevents someone from bypassing the PHP application by requesting a stored file directly through a URL.

Application-level authentication and authorization are still required.

---

# `.htaccess` and Apache Overrides

The root `.htaccess` is used to provide application-level Apache protections.

For these rules to work:

```apache
AllowOverride All
```

must be enabled for the project's directory.

After changing Apache configuration, restart Apache:

```bash
sudo systemctl restart apache2
```

If `.htaccess` errors occur, inspect:

```bash
sudo tail -n 50 /var/log/apache2/error.log
```

---

# First Run

After completing installation:

```text
https://localhost/passman/register.php
```

Register your first user.

Then log in:

```text
https://localhost/passman/login.php
```

After authentication, use the dashboard to manage credentials and other vault features.

---

# Recommended First Tests

Before entering real credentials, verify the complete application using test data.

## Test authentication

1. Register a test account.
2. Log in.
3. Log out.
4. Confirm protected pages are inaccessible after logout.

## Test the password vault

1. Create a test password entry.
2. Open it.
3. Edit it.
4. Delete it.

## Test TOTP

1. Add a test TOTP secret.
2. Verify a current code is generated.
3. Confirm the code changes according to the TOTP period.

## Test notes

1. Create a test note.
2. Edit it.
3. Reload it.
4. Delete it.

## Test files

1. Upload a harmless test file.
2. Download/view it through the application.
3. Delete it.
4. Confirm that direct public access to the protected storage path is denied.

## Test import/export

Use non-sensitive test data before using real vault data.

## Test two users

Create two test accounts and verify that each account can access only its own:

- Password entries
- Notes
- Files
- TOTP secrets
- Exported data

---

# Changing the Master Password

Changing the master password is a sensitive operation because encrypted data may need to be re-keyed.

Before changing the master password on an important account:

1. Back up the database.
2. Back up the protected vault data.
3. Make sure you can restore the backup.
4. Perform the password change.
5. Log out.
6. Log in using the new password.
7. Verify existing passwords, notes, TOTP data, and vault files.

Do not manually edit `key_salt`.

The setup migration adds `key_salt` to older installations when needed.

Older accounts can have:

```text
key_salt = NULL
```

until their protected data is moved through the application's supported rotation/migration flow.

---

# Backup and Recovery

A password manager should always have a tested backup strategy.

At minimum, back up:

- MySQL database
- private vault data
- application-specific encrypted storage

Example database backup:

```bash
mysqldump -u root -p mypassman > mypassman-backup.sql
```

Store backups outside the public web root.

Do not upload database dumps or private vault backups to a public GitHub repository.

For recovery, restore the database and protected files consistently. Encrypted vault data and the database should be treated as a matching set.

---

# GitHub: What to Commit

The repository should contain the application source and installation metadata.

Typical files/directories to commit include:

```text
actions/
assets/
includes/
.htaccess
composer.json
composer.lock
.env.example
.gitignore
README.md
dashboard.php
files.php
login.php
logout.php
notes.php
register.php
setup.php
```

Also commit any other source files that are part of the application.

---

# GitHub: What NOT to Commit

Do **not** commit:

```text
.env
vendor/
vault/
```

Also do not commit:

```text
*.sql
*.sql.gz
*.log
*.swp
.DS_Store
database backups
private uploaded files
private secure-note storage
personal test data
real credentials
API keys
private keys
```

The repository's `.gitignore` should protect the sensitive/generated locations.

---

# Verify Git Before the First Commit

From the project directory:

```bash
git status
```

Preview what would be added without staging:

```bash
git add -n .
```

Make sure sensitive items such as `.env`, `vendor/`, and private `vault/` data do not appear as files that will be committed.

Then stage only after reviewing:

```bash
git add .
git status
```

Commit:

```bash
git commit -m "Initial release"
```

Do not push secrets to GitHub.

If a secret was ever committed to Git history, deleting the file from the latest commit is not sufficient. Rotate the exposed secret and clean the Git history before making the repository public.

---

# Example GitHub Workflow

After installing Git and creating the repository:

```bash
cd /var/www/html/passman

git init
git add .
git status
git commit -m "Initial release"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/passman.git
git push -u origin main
```

Replace the repository URL with your actual GitHub repository.

---

# Updating an Existing Installation

After pulling a newer release:

```bash
git pull
composer install
```

Then review the release notes and run:

```bash
php setup.php
```

when a schema migration is included.

Always back up the database and protected vault data before migrations that modify encryption or database structure.

---

# Troubleshooting

## HTTP 500 Internal Server Error

Check Apache:

```bash
sudo tail -n 50 /var/log/apache2/error.log
```

Check PHP syntax:

```bash
find . -path './vendor' -prune -o -name '*.php' -type f -print0 | xargs -0 -n1 php -l
```

Do not enable verbose PHP errors on a production server.

---

## Database connection failed

Check:

```text
DB_HOST
DB_NAME
DB_USER
DB_PASS
```

in `.env`.

Test MySQL login separately:

```bash
mysql -u passman_user -p mypassman
```

---

## Composer/autoload error

Run:

```bash
composer install
```

Verify:

```text
vendor/autoload.php
```

exists.

---

## `.htaccess` error

Check Apache:

```bash
sudo apache2ctl configtest
```

Then:

```bash
sudo tail -n 50 /var/log/apache2/error.log
```

Make sure `AllowOverride All` is enabled for the Passman directory.

---

## Setup error

Run setup from the project directory:

```bash
php setup.php
```

Remember that `setup.php` is CLI-only in the current version.

Do not browse directly to:

```text
/setup.php
```

---

# Development

Run PHP syntax checks before making a release:

```bash
find . -path './vendor' -prune -o -name '*.php' -type f -print0 | xargs -0 -n1 php -l
```

Validate Composer:

```bash
composer validate
```

Install dependencies from the lock file:

```bash
composer install
```

Review modified files before committing:

```bash
git status
git diff
```

---

# Security Notes

Passman should be treated as security-sensitive software.

Recommended deployment practices:

- Use HTTPS.
- Keep PHP and Apache updated.
- Keep MySQL/MariaDB updated.
- Use a dedicated database user.
- Use a strong unique database password.
- Never commit `.env`.
- Do not expose the `vault/` directory directly.
- Do not use `chmod 777`.
- Keep backups offline or otherwise protected.
- Test restoration of backups.
- Do not use real credentials while testing application changes.
- Review Git changes before every push.
- Rotate any credential that was accidentally exposed.

---

# Privacy

The application stores sensitive credential information and private user data.

External resources used by the user interface may reveal limited metadata to third-party services. For example, the dashboard may use an external favicon service to display website icons. This can cause the browser to request a domain's favicon from that third-party service.

If strict privacy requirements apply to your deployment, replace external favicon loading with locally hosted or application-controlled icons.

---

# Project Structure

A typical installation contains:

```text
passman/
├── actions/
├── assets/
├── includes/
│   └── dashboard/
├── vault/
│   └── files/
├── composer.json
├── composer.lock
├── .env.example
├── .gitignore
├── .htaccess
├── dashboard.php
├── files.php
├── login.php
├── logout.php
├── notes.php
├── register.php
├── setup.php
└── README.md
```

The `vendor/` directory is generated by Composer and should not be committed.

The real `.env` file is local configuration and should not be committed.

Private vault/user-generated data should not be committed.

---

# License

No license is assumed 

---

# Disclaimer

Passman is provided as a self-hosted application. Review the implementation, server configuration, and operational security of your own deployment before relying on it for important credentials or sensitive information.
