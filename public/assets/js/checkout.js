/**
 * Logged-in checkout: address/payment cards, shipping total, address modal
 */
document.addEventListener('DOMContentLoaded', () => {
    const checkoutPage = document.querySelector('[data-checkout-page]');
    const accountAddresses = document.querySelector('[data-account-addresses]');

    if (checkoutPage) {
        const selectCard = (input) => {
            const group = input.closest('.checkout-card, .checkout-address-list') || checkoutPage;
            const name = input.name;
            group.querySelectorAll(`input[name="${name}"]`).forEach((el) => {
                el.closest('.address-card, .method-card')?.classList.toggle('is-selected', el === input && el.checked);
            });
            if (name === 'address_id') {
                checkoutPage.querySelectorAll('.address-card').forEach((card) => {
                    const radio = card.querySelector('input[name="address_id"]');
                    card.classList.toggle('is-selected', Boolean(radio?.checked));
                });
            }
        };

        checkoutPage.querySelectorAll('.address-card input, .method-card input').forEach((input) => {
            input.addEventListener('change', () => {
                selectCard(input);
                if (input.matches('[data-shipping-option]')) {
                    updateShipping(checkoutPage, input.value);
                }
            });
        });

        initContactModal();
        initAddressModal(checkoutPage);
    }

    if (accountAddresses) {
        initAddressModal(accountAddresses);
    }

    if (checkoutPage || accountAddresses) {
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('.checkout-modal:not([hidden])').forEach((modal) => {
                closeCheckoutModal(modal);
            });
        });
    }
});

function lockCheckoutModal(open) {
    document.body.classList.toggle('is-checkout-modal-open', open);
}

function openCheckoutModal(modal) {
    document.body.appendChild(modal);
    modal.hidden = false;
    lockCheckoutModal(true);
}

function closeCheckoutModal(modal) {
    modal.hidden = true;
    const stillOpen = document.querySelector('.checkout-modal:not([hidden])');
    lockCheckoutModal(Boolean(stillOpen));
}

function formatMoney(value) {
    return '₹' + Number(value || 0).toLocaleString('en-IN');
}

function updateShipping(page, method) {
    const base = Number(page.dataset.baseTotal || 0);
    const express = Number(page.dataset.expressCharge || 0);
    const charge = method === 'express' ? express : 0;
    const label = page.querySelector('[data-shipping-label]');
    const total = page.querySelector('[data-order-total]');
    if (label) label.textContent = charge > 0 ? formatMoney(charge) : 'FREE';
    if (total) total.textContent = formatMoney(base + charge);
}

function initContactModal() {
    const modal = document.querySelector('[data-contact-modal]');
    if (!modal) return;
    document.querySelector('[data-contact-toggle]')?.addEventListener('click', () => {
        openCheckoutModal(modal);
    });
    document.querySelector('[data-contact-close]')?.addEventListener('click', () => {
        closeCheckoutModal(modal);
    });
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeCheckoutModal(modal);
    });
}

function initAddressModal(page) {
    const modal = document.querySelector('[data-address-modal]');
    const form = document.querySelector('[data-address-form]');
    if (!modal || !form) return;

    const storeUrl = form.dataset.storeUrl || form.getAttribute('action');
    const methodInput = form.querySelector('[data-address-method]');
    const title = document.querySelector('[data-address-modal-title]');
    let addressMap = {};
    try {
        const blob = document.getElementById('checkout-address-data');
        addressMap = JSON.parse(blob?.textContent || '{}') || {};
    } catch (err) {
        addressMap = {};
    }

    const open = () => openCheckoutModal(modal);
    const close = () => closeCheckoutModal(modal);

    const fill = (data = {}) => {
        const setValue = (field, value) => {
            const el = form.querySelector(`[data-field="${field}"]`);
            if (!el) return;
            if (el.type === 'checkbox') {
                el.checked = value === true || value === 1 || value === '1';
                return;
            }
            el.value = value == null ? '' : String(value);
        };

        const label = String(data.label || 'home').toLowerCase();
        setValue('label', ['home', 'office', 'other'].includes(label) ? label : 'home');
        setValue('name', data.name || '');
        setValue('phone', data.phone || '');
        setValue('address_line1', data.address_line1 || '');
        setValue('address_line2', data.address_line2 || '');
        setValue('city', data.city || '');
        setValue('state', data.state || '');
        setValue('pincode', data.pincode || '');
        setValue('is_default', data.is_default);
    };

    const readAddressData = (btn) => {
        const id = String(btn.getAttribute('data-id') || '');
        if (id && addressMap[id]) {
            return addressMap[id];
        }

        const raw = btn.getAttribute('data-address') || '';
        if (raw) {
            try {
                const parsed = JSON.parse(raw);
                if (parsed && parsed.id) return parsed;
            } catch (err) {
                /* ignore invalid JSON */
            }
        }

        return {};
    };

    document.querySelector('[data-address-add]')?.addEventListener('click', () => {
        form.action = storeUrl;
        methodInput.value = 'POST';
        title.textContent = 'Add New Address';
        fill({
            label: 'home',
            name: form.dataset.defaultName || '',
            phone: form.dataset.defaultPhone || '',
            address_line1: '',
            address_line2: '',
            city: '',
            state: '',
            pincode: '',
            is_default: false,
        });
        open();
    });

    page.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-address-edit]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        const data = readAddressData(btn);
        if (!data.id) return;
        form.action = storeUrl.replace(/\/?$/, '') + '/' + data.id;
        methodInput.value = 'PUT';
        title.textContent = 'Edit Address';
        fill(data);
        open();
    });

    document.querySelector('[data-address-close]')?.addEventListener('click', close);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) close();
    });
}
