/**
 * Kundan collection page — filters, drawer, cart/wishlist UI
 */

document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-kundan-page]');
    if (!page) return;

    initPriceSliders();
    initFilterDrawer();
    initAutoSubmitSort();
});

function formatInr(value) {
    return '₹' + Number(value).toLocaleString('en-IN');
}

function initPriceSliders() {
    document.querySelectorAll('[data-price-range]').forEach((wrap) => {
        const minInput = wrap.querySelector('[data-price-min]');
        const maxInput = wrap.querySelector('[data-price-max]');
        const minLabel = wrap.querySelector('[data-price-min-label]');
        const maxLabel = wrap.querySelector('[data-price-max-label]');
        const fill = wrap.querySelector('[data-price-range-fill]');
        if (!minInput || !maxInput) return;

        const boundMin = Number(minInput.min);
        const boundMax = Number(minInput.max);

        const sync = () => {
            let minVal = Number(minInput.value);
            let maxVal = Number(maxInput.value);
            if (minVal > maxVal) {
                if (document.activeElement === minInput) {
                    maxInput.value = String(minVal);
                    maxVal = minVal;
                } else {
                    minInput.value = String(maxVal);
                    minVal = maxVal;
                }
            }
            if (minLabel) minLabel.textContent = formatInr(minVal);
            if (maxLabel) maxLabel.textContent = formatInr(maxVal);
            if (fill) {
                const left = ((minVal - boundMin) / (boundMax - boundMin)) * 100;
                const right = ((maxVal - boundMin) / (boundMax - boundMin)) * 100;
                fill.style.left = `${left}%`;
                fill.style.width = `${Math.max(0, right - left)}%`;
            }
        };

        minInput.addEventListener('input', sync);
        maxInput.addEventListener('input', sync);
        sync();
    });
}

function initFilterDrawer() {
    const drawer = document.querySelector('[data-filter-drawer]');
    if (!drawer) return;

    const openBtns = document.querySelectorAll('[data-filter-open]');
    const closeEls = drawer.querySelectorAll('[data-filter-close]');

    const open = () => {
        drawer.hidden = false;
        requestAnimationFrame(() => drawer.classList.add('is-open'));
        document.body.style.overflow = 'hidden';
        openBtns.forEach((btn) => btn.setAttribute('aria-expanded', 'true'));
    };

    const close = () => {
        drawer.classList.remove('is-open');
        openBtns.forEach((btn) => btn.setAttribute('aria-expanded', 'false'));
        document.body.style.overflow = '';
        setTimeout(() => {
            if (!drawer.classList.contains('is-open')) drawer.hidden = true;
        }, 280);
    };

    openBtns.forEach((btn) => btn.addEventListener('click', open));
    closeEls.forEach((el) => el.addEventListener('click', close));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
    });
}

function initAutoSubmitSort() {
    document.querySelectorAll('[data-auto-submit]').forEach((select) => {
        select.addEventListener('change', () => {
            select.closest('form')?.submit();
        });
    });
}
