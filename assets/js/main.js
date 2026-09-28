// ===============================
// main.js – Password manager JS
// ===============================

const mainSearch = document.getElementById('search-input');
if (mainSearch) mainSearch.focus();

const formFields = [
    document.querySelector('select[name="category"]'),
    document.querySelector('input[name="domain"]'),
    document.querySelector('input[name="username"]'),
    document.querySelector('#password-input'),
    document.querySelector('input[name="email"]'),
    document.querySelector('textarea[name="note"]'),
    document.querySelector('button[name="add_password"]')
];

formFields.forEach((field, i) => {
    if (!field) return;
    field.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const next = formFields[i + 1];
            if (next) next.focus();
            else field.click(); 
        }
    });
});


// ⏱ Idle timers
let idleTimer;
let countdownTimer;
let secondsLeft = 30;
const IDLE_TIME = 4.5 * 60 * 1000; // 4 minutes 30 seconds

// Start the idle timer
function startTimers() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(showTimeoutWarning, IDLE_TIME);
}

// Show logout warning modal
function showTimeoutWarning() {
    const modal = document.getElementById('timeout-modal');
    if (!modal) return;

    modal.classList.replace('hidden', 'block');
    secondsLeft = 30;
    
    const timerDisplay = document.getElementById('timer-seconds');
    if (timerDisplay) timerDisplay.innerText = secondsLeft;

    countdownTimer = setInterval(() => {
        secondsLeft--;
        if (timerDisplay) timerDisplay.innerText = secondsLeft;
        if (secondsLeft <= 0) {
            clearInterval(countdownTimer);
            logout();
        }
    }, 1000);
}

// Reset timers on user activity
['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(evt => {
    document.addEventListener(evt, () => {
        const modal = document.getElementById('timeout-modal');
        if (modal && modal.classList.contains('hidden')) {
            clearTimeout(idleTimer);
            startTimers();
        }
    });
});

// Reset modal and timers manually
function resetTimers() {
    const modal = document.getElementById('timeout-modal');
    if (modal) modal.classList.replace('block', 'hidden');

    clearTimeout(idleTimer);
    clearInterval(countdownTimer);
    startTimers();
}

// ===============================
// Password strength checker
// ===============================
function checkStrength(password, barId, textId) {
    const bar = document.getElementById(barId);
    const text = document.getElementById(textId);

    if (!bar) return;

    let strength = 0;

    // Empty password
    if (!password) {
        bar.style.width = '0%';
        bar.style.boxShadow = 'none';

        bar.classList.remove(
            'bg-red-500',
            'bg-orange-500',
            'bg-blue-500',
            'bg-emerald-500'
        );

        if (text) {
            text.innerText = '';
        }

        return;
    }

    // Password requirements
    if (password.length >= 8) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 25;
    if (password.match(/[^A-Za-z0-9]/)) strength += 25;

    // Update width
    bar.style.width = strength + '%';

    // Remove previous colors
    bar.classList.remove(
        'bg-red-500',
        'bg-orange-500',
        'bg-blue-500',
        'bg-emerald-500'
    );

    // Set color and label
    if (strength <= 25) {

        bar.classList.add('bg-red-500');
        bar.style.boxShadow = '0 0 10px rgba(239,68,68,0.5)';

        if (text) {
            text.innerText = 'Weak';
            text.className = 'text-[9px] text-red-500 uppercase font-bold';
        }

    } else if (strength <= 50) {

        bar.classList.add('bg-orange-500');
        bar.style.boxShadow = '0 0 10px rgba(249,115,22,0.5)';

        if (text) {
            text.innerText = 'Fair';
            text.className = 'text-[9px] text-orange-500 uppercase font-bold';
        }

    } else if (strength <= 75) {

        bar.classList.add('bg-blue-500');
        bar.style.boxShadow = '0 0 10px rgba(59,130,246,0.5)';

        if (text) {
            text.innerText = 'Good';
            text.className = 'text-[9px] text-blue-500 uppercase font-bold';
        }

    } else {

        bar.classList.add('bg-emerald-500');
        bar.style.boxShadow = '0 0 15px rgba(16,185,129,0.7)';

        if (text) {
            text.innerText = 'Strong';
            text.className = 'text-[9px] text-emerald-500 uppercase font-bold';
        }
    }
}

