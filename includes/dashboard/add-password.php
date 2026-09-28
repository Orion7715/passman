<form method="POST" action="actions/add_password.php" class="max-w-6xl mx-auto relative bg-[#0a0a0a]/60 backdrop-blur-2xl border border-[#926a2d]/30 p-6 rounded-2xl mb-12 shadow-2xl overflow-hidden">
    <div class="absolute -top-10 -left-10 w-40 h-40 bg-[#d4af37]/5 blur-[60px] rounded-full pointer-events-none"></div>

    <h2 class="text-xl font-black mb-6 text-[#d4af37] flex items-center gap-2 uppercase tracking-tight">
        <svg class="w-5 h-5 shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        Add New Entry
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
        <div class="group">
            <label class="text-xs font-black text-[#d4af37]/80 uppercase tracking-[0.1em] block ml-1 mb-2">Classification</label>
            <div class="relative">
                <select name="category" class="w-full bg-white/5 border border-white/10 group-hover:border-[#926a2d]/50 px-4 py-3 rounded-xl focus:bg-white/10 focus:border-[#d4af37] outline-none text-white font-bold text-sm transition-all appearance-none cursor-pointer">
                    <option value="General" class="bg-[#1a1a1a]">General</option>
                    <option value="Work" class="bg-[#1a1a1a]">Work</option>
                    <option value="Education" class="bg-[#1a1a1a]">Education</option>
                    <option value="Social" class="bg-[#1a1a1a]">Social Media</option>
                    <option value="Finance" class="bg-[#1a1a1a]">Finance</option>
                    <option value="Shopping" class="bg-[#1a1a1a]">Shopping</option>
                    <option value="Personal" class="bg-[#1a1a1a]">Personal</option>
                </select>
                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-[#926a2d]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>
        </div>

        <div class="group">
            <label class="text-xs font-black text-[#d4af37]/80 uppercase tracking-[0.1em] block ml-1 mb-2">Domain</label>
            <input type="text" name="domain" required placeholder="example.com" 
                class="w-full bg-white/5 border border-white/10 group-hover:border-[#926a2d]/50 px-4 py-3 rounded-xl focus:bg-white/10 focus:border-[#d4af37] outline-none text-white font-bold text-sm transition-all placeholder:text-gray-600 shadow-inner">
        </div>

        <div class="group">
            <label class="text-xs font-black text-[#d4af37]/80 uppercase tracking-[0.1em] block ml-1 mb-2">Username</label>
            <input type="text" name="username" placeholder="johndoe" 
                class="w-full bg-white/5 border border-white/10 group-hover:border-[#926a2d]/50 px-4 py-3 rounded-xl focus:bg-white/10 focus:border-[#d4af37] outline-none text-white font-bold text-sm transition-all placeholder:text-gray-600 shadow-inner">
        </div>

        <div class="group">
            <div class="flex justify-between items-center mb-2">
                <label class="text-xs font-black text-[#d4af37]/80 uppercase tracking-[0.1em] ml-1">Password</label>
                <span id="strength-text-add" class="text-[9px] font-black uppercase text-[#926a2d]"></span>
            </div>
            <div class="relative">
                <input type="text" name="password" id="password-input" required placeholder="••••••••" 
                    class="w-full bg-white/5 border border-white/10 group-hover:border-[#926a2d]/50 px-4 py-3 rounded-xl focus:bg-white/10 focus:border-[#d4af37] outline-none text-white font-bold text-sm transition-all pr-12 shadow-inner">
                
                <input type="hidden" name="csrf_token" value="<?= csrf_token('add_password') ?>">
                <button type="button" onclick="generatePassword('password-input')"  class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-[#926a2d] hover:text-[#d4af37] transition-colors focus:outline-none">
                    <svg class="h-5 w-5 transform transition-all duration-700 hover:rotate-[180deg]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
            <div id="strength-bar-add" class="mt-2 h-1 rounded-full bg-white/5 overflow-hidden border border-white/5">
                <div class="h-full transition-all duration-500 bg-[#926a2d]" style="width: 0%"></div>
            </div>
        </div>

        <div class="group">
    <div class="flex justify-between items-center mb-2">
        <label class="text-xs font-black text-[#d4af37]/80 uppercase tracking-[0.1em] ml-1">
            2FA Secret Key
        </label>

        <span class="text-[9px] font-black uppercase text-gray-500">
            Optional
        </span>
    </div>

    <input
        type="text"
        name="otp_secret"
        placeholder="Enter 2FA secret key"
        autocomplete="off"
        class="w-full bg-white/5 border border-white/10 group-hover:border-[#926a2d]/50 px-4 py-3 rounded-xl focus:bg-white/10 focus:border-[#d4af37] outline-none text-white font-bold text-sm transition-all placeholder:text-gray-600 shadow-inner"
    >
</div>

        <div class="group">
            <label class="text-xs font-black text-[#d4af37]/80 uppercase tracking-[0.1em] block ml-1 mb-2">Email</label>
            <input type="email" name="email" required placeholder="mail@example.com" 
                class="w-full bg-white/5 border border-white/10 group-hover:border-[#926a2d]/50 px-4 py-3 rounded-xl focus:bg-white/10 focus:border-[#d4af37] outline-none text-white font-bold text-sm transition-all placeholder:text-gray-600 shadow-inner">
        </div>

        <div class="lg:col-span-3 group">
            <!-- تم إصلاح إغلاق وسم الـ label هنا -->
            <label class="text-xs font-black text-[#d4af37]/80 uppercase tracking-[0.1em] block ml-1 mb-2">Additional (Note)</label>
            <textarea name="note" rows="3" placeholder="Press Enter for new line..." 
                class="w-full bg-white/5 border border-white/10 group-hover:border-[#926a2d]/50 px-4 py-3 rounded-xl focus:bg-white/10 focus:outline-none text-white transition-all duration-300 resize-none"></textarea>
        </div>
    </div>

    <button type="submit" name="add_password" 
        class="relative group mt-8 py-4 px-12 bg-transparent border-2 border-[#926a2d]/40 text-[#d4af37] rounded-xl font-black shadow-lg hover:shadow-[#926a2d]/20 transition-all duration-500 uppercase text-xs tracking-widest overflow-hidden active:scale-95">
        <span class="absolute inset-0 bg-gradient-to-r from-[#926a2d] to-[#d4af37] translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out"></span>
        <div class="relative z-10 flex items-center justify-center gap-3 group-hover:text-black transition-colors duration-500">
            <svg class="w-5 h-5 group-hover:-translate-y-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
            <span>Deploy to Vault</span>
        </div>
    </button>
</form>
