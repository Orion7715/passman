<?php
require_once 'includes/session.php';

// إذا كان المستخدم مسجل الدخول بالفعل، يتم توجيهه مباشرة للوحة التحكم
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    header("Location: dashboard.php");
    exit;
}

require_once "includes/db.php";
require_once "includes/functions.php";

$error = "";
$play_fail_sound = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    $user_captcha = $_POST["captcha_answer"] ?? "";

    if (!validate_csrf('login', $_POST['csrf_token'] ?? null)) {
        $error = 'Security validation failed. Please try again.';
        $play_fail_sound = true;
    } elseif (!isset($_SESSION['captcha_result']) || strtoupper($user_captcha) !== strtoupper($_SESSION['captcha_result'])) {
        $error = "Wrong Security Code, Try Again";
        $play_fail_sound = true;
        unset($_SESSION['captcha_result']); // تـعـديـل: تصفير الكابتشا فوراً لحظر الهجمات الآلية
    } else {
        $stmt = $pdo->prepare("SELECT id, master_password, key_salt FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["master_password"])) {
            unset($_SESSION['captcha_result']);
            session_regenerate_id(true);
            $_SESSION["logged_in"] = true;
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $username;

            $_SESSION['master_key'] = derive_encryption_key($password, $user['key_salt'] ?? null);
            $_SESSION['last_activity'] = time();
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid Credentials";
            $play_fail_sound = true;
            unset($_SESSION['captcha_result']); // text_edit: تصفير الكابتشا لضمان طلب كود جديد عند الخطأ في كلمة المرور
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Passman Vault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #050505;
            background-image: radial-gradient(circle at top right, #1e1b4b, transparent), 
                              radial-gradient(circle at bottom left, #2e1065, transparent);
        }
        .glass-card {
            background: rgba(15, 15, 15, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(139, 92, 246, 0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6 text-slate-200">

    <div class="w-full max-w-md">
        <div class="text-center mb-10">
            <div class="inline-block p-3 rounded-2xl bg-purple-600/10 border border-purple-500/20 mb-4">
                <svg class="w-10 h-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-4xl font-black tracking-tighter bg-gradient-to-r from-purple-400 to-fuchsia-400 bg-clip-text text-transparent">
                PASSMAN
            </h1>
            <p class="text-slate-500 text-xs mt-2 uppercase tracking-[0.3em]">Encrypted Security Suite</p>
        </div>

        <form method="POST" class="glass-card p-8 rounded-3xl shadow-2xl relative overflow-hidden">
            <input type="hidden" name="csrf_token" value="<?= csrf_token('login') ?>">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-purple-500 to-transparent opacity-50"></div>
            
            <h2 class="text-lg font-bold mb-8 text-white flex items-center gap-2">
                <span class="w-1.5 h-5 bg-purple-500 rounded-full"></span>
                Master Authentication
            </h2>

            <?php if (!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-3 animate-pulse">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php if ($play_fail_sound): ?>
                    <audio id="fail-sound" autoplay><source src="assets/sounds/fail.mp3" type="audio/mpeg"></audio>
                <?php endif; ?>
            <?php endif; ?>

            <div class="mb-6 group">
                <label class="text-[10px] text-slate-500 ml-1 uppercase tracking-widest font-bold mb-2 block group-focus-within:text-purple-400 transition-colors">Username</label>
                <div class="relative">
                    <input type="text" name="username" required placeholder="Username" 
                           class="w-full bg-black/40 border border-slate-800 px-4 py-3.5 rounded-xl focus:border-purple-500/50 focus:ring-4 focus:ring-purple-500/10 outline-none text-white transition-all text-left">
                    <div class="absolute inset-y-0 right-4 flex items-center text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                </div>
            </div>

            <div class="mb-8 group">
                <label class="text-[10px] text-slate-500 ml-1 uppercase tracking-widest font-bold mb-2 block group-focus-within:text-purple-400 transition-colors">Master Password</label>
                <div class="relative">
                    <input type="password" name="password" required placeholder="••••••••••••" 
                           class="w-full bg-black/40 border border-slate-800 px-4 py-3.5 rounded-xl focus:border-purple-500/50 focus:ring-4 focus:ring-purple-500/10 outline-none text-white transition-all text-left">
                    <div class="absolute inset-y-0 right-4 flex items-center text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                </div>
            </div>

            <div class="mb-8 p-4 bg-black/60 border border-purple-500/10 rounded-2xl">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Bot Defense</label>
                    <button type="button" onclick="document.getElementById('captcha-img').src='includes/captcha.php?'+Math.random();" class="text-[10px] text-purple-400 hover:text-purple-300 transition-colors flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Refresh
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-12 bg-[#111] border border-slate-800 rounded-xl overflow-hidden shadow-inner">
                        <img id="captcha-img" src="includes/captcha.php" alt="Captcha" class="w-full h-full object-contain select-none">
                    </div>
                    <div class="w-28">
                        <input type="text" name="captcha_answer" required autocomplete="off" placeholder="CODE"
                               class="w-full bg-black border border-slate-800 px-3 py-3 rounded-xl focus:border-purple-500 outline-none text-white text-center font-mono font-bold tracking-widest transition-all uppercase">
                    </div>
                </div>
            </div>

            <button type="submit" id="login-btn" 
                    class="relative w-full py-4 bg-purple-600 hover:bg-purple-500 text-white rounded-2xl shadow-xl shadow-purple-900/40 transition-all duration-300 font-bold uppercase text-[11px] tracking-[0.2em] overflow-hidden flex items-center justify-center gap-3 active:scale-95 group">
                <span id="btn-icon" class="z-10">
                    <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /></svg>
                </span>
                <span id="btn-text" class="z-10">Decrypt & Unlock</span>
                <div class="absolute inset-0 bg-gradient-to-r from-purple-400/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </button>

            <div class="mt-8 pt-6 border-t border-slate-800/50 text-center">
                <p class="text-xs text-slate-500">
                    New Operator? 
                    <a href="register.php" class="text-purple-400 hover:text-purple-300 font-bold ml-1 transition-colors">Initialize Account</a>
                </p>
            </div>
        </form>
    </div>

    <script>
        window.addEventListener('load', () => {
            document.getElementsByName('username')[0]?.focus();
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = document.getElementById('login-btn');
            const icon = document.getElementById('btn-icon');
            const text = document.getElementById('btn-text');
            
            btn.classList.add('opacity-80', 'cursor-wait');
            text.innerText = "Processing...";
            icon.innerHTML = `<svg class="w-5 h-5 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>`;
        });
    </script>
</body>
</html>
