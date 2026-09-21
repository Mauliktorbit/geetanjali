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
    const minus = root.querySelector('[data-qty-minus]');
    const plus = root.querySelector('[data-qty-plus]');
    if (!input) return;

    const bounds = () => {
        const min = Math.max(1, parseInt(input.getAttribute('min') || root.dataset.qtyMin || '1', 10) || 1);
        let max = parseInt(input.getAttribute('max') || root.dataset.qtyMax || '', 10);
        const stock = parseInt(root.dataset.stock || '', 10);

        if (!Number.isFinite(max) || max < min) {
            max = Number.isFinite(stock) && stock > 0 ? stock : min;
        }
        if (Number.isFinite(stock) && stock > 0) {
            max = Math.min(max, stock);
        }

        return { min, max: Math.max(min, max) };
    };

    const parseQty = (raw) => {
        const n = parseInt(String(raw ?? '').replace(/[^\d]/g, ''), 10);
        return Number.isFinite(n) ? n : bounds().min;
    };

    const syncButtons = (qty) => {
        const { min, max } = bounds();
        if (minus) {
            minus.disabled = qty <= min;
            minus.setAttribute('aria-disabled', qty <= min ? 'true' : 'false');
        }
        if (plus) {
            plus.disabled = qty >= max;
            plus.setAttribute('aria-disabled', qty >= max ? 'true' : 'false');
        }
    };

    const setQty = (value) => {
        const { min, max } = bounds();
        let qty = parseQty(value);
        if (qty < min) qty = min;
        if (qty > max) qty = max;
        input.value = String(qty);
        syncButtons(qty);
        return qty;
    };

    minus?.addEventListener('click', () => {
        setQty(parseQty(input.value) - 1);
    });
    plus?.addEventListener('click', () => {
        setQty(parseQty(input.value) + 1);
    });

    input.addEventListener('keydown', (event) => {
        if (['e', 'E', '+', '-', '.', ',', ' '].includes(event.key)) {
            event.preventDefault();
            return;
        }
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setQty(parseQty(input.value) - 1);
        }
        if (event.key === 'ArrowUp') {
            event.preventDefault();
            setQty(parseQty(input.value) + 1);
        }
    });

    input.addEventListener('input', () => {
        if (input.value === '') {
            syncButtons(bounds().min);
            return;
        }
        setQty(input.value);
    });

    input.addEventListener('blur', () => setQty(input.value === '' ? bounds().min : input.value));
    input.addEventListener('change', () => setQty(input.value));
    input.addEventListener('wheel', (event) => event.preventDefault(), { passive: false });

    setQty(input.value);
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

function deliveryCheckUrl(form) {
    const configured = window.Geetanjali?.routes?.deliveryCheck;
    const fallbackPath = () => {
        const prefix = window.location.pathname.replace(/\/product\/[\s\S]*$/, '');
        return `${window.location.origin}${prefix}/product/delivery-check`;
    };

    const raw = configured || form?.getAttribute('action') || form?.action || fallbackPath();
    try {
        const url = new URL(raw, window.location.href);
        url.protocol = window.location.protocol;
        url.host = window.location.host;
        const herePrefix = window.location.pathname.split('/product/')[0];
        const therePrefix = url.pathname.split('/product/')[0];
        if (herePrefix && therePrefix && herePrefix.toLowerCase() === therePrefix.toLowerCase()) {
            url.pathname = herePrefix + url.pathname.slice(therePrefix.length);
        }
        return url.toString();
    } catch (error) {
        return fallbackPath();
    }
}

function initDelivery(root) {
    const form = root.querySelector('[data-delivery-form]');
    const result = root.querySelector('[data-delivery-result]');
    const input = form?.querySelector('[name="pincode"]');
    const submit = form?.querySelector('[data-delivery-submit], button[type="submit"]');
    if (!form || !result || !input) return;

    const showResult = (message, ok) => {
        result.textContent = message;
        result.className = 'delivery-result';
        if (ok === true) result.classList.add('is-ok');
        if (ok === false) result.classList.add('is-fail');
    };

    const setBusy = (busy) => {
        form.classList.toggle('is-busy', busy);
        if (submit) {
            submit.disabled = busy;
            submit.classList.toggle('is-busy', busy);
            submit.setAttribute('aria-busy', busy ? 'true' : 'false');
        }
    };

    const checkPincode = async () => {
        const pincode = String(input.value || '').replace(/\D/g, '').slice(0, 6);
        input.value = pincode;

        if (pincode.length !== 6) {
            showResult('Enter a valid 6-digit pincode.', false);
            input.focus();
            return;
        }

        if (form.classList.contains('is-busy')) {
            return;
        }

        setBusy(true);
        showResult('Checking delivery…', null);

        try {
            const body = new FormData(form);
            body.set('pincode', pincode);
            body.set('product_id', String(root.dataset.productId || ''));

            const headers = {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            };
            const xsrf = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
            if (xsrf) {
                headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrf[1]);
            }

            const response = await fetch(deliveryCheckUrl(form), {
                method: 'POST',
                credentials: 'same-origin',
                redirect: 'error',
                headers,
                body,
            });
            const data = await response.json().catch(() => ({}));
            const available = Boolean(data.available);
            const message = data.message || (available ? 'Delivery available' : 'Delivery not available');
            showResult(
                available
                    ? `✓ ${message}${data.estimated_delivery ? ` · ${data.estimated_delivery}` : ''}`
                    : `✕ ${message}`,
                available
            );
        } catch (error) {
            showResult('Unable to check delivery right now. Please try again.', false);
        } finally {
            setBusy(false);
        }
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        event.stopPropagation();
        checkPincode();
    });
}

function initCartActions(root) {
    const qtyInput = root.querySelector('[data-qty-input]');
    const productId = Number(root.dataset.productId || 0);
    const cartUrl = root.dataset.cartUrl;
    const checkoutUrl = root.dataset.checkoutUrl || '/';

    const addToCart = async () => {
        if (Number(root.dataset.stock || 0) <= 0 || root.hasAttribute('data-out-of-stock')) {
            throw new Error('This product is out of stock.');
        }
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
