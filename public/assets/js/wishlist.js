/**
 * Wishlist page interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-wishlist-page]');
    if (!page) return;

    const csrf = page.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '';

    initShare(page);
    initMoveAll(page, csrf);
    initCards(page, csrf);
});

function updateCount(page, count) {
    const label = page.querySelector('[data-wishlist-count]');
    if (label) label.textContent = `(${count})`;

    const moveAll = page.querySelector('[data-move-all]');
    if (moveAll) moveAll.disabled = count < 1;
}

async function requestJson(url, method, csrf, body = null) {
    const options = {
        method,
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
    };

    if (body) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }

    const res = await fetch(url, options);
    return res.json();
}

function confirmRemove() {
    if (typeof Swal !== 'undefined') {
        return Swal.fire({
            title: 'Remove this item?',
            text: 'Remove this item from your wishlist?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#064e3b',
            cancelButtonColor: '#888',
            confirmButtonText: 'Remove',
            cancelButtonText: 'Cancel',
        }).then((result) => result.isConfirmed);
    }

    return Promise.resolve(window.confirm('Remove this item from your wishlist?'));
}

function initShare(page) {
    const btn = page.querySelector('[data-share-wishlist]');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const url = page.dataset.shareUrl || window.location.href;
        const title = 'My Geetanjali Jewellers Wishlist';

        try {
            if (navigator.share) {
                await navigator.share({ title, url });
                return;
            }
            await navigator.clipboard.writeText(url);
            btn.classList.add('is-copied');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2" aria-hidden="true"></i> Link Copied';
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('is-copied');
            }, 1800);
        } catch {
            window.prompt('Copy this wishlist link:', url);
        }
    });
}

function initMoveAll(page, csrf) {
    const btn = page.querySelector('[data-move-all]');
    const url = page.dataset.moveAllUrl;
    if (!btn || !url) return;

    btn.addEventListener('click', async () => {
        btn.disabled = true;
        try {
            const data = await requestJson(url, 'POST', csrf);
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    await Swal.fire({
                        icon: 'success',
                        title: data.message || 'Moved to bag',
                        confirmButtonColor: '#064e3b',
                        timer: 1400,
                        showConfirmButton: false,
                    });
                }
                window.location.href = (window.Geetanjali?.routes?.cart) || '/cart';
                return;
            }
        } catch {
            // fall through
        }
        btn.disabled = false;
        window.alert('Could not move items. Please try again.');
    });
}

function initCards(page, csrf) {
    page.querySelectorAll('[data-wishlist-item]').forEach((card) => {
        const removeBtn = card.querySelector('[data-wishlist-remove]');
        const moveBtn = card.querySelector('[data-wishlist-move]');

        removeBtn?.addEventListener('click', async () => {
            const ok = await confirmRemove();
            if (!ok) return;

            const url = card.dataset.removeUrl;
            try {
                const data = await requestJson(url, 'DELETE', csrf);
                if (data.success) {
                    card.classList.add('is-removing');
                    setTimeout(() => {
                        card.remove();
                        updateCount(page, data.wishlist_count ?? 0);
                        const grid = page.querySelector('[data-wishlist-grid]');
                        if (grid && !grid.querySelector('[data-wishlist-item]')) {
                            window.location.reload();
                        }
                    }, 220);
                }
            } catch {
                window.alert('Could not remove item.');
            }
        });

        moveBtn?.addEventListener('click', async () => {
            const url = card.dataset.moveUrl;
            moveBtn.disabled = true;
            try {
                const data = await requestJson(url, 'POST', csrf);
                if (data.success) {
                    card.classList.add('is-removing');
                    setTimeout(() => {
                        card.remove();
                        updateCount(page, data.wishlist_count ?? 0);
                        document.querySelectorAll('.cart-badge').forEach((badge) => {
                            badge.textContent = String(data.cart_count ?? badge.textContent);
                        });
                        const grid = page.querySelector('[data-wishlist-grid]');
                        if (grid && !grid.querySelector('[data-wishlist-item]')) {
                            window.location.reload();
                        }
                    }, 220);
                    return;
                }
            } catch {
                // fall through
            }
            moveBtn.disabled = false;
            window.alert('Could not move item to bag.');
        });
    });
}
