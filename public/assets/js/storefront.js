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

    const notifyBtn = event.target.closest('[data-stock-notify]');
    if (notifyBtn) {
        event.preventDefault();
        event.stopPropagation();
        await handleStockNotify(notifyBtn);
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
    const qtyInput = card.querySelector?.('[data-qty-input]');
    const min = Math.max(1, parseInt(qtyInput?.min || card.dataset.qtyMin || '1', 10) || 1);
    let max = parseInt(qtyInput?.max || card.dataset.qtyMax || '', 10);
    const stock = parseInt(card.dataset.stock || '', 10);
    if (!Number.isFinite(max) || max < min) {
        max = Number.isFinite(stock) && stock > 0 ? stock : min;
    }
    if (Number.isFinite(stock) && stock > 0) {
        max = Math.min(max, stock);
    }
    let quantity = parseInt(String(qtyInput?.value ?? '1'), 10);
    if (!Number.isFinite(quantity)) quantity = min;
    quantity = Math.min(max, Math.max(min, quantity));

    return {
        product_id: Number(el.dataset.productId || card.dataset.productId || 0),
        quantity,
        name: card.dataset.productName || '',
        slug: card.dataset.productSlug || '',
        image: card.dataset.productImage || '',
        price: card.dataset.productPrice || '',
        compare_at_price: card.dataset.productCompare || '',
        discount_label: priceDiscountLabel(card.dataset.productPrice, card.dataset.productCompare),
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
        const error = new Error(data.message || 'Unable to update your bag.');
        error.needsEmail = Boolean(data.needs_email);
        throw error;
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

async function promptNotifyEmail() {
    if (window.Swal && typeof window.Swal.fire === 'function') {
        const result = await window.Swal.fire({
            title: 'Notify me',
            text: 'Enter your email and we will let you know when this piece is back in stock.',
            input: 'email',
            inputPlaceholder: 'you@example.com',
            inputAttributes: {
                autocapitalize: 'off',
                autocomplete: 'email',
            },
            confirmButtonText: 'Notify me',
            showCancelButton: true,
            confirmButtonColor: '#064E3B',
            cancelButtonColor: '#888',
        });
        if (!result.isConfirmed) {
            return null;
        }
        return String(result.value || '').trim();
    }

    const email = window.prompt('Enter your email to be notified when this piece is back in stock.');
    return email ? email.trim() : null;
}

async function handleStockNotify(btn) {
    const url = storefrontRoutes().stockNotify;
    const payload = productPayload(btn);
    if (!url || !payload.product_id) {
        storefrontToast('Unable to save this alert.');
        return;
    }

    const body = { product_id: payload.product_id };
    const loggedIn = Boolean(window.Geetanjali?.loggedIn);
    if (!loggedIn) {
        const email = await promptNotifyEmail();
        if (!email) {
            return;
        }
        body.email = email;
    }

    btn.disabled = true;
    try {
        const data = await storefrontPost(url, body);
        storefrontToast(data.message || 'We’ll email you when this piece is back in stock.');
        btn.classList.add('is-notified');
    } catch (error) {
        if (error.needsEmail) {
            const email = await promptNotifyEmail();
            if (!email) {
                return;
            }
            try {
                const data = await storefrontPost(url, { product_id: payload.product_id, email });
                storefrontToast(data.message || 'We’ll email you when this piece is back in stock.');
                btn.classList.add('is-notified');
            } catch (retryError) {
                storefrontToast(retryError.message || 'Unable to save this alert.');
            }
            return;
        }
        storefrontToast(error.message || 'Unable to save this alert.');
    } finally {
        btn.disabled = false;
    }
}

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
        closeQuickView();
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

function priceDiscountLabel(price, compare) {
    price = Math.round(Number(price) || 0);
    compare = Math.round(Number(compare) || 0);
    if (price <= 0 || compare <= price) {
        return '';
    }

    const saved = compare - price;
    const raw = (saved / compare) * 100;
    const matches = (percent) => Math.round(compare * (1 - percent / 100)) === price;

    const whole = Math.round(raw);
    if (matches(whole)) {
        return whole + '% OFF';
    }

    for (let decimals = 1; decimals <= 2; decimals += 1) {
        const factor = 10 ** decimals;
        const candidate = Math.round(raw * factor) / factor;
        if (matches(candidate)) {
            return Number(candidate.toFixed(decimals)) + '% OFF';
        }
    }

    return 'Save ₹' + saved.toLocaleString('en-IN');
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
        discount.textContent = priceDiscountLabel(payload.price, payload.compare_at_price);
        discount.hidden = !discount.textContent;
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

    const notify = modal.querySelector('[data-qv-notify]');
    if (notify) {
        notify.dataset.productId = String(payload.product_id || '');
        notify.hidden = !outOfStock || !payload.product_id;
    }

    const similar = modal.querySelector('[data-qv-similar]');
    if (similar) {
        similar.href = card.dataset.productSimilar || payload.url || '#';
        similar.hidden = !outOfStock;
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

