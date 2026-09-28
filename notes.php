<?php
require_once 'includes/protect.php';
require_once 'includes/db.php';

$vault_dir = __DIR__ . '/vault';
$notes_file = $vault_dir . '/secure_notes.bin';

if (!file_exists($vault_dir)) {
    mkdir($vault_dir, 0750, true);
}

$master_key = $_SESSION['master_key'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) $_SESSION['user_id'];
    $lock = fopen($notes_file . '.lock', 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        $_SESSION['flash_error'] = 'Secure notes are temporarily unavailable.';
        header('Location: notes.php');
        exit;
    }

    try {
        if (isset($_POST['note_title']) && !isset($_POST['note_index'])) {
            $title = trim((string) $_POST['note_title']);
            $content = trim((string) ($_POST['note_content'] ?? ''));
            if ($title === '' || $content === '' || strlen($title) > 255 || strlen($content) > 12000) {
                throw new RuntimeException('Title and content are required (255 and 12000 characters maximum).');
            }
            $data = json_encode(['title' => $title, 'content' => $content, 'user_id' => $userId], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            if (file_put_contents($notes_file, encrypt_with_master($data, $master_key) . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
                throw new RuntimeException('Secure note could not be saved.');
            }
            $_SESSION['flash_success'] = 'Entry secured in vault.';
        } else {
            $isDelete = isset($_POST['delete_note_index']);
            $index = filter_var($isDelete ? $_POST['delete_note_index'] : ($_POST['note_index'] ?? null), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $lines = is_file($notes_file) ? file($notes_file, FILE_IGNORE_NEW_LINES) : [];
            if ($index === false || $index === null || $lines === false || !isset($lines[$index])) {
                throw new RuntimeException('The requested secure note was not found.');
            }
            $decrypted = decrypt_with_master($lines[$index], $master_key);
            $decoded = $decrypted === false ? null : json_decode($decrypted, true);
            if (!is_array($decoded) || (int) ($decoded['user_id'] ?? 0) !== $userId) {
                throw new RuntimeException('Security validation failed.');
            }

            if ($isDelete) {
                unset($lines[$index]);
                $lines = array_values($lines);
                $_SESSION['flash_success'] = 'Secure note deleted.';
            } else {
                $title = trim((string) ($_POST['edit_note_title'] ?? ''));
                $content = trim((string) ($_POST['edit_note_content'] ?? ''));
                if ($title === '' || $content === '' || strlen($title) > 255 || strlen($content) > 12000) {
                    throw new RuntimeException('Title and content are required (255 and 12000 characters maximum).');
                }
                $lines[$index] = encrypt_with_master(
                    json_encode(['title' => $title, 'content' => $content, 'user_id' => $userId], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    $master_key
                );
                $_SESSION['flash_success'] = 'Secure note updated.';
            }

            if (!atomic_write($notes_file, empty($lines) ? '' : implode(PHP_EOL, $lines) . PHP_EOL)) {
                throw new RuntimeException('Secure note could not be saved.');
            }
        }
    } catch (Throwable $exception) {
        error_log('Passman notes update failed: ' . $exception->getMessage());
        $_SESSION['flash_error'] = $exception instanceof RuntimeException ? $exception->getMessage() : 'Secure note could not be saved.';
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    header('Location: notes.php');
    exit;
}

$display_notes = [];
if (file_exists($notes_file)) {
    $lines = file($notes_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $idx => $line) {
        $decrypted = decrypt_with_master($line, $master_key);
        if ($decrypted) {
            $decoded = json_decode($decrypted, true);
            if ($decoded && (int)$decoded['user_id'] === (int)$_SESSION['user_id']) {
                $decoded['index'] = $idx;
                $display_notes[] = $decoded;
            }
        }
    }
}
$display_notes = array_reverse($display_notes);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes - Passman Vault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #050505;
            background-image: radial-gradient(circle at top left, #1e1b4b, transparent), 
                              radial-gradient(circle at bottom right, #2e1065, transparent);
            font-family: system-ui, -apple-system, sans-serif;
        }
        .glass-card {
            background: rgba(15, 15, 15, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(139, 92, 246, 0.1);
        }
        .glass-input {
            background: rgba(0, 0, 0, 0.4) !important;
            border: 1px solid rgba(139, 92, 246, 0.1) !important;
            color: #fff !important;
        }
        .glass-input:focus {
            border-color: rgba(139, 92, 246, 0.5) !important;
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.1);
        }
        .note-item { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="min-h-screen p-4 md:p-8 text-slate-200">

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tighter bg-gradient-to-r from-purple-400 to-fuchsia-400 bg-clip-text text-transparent uppercase">
                    VAULT.NOTES
                </h1>
                <p class="text-slate-500 text-[10px] uppercase tracking-[0.3em]">Encrypted Intelligence Database</p>
            </div>
            <a href="dashboard.php" class="px-5 py-2 rounded-xl border border-slate-800 hover:border-purple-500/50 text-xs text-slate-400 transition-all uppercase tracking-widest font-bold">
                Back to Terminal
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-4">
                <form method="POST" class="glass-card p-6 rounded-3xl sticky top-8 border-purple-500/10">
                    <h2 class="text-sm font-bold mb-6 text-white flex items-center gap-2 uppercase tracking-wider">
                        <span class="w-1.5 h-4 bg-purple-500 rounded-full"></span>
                        New Record
                    </h2>
                    <input type="hidden" name="csrf_token" value="<?= csrf_token('notes') ?>">
                    
                    <div class="space-y-4">
                        <input type="text" name="note_title" required placeholder="Entry Title" 
                               class="w-full glass-input px-4 py-3 rounded-xl outline-none text-sm transition-all font-bold">
                        
                        <textarea name="note_content" required placeholder="Secure content..." rows="5" 
                                  class="w-full glass-input px-4 py-3 rounded-xl outline-none text-sm transition-all resize-none"></textarea>
                        
                        <button type="submit" class="w-full py-3 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-purple-900/20">
                            Lock Information
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-8">
                <div class="relative mb-8 group">
                    <div class="absolute inset-y-0 right-4 flex items-center text-slate-500 group-focus-within:text-purple-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search decrypted intelligence..." 
                           class="w-full glass-input pr-12 pl-4 py-4 rounded-2xl outline-none text-sm transition-all focus:ring-1 ring-purple-500/20">
                </div>

                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div id="flash-message" class="mb-6 p-4 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-xs font-bold rounded-xl flex items-center gap-3">
                        <span class="w-2 h-2 bg-purple-400 rounded-full animate-ping"></span>
                        <?= string_sanitize($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div id="flash-message-err" class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold rounded-xl flex items-center gap-3">
                        <span class="w-2 h-2 bg-red-400 rounded-full animate-pulse"></span>
                        <?= string_sanitize($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                    </div>
                <?php endif; ?>

                <div id="notesContainer" class="grid gap-4">
                    <?php if (empty($display_notes)): ?>
                        <div id="emptyMsg" class="text-center py-20 glass-card rounded-3xl border-dashed border-slate-800">
                            <p class="text-slate-600 text-xs uppercase tracking-widest font-bold">No Records Found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($display_notes as $note): ?>
                            <div onclick="openEditModal(this)" 
                                 class="note-item glass-card p-6 rounded-2xl cursor-pointer hover:border-purple-500/30 transition-all hover:translate-x-1 group"
                                 data-id="<?= $note['index'] ?>"
                                 data-title="<?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?>"
                                 data-content="<?= htmlspecialchars($note['content'], ENT_QUOTES, 'UTF-8') ?>"
                                 data-search-title="<?= htmlspecialchars(strtolower($note['title'])) ?>"
                                 data-search-content="<?= htmlspecialchars(strtolower($note['content'])) ?>">
                                <div class="flex justify-between items-start">
                                    <h3 class="text-white font-bold group-hover:text-purple-400 transition-colors"><?= htmlspecialchars($note['title']) ?></h3>
                                    <span class="text-[9px] font-mono text-slate-600">OFFSET: 0x<?= dechex($note['index']) ?></span>
                                </div>
                                <p class="text-slate-500 text-xs mt-2 line-clamp-1 italic"><?= htmlspecialchars($note['content']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-all duration-300 backdrop-blur-md px-4">
        <div class="absolute inset-0 bg-black/60" onclick="closeModal()"></div>
        <div class="glass-card w-full max-w-2xl p-8 rounded-[2.5rem] relative z-10 shadow-2xl scale-95 transition-all" id="modalContainer">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-purple-500 to-transparent opacity-50"></div>
            
            <form action="notes.php" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= csrf_token('notes') ?>">
                <input type="hidden" name="note_index" id="modalIndex">
                
                <div>
                    <label class="text-[10px] text-purple-400 font-bold uppercase tracking-widest mb-2 block">Subject Header</label>
                    <input type="text" name="edit_note_title" id="modalTitle" required 
                           class="w-full glass-input px-0 py-2 text-2xl font-black outline-none border-0 border-b border-slate-800 focus:border-purple-500 transition-all bg-transparent !border-t-0 !border-x-0">
                </div>

                <div>
                    <label class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-2 block">Payload Content</label>
                    <textarea name="edit_note_content" id="modalContent" required rows="8" 
                              class="w-full glass-input px-4 py-4 rounded-2xl text-slate-300 text-sm outline-none resize-none"></textarea>
                </div>

                <div class="flex flex-col md:flex-row gap-3">
                    <button type="submit" class="flex-1 py-4 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold uppercase tracking-widest transition-all active:scale-95">
                        Commit Changes
                    </button>
            </form>
                
                <form action="notes.php" method="POST" onsubmit="return confirm('Wipe this intelligence permanently?');" class="md:w-1/4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token('notes') ?>">
                    <input type="hidden" name="delete_note_index" id="modalDeleteIndex">
                    <button type="submit" class="w-full py-4 bg-red-500/10 border border-red-500/20 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all text-xs font-bold uppercase tracking-widest">
                        Wipe
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Timeout Warning Modal -->
    <div id="timeout-modal" class="hidden fixed inset-0 flex items-center justify-center z-[9999] backdrop-blur-md px-4" dir="ltr">
        <div class="absolute inset-0 bg-black/70"></div>
        <div class="glass-card w-full max-w-md p-8 rounded-[2rem] relative z-10 shadow-2xl text-center border-purple-500/20 bg-[#0f0f0f]/90">
            <h3 class="text-white font-bold text-lg mb-2 uppercase tracking-wider">Security Timeout Warning</h3>
            <p class="text-slate-400 text-sm mb-6">
                You have been inactive. For your protection, your vault will lock automatically in 
                <span id="timer-seconds" class="text-purple-400 font-mono font-bold text-base">30</span> seconds.
            </p>
            <button onclick="resetTimers()" class="w-full py-3.5 bg-purple-600 hover:bg-purple-500 text-white rounded-xl font-bold text-xs uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-purple-900/30">
                Stay Logged In
            </button>
        </div>
    </div>

    <form id="logout-form" action="logout.php" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?= csrf_token('logout') ?>">
    </form>
    <script src="assets/js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const noteItems = document.querySelectorAll('.note-item');
            const notesContainer = document.getElementById('notesContainer');
            const flash = document.getElementById('flash-message');
            const flashErr = document.getElementById('flash-message-err');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    let hasResults = false;

                    noteItems.forEach(card => {
                        const title = card.getAttribute('data-search-title');
                        const content = card.getAttribute('data-search-content');

                        if (title.includes(query) || content.includes(query)) {
                            card.style.display = 'block';
                            hasResults = true;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    let noResultsMsg = document.getElementById('dynamicNoResults');
                    if (!hasResults) {
                        if (!noResultsMsg && notesContainer) {
                            noResultsMsg = document.createElement('div');
                            noResultsMsg.id = 'dynamicNoResults';
                            noResultsMsg.className = 'text-center py-20 glass-card rounded-3xl border-dashed border-slate-800';
                            noResultsMsg.innerHTML = '<p class="text-slate-600 text-xs uppercase tracking-widest font-bold">No Intel Matches Your Query</p>';
                            notesContainer.appendChild(noResultsMsg);
                        }
                    } else if (noResultsMsg) {
                        noResultsMsg.remove();
                    }
                });
                setTimeout(() => searchInput.focus(), 200);
            }

            const clearMsg = (el) => {
                if(el) {
                    setTimeout(() => {
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(-10px)';
                        el.style.transition = 'all 0.6s ease';
                        setTimeout(() => el.remove(), 600);
                    }, 6000);
                }
            };
            clearMsg(flash);
            clearMsg(flashErr);
        });

        function openEditModal(element) {
            document.getElementById('modalIndex').value = element.getAttribute('data-id');
            document.getElementById('modalDeleteIndex').value = element.getAttribute('data-id');
            document.getElementById('modalTitle').value = element.getAttribute('data-title');
            document.getElementById('modalContent').value = element.getAttribute('data-content');
            
            const modal = document.getElementById('editModal');
            const container = document.getElementById('modalContainer');
            if (modal && container) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                container.classList.remove('scale-95');
            }
        }

        function closeModal() {
            const modal = document.getElementById('editModal');
            const container = document.getElementById('modalContainer');
            if (modal && container) {
                modal.classList.add('opacity-0', 'pointer-events-none');
                container.classList.add('scale-95');
            }
        }
    </script>
</body>
</html>
