<?php
require_once 'includes/protect.php';
require_once 'includes/db.php';

$master_key = $_SESSION['master_key'];
$user_id = (int) $_SESSION['user_id'];
$upload_dir = __DIR__ . '/vault/files';

if (!is_dir($upload_dir) && !mkdir($upload_dir, 0750, true) && !is_dir($upload_dir)) {
    http_response_code(500);
    exit('File vault is unavailable.');
}

$isVaultFilename = static fn (string $filename): bool => (bool) preg_match('/\A[a-f0-9]{32}\.enc\z/', $filename);
$readPacket = static function (string $path): ?array {
    if (!is_file($path) || is_link($path)) {
        return null;
    }
    $contents = file_get_contents($path);
    $packet = $contents === false ? null : json_decode($contents, true);
    return is_array($packet) ? $packet : null;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_to_upload'])) {
    $file = $_FILES['file_to_upload'];
    $blockedExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'cgi', 'pl', 'py', 'sh', 'bat', 'cmd', 'com', 'exe', 'msi'];
    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    $mime = is_uploaded_file($file['tmp_name'] ?? '') ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : false;

    try {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('File upload failed.');
        }
        if ((int) $file['size'] < 1 || (int) $file['size'] > 10 * 1024 * 1024) {
            throw new RuntimeException('Files must be between 1 byte and 10 MiB.');
        }
        if (in_array($extension, $blockedExtensions, true) || in_array($mime, ['application/x-httpd-php', 'text/x-php'], true)) {
            throw new RuntimeException('Executable files are not allowed in the vault.');
        }

        $originalName = safe_download_filename((string) $file['name']);
        $contents = file_get_contents($file['tmp_name']);
        if ($contents === false) {
            throw new RuntimeException('Uploaded file could not be read.');
        }
        $packet = json_encode([
            'original_name' => $originalName,
            'user_id' => $user_id,
            'data' => encrypt_with_master($contents, $master_key),
            'timestamp' => time(),
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $safeFilename = bin2hex(random_bytes(16)) . '.enc';
        if (!atomic_write($upload_dir . '/' . $safeFilename, $packet)) {
            throw new RuntimeException('Encrypted file could not be stored.');
        }
        $_SESSION['flash_success'] = 'File encrypted and vaulted.';
    } catch (Throwable $exception) {
        error_log('Passman file upload failed: ' . $exception->getMessage());
        $_SESSION['flash_error'] = $exception instanceof RuntimeException ? $exception->getMessage() : 'File upload failed.';
    }
    header('Location: files.php');
    exit;
}

if (isset($_GET['download'])) {
    $target = (string) $_GET['download'];
    $path = $upload_dir . '/' . $target;
    $packet = $isVaultFilename($target) ? $readPacket($path) : null;
    if (is_array($packet) && (int) ($packet['user_id'] ?? 0) === $user_id) {
        $decryptedData = decrypt_with_master($packet['data'] ?? null, $master_key);
        if ($decryptedData !== false) {
            $filename = safe_download_filename((string) ($packet['original_name'] ?? 'download.bin'));
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . strlen($decryptedData));
            header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
            echo $decryptedData;
            exit;
        }
    }
    http_response_code(404);
    exit('File not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $target = (string) $_POST['delete_file'];
    $path = $upload_dir . '/' . $target;
    $packet = $isVaultFilename($target) ? $readPacket($path) : null;
    if (is_array($packet) && (int) ($packet['user_id'] ?? 0) === $user_id && unlink($path)) {
        $_SESSION['flash_success'] = 'File permanently wiped.';
    } else {
        $_SESSION['flash_error'] = 'File was not found or could not be deleted.';
    }
    header('Location: files.php');
    exit;
}

