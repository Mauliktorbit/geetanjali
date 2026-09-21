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

    function isSamePage(url) {
        return url.pathname === window.location.pathname && url.search === window.location.search;
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
            }
        } catch (err) {
            // ignore invalid href
        }
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

    if (document.readyState === 'complete') {
        schedulePinCollectionListing();
    } else {
        window.addEventListener('load', schedulePinCollectionListing);
    }

    document.addEventListener('DOMContentLoaded', schedulePinCollectionListing);

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
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

                event.preventDefault();
                window.location.assign(link.href);
            });
        }
    });
})();
