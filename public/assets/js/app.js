/**
 * Geetanjali Jewellers — frontend JS
 * Loaded from public/assets/js (Laravel asset()).
 * Page scripts are included from the layout after Bootstrap.
 */
(function () {
    const skipLinkSelector = [
        '[data-no-loading]',
        '[data-bs-toggle]',
        '[data-bs-dismiss]',
        '[data-add-to-cart]',
        '[data-buy-now]',
        '[data-wishlist-toggle]',
        '[download]',
    ].join(',');

    function loaderEl() {
        return document.getElementById('site-page-loader');
    }

    function showLoader() {
        const loader = loaderEl();
        if (!loader) {
            return;
        }
        loader.classList.add('is-active', 'active');
        loader.setAttribute('aria-busy', 'true');
        loader.setAttribute('aria-hidden', 'false');
    }

    function hideLoader() {
        const loader = loaderEl();
        if (!loader) {
            return;
        }
        loader.classList.remove('is-active', 'active');
        loader.setAttribute('aria-busy', 'false');
        loader.setAttribute('aria-hidden', 'true');
    }

    window.SiteLoader = { show: showLoader, hide: hideLoader };

    function isSamePage(url) {
        return url.pathname === window.location.pathname && url.search === window.location.search;
    }

    function shouldIgnoreLink(link) {
        if (!link || link.closest(skipLinkSelector)) {
            return true;
        }
        if (link.target === '_blank') {
            return true;
        }

        const href = (link.getAttribute('href') || '').trim();
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) {
            return true;
        }
        if (href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) {
            return true;
        }

        try {
            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin) {
                return true;
            }
            if (isSamePage(url) && url.hash) {
                return true;
            }
            if (isSamePage(url)) {
                return true;
            }
        } catch (err) {
            return true;
        }

        return false;
    }

    document.addEventListener('click', (event) => {
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const link = event.target.closest('a[href]');
        if (!link || shouldIgnoreLink(link)) {
            return;
        }

        showLoader();
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        if (form.hasAttribute('data-no-loading') || form.closest('[data-no-loading]')) {
            return;
        }
        if ((form.getAttribute('target') || '') === '_blank') {
            return;
        }
        showLoader();
    }, true);

    const hideReady = () => window.setTimeout(hideLoader, 220);

    if (document.readyState === 'complete') {
        hideReady();
    } else {
        window.addEventListener('load', hideReady);
    }

    window.setTimeout(hideLoader, 8000);
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            hideLoader();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            window.csrfToken = token;
        }

        const mobileNav = document.getElementById('mobileNav');
        if (mobileNav) {
            mobileNav.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (!link || link.hasAttribute('data-bs-dismiss')) {
                    return;
                }

                const href = link.getAttribute('href');
                if (!href || href === '#') {
                    return;
                }

                if (!shouldIgnoreLink(link)) {
                    showLoader();
                }

                event.preventDefault();
                window.location.assign(link.href);
            });
        }
    });
})();
