/**
 * Geetanjali Jewellers — frontend JS
 * Loaded from public/assets/js (Laravel asset()).
 * Page scripts are included from the layout after Bootstrap.
 */
(function () {
    const listingIds = ['collection-products', 'bridal-products'];
    const listingQueryNames = ['page', 'sort', 'view', 'price', 'min_price', 'max_price'];
    const listingQueryPrefixes = ['type', 'category', 'metal', 'stone', 'occasion'];

    function hasListingQuery(params) {
        for (let i = 0; i < listingQueryNames.length; i += 1) {
            if (params.has(listingQueryNames[i])) {
                return true;
            }
        }

        for (const [key] of params.entries()) {
            const name = key.replace(/\[\]$/, '').replace(/\[\d+\]$/, '');
            if (listingQueryPrefixes.indexOf(name) !== -1) {
                return true;
            }
        }

        return false;
    }

    function shouldPinCollectionListing() {
        if (!listingEl()) {
            return false;
        }
        const params = new URLSearchParams(window.location.search);
        const hash = (window.location.hash || '').replace(/^#/, '');
        return listingIds.indexOf(hash) !== -1 || hasListingQuery(params);
    }

    if (shouldPinCollectionListing() && 'scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    function listingEl() {
        for (let i = 0; i < listingIds.length; i += 1) {
            const el = document.getElementById(listingIds[i]);
            if (el) {
                return el;
            }
        }
        return null;
    }

    function pinCollectionListing() {
        if (!shouldPinCollectionListing()) {
            return;
        }

        const listing = listingEl();
        if (!listing) {
            return;
        }

        const header = document.querySelector('.site-header');
        const offset = header ? header.getBoundingClientRect().height : 88;
        const top = listing.getBoundingClientRect().top + window.pageYOffset - offset - 8;
        window.scrollTo(0, Math.max(0, top));
    }

    function schedulePinCollectionListing() {
        pinCollectionListing();
        window.requestAnimationFrame(pinCollectionListing);
        window.setTimeout(pinCollectionListing, 60);
        window.setTimeout(pinCollectionListing, 280);
    }

    function ensureCollectionPaginationHash(link) {
        if (!link || !listingEl()) {
            return;
        }
        if (!link.closest('.pagination, .kundan-pagination, .bridal-pagination, .na-pagination')) {
            return;
        }

        try {
            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin) {
                return;
            }
            if (!url.hash) {
                url.hash = 'collection-products';
                link.setAttribute('href', url.toString());
            }
        } catch (err) {
            // ignore invalid href
        }
    }

    const skipLinkSelector = [
        '[data-no-loading]',
        '[data-bs-toggle]',
        '[data-bs-dismiss]',
        '[data-add-to-cart]',
        '[data-buy-now]',
        '[data-wishlist-toggle]',
        '[data-quick-view]',
        '[data-quick-view-close]',
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
            pinCollectionListing();
            return;
        }
        loader.classList.remove('is-active', 'active');
        loader.setAttribute('aria-busy', 'false');
        loader.setAttribute('aria-hidden', 'true');
        pinCollectionListing();
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
        if (!link) {
            return;
        }

        ensureCollectionPaginationHash(link);

        try {
            const url = new URL(link.href, window.location.href);
            if (url.origin === window.location.origin && isSamePage(url) && !url.hash) {
                event.preventDefault();
                window.scrollTo(0, 0);
                return;
            }
        } catch (err) {
            // ignore invalid href
        }

        if (shouldIgnoreLink(link)) {
            return;
        }

        showLoader();
    }, true);

    document.addEventListener('change', (event) => {
        const field = event.target;
        if (!(field instanceof HTMLElement) || !field.hasAttribute('data-auto-submit')) {
            return;
        }

        const form = field.closest('form');
        if (!(form instanceof HTMLFormElement) || form.dataset.listingSubmitting === '1') {
            return;
        }

        form.dataset.listingSubmitting = '1';
        const action = form.getAttribute('action') || '';
        const hashAt = action.indexOf('#');
        if (hashAt !== -1) {
            form.setAttribute('action', action.slice(0, hashAt));
        }
        form.submit();
    });

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
        schedulePinCollectionListing();
    } else {
        window.addEventListener('load', () => {
            hideReady();
            schedulePinCollectionListing();
        });
    }

    document.addEventListener('DOMContentLoaded', schedulePinCollectionListing);

    window.setTimeout(hideLoader, 8000);
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            hideLoader();
            schedulePinCollectionListing();
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
