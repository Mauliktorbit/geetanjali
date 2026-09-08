/**
 * Auth pages — password visibility toggle
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-auth-page]');
    if (!page) return;

    page.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const wrap = btn.closest('.auth-input');
            const input = wrap?.querySelector('[data-password-input], input[type="password"], input[type="text"]');
            const icon = btn.querySelector('i');
            if (!input) return;

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('bi-eye', show);
                icon.classList.toggle('bi-eye-slash', !show);
            }
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });

    page.querySelectorAll('[data-otp-input]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 6);
        });
    });
});
