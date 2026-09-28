<div id="passwords-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
    <?php 
    require_once __DIR__ . '/../../includes/totp.php';
    foreach ($passwords as $p): 
        $domain_dec = decrypt_with_master($p['domain'], $master_key) ?: '[decrypt error]';
        $email_dec  = decrypt_with_master($p['email'], $master_key) ?: '[decrypt error]';
        $pass_dec   = decrypt_with_master($p['password'], $master_key) ?: '';

        $otp_dec = !empty($p['otp_secret'])
            ? decrypt_with_master($p['otp_secret'], $master_key) : '';
        $otp_code = !empty($otp_dec)
            ? generate_totp_code($otp_dec) : '';

        $note_dec   = decrypt_with_master($p['note'], $master_key) ?: '';
$clean_url = str_replace(
    ['http://', 'https://', 'www.'],
    '',
    strtolower(trim($domain_dec))
);
        $cat = in_array($p['category'] ?? '', PASSMAN_CATEGORIES, true) ? $p['category'] : 'General';
    ?>
    <div class="password-card group relative bg-white/[0.03] hover:bg-white/[0.08] p-5 rounded-2xl border border-white/10 transition-all cursor-pointer"
         data-domain="<?= string_sanitize(strtolower($domain_dec)) ?>" 
         data-category="<?= string_sanitize($cat) ?>"
         onclick="openModal(<?= (int)$p['id'] ?>)">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-xl bg-black/40 border border-white/5">
	<img
    src="https://www.google.com/s2/favicons?sz=128&domain=<?= rawurlencode($clean_url) ?>"
    class="w-7 h-7 object-contain"
    alt=""
    loading="lazy"
    onerror="this.style.display='none';"
>	
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                     <h3 class="text-white font-black truncate text-base tracking-tight"><?= string_sanitize($domain_dec) ?></h3>
                     <span class="category-tag cat-<?= string_sanitize($cat) ?> text-[9px] font-black uppercase tracking-widest border border-[#926a2d]/30 px-2 py-0.5 rounded"><?= string_sanitize($cat) ?></span>
                </div>
                <p class="text-gray-400 font-black text-xs truncate mt-0.5"><?= string_sanitize($p['username'] ?: "N/A") ?></p>
            </div>
        </div>
    </div>

    <div id="modal-<?= $p['id'] ?>" class="fixed inset-0 bg-black/95 hidden items-center justify-center z-50 p-4 backdrop-blur-md transition-all duration-500">
        <div class="bg-[#0c0c0c] border border-[#926a2d]/30 rounded-3xl shadow-[0_0_50px_rgba(0,0,0,1)] max-w-lg w-full p-8 relative overflow-hidden" onclick="event.stopPropagation()">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#926a2d]/10 blur-[60px] rounded-full pointer-events-none"></div>
            <button onclick="closeModal(<?= (int)$p['id'] ?>)" class="absolute top-5 right-5 text-[#926a2d] hover:text-[#d4af37] transition-colors text-2xl z-20 focus:outline-none">&times;</button>
            
            <div class="flex items-center mb-8 border-b border-[#926a2d]/10 pb-6 relative z-10">
                <div class="p-2 bg-[#926a2d]/10 rounded-xl border border-[#926a2d]/20 mr-4">
			<img
    src="https://www.google.com/s2/favicons?sz=64&domain=<?= rawurlencode($clean_url) ?>"
    class="w-10 h-10 rounded-lg shadow-lg object-contain"
    alt="icon"
    onerror="this.style.display='none';"
