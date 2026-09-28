
<div id="timeout-modal" class="fixed bottom-10 right-10 bg-red-900/90 border border-red-500 p-6 rounded-2xl shadow-2xl z-[200] hidden max-w-xs backdrop-blur-md"> 
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-red-600 flex items-center justify-center animate-pulse text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm font-bold text-white">Inactivity Timeout!</p>
            <p class="text-xs text-red-200">Logging out in <span id="timer-seconds" class="font-mono font-bold text-lg">30</span>s</p>
        </div>
    </div>
    <button onclick="resetTimers()" class="w-full mt-4 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-lg transition-all uppercase tracking-widest">Keep me logged in</button>
</div>