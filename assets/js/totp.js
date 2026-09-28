function getTotpRemainingSeconds() {
    return 30 - (Math.floor(Date.now() / 1000) % 30);
}

function updateTotpTimers() {
    document.querySelectorAll('[data-totp-timer]').forEach(timer => {
        timer.textContent = getTotpRemainingSeconds() + 's';
    });
}

async function refreshTotpCode(element) {
    const id = element.dataset.totpId;

    if (!id) return;


    try {
        const response = await fetch(
    'actions/get_totp.php?id=' + encodeURIComponent(id),
    {
        method: 'GET',
        cache: 'no-store'
    }
);

        const data = await response.json();

        if (data.success && data.code) {

            const codeElement = document.querySelector(
                `[data-totp-code="${id}"]`
            );

            if (codeElement) {
                codeElement.textContent = data.code;
            }

            const copyButton = document.querySelector(
                `[data-totp-copy="${id}"]`
            );

            if (copyButton) {
                copyButton.dataset.code = data.code;
            }
        }

    } catch (error) {
        console.error('TOTP refresh failed:', error);
    }
}

function copyTotpCode(button) {
    const code = button.dataset.code;

    if (!code) return;

    navigator.clipboard.writeText(code)
        .then(() => {
            const originalHTML = button.innerHTML;

            button.innerHTML = `
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
            `;

            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 1200);
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

            const originalHTML = button.innerHTML;

            button.innerHTML = `
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
            `;

            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 1200);
        });
}

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('[data-totp-id]').forEach(element => {
        refreshTotpCode(element);
    });

    updateTotpTimers();

    setInterval(() => {

        const remaining = getTotpRemainingSeconds();

        updateTotpTimers();

        if (remaining === 30) {
            document.querySelectorAll('[data-totp-id]').forEach(element => {
                refreshTotpCode(element);
            });
        }

    }, 1000);
});