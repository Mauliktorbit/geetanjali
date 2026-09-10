(function () {
    'use strict';

    var eye =
        '<svg class="password-toggle__icon password-toggle__icon--show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    var eyeOff =
        '<svg class="password-toggle__icon password-toggle__icon--hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"/><path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.83 21.83 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

    function findInput(btn) {
        var wrap = btn.closest('.password-field, .auth-input');
        if (!wrap) {
            return null;
        }

        return wrap.querySelector('input[data-password-input], input[type="password"], input[type="text"]');
    }

    function setState(input, btn, show) {
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        btn.classList.toggle('is-visible', show);

        var wrap = btn.closest('.password-field, .auth-input');
        if (wrap) {
            wrap.classList.toggle('is-visible', show);
        }

        var icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('bi-eye', show);
            icon.classList.toggle('bi-eye-slash', !show);
        }
    }

    function enhance(input) {
        if (input.dataset.passwordReady === '1') {
            return;
        }

        if (input.closest('.auth-input') && input.closest('.auth-input').querySelector('[data-toggle-password]')) {
            input.dataset.passwordReady = '1';
            return;
        }

        if (input.closest('.password-field')) {
            input.dataset.passwordReady = '1';
            return;
        }

        var wrap = document.createElement('div');
        wrap.className = 'password-field';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'password-field__toggle';
        btn.setAttribute('data-toggle-password', '');
        btn.setAttribute('aria-label', 'Show password');
        btn.innerHTML = eye + eyeOff;
        wrap.appendChild(btn);

        input.dataset.passwordReady = '1';
    }

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-toggle-password], .password-field__toggle');
        if (!btn) {
            return;
        }

        var input = findInput(btn);
        if (!input) {
            return;
        }

        event.preventDefault();
        setState(input, btn, input.type === 'password');
    });

    function scan() {
        document.querySelectorAll('input[type="password"]').forEach(enhance);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scan);
    } else {
        scan();
    }
})();
