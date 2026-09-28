<div id="change-master-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="bg-[#121212] border border-[#926a2d]/40 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-[#926a2d]/20 flex justify-between items-center">
            <h3 class="text-xl font-bold text-[#d4af37]">Change Master Password</h3>
            <button onclick="closeChangeMasterModal()" class="text-[#926a2d] hover:text-[#d4af37] transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="masterKeyForm" method="POST" action="actions/change_master.php" class="p-6 space-y-4 text-left">
            <input type="hidden" name="csrf_token" value="<?=  csrf_token('change_master') ?>">
            <div>
                <label class="block text-[#926a2d] text-sm mb-2 ml-1">Old Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-[#926a2d]/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <input type="password" id="oldPass" name="old_master" required
                        class="form-input w-full pl-10 py-3 bg-black/40 border border-[#926a2d]/30 rounded-xl text-white focus:outline-none focus:border-[#d4af37] transition-all">
                </div>
            </div>
            <div>
                <label class="block text-[#926a2d] text-sm mb-2 ml-1">New Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-[#926a2d]/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </span>
                    <input type="password" id="newPass" name="new_master" required
                        class="form-input w-full pl-10 py-3 bg-black/40 border border-[#926a2d]/30 rounded-xl text-white focus:outline-none focus:border-[#d4af37] transition-all">
                </div>
            </div>
            <div>
                <label class="block text-[#926a2d] text-sm mb-2 ml-1">Confirm New Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-[#926a2d]/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <input type="password" name="confirm_master" id="confirmPass" required
                        class="form-input w-full pl-10 py-3 bg-black/40 border border-[#926a2d]/30 rounded-xl text-white focus:outline-none focus:border-[#d4af37] transition-all">
                </div>
                <p id="matchMessage" class="text-xs mt-2 hidden font-medium"></p>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" id="submitBtn" name="update_master_btn" disabled
                    class="flex-1 py-3 bg-[#d4af37] text-black font-bold rounded-xl opacity-50 cursor-not-allowed hover:shadow-[0_0_15px_rgba(212,175,55,0.3)] transition-all">
                    Update Password
                </button>
                <button type="button" onclick="closeChangeMasterModal()"
                    class="flex-1 py-3 bg-transparent border border-[#926a2d]/50 text-[#926a2d] rounded-xl hover:bg-[#926a2d]/10 transition-all">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>



<script>
    window.currentUsername = <?= json_encode($_SESSION['username']) ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('change-master-modal');
        const oldPass = document.getElementById('oldPass');
        const newPass = document.getElementById('newPass');
        const confirmPass = document.getElementById('confirmPass');
        const matchMessage = document.getElementById('matchMessage');
        const submitBtn = document.getElementById('submitBtn');
        const formInputs = document.querySelectorAll('.form-input');
        const masterForm = document.getElementById('masterKeyForm');

        window.openChangeMasterModal = function() {
            modal.classList.replace('hidden', 'flex');
            setTimeout(() => { oldPass.focus(); }, 200);
        };

        window.closeChangeMasterModal = function() {
            modal.classList.replace('flex', 'hidden');
            masterForm.reset();
            matchMessage.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        };

        // This Function To Vaildate Password when Chnage the Master Key 
        // It's Compare New Key + Convfirm Key before send request

        function validatePasswords() {
            const p1 = newPass.value;
            const p2 = confirmPass.value;

            if (p2.length === 0) {
                matchMessage.classList.add('hidden');
                return;
            }

            matchMessage.classList.remove('hidden');

            if (p1 === p2 && p1 !== "") {
                matchMessage.textContent = '✓ Passwords match';
                matchMessage.className = 'text-xs mt-2 text-green-500';
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                matchMessage.textContent = '× Passwords do not match';
                matchMessage.className = 'text-xs mt-2 text-red-500';
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        formInputs.forEach((input, index) => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault(); 
                    const nextInput = formInputs[index + 1];
                    if (nextInput) {
                        nextInput.focus();
                    } else if (!submitBtn.disabled) {
                        masterForm.dispatchEvent(new Event('submit'));
                    }
                }
            });
        });

        newPass.addEventListener('input', validatePasswords);
        confirmPass.addEventListener('input', validatePasswords);
    });
</script>