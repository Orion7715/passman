<nav class="fixed top-0 left-0 w-full z-50 py-3 px-6 transition-all duration-500 bg-[#12100b]/70 backdrop-blur-xl border-b border-[#926a2d]/30 shadow-[0_4px_30px_rgba(146,106,45,0.1)]">
    
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[50%] left-1/2 -translate-x-1/2 w-[60%] h-[100%] bg-[#926a2d]/10 blur-[80px] rounded-full"></div>
    </div>

    <div class="relative w-full flex justify-between items-center">
        
        <div class="flex items-center gap-3 group cursor-pointer">
            <div class="p-2 bg-gradient-to-br from-[#926a2d] to-[#5e441d] rounded-xl shadow-[0_0_15px_rgba(146,106,45,0.4)] group-hover:shadow-[0_0_25px_rgba(212,175,55,0.6)] transition-all duration-500">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h1 class="text-2xl font-black tracking-tighter italic bg-gradient-to-b from-[#d4af37] via-[#926a2d] to-[#5e441d] bg-clip-text text-transparent drop-shadow-sm">
                Passman
            </h1>
        </div>

        <div class="flex-1 flex justify-center px-8">
            <div class="group relative flex items-center">
                <div class="absolute left-4 z-10 text-[#926a2d] group-focus-within:text-[#d4af37] transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="search-input" placeholder="Search Vault..." 
                    class="w-14 group-hover:w-72 focus:w-96 h-11 pl-12 pr-4 bg-black/40 border border-[#926a2d]/20 group-hover:border-[#926a2d]/50 focus:border-[#d4af37] rounded-2xl text-sm text-[#d4af37] placeholder-transparent group-hover:placeholder-[#926a2d]/60 transition-all duration-700 ease-in-out outline-none shadow-[inset_0_2px_10px_rgba(0,0,0,0.5)]">
            </div>
        </div>

        <div class="flex items-center gap-5">
            <div class="hidden md:flex items-center gap-3 px-4 py-2 bg-[#926a2d]/5 border border-[#926a2d]/20 rounded-xl">
                <div class="w-2 h-2 rounded-full bg-[#d4af37] shadow-[0_0_8px_#d4af37]"></div>
                <span class="text-xs font-bold font-mono text-[#d4af37]/80 uppercase tracking-widest">
                    <?= string_sanitize($_SESSION["username"]) ?>
                </span>
            </div>

            <form id="logout-form" action="logout.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token('logout') ?>">
                <button type="submit"
               class="group flex items-center gap-3 py-2 px-5 bg-gradient-to-r from-red-950/40 to-black/40 border border-red-900/30 rounded-xl transition-all duration-300 hover:from-red-600 hover:to-red-700 hover:border-red-500 hover:shadow-[0_0_20px_rgba(220,38,38,0.4)]">
                <span class="text-[11px] font-black uppercase tracking-tighter text-red-500 group-hover:text-white transition-colors">
                    Logout
                </span>
                <svg class="w-4 h-4 text-red-500 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                </button>
            </form>

            <!-- Insert this right before your Logout button link inside dashboard.php navigation bar -->


            <button onclick="openChangeMasterModal()" 
   class="group flex items-center gap-3 py-2 px-5 bg-gradient-to-r from-amber-950/40 to-black/40 border border-amber-900/30 rounded-xl transition-all duration-300 hover:from-[#926a2d] hover:to-[#5e441d] hover:border-[#d4af37] hover:shadow-[0_0_20px_rgba(212,175,55,0.4)]">
    <span class="text-[11px] font-black uppercase tracking-tighter text-[#d4af37] group-hover:text-black transition-colors">
        Master Key
    </span>
    <svg class="w-4 h-4 text-[#d4af37] group-hover:text-black transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
    </svg>
</button>
        </div>
    </div>
</nav>
