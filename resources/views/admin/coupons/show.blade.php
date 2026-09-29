@extends('admin.layouts.app')
@section('title', $item->code)
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $item->code }}</h1>
        <p class="subtitle">{{ $item->name }}</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Coupons', 'url' => route('admin.coupons.index')], ['label' => $item->code]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.coupons.edit', $item) }}" class="btn btn-primary">Edit</a>
        <form method="POST" action="{{ route('admin.coupons.destroy', $item) }}" data-no-loading>
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" type="submit" onclick="return confirm('Delete coupon {{ $item->code }}? Customers will no longer be able to use this code.')">Delete</button>
        </form>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card">
    <div class="detail-list">
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])</span>
        </div>
        <div class="detail-item">
            <span class="label">Code</span>
            <span class="value">{{ $item->code }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Name</span>
            <span class="value">{{ $item->name }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Discount</span>
            <span class="value">{{ $item->discountLabel() }} ({{ $item->typeLabel() }})</span>
        </div>
        <div class="detail-item">
            <span class="label">Dates</span>
            <span class="value">{{ $item->dateRange() }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Minimum order</span>
            <span class="value">{{ $item->minimum_cart !== null ? money($item->minimum_cart) : 'No minimum' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Maximum discount</span>
            <span class="value">{{ $item->maximum_discount !== null ? money($item->maximum_discount) : 'No cap' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Total uses</span>
            <span class="value">
                {{ number_format((int) $item->usage_count) }}
                /
                {{ $item->usage_limit ? number_format((int) $item->usage_limit) : 'Unlimited' }}
            </span>
        </div>
        <div class="detail-item">
            <span class="label">Uses per customer</span>
            <span class="value">{{ ((int) $item->per_customer_limit) > 0 ? number_format((int) $item->per_customer_limit) : 'Unlimited' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Who can use it</span>
            <span class="value">{{ $item->customerAudienceLabel() }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Applies to</span>
            <span class="value">
                @if (! $item->hasTargeting())
                    Entire store
                @else
                    @if (($scopeCategories ?? collect())->isNotEmpty())
                        <div>Categories: {{ $scopeCategories->implode(', ') }}</div>
                    @endif
                    @if (($scopeCollections ?? collect())->isNotEmpty())
                        <div>Collections: {{ $scopeCollections->implode(', ') }}</div>
                    @endif
                    @if (($scopeProducts ?? collect())->isNotEmpty())
                        <div>Products: {{ $scopeProducts->map(fn ($product) => $product->sku ? $product->name.' ('.$product->sku.')' : $product->name)->implode(', ') }}</div>
                    @endif
                @endif
            </span>
        </div>
        <div class="detail-item">
            <span class="label">Already discounted products</span>
            <span class="value">{{ $item->exclude_sale_items ? 'Excluded — coupon will not apply' : 'Allowed' }}</span>
        </div>
    </div>
</div>
@endsection
