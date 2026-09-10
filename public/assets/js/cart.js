/**
 * Cart page interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-cart-page]');
    if (!page) return;

    const csrf = page.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '';
    const updateUrl = page.dataset.updateUrl;
    const couponUrl = page.dataset.couponUrl;
    const giftUrl = page.dataset.giftUrl;

    initQty(page, updateUrl, csrf);
    initRemove(page);
    initCoupon(page, couponUrl, csrf);
    initGift(page, giftUrl, csrf);
});

function money(value) {
    return '₹' + Number(value || 0).toLocaleString('en-IN');
}

function applySummary(page, cart) {
    if (!cart) return;
    const count = cart.count || 0;
    page.querySelectorAll('[data-cart-count-label]').forEach((el) => {
        el.textContent = `(${count} ${count === 1 ? 'Item' : 'Items'})`;
    });
    const subLabel = page.querySelector('[data-label-subtotal]');
    if (subLabel) subLabel.textContent = `Subtotal (${count} Items)`;
    const sub = page.querySelector('[data-subtotal]');
    if (sub) sub.textContent = money(cart.subtotal);
    const discount = page.querySelector('[data-discount]');
    if (discount) discount.textContent = `- ${money(cart.discount)}`;
    const total = page.querySelector('[data-total]');
    if (total) total.textContent = money(cart.total);
    const savings = page.querySelector('[data-savings]');
    if (savings) savings.textContent = `You Save ${money(cart.savings)} on this order`;

    document.querySelectorAll('[data-cart-badge]').forEach((badge) => {
        badge.textContent = String(count);
        badge.setAttribute('aria-label', `${count} items in cart`);
    });
}

async function postJson(url, body, csrf) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });
    return res.json();
}

function initQty(page, updateUrl, csrf) {
    page.querySelectorAll('[data-cart-item]').forEach((row) => {
        const input = row.querySelector('[data-qty-input]');
        const minus = row.querySelector('[data-qty-minus]');
        const plus = row.querySelector('[data-qty-plus]');
        const lineTotal = row.querySelector('[data-line-total]');
        const productId = Number(row.dataset.productId);
        const unit = Number(row.dataset.unitPrice || 0);

        const sync = async (qty) => {
            qty = Math.min(10, Math.max(1, qty));
            input.value = String(qty);
            if (lineTotal) lineTotal.textContent = money(unit * qty);
            try {
                const data = await postJson(updateUrl, { product_id: productId, quantity: qty }, csrf);
                if (data.cart) applySummary(page, data.cart);
            } catch {
                // keep UI optimistic
            }
        };

        minus?.addEventListener('click', () => sync(Number(input.value || 1) - 1));
        plus?.addEventListener('click', () => sync(Number(input.value || 1) + 1));
        input?.addEventListener('change', () => sync(Number(input.value || 1)));
    });
}

function initRemove(page) {
    page.querySelectorAll('[data-remove-form]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            if (form.dataset.swalConfirmed === '1') return;
            e.preventDefault();
            const ok = window.AppAlert
                ? await window.AppAlert.confirm('Remove this item from your cart?', form)
                : window.confirm('Remove this item from your cart?');
            if (!ok) return;
            form.dataset.swalConfirmed = '1';
            form.submit();
        });
    });
}

function initCoupon(page, couponUrl, csrf) {
    const form = page.querySelector('[data-coupon-form]');
    const msg = page.querySelector('[data-coupon-msg]');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const code = form.querySelector('[name="coupon"]')?.value || '';
        try {
            const data = await postJson(couponUrl, { coupon: code }, csrf);
            if (msg) {
                msg.textContent = data.message || '';
                msg.dataset.success = data.success ? '1' : '0';
            }
            if (data.cart) applySummary(page, data.cart);
        } catch {
            if (msg) {
                msg.textContent = 'Unable to apply coupon. Please try again.';
                msg.dataset.success = '0';
            }
        }
    });
}

function initGift(page, giftUrl, csrf) {
    const openBtn = page.querySelector('[data-gift-open]');
    const panel = page.querySelector('#giftMessagePanel');
    const saveBtn = page.querySelector('[data-gift-save]');
    if (!openBtn || !panel) return;

    openBtn.addEventListener('click', () => {
        const open = panel.hasAttribute('hidden');
        if (open) panel.removeAttribute('hidden');
        else panel.setAttribute('hidden', '');
        openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    saveBtn?.addEventListener('click', async () => {
        const message = panel.querySelector('#gift-message')?.value || '';
        try {
            const data = await postJson(giftUrl, { gift_message: message }, csrf);
            if (data.success) {
                panel.setAttribute('hidden', '');
                openBtn.setAttribute('aria-expanded', 'false');
                const copy = openBtn.querySelector('small');
                if (copy && message.trim()) copy.textContent = 'Gift message saved';
            }
        } catch {
            // ignore
        }
    });
}
