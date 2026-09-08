/**
 * Offers page — coupon copy toast
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-offers-page]');
    if (!page) return;

    page.querySelectorAll('[data-copy-code]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const code = btn.getAttribute('data-copy-code') || '';
            if (!code) return;

            try {
                await navigator.clipboard.writeText(code);
                showOfferToast(`Coupon code ${code} copied successfully.`);
            } catch {
                // Fallback for older browsers / insecure contexts
                const input = document.createElement('input');
                input.value = code;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                input.remove();
                showOfferToast(`Coupon code ${code} copied successfully.`);
            }
        });
    });
});

function showOfferToast(message) {
    let toast = document.querySelector('.offer-toast') || document.querySelector('.product-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'offer-toast';
        toast.setAttribute('role', 'status');
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('is-visible');
    clearTimeout(showOfferToast._timer);
    showOfferToast._timer = setTimeout(() => toast.classList.remove('is-visible'), 2500);
}
