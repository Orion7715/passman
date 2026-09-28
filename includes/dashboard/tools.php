<div class="mt-12 w-full max-w-[1400px] mx-auto px-4 space-y-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-[#0a0a0a]/60 backdrop-blur-2xl border border-[#926a2d]/20 rounded-3xl shadow-2xl">
        <a href="notes.php" class="group flex flex-col items-center justify-center gap-3 px-4 py-8 rounded-2xl border border-emerald-500/10 bg-emerald-500/5 transition-all duration-500 hover:border-emerald-500/40 hover:bg-emerald-500/10 shadow-lg">
            <svg class="w-7 h-7 text-emerald-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-xs font-black uppercase tracking-[0.2em] text-emerald-500">Notes</span>
        </a>

        <a href="files.php" class="group flex flex-col items-center justify-center gap-3 px-4 py-8 rounded-2xl border border-sky-500/10 bg-sky-500/5 transition-all duration-500 hover:border-sky-500/40 hover:bg-sky-500/10 shadow-lg">
            <svg class="w-7 h-7 text-sky-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span class="text-xs font-black uppercase tracking-[0.2em] text-sky-500">Files</span>
        </a>

        <button type="button" onclick="toggleExportModal()" class="group w-full flex flex-col items-center justify-center gap-3 px-4 py-8 rounded-2xl border border-amber-500/10 bg-amber-500/5 transition-all duration-500 hover:border-amber-500/40 hover:bg-amber-500/10 shadow-lg">
            <svg class="w-7 h-7 text-amber-500 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span class="text-xs font-black uppercase tracking-[0.2em] text-amber-500">Export Data</span>
        </button>

        <div id="exportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
            <div class="bg-[#1a1a1a] border border-amber-500/20 w-full max-w-md rounded-3xl p-8 shadow-2xl transform transition-all">
                <div class="text-center mb-8">
                    <h3 class="text-xl font-bold text-amber-500 mb-2">Export As</h3>
                    <p class="text-gray-400 text-sm">Please select your preferred export file type</p>
                </div>
                <form method="POST" action="actions/export_passwords.php" class="grid grid-cols-1 gap-4 text-left">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token('export_passwords') ?>">
                    <button type="submit" name="export_type" value="encrypted" class="flex items-center justify-between p-4 rounded-xl border border-amber-500/10 bg-amber-500/5 hover:bg-amber-500/10 hover:border-amber-500/40 transition-all group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="text-white font-medium">Export Encrypted</span>
                        </div>
                        <span class="text-[10px] text-amber-500/50 uppercase font-bold">Safe</span>
                    </button>
                    <button type="submit" name="export_type" value="plain" class="flex items-center justify-between p-4 rounded-xl border border-white/5 bg-white/5 hover:bg-red-500/5 hover:border-red-500/30 transition-all group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-white font-medium group-hover:text-red-500">Export Plain Text</span>
                        </div>
                        <span class="text-[10px] text-red-500/50 uppercase font-bold">Unsafe</span>
                    </button>
                    <button type="button" onclick="toggleExportModal()" class="mt-4 text-gray-500 hover:text-white text-sm font-medium transition-colors">
                        Cancel Process
                    </button>
                </form>
            </div>
        </div>

        <div class="w-full">
            <button type="button" onclick="toggleImportModal()" class="group w-full flex flex-col items-center justify-center gap-3 px-4 py-8 rounded-2xl border border-indigo-500/10 bg-indigo-500/5 transition-all duration-500 hover:border-indigo-500/40 hover:bg-indigo-500/10 shadow-lg">
                <svg class="w-7 h-7 text-indigo-500 group-hover:translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="text-xs font-black uppercase tracking-[0.2em] text-indigo-500">Import</span>
            </button>
        </div>

        <div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
            <div class="bg-[#1a1a1a] border border-indigo-500/20 w-full max-w-md rounded-3xl p-8 shadow-2xl">
                <div class="text-center mb-8">
                    <h3 class="text-xl font-bold text-indigo-500 mb-2">Import Options</h3>
                    <p class="text-gray-400 text-sm">How should we process your CSV file?</p>
                </div>
                <form id="importForm" method="POST" action="actions/import_passwords.php" enctype="multipart/form-data" class="grid grid-cols-1 gap-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token('import_passwords') ?>">
                    <input type="file" id="csvFileInput" name="csv_file" class="hidden" accept=".csv" onchange="submitImport()">
                    <input type="hidden" id="importType" name="import_type" value="">

                    <button type="button" onclick="triggerFileSelect('encrypted')" class="flex items-center justify-between p-4 rounded-xl border border-indigo-500/10 bg-indigo-500/5 hover:bg-indigo-500/10 hover:border-indigo-500/40 transition-all group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="text-white font-medium text-left">
                                Encrypted CSV
                                <span class="block text-[10px] text-gray-500 font-normal italic">File was previously exported as encrypted</span>
                            </span>
                        </div>
                    </button>

                    <button type="button" onclick="triggerFileSelect('plain')" class="flex items-center justify-between p-4 rounded-xl border border-white/5 bg-white/5 hover:bg-indigo-500/10 hover:border-indigo-500/40 transition-all group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-white font-medium text-left">
                                Plain Text CSV
                                <span class="block text-[10px] text-gray-500 font-normal italic">Standard readable CSV (will be encrypted on upload)</span>
                            </span>
                        </div>
                    </button>
                    <button type="button" onclick="toggleImportModal()" class="mt-4 text-gray-500 hover:text-white text-sm font-medium transition-colors">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>