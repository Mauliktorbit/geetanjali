/**
 * SweetAlert2 replacements for native alert/confirm across admin + storefront.
 */
(function () {
  'use strict';

  const COLORS = {
    brand: '#16352D',
    gold: '#C98B19',
    danger: '#b42318',
    cancel: '#6b7280',
  };

  function injectStyles() {
    if (document.getElementById('gj-swal-styles')) return;
    const style = document.createElement('style');
    style.id = 'gj-swal-styles';
    style.textContent = `
      .gj-swal.swal2-popup {
        font-family: Poppins, "Segoe UI", sans-serif;
        border-radius: 18px;
        padding: 1.6rem 1.4rem 1.35rem;
      }
      .gj-swal .swal2-title {
        font-family: "Cormorant Garamond", Georgia, serif;
        color: ${COLORS.brand};
        font-size: 1.7rem;
        font-weight: 600;
      }
      .gj-swal .swal2-html-container {
        color: #4b5563;
        font-size: 0.95rem;
        line-height: 1.5;
      }
      .gj-swal .swal2-actions { gap: 0.6rem; }
      .gj-swal .swal2-styled { border-radius: 999px !important; padding: 0.65rem 1.35rem !important; font-weight: 600 !important; }
    `;
    document.head.appendChild(style);
  }

  function decodeMessage(raw) {
    return String(raw || '')
      .replace(/\\n/g, '\n')
      .replace(/\\'/g, "'")
      .replace(/\\"/g, '"')
      .replace(/&amp;/g, '&')
      .replace(/&quot;/g, '"')
      .replace(/&#039;/g, "'")
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>');
  }

  function extractConfirmMessage(attr) {
    if (!attr) return null;
    const match = String(attr).match(/confirm\s*\(\s*(['"`])([\s\S]*?)\1\s*\)/);
    return match ? decodeMessage(match[2]) : null;
  }

  function isDanger(el, message) {
    if (el && el.classList && typeof el.classList.contains === 'function') {
      if (el.classList.contains('btn-danger')) return true;
      if (typeof el.hasAttribute === 'function' && el.hasAttribute('data-confirm-danger')) return true;
    }
    return /\b(delete|remove|cancel|refund|reject|block|restore)\b/i.test(message || '');
  }

  function splitMessage(message) {
    const text = String(message || '').trim() || 'Are you sure you want to continue?';
    const q = text.indexOf('?');
    if (q > -1 && text.slice(q + 1).trim()) {
      return { title: text.slice(0, q + 1).trim(), text: text.slice(q + 1).trim() };
    }
    return { title: text.replace(/\?+$/, '') || 'Please confirm', text: '' };
  }

  function confirmButtonLabel(message, danger) {
    if (/\blog ?out\b/i.test(message) || /\bsign out\b/i.test(message)) return 'Yes, logout';
    if (/\bremove\b/i.test(message)) return 'Yes, remove';
    if (/\bdelete\b/i.test(message)) return 'Yes, delete';
    if (/\bcancel\b/i.test(message)) return 'Yes, cancel';
    return danger ? 'Yes, confirm' : 'Yes, continue';
  }

  function ready() {
    return typeof window.Swal !== 'undefined';
  }

  async function confirmDialog(message, el) {
    if (!ready()) return window.confirm(message);
    injectStyles();
    const danger = isDanger(el, message);
    const parts = splitMessage(message);
    const result = await window.Swal.fire({
      title: parts.title,
      text: parts.text,
      icon: danger ? 'warning' : 'question',
      showCancelButton: true,
      reverseButtons: true,
      focusCancel: true,
      confirmButtonText: confirmButtonLabel(message, danger),
      cancelButtonText: 'Cancel',
      confirmButtonColor: danger ? COLORS.danger : COLORS.brand,
      cancelButtonColor: COLORS.cancel,
      customClass: { popup: 'gj-swal' },
    });
    return result.isConfirmed;
  }

  function alertDialog(message, icon) {
    if (!ready()) {
      window.console.log(message);
      return Promise.resolve();
    }
    injectStyles();
    return window.Swal.fire({
      title: String(message || ''),
      icon: icon || 'info',
      confirmButtonText: 'OK',
      confirmButtonColor: COLORS.brand,
      customClass: { popup: 'gj-swal' },
    });
  }

  function toast(message, icon) {
    if (!ready()) return;
    window.Swal.fire({
      toast: true,
      position: 'top-end',
      icon: icon || 'success',
      title: String(message || ''),
      showConfirmButton: false,
      timer: 3500,
      timerProgressBar: true,
    });
  }

  function associatedForm(el) {
    if (!el) return null;
    if (el instanceof HTMLFormElement) return el;
    if (el.form) return el.form;
    const formId = el.getAttribute('form');
    return formId ? document.getElementById(formId) : el.closest('form');
  }

  function submitNow(el) {
    const form = associatedForm(el);
    if (form) {
      form.dataset.swalConfirmed = '1';
      if (el && el !== form) el.dataset.swalConfirmed = '1';
      if (typeof form.requestSubmit === 'function' && el && (el.tagName === 'BUTTON' || el.type === 'submit')) {
        form.requestSubmit(el);
      } else {
        form.submit();
      }
      return;
    }
    if (el && el.tagName === 'A' && el.href) {
      window.location.href = el.href;
    }
  }

  function messageFrom(el) {
    if (!el) return null;
    return (
      el.getAttribute('data-confirm') ||
      extractConfirmMessage(el.getAttribute('onclick')) ||
      extractConfirmMessage(el.getAttribute('onsubmit'))
    );
  }

  document.addEventListener(
    'click',
    async (e) => {
      if (!ready()) return;
      if (e.target.closest('.swal2-container')) return;
      const el = e.target.closest('button, input[type="submit"], a[onclick], [data-confirm]');
      if (!el || el.dataset.swalConfirmed === '1') return;
      const message = messageFrom(el);
      if (!message) return;

      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

      const ok = await confirmDialog(message, el);
      if (!ok) return;

      el.removeAttribute('onclick');
      const form = associatedForm(el);
      if (form) form.removeAttribute('onsubmit');
      submitNow(el);
    },
    true
  );

  document.addEventListener(
    'submit',
    async (e) => {
      if (!ready()) return;
      const form = e.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (form.dataset.swalConfirmed === '1') return;

      const submitter = e.submitter;
      if (submitter && submitter.dataset.swalConfirmed === '1') return;

      const message = messageFrom(submitter) || messageFrom(form);
      if (!message) return;

      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

      const ok = await confirmDialog(message, submitter || form);
      if (!ok) return;

      form.removeAttribute('onsubmit');
      if (submitter) submitter.removeAttribute('onclick');
      form.dataset.swalConfirmed = '1';
      if (typeof form.requestSubmit === 'function' && submitter) {
        submitter.dataset.swalConfirmed = '1';
        form.requestSubmit(submitter);
      } else {
        form.submit();
      }
    },
    true
  );

  window.alert = function (message) {
    alertDialog(message, 'info');
  };

  window.AppAlert = {
    confirm: (message, el) => confirmDialog(message, el),
    success: (message) => alertDialog(message, 'success'),
    error: (message) => alertDialog(message, 'error'),
    info: (message) => alertDialog(message, 'info'),
    toast,
    notice: (item) => {
      if (!ready()) return Promise.resolve();
      injectStyles();
      const payload = item || {};
      const isReturn = payload.type === 'return_requested';
      return window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon: isReturn ? 'warning' : 'success',
        title: payload.title || 'New alert',
        text: payload.message || '',
        showConfirmButton: true,
        confirmButtonText: 'View',
        showCloseButton: true,
        timer: 9000,
        timerProgressBar: true,
        confirmButtonColor: COLORS.brand,
        customClass: { popup: 'gj-swal gj-swal-notice', container: 'gj-swal-notice-wrap' },
      }).then((res) => {
        if (!res.isConfirmed || !payload.read_url) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = payload.read_url;
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = csrf;
        form.appendChild(token);
        document.body.appendChild(form);
        form.submit();
      });
    },
  };

  window.AdminConfirm = function (options) {
    const opts = options || {};
    const message = opts.message || opts.title || 'Are you sure?';
    confirmDialog(message, { classList: { contains: () => !!opts.danger }, hasAttribute: () => !!opts.danger }).then((ok) => {
      if (!ok) return;
      if (typeof opts.onConfirm === 'function') opts.onConfirm();
      else if (opts.form instanceof HTMLFormElement) opts.form.submit();
      else if (typeof opts.href === 'string') window.location.href = opts.href;
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    injectStyles();
    const flash = document.getElementById('admin-flash-data') || document.getElementById('app-flash-data');
    if (!flash || !ready()) return;
    try {
      const data = JSON.parse(flash.textContent || '{}');
      const shown = new Set();
      const showOnce = (message, icon) => {
        const text = String(message || '').trim();
        if (!text || shown.has(text)) return;
        shown.add(text);
        toast(text, icon);
      };
      if (data.success) showOnce(data.success, 'success');
      if (data.error) showOnce(data.error, 'error');
      if (data.warning) showOnce(data.warning, 'warning');
      if (data.info) showOnce(data.info, 'info');
      if (data.status) showOnce(data.status, 'success');
      if (Array.isArray(data.errors)) data.errors.forEach((msg) => showOnce(msg, 'error'));
    } catch (err) {
      // ignore malformed flash payload
    }
  });
})();
