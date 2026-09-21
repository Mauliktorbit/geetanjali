/**
 * Wishlist page interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-wishlist-page]');
    if (!page) return;

    const csrf = page.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '';
    const isShared = page.dataset.shared === '1';

    if (!isShared) {
        initShare(page, csrf);
        initMoveAll(page, csrf);
        initCards(page, csrf);
    } else {
        initSharedAdd(page, csrf);
    }
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

function initShare(page, csrf) {
    const btn = page.querySelector('[data-share-wishlist]');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        btn.disabled = true;
        let url = '';
        try {
            const endpoint = page.dataset.shareEndpoint;
            if (endpoint) {
                const data = await requestJson(endpoint, 'POST', csrf);
                if (data.success && data.url) {
                    url = data.url;
                    page.dataset.shareUrl = url;
                }
            }
        } catch {
            url = '';
        }

        if (!url) {
            btn.disabled = false;
            window.alert('Add items to your wishlist before sharing.');
            return;
        }

        const title = 'My Geetanjali Jewellers Wishlist';

        try {
            if (navigator.share) {
                await navigator.share({ title, url });
                btn.disabled = false;
                return;
            }
            await navigator.clipboard.writeText(url);
            btn.classList.add('is-copied');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2" aria-hidden="true"></i> Link Copied';
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('is-copied');
                btn.disabled = false;
            }, 1800);
        } catch {
            btn.disabled = false;
            window.prompt('Copy this wishlist link:', url);
        }
    });
}

function initSharedAdd(page, csrf) {
    const cartUrl = window.Geetanjali?.routes?.cartAdd;
    if (!cartUrl) return;

    page.querySelectorAll('[data-shared-add]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const card = btn.closest('[data-wishlist-item]');
            if (!card) return;
            btn.disabled = true;
            try {
                const data = await requestJson(cartUrl, 'POST', csrf, {
                    product_id: Number(card.dataset.productId || 0),
                    quantity: 1,
                    name: card.dataset.productName || '',
                    slug: card.dataset.productSlug || '',
                    image: card.dataset.productImage || '',
                    price: card.dataset.productPrice || '',
                    compare_at_price: card.dataset.productCompare || '',
                    discount_label: card.dataset.productDiscount || '',
                    metal: card.dataset.productMetal || '',
                    weight: card.dataset.productWeight || '',
                    url: card.dataset.productUrl || '',
                });
                if (data.success) {
                    document.querySelectorAll('[data-cart-badge], .cart-badge').forEach((badge) => {
                        badge.textContent = String(data.cart_count ?? badge.textContent);
                    });
                    btn.textContent = 'Added';
                    return;
                }
                window.alert(data.message || 'Could not add item to bag.');
            } catch {
                window.alert('Could not add item to bag.');
            }
            btn.disabled = false;
        });
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
