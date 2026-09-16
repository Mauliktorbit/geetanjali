/**
 * Shared cart + wishlist actions for product cards and product pages.
 */
document.addEventListener('click', async (event) => {
    const wishBtn = event.target.closest('[data-wishlist-toggle]');
    if (wishBtn) {
        event.preventDefault();
        event.stopPropagation();
        await handleWishlistToggle(wishBtn);
        return;
    }

    const cartBtn = event.target.closest('[data-add-to-cart]');
    if (cartBtn) {
        event.preventDefault();
        await handleAddToCart(cartBtn, false);
        return;
    }

    const quickViewBtn = event.target.closest('[data-quick-view]');
    if (quickViewBtn) {
        event.preventDefault();
        event.stopPropagation();
        openQuickView(quickViewBtn);
        return;
    }

    if (event.target.closest('[data-quick-view-close]')) {
        event.preventDefault();
        closeQuickView();
        return;
    }

    const buyBtn = event.target.closest('[data-buy-now]');
    if (buyBtn) {
        event.preventDefault();
        await handleAddToCart(buyBtn, true);
    }
});

function storefrontRoutes() {
    return window.Geetanjali?.routes || {};
}

function storefrontCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || window.csrfToken || '';
}

function storefrontToast(message) {
    let toast = document.querySelector('.product-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'product-toast';
        toast.setAttribute('role', 'status');
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('is-visible');
    clearTimeout(storefrontToast._timer);
    storefrontToast._timer = setTimeout(() => toast.classList.remove('is-visible'), 2600);
}

function productPayload(el) {
    const card = el.closest('.product-card, [data-product-page]') || el.closest('[data-product-id]') || el;
    const qty = card.querySelector?.('[data-qty-input]')?.value;
    return {
        product_id: Number(el.dataset.productId || card.dataset.productId || 0),
        quantity: Number(qty || 1),
        name: card.dataset.productName || '',
        slug: card.dataset.productSlug || '',
        image: card.dataset.productImage || '',
        price: card.dataset.productPrice || '',
        compare_at_price: card.dataset.productCompare || '',
        discount_label: card.dataset.productDiscount || '',
        metal: card.dataset.productMetal || '',
        weight: card.dataset.productWeight || '',
        url: card.dataset.productUrl || '',
    };
}

function storefrontXsrfCookie() {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    return match ? decodeURIComponent(match[1]) : '';
}

async function storefrontPost(url, body) {
    const headers = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': storefrontCsrf(),
        'X-Requested-With': 'XMLHttpRequest',
    };
    const xsrf = storefrontXsrfCookie();
    if (xsrf) {
        headers['X-XSRF-TOKEN'] = xsrf;
    }

    const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers,
        body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.success === false) {
        throw new Error(data.message || 'Unable to update your bag.');
    }
    return data;
}

function updateStorefrontBadges(data) {
    if (data.cart_count != null) {
        document.querySelectorAll('[data-cart-badge]').forEach((el) => {
            el.textContent = String(data.cart_count);
        });
    }
    if (data.wishlist_count != null) {
        document.querySelectorAll('[data-wishlist-badge]').forEach((el) => {
            el.textContent = String(data.wishlist_count);
        });
    }
}

function setWishlistButton(btn, on) {
    btn.classList.toggle('is-active', on);
    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    const name = btn.closest('[data-product-name]')?.dataset.productName
        || document.querySelector('[data-product-page]')?.dataset.productName
        || 'this piece';
    btn.setAttribute('aria-label', on ? `Remove ${name} from wishlist` : `Add ${name} to wishlist`);
    const icon = btn.querySelector('i');
    if (icon) {
        icon.classList.toggle('bi-heart', !on);
        icon.classList.toggle('bi-heart-fill', on);
    }
}

function wishlistIdSet() {
    const ids = window.Geetanjali?.wishlistIds;
    return new Set((Array.isArray(ids) ? ids : []).map((id) => Number(id)));
}

function syncWishlistButtons() {
    const ids = wishlistIdSet();
    document.querySelectorAll('[data-wishlist-toggle]').forEach((btn) => {
        const payload = productPayload(btn);
        if (!payload.product_id) {
            return;
        }
        setWishlistButton(btn, ids.has(payload.product_id));
    });
}

