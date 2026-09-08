/**
 * Product details page interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-product-page]');
    if (!root) return;

    initGallery(root);
    initQuantity(root);
    initTabs(root);
    initDelivery(root);
    initLightbox(root);
});

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
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

function initGallery(root) {
    const mainImg = root.querySelector('[data-main-image]');
    const thumbs = [...root.querySelectorAll('[data-thumb]')];
    const thumbTrack = root.querySelector('[data-thumbs]');
    if (!mainImg || !thumbs.length) return;

    const setActive = (index) => {
        const thumb = thumbs[index];
        if (!thumb) return;
        thumbs.forEach((t, i) => t.classList.toggle('is-active', i === index));
        mainImg.src = thumb.dataset.full || thumb.querySelector('img')?.src;
        mainImg.alt = thumb.dataset.alt || mainImg.alt;
        root.dataset.activeImage = String(index);
    };

    thumbs.forEach((thumb, index) => {
        thumb.addEventListener('click', () => setActive(index));
    });

    root.querySelector('[data-thumb-prev]')?.addEventListener('click', () => {
        thumbTrack?.scrollBy({ left: -120, behavior: 'smooth' });
    });
    root.querySelector('[data-thumb-next]')?.addEventListener('click', () => {
        thumbTrack?.scrollBy({ left: 120, behavior: 'smooth' });
    });

    setActive(0);
}

function initQuantity(root) {
    const input = root.querySelector('[data-qty-input]');
    const max = Number(root.dataset.stock || input?.max || 10);
    if (!input) return;

    const clamp = (value) => Math.min(Math.max(1, value), max || 10);

    root.querySelector('[data-qty-minus]')?.addEventListener('click', () => {
        input.value = String(clamp(Number(input.value || 1) - 1));
    });
    root.querySelector('[data-qty-plus]')?.addEventListener('click', () => {
        input.value = String(clamp(Number(input.value || 1) + 1));
    });
    input.addEventListener('change', () => {
        input.value = String(clamp(Number(input.value || 1)));
    });
}

function initWishlist(root) {
    const btn = root.querySelector('[data-wishlist-toggle]');
    if (!btn) return;

    btn.addEventListener('click', () => {
        const active = btn.classList.toggle('is-active');
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('bi-heart', !active);
            icon.classList.toggle('bi-heart-fill', active);
        }
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        showToast(active ? 'Added to wishlist' : 'Removed from wishlist');
    });
}

function initTabs(root) {
    const tabs = [...root.querySelectorAll('[data-product-tab]')];
    const panels = [...root.querySelectorAll('[data-product-panel]')];
    if (!tabs.length) return;

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.productTab;
            tabs.forEach((t) => {
                const on = t === tab;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.dataset.productPanel === target);
            });
        });
    });

    root.querySelectorAll('[data-open-reviews]').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            root.querySelector('[data-product-tab="reviews"]')?.click();
            root.querySelector('#product-tabs')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
}

function initDelivery(root) {
    const form = root.querySelector('[data-delivery-form]');
    const result = root.querySelector('[data-delivery-result]');
    if (!form || !result) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const pincode = form.querySelector('[name="pincode"]')?.value?.trim() || '';
        result.textContent = 'Checking...';
        result.className = 'delivery-result';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    pincode,
                    product_id: Number(root.dataset.productId || 0),
                }),
            });
            const data = await response.json();
            result.textContent = data.available
                ? `✓ ${data.message}${data.estimated_delivery ? ` · ${data.estimated_delivery}` : ''}`
                : `✕ ${data.message || 'Delivery not available'}`;
            result.classList.add(data.available ? 'is-ok' : 'is-fail');
        } catch (error) {
            result.textContent = 'Unable to check delivery right now.';
            result.classList.add('is-fail');
        }
    });
}

function initCartActions(root) {
    const qtyInput = root.querySelector('[data-qty-input]');
    const productId = Number(root.dataset.productId || 0);
    const cartUrl = root.dataset.cartUrl;
    const checkoutUrl = root.dataset.checkoutUrl || '/';

    const addToCart = async () => {
        const quantity = Number(qtyInput?.value || 1);
        const response = await fetch(cartUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, quantity }),
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Unable to add to cart');
        }

        const badge = document.querySelector('.cart-badge');
        if (badge && data.cart_count) {
            badge.textContent = String(data.cart_count);
        }
        showToast(data.message || 'Product added to cart');
        return data;
    };

    root.querySelector('[data-add-to-cart]')?.addEventListener('click', async () => {
        try {
            await addToCart();
        } catch (error) {
            showToast(error.message || 'Unable to add to cart');
        }
    });

    root.querySelector('[data-buy-now]')?.addEventListener('click', async () => {
        try {
            await addToCart();
            window.location.href = checkoutUrl;
        } catch (error) {
            showToast(error.message || 'Unable to continue');
        }
    });
}

function initLightbox(root) {
    const lightbox = document.querySelector('[data-product-lightbox]');
    const image = lightbox?.querySelector('[data-lightbox-image]');
    const thumbs = [...root.querySelectorAll('[data-thumb]')];
    if (!lightbox || !image || !thumbs.length) return;

    let index = 0;

    const open = (i = 0) => {
        index = i;
        const thumb = thumbs[index];
        image.src = thumb.dataset.full || thumb.querySelector('img')?.src;
        image.alt = thumb.dataset.alt || '';
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    const move = (step) => {
        index = (index + step + thumbs.length) % thumbs.length;
        open(index);
    };

    root.querySelector('[data-open-zoom]')?.addEventListener('click', () => {
        open(Number(root.dataset.activeImage || 0));
    });

    lightbox.querySelector('[data-lightbox-close]')?.addEventListener('click', close);
    lightbox.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => move(-1));
    lightbox.querySelector('[data-lightbox-next]')?.addEventListener('click', () => move(1));
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) close();
    });
    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') move(-1);
        if (e.key === 'ArrowRight') move(1);
    });
}
