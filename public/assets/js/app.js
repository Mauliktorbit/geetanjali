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

    function listingUrlFromForm(form) {
        const url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
        url.hash = '';
        const params = new URLSearchParams();
        new FormData(form).forEach((value, key) => {
            if (String(value).trim() === '') {
                return;
            }
            params.append(key, String(value));
        });
        url.search = params.toString();
        return url;
    }

    function submitListingForm(form) {
        window.location.replace(listingUrlFromForm(form).toString());
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
        submitListingForm(form);
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
