@extends('admin.layouts.app')
@section('title', $item->name)
@section('content')
@php
    $gallery = collect([$item->main_image])
        ->merge($item->gallery_images ?? [])
        ->filter()
        ->unique()
        ->values();
    if ($gallery->isEmpty()) {
        $gallery = collect([$item->imagePath()]);
    }
    $selling = $item->sale_price ?: $item->regular_price;
    $weightLabel = $item->weight !== null
        ? rtrim(rtrim(number_format((float) $item->weight, 3, '.', ''), '0'), '.').' g'
        : null;
    $specs = [
        'SKU' => $item->sku,
        'Category' => $item->category?->name,
        'Metal' => $item->metal,
        'Purity' => $item->purity,
        'Stone' => $item->stone,
        'Style' => $item->style,
        'Net weight' => $weightLabel,
        'Dimensions' => $item->dimensions_text,
        'Occasion' => $item->occasion,
        'Certification' => $item->certification,
        'Delivery' => $item->estimated_delivery,
    ];
@endphp

<div class="page-header">
    <div>
        <h1>{{ $item->name }}</h1>
        <p class="subtitle">How this product appears to customers</p>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Products','url'=>route('admin.products.index')], ['label'=>$item->name]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('products.show', $item->slug) }}" class="btn btn-ghost" target="_blank" rel="noopener">View on website</a>
        <a href="{{ route('admin.products.edit', $item) }}" class="btn btn-primary">Edit product</a>
        <form method="POST" action="{{ route($item->is_active ? 'admin.products.deactivate' : 'admin.products.activate', $item) }}">
            @csrf
            <button class="btn btn-secondary" type="submit" onclick="return confirm('Change publish status?')">
                {{ $item->is_active ? 'Unpublish' : 'Publish' }}
            </button>
        </form>
    </div>
</div>
@include('admin.components.alerts')

<div class="product-show">
    <div class="product-show__hero card">
        <div class="product-show__gallery">
            @if ($gallery->isNotEmpty())
                <img class="product-show__main-img" src="{{ storefront_image($gallery->first()) }}" alt="{{ $item->name }}">
                @if ($gallery->count() > 1)
                    <div class="product-show__thumbs">
                        @foreach ($gallery as $image)
                            <img src="{{ storefront_image($image) }}" alt="">
                        @endforeach
                    </div>
                @endif
            @else
                <div class="product-show__placeholder">No photo uploaded</div>
            @endif
        </div>

        <div class="product-show__summary">
            <div class="product-show__meta">
                @include('admin.components.status-badge', ['status' => $item->is_active ? 'active' : 'inactive', 'label' => $item->is_active ? 'Published' : 'Unpublished'])
                @if ($item->badge)
                    <span class="product-show__badge">{{ $item->badge }}</span>
                @endif
                @if ($item->sku)
                    <span class="product-show__sku">SKU {{ $item->sku }}</span>
                @endif
            </div>

            <h2 class="product-show__title">{{ $item->name }}</h2>

            <div class="product-show__price">
                <strong>{{ money($selling) }}</strong>
                @if ($item->sale_price && (float) $item->sale_price < (float) $item->regular_price)
                    <s>{{ money($item->regular_price) }}</s>
                @endif
                <span>{{ $item->tax_note ?: 'Inclusive of all taxes' }}</span>
            </div>

            @if ($item->short_description)
                <p class="product-show__intro">{{ $item->short_description }}</p>
            @endif

            @if ($item->collections->isNotEmpty())
                <p class="form-hint" style="margin-bottom: 0.4rem;">Shown on collection</p>
                <div class="product-show__chips">
                    @foreach ($item->collections as $collection)
                        <span class="choice-chip">{{ $collection->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="product-show__grid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Product details</h3>
            </div>
            <dl class="spec-grid">
                @foreach ($specs as $label => $value)
                    <div class="spec-grid__row">
                        <dt>{{ $label }}</dt>
                        <dd>{{ filled($value) ? $value : '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Description</h3>
            </div>
            @if ($item->description)
                <p class="product-show__copy">{{ $item->description }}</p>
            @else
                <p class="form-hint">No description added yet.</p>
            @endif

            @if (!empty($item->highlights))
                <h4 class="product-show__sub">Highlights</h4>
                <ul class="product-show__ticks">
                    @foreach ($item->highlights as $highlight)
                        <li>{{ $highlight }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
