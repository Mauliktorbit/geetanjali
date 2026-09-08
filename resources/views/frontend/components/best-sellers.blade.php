@props([
    'tabs' => [],
    'productsByTab' => [],
])

@php
    $firstTab = $tabs[0] ?? null;
@endphp

<section class="section best-sellers" id="bestsellers" aria-labelledby="bestsellers-heading">
    <div class="site-container">
        <h2 id="bestsellers-heading" class="section-title reveal">Best Sellers</h2>
        <div class="section-divider reveal" aria-hidden="true"></div>

        <div class="product-tabs" role="tablist" aria-label="Best seller collections">
            @foreach ($tabs as $index => $tab)
                <button
                    type="button"
                    role="tab"
                    data-product-tab="{{ $tab['key'] }}"
                    data-view-all-url="{{ $tab['view_all_url'] ?? '#' }}"
                    data-view-all-label="{{ $tab['view_all_label'] ?? 'View All' }}"
                    class="{{ $index === 0 ? 'is-active' : '' }}"
                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                >
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>

        @foreach ($tabs as $index => $tab)
            <div
                class="product-grid {{ $index === 0 ? '' : 'd-none' }}"
                data-product-panel="{{ $tab['key'] }}"
                role="tabpanel"
                aria-label="{{ $tab['label'] }}"
            >
                @foreach (($productsByTab[$tab['key']] ?? []) as $product)
                    <div class="reveal">
                        @include('frontend.components.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="best-sellers__cta reveal">
            <a
                href="{{ $firstTab['view_all_url'] ?? '#' }}"
                class="btn-outline-gold"
                data-bestsellers-view-all
            >
                {{ $firstTab['view_all_label'] ?? 'View All' }}
            </a>
        </div>
    </div>
</section>
