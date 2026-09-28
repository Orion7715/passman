<?php
require_once 'includes/session.php';
require "includes/db.php";
require "includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm  = $_POST["confirm_password"] ?? "";

    if (!validate_csrf('register', $_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Security validation failed. Please try again.';
    } elseif (empty($username) || empty($password) || empty($confirm)) {
        $_SESSION["error"] = "All Fields are Required";
    } elseif (!is_valid_username($username)) {
        $_SESSION['error'] = 'Username must be 3-50 characters and use only letters, numbers, dots, hyphens, or underscores.';
    } elseif ($password !== $confirm) {
        $_SESSION["error"] = "Passwords Do Not Match";
    } else {
        $passwordErrors = is_strong_password($password);
        if (!empty($passwordErrors)) {
            $_SESSION["error"] = implode("\n", $passwordErrors);
            header("Location: register.php");
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION["error"] = "User '{$username}' Already Exists";
            header("Location: register.php");
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $keySalt = new_encryption_salt();
        $stmt = $pdo->prepare("INSERT INTO users (username, master_password, key_salt) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $hash, $keySalt]);
        } catch (PDOException $exception) {
            $_SESSION['error'] = 'Unable to create the account. Please choose a different username.';
            header("Location: register.php");
            exit;
        }

        $_SESSION["success"] = "Account Created Successfully";
        header("Location: login.php");
        exit;
    }
    header("Location: register.php");
    exit;
}

$error = $_SESSION["error"] ?? "";
unset($_SESSION["error"]);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Passman Golden Edition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #080705;
            background-image: radial-gradient(circle at top right, #451a03, transparent), 
                              radial-gradient(circle at bottom left, #78350f, transparent);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .glass-card {
            background: rgba(20, 15, 5, 0.85);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(251, 191, 36, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .gold-glow { box-shadow: 0 0 20px rgba(251, 191, 36, 0.2); }
        .gold-glow-focus:focus { box-shadow: 0 0 15px rgba(245, 158, 11, 0.4); border-color: #f59e0b !important; }
        .strength-step { height: 4px; border-radius: 2px; transition: all 0.4s ease; background: #1c1917; }
        .match-success { border-color: #fbbf24 !important; box-shadow: 0 0 15px rgba(251, 191, 36, 0.3); }
        .match-error { border-color: #ef4444 !important; box-shadow: 0 0 15px rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6 text-orange-50/90">

    <div class="w-full max-w-md">
        <div class="text-center mb-10 scale-110">
            <div class="inline-block p-4 rounded-3xl bg-orange-500/10 border border-orange-500/20 mb-4 gold-glow">
                <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h1 class="text-4xl font-black tracking-[0.15em] bg-gradient-to-b from-amber-300 via-orange-500 to-amber-600 bg-clip-text text-transparent drop-shadow-sm">
                PASSMAN
            </h1>
            <p class="text-amber-700 text-[9px] mt-1 font-bold uppercase tracking-[0.5em]">Vault Security Protocol</p>
        </div>

        <form method="POST" id="reg-form" class="glass-card p-8 rounded-[2rem] relative overflow-hidden">
            <input type="hidden" name="csrf_token" value="<?= csrf_token('register') ?>">
            <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-amber-500 to-transparent"></div>
            
            <h2 class="text-lg font-bold mb-8 text-amber-100 flex items-center gap-3">
                <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse shadow-[0_0_8px_#f97316]"></span>
                Registration Terminal
            </h2>

            <?php if (!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-2xl mb-6 text-xs flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <label class="text-[10px] text-amber-600/70 ml-1 uppercase tracking-widest font-black mb-2 block">Username</label>
                <div class="relative">
                    <input type="text" name="username" id="u_field" required placeholder="Enter Identity" 
                           class="w-full bg-black/60 border border-stone-800 px-5 py-4 rounded-2xl outline-none text-amber-50 transition-all gold-glow-focus text-left">
                    <div class="absolute inset-y-0 right-5 flex items-center text-amber-900/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <label class="text-[10px] text-amber-600/70 ml-1 uppercase tracking-widest font-black mb-2 block">Master Password</label>
                <div class="relative">
                    <input type="password" name="password" id="p_field" required placeholder="••••••••" 
                           class="w-full bg-black/60 border border-stone-800 px-5 py-4 rounded-2xl outline-none text-amber-50 transition-all gold-glow-focus text-left">
                    <div class="absolute inset-y-0 right-5 flex items-center text-amber-900/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                </div>
                <div class="mt-4 flex gap-1.5 px-1">
                    <div id="s-1" class="strength-step flex-1"></div>
                    <div id="s-2" class="strength-step flex-1"></div>
                    <div id="s-3" class="strength-step flex-1"></div>
                    <div id="s-4" class="strength-step flex-1"></div>
                </div>
            </div>

            <div class="mb-8">
                <label class="text-[10px] text-amber-600/70 ml-1 uppercase tracking-widest font-black mb-2 block">Confirm Password</label>
                <div class="relative">
                    <input type="password" name="confirm_password" id="c_field" required placeholder="••••••••" 
                           class="w-full bg-black/60 border border-stone-800 px-5 py-4 rounded-2xl outline-none text-amber-50 transition-all text-left">
                    <div id="m-icon" class="absolute inset-y-0 right-5 flex items-center text-amber-900/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
            </div>

            <button type="submit" id="submit-btn" disabled
                    class="relative w-full py-4 bg-stone-900 text-stone-600 rounded-2xl font-black uppercase text-[11px] tracking-[0.3em] overflow-hidden transition-all duration-500 cursor-not-allowed">
                Create Vault
            </button>

            <div class="mt-8 pt-6 border-t border-amber-900/20 text-center">
                <p class="text-xs text-amber-700 font-medium">
                    Already Authorized? 
                    <a href="login.php" class="text-amber-500 hover:text-amber-400 font-black ml-1 transition-colors underline decoration-amber-900 underline-offset-4">Login Here</a>
                </p>
            </div>
        </form>
    </div>

    <script>
        const u = document.getElementById('u_field');
        const p = document.getElementById('p_field');
        const c = document.getElementById('c_field');
        const btn = document.getElementById('submit-btn');
        const mIcon = document.getElementById('m-icon');

        window.onload = () => u.focus();

        u.addEventListener('keypress', (e) => { if(e.key === 'Enter') { e.preventDefault(); p.focus(); } });
        p.addEventListener('keypress', (e) => { if(e.key === 'Enter') { e.preventDefault(); c.focus(); } });

        function validate() {
            const pVal = p.value;
            const cVal = c.value;

            let s = 0;
            if (pVal.length >= 8) s++;
            if (pVal.match(/[A-Z]/)) s++;
            if (pVal.match(/[0-9]/)) s++;
            if (pVal.match(/[^A-Za-z0-9]/)) s++;
            
            const colors = ['#1c1917', '#ef4444', '#f97316', '#fbbf24', '#f59e0b'];
            for(let i=1; i<=4; i++) {
                const el = document.getElementById('s-'+i);
                el.style.backgroundColor = i <= s ? colors[s] : '#1c1917';
                el.style.boxShadow = i <= s ? `0 0 10px ${colors[s]}44` : 'none';
            }

            if (cVal.length > 0) {
                if (pVal === cVal && pVal !== "") {
                    c.className = "w-full bg-black/60 border px-5 py-4 rounded-2xl outline-none text-amber-50 transition-all text-left match-success";
                    mIcon.className = "absolute inset-y-0 right-5 flex items-center text-amber-500 drop-shadow-[0_0_5px_#fbbf24]";
                    btn.disabled = false;
                    btn.className = "relative w-full py-4 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white rounded-2xl font-black uppercase text-[11px] tracking-[0.3em] overflow-hidden transition-all duration-300 shadow-[0_0_20px_rgba(245,158,11,0.3)] cursor-pointer active:scale-95";
                } else {
                    c.className = "w-full bg-black/60 border px-5 py-4 rounded-2xl outline-none text-amber-50 transition-all text-left match-error";
                    mIcon.className = "absolute inset-y-0 right-5 flex items-center text-red-500";
                    btn.disabled = true;
                    btn.className = "relative w-full py-4 bg-stone-900 text-stone-600 rounded-2xl font-black uppercase text-[11px] tracking-[0.3em] overflow-hidden transition-all duration-500 cursor-not-allowed";
                }
            }
        }

        p.addEventListener('input', validate);
        c.addEventListener('input', validate);

        document.getElementById('reg-form').onsubmit = () => {
            btn.innerHTML = `<span class="animate-pulse">Authorizing...</span>`;
        };
    </script>
</body>
</html>
