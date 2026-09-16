/**
 * Geetanjali Jewellers — frontend interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    initStickyHeader();
    initHeroCarousel();
    initCategorySlider();
    initRevealOnScroll();
    initProductTabs();
    initTestimonialDots();
});

function initHeroCarousel() {
    const el = document.getElementById('heroCarousel');
    if (!el || typeof bootstrap === 'undefined') return;

    const existing = bootstrap.Carousel.getInstance(el);
    if (existing) {
        existing.dispose();
    }

    const carousel = new bootstrap.Carousel(el, {
        interval: 5000,
        ride: 'carousel',
        pause: 'hover',
        wrap: true,
        touch: true,
    });

    carousel.cycle();
}

function initCategorySlider() {
    const track = document.querySelector('[data-category-slider]');
    if (!track || typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.slick === 'undefined') {
        return;
    }

    const $track = window.jQuery(track);
    if ($track.hasClass('slick-initialized')) {
        $track.slick('unslick');
    }

    $track.slick({
        slidesToShow: 8,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2200,
        speed: 500,
        infinite: true,
        arrows: false,
        dots: false,
        pauseOnHover: true,
        pauseOnFocus: true,
        swipe: true,
        touchMove: true,
        cssEase: 'ease',
        responsive: [
            { breakpoint: 1200, settings: { slidesToShow: 6 } },
            { breakpoint: 992, settings: { slidesToShow: 5 } },
            { breakpoint: 768, settings: { slidesToShow: 4 } },
            { breakpoint: 576, settings: { slidesToShow: 3 } },
            { breakpoint: 400, settings: { slidesToShow: 2 } },
        ],
    });
}

function initStickyHeader() {
    const header = document.querySelector('[data-site-header]');
    if (!header) return;

    const onScroll = () => {
        header.classList.toggle('is-sticky', window.scrollY > 24);
    };

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

function initRevealOnScroll() {
    const items = document.querySelectorAll('.reveal');
    if (!items.length || !('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    items.forEach((el) => observer.observe(el));
}

function initProductTabs() {
    const root = document.querySelector('#bestsellers');
    if (!root) return;

    const tabs = root.querySelectorAll('[data-product-tab]');
    const panels = root.querySelectorAll('[data-product-panel]');
    const viewAll = root.querySelector('[data-bestsellers-view-all]');
    if (!tabs.length || !panels.length) return;

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-product-tab');

            tabs.forEach((t) => {
                t.classList.toggle('is-active', t === tab);
                t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
            });

            panels.forEach((panel) => {
                const match = panel.getAttribute('data-product-panel') === target;
                panel.classList.toggle('d-none', !match);
            });

            if (viewAll) {
                const url = tab.getAttribute('data-view-all-url');
                const label = tab.getAttribute('data-view-all-label');
                if (url) viewAll.setAttribute('href', url);
                if (label) viewAll.textContent = label;
            }
        });
    });
}

function initTestimonialDots() {
    const dots = document.querySelectorAll('[data-testimonial-dot]');
    const cards = document.querySelectorAll('[data-testimonial-card]');
    if (!dots.length || !cards.length) return;

    const mq = window.matchMedia('(max-width: 767.98px)');

    const apply = (index = 0) => {
        if (!mq.matches) {
            cards.forEach((card) => card.classList.remove('d-none'));
            dots.forEach((dot, i) => dot.classList.toggle('is-active', i === 0));
            return;
        }

        cards.forEach((card, i) => card.classList.toggle('d-none', i !== index));
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === index));
    };

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => apply(index));
    });

    apply(0);
    mq.addEventListener('change', () => apply(0));
}
