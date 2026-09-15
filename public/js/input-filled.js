/**
 * Mark text fields as filled so the background can change after typing.
 * Used on admin and storefront.
 */
(function () {
    'use strict';

    var skipTypes = {
        checkbox: true,
        radio: true,
        hidden: true,
        file: true,
        button: true,
        submit: true,
        reset: true,
        image: true,
        color: true,
        range: true,
    };

    function isFillable(el) {
        if (!el || el.nodeType !== 1) {
            return false;
        }

        var tag = el.tagName;
        if (tag === 'TEXTAREA' || tag === 'SELECT') {
            return true;
        }

        return tag === 'INPUT' && !skipTypes[el.type];
    }

    function isFilled(el) {
        if (el.tagName === 'SELECT') {
            var option = el.options[el.selectedIndex];
            if (!option) {
                return false;
            }
            return String(option.value || '').trim() !== '';
        }

        return String(el.value || '').trim() !== '';
    }

    function sync(el) {
        if (!isFillable(el)) {
            return;
        }

        el.classList.toggle('is-filled', isFilled(el));
    }

    function scan(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('input, textarea, select').forEach(sync);
    }

    function onEvent(event) {
        sync(event.target);
    }

    document.addEventListener('input', onEvent, true);
    document.addEventListener('change', onEvent, true);
    document.addEventListener('blur', onEvent, true);
    document.addEventListener('keyup', onEvent, true);

    function boot() {
        scan(document);

        if (typeof MutationObserver === 'undefined') {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) {
                        return;
                    }
                    if (isFillable(node)) {
                        sync(node);
                    }
                    if (node.querySelectorAll) {
                        scan(node);
                    }
                });
            });
        });

        observer.observe(document.documentElement, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
