/**
 * Auth pages — OTP input
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-auth-page]');
    if (!page) return;

    page.querySelectorAll('[data-otp-input]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 6);
        });
    });
});