// ===============================
// Search filter
// ===============================
const searchInput = document.getElementById('search-input');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('.password-card').forEach(c => {
            c.style.display = c.dataset.domain.includes(q) ? 'block' : 'none';
        });
    });
}

// ===============================
// Update Password Strength Bar
// ===============================

function updatePasswordStrength(input) {
    const inputId = input.id;

    if (inputId === 'password-input') {
        checkStrength(
            input.value,
            'strength-bar-add',
            'strength-text-add'
        );
    } else if (inputId.startsWith('pass-')) {
        const idNum = inputId.split('-')[1];

        checkStrength(
            input.value,
            'strength-bar-' + idNum,
            'strength-text-' + idNum
        );
    }
}

// ===============================
// Password generation
// ===============================
function generatePassword(inputId) {
    const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    const values = new Uint32Array(16);
    crypto.getRandomValues(values);
    const pass = Array.from(values, value => chars[value % chars.length]).join('');

    const input = document.getElementById(inputId);

    if (input) {
        input.value = pass;
        input.type = 'text';

        updatePasswordStrength(input);
    }
}

document.addEventListener('input', function (e) {
    if (e.target.matches('#password-input, [id^="pass-"]')) {
        updatePasswordStrength(e.target);
    }
});

function logout() {
    const form = document.getElementById('logout-form');
    if (form) form.submit();
}

// ===============================
// Password visibility toggle
// ===============================
function togglePasswordVisibility(inputId, buttonElement) {
    const passwordInput = document.getElementById(inputId);
    if (!passwordInput) return;
    
    const eyeOpen = buttonElement.querySelector(`#eye-open-${inputId.replace('pass-', '')}`);
    const eyeClosed = buttonElement.querySelector(`#eye-closed-${inputId.replace('pass-', '')}`);

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        if (eyeOpen) eyeOpen.classList.add('hidden');
        if (eyeClosed) eyeClosed.classList.remove('hidden');
    } else {
        passwordInput.type = "password";
        if (eyeOpen) eyeOpen.classList.remove('hidden');
        if (eyeClosed) eyeClosed.classList.add('hidden');
    }
}

// ===============================
// Copy password to clipboard
// ===============================
function copyToClipboard(id, btn) {
    const input = document.getElementById(id);
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '<svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        setTimeout(() => { btn.innerHTML = original; }, 1500);
    });
}

// ===============================
// Modal management
// ===============================
function openModal(id) {
    const modal = document.getElementById('modal-' + id);
    if (!modal) return;
    modal.classList.replace('hidden', 'flex');
    
    const passInput = document.getElementById('pass-' + id);
    if (passInput) {
        let pass = passInput.value;
        checkStrength(pass, 'strength-bar-' + id, 'strength-text-' + id);
    }
    syncPasswordIcon('pass-' + id);
}

function closeModal(id) {
    const modal = document.getElementById('modal-' + id);
    if (!modal) return;
    modal.classList.replace('flex', 'hidden');

    const form = document.getElementById('edit-form-' + id);
    if (form) form.reset();

    const passInput = document.getElementById('pass-' + id);
    if (passInput) {
        passInput.type = 'password';
        checkStrength(passInput.value, 'strength-bar-' + id, 'strength-text-' + id);
    }
}

// ===============================
// Delete confirmation modal
// ===============================
function confirmDelete(id) {
    const input = document.getElementById('delete-id-input');
    const modal = document.getElementById('delete-modal');
    if (input) input.value = id;
    if (modal) modal.classList.replace('hidden', 'flex');
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    if (modal) modal.classList.replace('flex', 'hidden');
}

// ===============================
// CSV import/export
// ===============================
function validateAndUploadCSV(input) {
    const importForm = document.getElementById('importForm');
    if (input.files[0] && importForm) importForm.submit();
}

// ===============================
// Category filter
// ===============================
function filterCategory(cat, btn) {
    document.querySelectorAll('.category-pill').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    document.querySelectorAll('.password-card').forEach(card => {
        card.style.display = (cat === 'all' || card.dataset.category === cat) ? 'block' : 'none';
    });
}

// ===============================
// Confirm update
// ===============================
let currentUpdateId = null;
function confirmUpdate(id) {
    currentUpdateId = id;
    const modal = document.getElementById('update-confirm-modal');
    if (modal) modal.classList.replace('hidden', 'flex');
}

