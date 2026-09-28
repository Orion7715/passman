
<div id="delete-modal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-[100] p-4 backdrop-blur-md">
    <div class="bg-[#1a1a1a] border border-gray-800 rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
        <div class="w-16 h-16 bg-red-900/20 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-800/30">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Are you sure?</h3>
        <p class="text-gray-400 text-sm mb-8">This action cannot be undone.</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 py-3 bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 transition font-bold text-xs uppercase tracking-widest">Cancel</button>
            <form method="POST" action="actions/delete_password.php" class="flex-1">
                <input type="hidden" name="delete_id" id="delete-id-input">
                <input type="hidden" name="csrf_token" value="<?= csrf_token('delete_password') ?>">
                <button type="submit" name="confirm_delete" class="w-full py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-bold text-xs uppercase tracking-widest">Delete</button>
            </form>
        </div>
    </div>
</div>

<div id="update-confirm-modal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-[110] p-4 backdrop-blur-md">
    <div class="bg-[#1a1a1a] border border-gray-800 rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl">
        <div class="w-16 h-16 bg-blue-900/20 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-800/30">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Confirm Update?</h3>
        <p class="text-gray-400 text-sm mb-8">Are you sure you want to save these changes to your credential?</p>
        <div class="flex gap-3">
            <button onclick="closeUpdateModal()" class="flex-1 py-3 bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 transition font-bold text-xs uppercase tracking-widest">Cancel</button>
            <button id="final-update-btn" class="flex-1 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-bold text-xs uppercase tracking-widest">Yes, Update</button>
        </div>
    </div>
</div>
