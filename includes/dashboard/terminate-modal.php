<div class="w-full max-w-[1400px] mx-auto px-4 mt-6">
    <button type="button" onclick="openTerminateModal()"
        class="group w-full py-5 bg-black/40 border border-purple-900/30 text-purple-600 rounded-xl transition-all flex items-center justify-center gap-2">
        <svg class="w-5 h-5 text-purple-600 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        <span class="font-bold">Terminate Account & Vault</span>
    </button>
</div>

<div id="terminate-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4 text-left">
    <div id="terminate-content" class="bg-[#0f0f0f] border border-purple-900/40 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl scale-95 opacity-0 transition-all duration-300">
        <div class="p-6 border-b border-purple-900/20 bg-purple-950/10">
            <h3 class="text-xl font-bold text-purple-500 uppercase tracking-tighter">Extreme Danger Zone</h3>
            <p class="text-purple-400/60 text-sm mt-1">This action is permanent and cannot be undone.</p>
        </div>
        <form action="actions/delete_account.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token('delete_account') ?>">
            <p class="text-gray-400 text-sm">
                To confirm deletion, please type your username <span class="text-purple-400 font-bold"><?= string_sanitize($_SESSION['username']) ?></span> below:
            </p>
            <input type="text" id="username-confirm-input" name="username_confirm" autocomplete="off" placeholder="Type username here..."
                class="w-full px-4 py-3 bg-black/60 border border-purple-900/30 rounded-xl text-white focus:outline-none focus:border-purple-600 transition-all">
            <div class="flex gap-3 pt-2">
                <button type="submit" id="final-terminate-btn" disabled
                    class="flex-1 py-3 bg-purple-600/10 text-purple-500/30 rounded-xl font-bold uppercase text-[10px] tracking-widest cursor-not-allowed transition-all">
                    TERMINATE EVERYTHING
                </button>
                <button type="button" onclick="closeTerminateModal()"
                    class="flex-1 py-3 bg-transparent border border-gray-800 text-gray-500 rounded-xl font-bold uppercase text-[10px] tracking-widest hover:bg-gray-800 transition-all">
                    CANCEL
                </button>
            </div>
        </form>
    </div>
</div>