async function handleWishlistToggle(btn) {
    const url = storefrontRoutes().wishlistToggle;
    const payload = productPayload(btn);
    if (!url || !payload.product_id) {
        storefrontToast('Unable to save this piece.');
        return;
    }

    btn.disabled = true;
    try {
        const data = await storefrontPost(url, payload);
        const on = Boolean(data.in_wishlist);
        const ids = wishlistIdSet();
        if (on) {
            ids.add(payload.product_id);
        } else {
            ids.delete(payload.product_id);
        }
        window.Geetanjali = Object.assign(window.Geetanjali || {}, {
            wishlistIds: Array.from(ids),
        });
        document.querySelectorAll('[data-wishlist-toggle]').forEach((heart) => {
            const id = productPayload(heart).product_id;
            if (id === payload.product_id) {
                setWishlistButton(heart, on);
            }
        });
        updateStorefrontBadges(data);
        storefrontToast(data.message || (on ? 'Added to wishlist.' : 'Removed from wishlist.'));
    } catch (error) {
        storefrontToast(error.message || 'Unable to update wishlist.');
    } finally {
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', syncWishlistButtons);

async function handleAddToCart(btn, buyNow) {
    if (btn.disabled || btn.closest('[data-out-of-stock]')) {
        storefrontToast('This product is out of stock.');
        return;
    }
    const url = storefrontRoutes().cartAdd;
    const payload = productPayload(btn);
    if (!url || !payload.product_id) {
        storefrontToast('Unable to add this piece.');
        return;
    }

    btn.disabled = true;
    try {
        const data = await storefrontPost(url, payload);
        updateStorefrontBadges(data);
        storefrontToast(data.message || 'Product added to cart');
        if (buyNow) {
            window.location.href = storefrontRoutes().checkout || '/';
        }
    } catch (error) {
        storefrontToast(error.message || 'Unable to add to cart.');
    } finally {
        btn.disabled = false;
    }
}

function quickViewModal() {
    return document.querySelector('[data-quick-view-modal]');
}

function rupee(value) {
    const amount = Number(value);
    if (!amount) {
        return '';
    }
    return `₹${amount.toLocaleString('en-IN')}`;
}

function fillQuickView(card) {
    const modal = quickViewModal();
    if (!modal) {
        return;
    }

    const dialog = modal.querySelector('[data-quick-view-dialog]') || modal;
    const payload = productPayload(card);
    const outOfStock = card.hasAttribute('data-out-of-stock');
    const meta = [card.dataset.productMetal, card.dataset.productWeight].filter(Boolean).join(' · ');

    [
        'productId',
        'productName',
        'productPrice',
        'productImage',
        'productUrl',
        'productSlug',
        'productCompare',
        'productDiscount',
        'productMetal',
        'productWeight',
    ].forEach((key) => {
        if (card.dataset[key] != null) {
            dialog.dataset[key] = card.dataset[key];
        }
    });

    if (outOfStock) {
        dialog.setAttribute('data-out-of-stock', '1');
    } else {
        dialog.removeAttribute('data-out-of-stock');
    }

    const image = modal.querySelector('[data-qv-image]');
    if (image) {
        image.src = payload.image || '';
        image.alt = payload.name || '';
    }

    const name = modal.querySelector('[data-qv-name]');
    if (name) {
        name.textContent = payload.name || '';
    }

    const metaEl = modal.querySelector('[data-qv-meta]');
    if (metaEl) {
        metaEl.textContent = meta;
        metaEl.hidden = !meta;
    }

    const price = modal.querySelector('[data-qv-price]');
    if (price) {
        price.textContent = rupee(payload.price);
    }

    const compare = modal.querySelector('[data-qv-compare]');
    if (compare) {
        compare.textContent = rupee(payload.compare_at_price);
        compare.hidden = !payload.compare_at_price;
    }

    const discount = modal.querySelector('[data-qv-discount]');
    if (discount) {
        discount.textContent = payload.discount_label || '';
        discount.hidden = !payload.discount_label;
    }

    const link = modal.querySelector('[data-qv-link]');
    if (link) {
        link.href = payload.url || '#';
    }

    const cart = modal.querySelector('[data-qv-cart]');
    if (cart) {
        cart.dataset.productId = String(payload.product_id || '');
        cart.hidden = outOfStock || !payload.product_id;
        cart.disabled = outOfStock;
    }
}

function openQuickView(trigger) {
    const card = trigger.closest('.product-card');
    const modal = quickViewModal();
    if (!card || !modal) {
        return;
    }

    fillQuickView(card);
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    modal.querySelector('[data-quick-view-close]')?.focus();
}

function closeQuickView() {
    const modal = quickViewModal();
    if (!modal || modal.hidden) {
        return;
    }
    modal.hidden = true;
    document.body.style.overflow = '';
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeQuickView();
    }
});