>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight italic"><?= string_sanitize($domain_dec) ?></h3>
                    <p class="text-[10px] text-[#926a2d] uppercase tracking-[0.2em] font-bold">Edit Vault Item</p>
                </div>
            </div>

            <form id="edit-form-<?= $p['id'] ?>" action="actions/update_password.php" method="POST" class="space-y-5 relative z-10">
                <input type="hidden" name="edit_password" value="1">
                <input type="hidden" name="csrf_token" value="<?= csrf_token('update_password') ?>">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

                <div class="grid grid-cols-2 gap-5 text-left">
                    <div class="col-span-1">
                        <label class="text-[10px] text-[#926a2d] font-black uppercase ml-1 tracking-widest">Category</label>
                        <div class="relative">
                            <select name="category" class="w-full bg-black/50 border border-[#926a2d]/20 px-4 py-3 rounded-xl text-gray-300 outline-none focus:border-[#d4af37] focus:ring-1 focus:ring-[#d4af37]/30 transition-all cursor-pointer appearance-none">
                                <?php foreach($cats as $opt): ?>
                                    <option value="<?= string_sanitize($opt) ?>" <?= ($cat == $opt) ? 'selected' : '' ?>>
                                        <?= string_sanitize($opt) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-[#926a2d]">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1">
                        <label class="text-[10px] text-[#926a2d] font-black uppercase ml-1 tracking-widest">Domain</label>
                        <input type="text" name="domain" value="<?= string_sanitize($domain_dec) ?>" 
                               class="w-full bg-black/50 border border-[#926a2d]/20 px-4 py-3 rounded-xl text-white outline-none focus:border-[#d4af37] transition-all">
                    </div>

                    <div class="col-span-1">
                        <label class="text-[10px] text-[#926a2d] font-black uppercase ml-1 tracking-widest">Username</label>
                        <input type="text" name="username" value="<?= string_sanitize($p['username']) ?>" 
                               class="w-full bg-black/50 border border-[#926a2d]/20 px-4 py-3 rounded-xl text-white outline-none focus:border-[#d4af37] transition-all">
                    </div>

                    <div class="col-span-1">
                        <label class="text-[10px] text-[#926a2d] font-black uppercase ml-1 tracking-widest">Email</label>
                        <input type="email" name="email" value="<?= string_sanitize($email_dec) ?>" 
                               class="w-full bg-black/50 border border-[#926a2d]/20 px-4 py-3 rounded-xl text-white outline-none focus:border-[#d4af37] transition-all">
                    </div>

                    <div class="col-span-2">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-[10px] text-[#926a2d] font-black uppercase ml-1 tracking-widest">Password</label>
                            <span id="strength-text-<?= $p['id'] ?>" class="text-[9px] font-bold uppercase"></span>
                        </div>
                        <div class="flex gap-2">
                            <div class="relative flex-1 group">
                                <input type="password" id="pass-<?= $p['id'] ?>" name="password" value="<?= string_sanitize($pass_dec) ?>" 
                                    class="w-full bg-black border border-[#926a2d]/30 px-4 py-3 rounded-xl text-white outline-none focus:border-[#d4af37] shadow-inner transition-all pr-12">
                                <button type="button" onclick="generatePassword('pass-<?= $p['id'] ?>')"
                                    class="absolute right-3 top-3 text-[#926a2d] hover:text-[#d4af37] transition-all duration-700 group-hover:rotate-[360deg] focus:outline-none">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                            </div>

                            <button type="button" onclick="togglePasswordVisibility('pass-<?= $p['id'] ?>', this)" 
                                class="p-3 bg-[#926a2d]/10 border border-[#926a2d]/20 rounded-xl text-[#926a2d] hover:bg-[#926a2d]/20 hover:text-[#d4af37] transition-all active:scale-90">
                                <span id="icon-container-<?= $p['id'] ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </span>
                            </button>

                            <button type="button" onclick="copyToClipboard('pass-<?= $p['id'] ?>', this)" 
                                class="p-3 bg-[#926a2d]/10 border border-[#926a2d]/20 rounded-xl text-[#926a2d] hover:bg-[#926a2d]/20 hover:text-[#d4af37] transition-all active:scale-90">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" />
                                </svg>
                            </button>
                        </div>
                        
                        <div id="strength-bar-<?= $p['id'] ?>" class="h-1 mt-2 rounded-full bg-gray-900 overflow-hidden">
                            <div class="h-full w-0 transition-all duration-500"></div>
                        </div>
                    </div>
<div class="col-span-2">

    <div class="flex justify-between items-center mb-2">

        <label class="text-[10px] text-[#926a2d] font-black uppercase ml-1 tracking-widest">
            2FA Code
        </label>

        <span class="text-[9px] text-gray-500 font-bold uppercase">
            TOTP
        </span>

    </div>

    <?php if ($otp_code): ?>

        <div
            data-totp-id="<?= (int)$p['id'] ?>"
            class="flex items-center gap-3"
        >

            <!-- TOTP Code -->
            <div
                data-totp-code="<?= (int)$p['id'] ?>"
                class="flex-1 bg-black/50 border border-[#926a2d]/20 px-4 py-3 rounded-xl text-[#d4af37] font-mono text-xl tracking-[0.3em]"
            >
                <?= string_sanitize($otp_code) ?>
            </div>

            <!-- Timer -->
            <span
                data-totp-timer="<?= (int)$p['id'] ?>"
                class="min-w-[38px] text-center text-[11px] font-mono font-bold text-gray-500"
            >
                30s
            </span>

            <!-- Copy Button -->
            <button
                type="button"
                onclick="copyTotpCode(this)"
                data-totp-copy="<?= (int)$p['id'] ?>"
                data-code="<?= string_sanitize($otp_code) ?>"
                title="Copy 2FA Code"
                aria-label="Copy 2FA Code"
                class="p-3 bg-[#926a2d]/10 border border-[#926a2d]/20 rounded-xl text-[#926a2d] hover:bg-[#926a2d]/20 hover:text-[#d4af37] transition-all active:scale-90"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3"
                    />
                </svg>
            </button>

        </div>

    <?php else: ?>

        <div class="bg-black/50 border border-[#926a2d]/20 px-4 py-3 rounded-xl text-gray-500 font-mono text-sm">
            Not configured
        </div>

    <?php endif; ?>

</div>
                    <div class="col-span-2">

    <div class="flex justify-between items-center mb-1">

        <label class="text-[10px] text-[#926a2d] font-black uppercase ml-1 tracking-widest">
            2FA Secret Key
        </label>

        <span class="text-[9px] text-gray-500 font-bold uppercase">
            Optional
        </span>

    </div>

    <input
        type="text"
        name="otp_secret"
        value="<?= string_sanitize($otp_dec) ?>"
        autocomplete="off"
        placeholder="Leave empty to disable 2FA"
        class="w-full bg-black/50 border border-[#926a2d]/20 px-4 py-3 rounded-xl text-white outline-none focus:border-[#d4af37] transition-all"
    >

</div>
                    <div class="col-span-2">
                        <label class="text-[10px] text-[#926a2d] font-black uppercase ml-1 tracking-widest">Secure Note</label>
                        <textarea name="note" rows="2" class="w-full bg-black/50 border border-[#926a2d]/20 px-4 py-3 rounded-xl text-gray-300 outline-none focus:border-[#d4af37] transition-all resize-none italic"><?= string_sanitize($note_dec) ?></textarea>
                    </div>
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="button" onclick="confirmUpdate(<?= (int)$p['id'] ?>)" 
                        class="group flex-1 relative py-4 px-6 bg-gradient-to-r from-[#926a2d] to-[#5e441d] rounded-2xl font-black text-white shadow-xl hover:shadow-[0_0_20px_rgba(146,106,45,0.4)] transition-all duration-300 active:scale-95 overflow-hidden">
                        <div class="flex items-center justify-center gap-3 relative z-10">
                            <svg class="w-5 h-5 group-hover:rotate-180 transition-transform duration-700 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="uppercase tracking-[0.2em] text-[11px]">Update Vault</span>
                        </div>
                    </button>

                    <button type="button" onclick="confirmDelete(<?= (int)$p['id'] ?>)" 
                        class="flex items-center justify-center px-6 py-4 bg-red-950/20 border border-red-900/30 text-red-500 rounded-2xl hover:bg-red-600 hover:text-white transition-all duration-300 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
