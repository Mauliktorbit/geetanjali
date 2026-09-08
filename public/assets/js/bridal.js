/**
 * Bridal collection — filters, drawer, view toggle, wishlist UI
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-bridal-page]');
    if (!page) return;

    initAutoSubmit();
    initViewToggle(page);
    initDrawer();
});

function initAutoSubmit() {
    document.querySelectorAll('[data-bridal-page] [data-auto-submit], [data-bridal-drawer] [data-auto-submit]').forEach((el) => {
        el.addEventListener('change', () => {
            el.closest('form')?.submit();
        });
    });
}

function initViewToggle(page) {
    const grid = page.querySelector('[data-bridal-grid]');

    document.querySelectorAll('[data-bridal-filter-form]').forEach((form) => {
        const viewInput = form.querySelector('[data-view-input]');
        form.querySelectorAll('[data-view]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const mode = btn.dataset.view || 'grid';
                if (viewInput) viewInput.value = mode;

                form.querySelectorAll('[data-view]').forEach((b) => {
                    const active = b.dataset.view === mode;
                    b.classList.toggle('is-active', active);
                    b.setAttribute('aria-pressed', active ? 'true' : 'false');
                });

                if (grid) {
                    grid.classList.toggle('bridal-grid--list', mode === 'list');
                }
                page.dataset.view = mode;

                // Persist via query string without losing other filters
                const url = new URL(window.location.href);
                url.searchParams.set('view', mode);
                window.history.replaceState({}, '', url.toString());
            });
        });
    });
}

function initDrawer() {
    const drawer = document.querySelector('[data-bridal-drawer]');
    if (!drawer) return;

    const openBtns = document.querySelectorAll('[data-bridal-drawer-open]');
    const closeEls = drawer.querySelectorAll('[data-bridal-drawer-close]');

    const open = () => {
        drawer.hidden = false;
        requestAnimationFrame(() => drawer.classList.add('is-open'));
        document.body.style.overflow = 'hidden';
        openBtns.forEach((btn) => btn.setAttribute('aria-expanded', 'true'));
    };

    const close = () => {
        drawer.classList.remove('is-open');
        openBtns.forEach((btn) => btn.setAttribute('aria-expanded', 'false'));
        document.body.style.overflow = '';
        setTimeout(() => {
            if (!drawer.classList.contains('is-open')) drawer.hidden = true;
        }, 280);
    };

    openBtns.forEach((btn) => btn.addEventListener('click', open));
    closeEls.forEach((el) => el.addEventListener('click', close));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });
}

function showToast(message) {
    let toast = document.querySelector('.product-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'product-toast';
        toast.setAttribute('role', 'status');
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('is-visible');
    clearTimeout(showToast._timer);
    showToast._timer = setTimeout(() => toast.classList.remove('is-visible'), 2600);
}
