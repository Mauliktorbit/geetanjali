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
    const card = el.closest('[data-product-id], .product-card, [data-product-page]') || el;
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

async function storefrontPost(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': storefrontCsrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
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
    const icon = btn.querySelector('i');
    if (icon) {
        icon.classList.toggle('bi-heart', !on);
        icon.classList.toggle('bi-heart-fill', on);
    }
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
        setWishlistButton(btn, Boolean(data.in_wishlist));
        updateStorefrontBadges(data);
        storefrontToast(data.message || (data.in_wishlist ? 'Added to wishlist.' : 'Removed from wishlist.'));
    } catch (error) {
        storefrontToast(error.message || 'Unable to update wishlist.');
    } finally {
        btn.disabled = false;
    }
}

async function handleAddToCart(btn, buyNow) {
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
