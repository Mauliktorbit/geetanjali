/**
 * Geetanjali Jewellers — frontend JS
 * Loaded from public/assets/js (Laravel asset()).
 * Page scripts are included from the layout after Bootstrap.
 */
document.addEventListener('DOMContentLoaded', () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        window.csrfToken = token;
    }

    const mobileNav = document.getElementById('mobileNav');
    if (mobileNav) {
        mobileNav.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');
            if (!link || link.hasAttribute('data-bs-dismiss')) {
                return;
            }

            const href = link.getAttribute('href');
            if (!href || href === '#') {
                return;
            }

            event.preventDefault();
            window.location.assign(link.href);
        });
    }
});
