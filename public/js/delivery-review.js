/**
 * Step-by-step delivery → review popup for logged-in customers.
 */
(function () {
  'use strict';

  const root = document.getElementById('delivery-review-modal');
  const cfg = window.Geetanjali || {};
  const data = cfg.deliveryReview;
  if (!root || !data || !data.order_id || !Array.isArray(data.products) || !data.products.length) {
    return;
  }

  const laterKey = 'gj-review-later-' + data.order_id;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const form = document.getElementById('delivery-review-form');
  const errorEl = root.querySelector('[data-dr-error]');
  const progressEl = root.querySelector('[data-dr-progress]');
  const nameEl = root.querySelector('[data-dr-name]');
  const imageEl = root.querySelector('[data-dr-image]');
  const thanksEl = root.querySelector('[data-dr-thanks]');
  let index = 0;

  function step(name) {
    root.querySelectorAll('[data-dr-step]').forEach((el) => {
      el.hidden = el.getAttribute('data-dr-step') !== name;
    });
  }

  function open() {
    root.hidden = false;
    document.body.classList.add('dr-modal-open');
    step('delivered');
  }

  function close() {
    root.hidden = true;
    document.body.classList.remove('dr-modal-open');
  }

  function fillProduct() {
    const product = data.products[index];
    if (!product || !form) return;
    form.product_id.value = product.id;
    if (nameEl) nameEl.textContent = product.name || 'Jewellery';
    if (imageEl) {
      imageEl.src = product.image || '';
      imageEl.alt = product.name || '';
    }
    const total = data.products.length;
    if (progressEl) {
      progressEl.textContent = total > 1
        ? 'Step 2 of 3 · Item ' + (index + 1) + ' of ' + total
        : 'Step 2 of 3';
    }
    form.reset();
    form.order_id.value = data.order_id;
    form.product_id.value = product.id;
    if (errorEl) {
      errorEl.hidden = true;
      errorEl.textContent = '';
    }
  }

  async function later() {
    try {
      sessionStorage.setItem(laterKey, '1');
    } catch (err) {
      // ignore private mode
    }
    try {
      await fetch(data.later_url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ order_id: data.order_id }),
      });
    } catch (err) {
      // still close so it is not blocking
    }
    close();
  }

  function showError(message) {
    if (!errorEl) return;
    errorEl.textContent = message || 'Please try again.';
    errorEl.hidden = false;
  }

  root.addEventListener('click', (e) => {
    if (e.target.closest('[data-dr-later]')) {
      later();
      return;
    }
    if (e.target.closest('[data-dr-next]')) {
      fillProduct();
      step('review');
      return;
    }
    if (e.target.closest('[data-dr-close]')) {
      close();
    }
  });

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const rating = form.querySelector('input[name="rating"]:checked');
      if (!rating) {
        showError('Please choose a star rating.');
        return;
      }

      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const res = await fetch(data.store_url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({
            order_id: Number(form.order_id.value),
            product_id: Number(form.product_id.value),
            rating: Number(rating.value),
          }),
        });
        const payload = await res.json().catch(() => ({}));
        if (!res.ok || !payload.ok) {
          const firstError = payload.errors ? Object.values(payload.errors)[0] : null;
          showError((Array.isArray(firstError) ? firstError[0] : payload.message) || 'Could not save your review.');
          return;
        }

        index += 1;
        if (index < data.products.length) {
          fillProduct();
          step('review');
          return;
        }

        if (thanksEl && payload.message) thanksEl.textContent = payload.message;
        try {
          sessionStorage.setItem(laterKey, '1');
        } catch (err) {
          // ignore
        }
        step('thanks');
      } catch (err) {
        showError('Could not save your review. Please try again.');
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  let dismissed = false;
  try {
    dismissed = sessionStorage.getItem(laterKey) === '1';
  } catch (err) {
    dismissed = false;
  }

  if (dismissed) return;

  window.setTimeout(open, 700);
})();
