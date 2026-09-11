(function () {
    'use strict';

    var skipTypes = {
        password: true,
        email: true,
        file: true,
        checkbox: true,
        radio: true,
        hidden: true,
        submit: true,
        button: true,
        reset: true,
        color: true,
        date: true,
        'datetime-local': true,
        month: true,
        time: true,
        week: true,
        range: true,
        search: true,
        url: true,
    };

    var phoneKeys = { phone: true, mobile: true };
    var pinKeys = { pincode: true, pin: true, zip: true };
    var intKeys = { quantity: true, qty: true, stock: true };

    function lastKey(name) {
        if (!name) {
            return '';
        }

        var parts = String(name).toLowerCase().replace(/]/g, '').split(/\[|_/);
        return parts[parts.length - 1] || '';
    }

    function kindOf(el) {
        if (!el || el.tagName !== 'INPUT' || el.disabled || el.readOnly) {
            return null;
        }

        if (skipTypes[el.type]) {
            return null;
        }

        var explicit = (el.getAttribute('data-input-kind') || '').toLowerCase();
        if (explicit) {
            return explicit;
        }

        var name = (el.getAttribute('name') || '').toLowerCase();
        var id = (el.id || '').toLowerCase();
        var key = lastKey(name);
        var hay = name + ' ' + id;

        if (el.type === 'tel' || phoneKeys[key] || name === 'customer_phone' || name === 'store_phone') {
            return 'phone';
        }

        if (pinKeys[key] || /pincode|pin_code/.test(hay)) {
            return 'pincode';
        }

        if (key === 'otp' || id.indexOf('otp') !== -1) {
            return 'otp';
        }

        if (name === 'last_four') {
            return 'digits4';
        }

        if (el.type === 'number') {
            var step = el.getAttribute('step');
            if (step && step !== '1' && (step === 'any' || step.indexOf('.') !== -1)) {
                return 'decimal';
            }
            return 'integer';
        }

        if (intKeys[key]) {
            return 'digits';
        }

        return null;
    }

    function digits(value, max) {
        var out = String(value || '').replace(/\D/g, '');
        return max ? out.slice(0, max) : out;
    }

    function normalizeMobile(value) {
        var next = digits(value);

        if (next.length > 10 && next.indexOf('91') === 0) {
            next = next.slice(2);
        }

        if (next.length === 11 && next.charAt(0) === '0') {
            next = next.slice(1);
        }

        return next.slice(0, 10);
    }

    function limitLength(el, max) {
        if (el.maxLength < 0 || el.maxLength > max) {
            el.maxLength = max;
        }
        if (!el.getAttribute('inputmode')) {
            el.setAttribute('inputmode', 'numeric');
        }
    }

    function sanitizeNumberValue(el, allowDecimal) {
        var raw = el.value;
        if (raw === '' || raw === '-' || raw === '.') {
            return;
        }

        var next = allowDecimal ? raw.replace(/[^0-9.]/g, '') : raw.replace(/[^0-9-]/g, '');

        if (allowDecimal) {
            var parts = next.split('.');
            next = parts.shift() + (parts.length ? '.' + parts.join('') : '');
        } else {
            next = next.replace(/-/g, '');
            var min = el.getAttribute('min');
            if (min !== null && Number(min) < 0 && raw.charAt(0) === '-') {
                next = '-' + next;
            }
        }

        if (next !== raw) {
            el.value = next;
        }
    }

    function sanitize(el) {
        var kind = kindOf(el);
        if (!kind) {
            return;
        }

        if (kind === 'phone') {
            limitLength(el, 10);
            el.value = normalizeMobile(el.value);
            return;
        }

        if (kind === 'pincode') {
            limitLength(el, 6);
            el.value = digits(el.value, 6);
            return;
        }

        if (kind === 'otp') {
            limitLength(el, 6);
            el.value = digits(el.value, 6);
            return;
        }

        if (kind === 'digits4') {
            limitLength(el, 4);
            el.value = digits(el.value, 4);
            return;
        }

        if (kind === 'digits') {
            el.value = digits(el.value);
            return;
        }

        if (kind === 'datedmy') {
            limitLength(el, 10);
            var day = digits(el.value, 8);
            if (day.length > 4) {
                el.value = day.slice(0, 2) + '/' + day.slice(2, 4) + '/' + day.slice(4);
            } else if (day.length > 2) {
                el.value = day.slice(0, 2) + '/' + day.slice(2);
            } else {
                el.value = day;
            }
            return;
        }

        if (kind === 'integer' || kind === 'decimal') {
            sanitizeNumberValue(el, kind === 'decimal');
        }
    }

    function allowsMinus(el) {
        var min = el.getAttribute('min');
        return min !== null && Number(min) < 0 && el.selectionStart === 0 && el.value.indexOf('-') === -1;
    }

    function onKeydown(event) {
        var el = event.target;
        var kind = kindOf(el);
        if (!kind) {
            return;
        }

        if (event.ctrlKey || event.metaKey || event.altKey) {
            return;
        }

        if (event.key.length !== 1) {
            return;
        }

        if (kind === 'phone' || kind === 'pincode' || kind === 'otp' || kind === 'digits4' || kind === 'digits' || kind === 'integer' || kind === 'datedmy') {
            if (kind === 'integer' && event.key === '-' && allowsMinus(el)) {
                return;
            }
            if (!/[0-9]/.test(event.key)) {
                event.preventDefault();
            }
            return;
        }

        if (kind === 'decimal') {
            if (event.key === '.' && el.value.indexOf('.') === -1) {
                return;
            }
            if (event.key === '-' && allowsMinus(el)) {
                return;
            }
            if (!/[0-9]/.test(event.key)) {
                event.preventDefault();
            }
        }
    }

    function onPaste(event) {
        var el = event.target;
        if (!kindOf(el) || !event.clipboardData) {
            return;
        }

        event.preventDefault();
        var text = event.clipboardData.getData('text') || '';
        var start = el.selectionStart || 0;
        var end = el.selectionEnd || 0;
        el.value = el.value.slice(0, start) + text + el.value.slice(end);
        sanitize(el);
    }

    function onInput(event) {
        sanitize(event.target);
    }

    document.addEventListener('keydown', onKeydown, true);
    document.addEventListener('paste', onPaste, true);
    document.addEventListener('input', onInput, true);

    function scan() {
        document.querySelectorAll('input').forEach(sanitize);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scan);
    } else {
        scan();
    }
})();
