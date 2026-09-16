<div
    class="quick-view"
    data-quick-view-modal
    hidden
>
    <div class="quick-view__backdrop" data-quick-view-close></div>
    <div
        class="quick-view__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quick-view-title"
        data-quick-view-dialog
    >
        <button type="button" class="quick-view__close" data-quick-view-close aria-label="Close quick view">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
        <div class="quick-view__grid">
            <div class="quick-view__media">
                <img src="" alt="" data-qv-image width="480" height="480">
            </div>
            <div class="quick-view__info">
                <h2 id="quick-view-title" class="quick-view__title" data-qv-name></h2>
                <p class="quick-view__meta" data-qv-meta></p>
                <div class="quick-view__pricing">
                    <span class="price-current" data-qv-price></span>
                    <span class="price-original" data-qv-compare></span>
                    <span class="price-discount" data-qv-discount></span>
                </div>
                <div class="quick-view__actions">
                    <button
                        type="button"
                        class="btn-add-cart-lg"
                        data-add-to-cart
                        data-qv-cart
                        data-no-loading
                    >
                        <i class="bi bi-bag" aria-hidden="true"></i>
                        Add to Cart
                    </button>
                    <a class="quick-view__details" href="#" data-qv-link>
                        View Full Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