$user_files = [];
foreach (scandir($upload_dir) ?: [] as $filename) {
    if (!$isVaultFilename($filename)) {
        continue;
    }
    $packet = $readPacket($upload_dir . '/' . $filename);
    if (is_array($packet) && (int) ($packet['user_id'] ?? 0) === $user_id) {
        $user_files[] = [
            'safe_name' => $filename,
            'original_name' => safe_download_filename((string) ($packet['original_name'] ?? 'download.bin')),
            'time' => (int) ($packet['timestamp'] ?? 0),
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files - Passman Vault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #050505;
            background-image: radial-gradient(circle at top left, #1e1b4b, transparent), 
                              radial-gradient(circle at bottom right, #2e1065, transparent);
        }
        .glass-card {
            background: rgba(15, 15, 15, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(139, 92, 246, 0.1);
        }
    </style>
</head>
<body class="min-h-screen p-4 md:p-8 text-slate-200">

    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tighter bg-gradient-to-r from-purple-400 to-fuchsia-400 bg-clip-text text-transparent uppercase">
                    FILE.VAULT
                </h1>
                <p class="text-slate-500 text-[10px] uppercase tracking-[0.3em]">Encrypted Blob Storage</p>
            </div>
            <a href="dashboard.php" class="px-5 py-2 rounded-xl border border-slate-800 hover:border-purple-500/50 text-xs text-slate-400 transition-all uppercase tracking-widest font-bold">
                Back to Terminal
            </a>
        </div>

        <form method="POST" enctype="multipart/form-data" class="glass-card p-6 rounded-3xl mb-10 border-purple-500/10">
            <input type="hidden" name="csrf_token" value="<?= csrf_token('files') ?>">
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <label class="flex-1 w-full p-4 border-2 border-dashed border-slate-800 rounded-2xl hover:border-purple-500/40 transition-all cursor-pointer group text-center">
                    <input type="file" name="file_to_upload" class="hidden" onchange="this.nextElementSibling.innerText = this.files[0].name">
                    <span class="text-slate-500 text-sm group-hover:text-purple-400">Click to select classified file...</span>
                </label>
                <button type="submit" class="w-full md:w-auto px-8 py-4 bg-purple-600 hover:bg-purple-500 text-white rounded-2xl font-bold text-xs uppercase tracking-widest transition-all active:scale-95">
                    Encrypt & Vault
                </button>
            </div>
        </form>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="mb-6 p-4 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-xs font-bold rounded-xl flex items-center gap-3">
                <span class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></span>
                <?= string_sanitize($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold rounded-xl">
                <?= string_sanitize($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <div class="grid gap-4">
            <?php if (empty($user_files)): ?>
                <div class="text-center py-20 glass-card rounded-3xl border-dashed border-slate-800">
                    <p class="text-slate-600 text-xs uppercase tracking-widest font-bold">No Encrypted Files Found</p>
                </div>
            <?php else: ?>
                <?php foreach ($user_files as $file): ?>
                    <div class="glass-card p-4 md:p-6 rounded-2xl flex items-center justify-between hover:border-purple-500/30 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-xl bg-purple-500/10 text-purple-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-white font-bold text-sm"><?= htmlspecialchars($file['original_name']) ?></h3>
                                <p class="text-[9px] text-slate-500 uppercase tracking-widest mt-1">
                                    AES-256 • <?= date('Y-m-d H:i', $file['time']) ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <a href="?download=<?= urlencode($file['safe_name']) ?>" class="p-3 rounded-xl hover:bg-purple-500/20 text-purple-400 transition-colors" title="Decrypt & Download">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            </a>
                            
                            <form method="POST" onsubmit="return confirm('Permanently wipe this file from vault?');">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token('files') ?>">
                                <input type="hidden" name="delete_file" value="<?= htmlspecialchars($file['safe_name']) ?>">
                                <button type="submit" class="p-3 rounded-xl hover:bg-red-500/20 text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Security Timeout Warning Modal -->
    <div id="timeout-modal" class="hidden fixed inset-0 flex items-center justify-center z-[9999] backdrop-blur-md px-4" dir="ltr">
        <div class="absolute inset-0 bg-black/70"></div>
        <div class="glass-card w-full max-w-md p-8 rounded-[2rem] relative z-10 shadow-2xl text-center border-purple-500/20 bg-[#0f0f0f]/90">
            <h3 class="text-white font-bold text-lg mb-2 uppercase tracking-wider">Security Timeout Warning</h3>
            <p class="text-slate-400 text-sm mb-6">
                You have been inactive. For your protection, your vault will lock automatically in 
                <span id="timer-seconds" class="text-purple-400 font-mono font-bold text-base">30</span> seconds.
            </p>
            <button onclick="resetTimers()" class="w-full py-3.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-purple-900/30">
                Stay Logged In
            </button>
        </div>
    </div>

    <form id="logout-form" action="logout.php" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?= csrf_token('logout') ?>">
    </form>
    <script src="assets/js/main.js"></script>
</body>
</html>