const finalUpdateBtn = document.getElementById('final-update-btn');
if (finalUpdateBtn) {
    finalUpdateBtn.addEventListener('click', function () {
        if (currentUpdateId !== null) {
            const form = document.getElementById('edit-form-' + currentUpdateId);
            if (form) form.submit();
        }
    });
}

function closeUpdateModal() {
    const modal = document.getElementById('update-confirm-modal');
    if (modal) modal.classList.replace('flex', 'hidden');
}


// ===============================
// Terminate account modal (Purple Theme)
// ===============================

// Assume window.currentUsername is set during login
// window.currentUsername = "Orion7715"; 

function openTerminateModal() {
    const modal = document.getElementById('terminate-modal');
    const content = document.getElementById('terminate-content');
    const displayUser = document.getElementById('display-username');
    
    if(displayUser) displayUser.textContent = window.currentUsername;
    if (!modal) return;

    modal.classList.replace('hidden', 'flex');
    setTimeout(() => {
        if (content) {
            content.classList.replace('scale-95', 'scale-100');
            content.classList.replace('opacity-0', 'opacity-100');
        }
    }, 10);
}

function closeTerminateModal() {
    const modal = document.getElementById('terminate-modal');
    const content = document.getElementById('terminate-content');
    const input = document.getElementById('username-confirm-input');
    if (!modal) return;

    if (content) {
        content.classList.replace('scale-100', 'scale-95');
        content.classList.replace('opacity-100', 'opacity-0');
    }
    
    setTimeout(() => {
        modal.classList.replace('flex', 'hidden');
        if (input) input.value = '';
        toggleTerminateBtn(false);
    }, 200);
}

function toggleTerminateBtn(isActive) {
    const btn = document.getElementById('final-terminate-btn');
    if (!btn) return;
    
    if (isActive) {
        btn.disabled = false;
        btn.className = "flex-1 py-3 bg-purple-600 text-white rounded-xl font-bold uppercase text-[10px] tracking-widest shadow-lg shadow-purple-900/40 cursor-pointer transition-all hover:bg-purple-500 hover:scale-[1.02]";
    } else {
        btn.disabled = true;
        btn.className = "flex-1 py-3 bg-purple-600/10 text-purple-500/30 rounded-xl font-bold uppercase text-[10px] tracking-widest cursor-not-allowed transition-all shadow-none";
    }
}

// Enable terminate button when username matches
const usernameConfirmInput = document.getElementById('username-confirm-input');
if (usernameConfirmInput) {
    usernameConfirmInput.addEventListener('input', function(e) {
        toggleTerminateBtn(e.target.value === window.currentUsername);
    });
}


// ===============================
// Change master password modal
// ===============================
function openChangeMasterModal() {
    const modal = document.getElementById('change-master-modal');
    if (modal) modal.classList.replace('hidden', 'flex');
}

function closeChangeMasterModal() {
    const modal = document.getElementById('change-master-modal');
    if (modal) modal.classList.replace('flex', 'hidden');
}

// Start idle timer
startTimers();


function syncPasswordIcon(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const idNum = inputId.replace('pass-', '');
    const eyeOpen = document.getElementById(`eye-open-${idNum}`);
    const eyeClosed = document.getElementById(`eye-closed-${idNum}`);

    if (input.type === "password") {
        if (eyeOpen) eyeOpen.classList.remove('hidden');
        if (eyeClosed) eyeClosed.classList.add('hidden');
    } else {
        if (eyeOpen) eyeOpen.classList.add('hidden');
        if (eyeClosed) eyeClosed.classList.remove('hidden');
    }
}

function copyTotpCode(button) {
    const code = button.dataset.code;

    if (!code) return;

    navigator.clipboard.writeText(code)
        .then(() => {
            const originalText = button.innerText;

            button.innerText = 'Copied!';

            setTimeout(() => {
                button.innerText = originalText;
            }, 1500);
        })
        .catch(() => {
            const textarea = document.createElement('textarea');
            textarea.value = code;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';

            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            textarea.remove();

            const originalText = button.innerText;

            button.innerText = 'Copied!';

            setTimeout(() => {
                button.innerText = originalText;
            }, 1500);
        });
}
