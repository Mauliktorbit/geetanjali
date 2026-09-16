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
    const input = form?.querySelector('[name="coupon"]');
    const list = page.querySelector('[data-coupon-list]');
    const toggle = page.querySelector('[data-coupon-toggle]');
    if (!form) return;

    const setMessage = (text, ok) => {
        if (!msg) return;
        msg.textContent = text || '';
        if (ok == null) {
            delete msg.dataset.success;
            return;
        }
        msg.dataset.success = ok ? '1' : '0';
    };

    const markApplied = (code) => {
        const applied = String(code || '').toUpperCase();
        page.querySelectorAll('[data-coupon-card]').forEach((card) => {
            const cardCode = (card.dataset.couponCode || '').toUpperCase();
            const isOn = applied !== '' && cardCode === applied;
            card.classList.toggle('is-applied', isOn);
            const btn = card.querySelector('[data-coupon-apply], [data-coupon-remove]');
            if (!btn || btn.disabled) return;
            if (isOn) {
                btn.textContent = 'Remove';
                btn.removeAttribute('data-coupon-apply');
                btn.setAttribute('data-coupon-remove', '');
            } else {
                btn.textContent = 'Apply';
                btn.removeAttribute('data-coupon-remove');
                btn.setAttribute('data-coupon-apply', card.dataset.couponCode || '');
            }
        });
    };

    const submitCode = async (code) => {
        try {
            const data = await postJson(couponUrl, { coupon: code }, csrf);
            setMessage(data.message || '', Boolean(data.success));
            if (data.cart) applySummary(page, data.cart);
            if (input) input.value = data.code || code || '';
            if (data.success) {
                markApplied(data.code || code);
            }
            return data;
        } catch {
            setMessage('Unable to apply coupon. Please try again.', false);
            return null;
        }
    };

    toggle?.addEventListener('click', () => {
        if (!list) return;
        const open = list.hasAttribute('hidden');
        if (open) {
            list.removeAttribute('hidden');
        } else {
            list.setAttribute('hidden', '');
        }
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        const label = toggle.querySelector('[data-coupon-toggle-label]');
        if (label) label.textContent = open ? 'Hide coupons' : 'View all coupons';
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await submitCode(input?.value || '');
    });

    page.querySelector('[data-cart-coupons]')?.addEventListener('click', async (event) => {
        const applyBtn = event.target.closest('[data-coupon-apply]');
        if (applyBtn) {
            const code = applyBtn.getAttribute('data-coupon-apply') || '';
            if (input) input.value = code;
            await submitCode(code);
            return;
        }

        const removeBtn = event.target.closest('[data-coupon-remove]');
        if (removeBtn) {
            if (input) input.value = '';
            await submitCode('');
            markApplied('');
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
