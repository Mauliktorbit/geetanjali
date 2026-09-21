@props(['view' => 'grid'])

@php
    $view = $view === 'list' ? 'list' : 'grid';
@endphp

<div class="listing-view-toggle d-none d-lg-inline-flex" role="group" aria-label="Product layout">
    <a
        href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}#collection-products"
        class="listing-view-btn {{ $view === 'grid' ? 'is-active' : '' }}"
        aria-label="Grid view"
        aria-current="{{ $view === 'grid' ? 'page' : 'false' }}"
    >
        <i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i>
    </a>
    <a
        href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}#collection-products"
        class="listing-view-btn {{ $view === 'list' ? 'is-active' : '' }}"
        aria-label="List view"
        aria-current="{{ $view === 'list' ? 'page' : 'false' }}"
    >
        <i class="bi bi-list" aria-hidden="true"></i>
    </a>
</div>
