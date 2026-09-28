const usernameField = document.getElementsByName('username')[0];
if (usernameField) usernameField.focus();

document.querySelector('form').addEventListener('submit', function(e) {
    const btn = document.getElementById('login-btn');
    const icon = document.getElementById('btn-icon');
    const text = document.getElementById('btn-text');
    btn.classList.add('cursor-wait');
    text.innerText = "Authenticating...";
    icon.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>`;
});

