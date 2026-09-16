/**
 * New Arrivals — filter drawer, sort, grid/list, wishlist
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-new-arrivals-page]');
    if (!page) return;

    initViewToggle(page);
    initDrawer();
});

function initViewToggle(page) {
    const grid = page.querySelector('[data-na-grid]');
    const form = page.querySelector('[data-na-sort-form]');
    const viewInput = form?.querySelector('[data-view-input]');

    form?.querySelectorAll('[data-view]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const mode = btn.dataset.view || 'grid';
            if (viewInput) viewInput.value = mode;

            form.querySelectorAll('[data-view]').forEach((b) => {
                const active = b.dataset.view === mode;
                b.classList.toggle('is-active', active);
                b.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            grid?.classList.toggle('na-grid--list', mode === 'list');
            page.dataset.view = mode;

            const url = new URL(window.location.href);
            url.searchParams.set('view', mode);
            window.history.replaceState({}, '', url.toString());
        });
    });
}

function initDrawer() {
    const drawer = document.querySelector('[data-na-drawer]');
    if (!drawer) return;

    const openBtns = document.querySelectorAll('[data-na-drawer-open]');
    const closeEls = drawer.querySelectorAll('[data-na-drawer-close]');

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
