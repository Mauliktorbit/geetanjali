@extends('admin.layouts.app')
@section('title', 'Update Stock')
@section('content')
<div class="page-header">
    <div>
        <h1>Update Stock</h1>
        <p class="subtitle">See the current stock and set how many pieces this product should have.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Inventory', 'url' => route('admin.inventory.index')], ['label' => 'Update Stock']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.inventory.adjust.store') }}">
        @csrf
        @if ($selectedProduct)
            <input type="hidden" name="product_id" value="{{ $selectedProduct->id }}">
        @endif
        <div class="collection-form">
            <div class="collection-form__fields">
                <div class="form-group">
                    <label for="inventory-product">Product *</label>
                    <select
                        id="inventory-product"
                        class="form-control @error('product_id') is-invalid @enderror"
                        @if (! $selectedProduct) name="product_id" required @endif
                        data-adjust-url="{{ url('admin/inventory/adjust') }}"
                        onchange="if (this.value) { window.location.href = this.dataset.adjustUrl + '/' + this.value; }"
                    >
                        <option value="">Select product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((int) $selectedProductId === (int) $product->id)>
                                {{ $product->name }}{{ $product->sku ? ' ('.$product->sku.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="inventory-current">Current stock</label>
                    <input
                        id="inventory-current"
                        type="text"
                        class="form-control"
                        value="{{ $selectedProduct ? $currentStock : '' }}"
                        placeholder="Choose a product first"
                        readonly
                        tabindex="-1"
                    >
                </div>

                <div class="form-group">
                    <label for="inventory-stock">New stock *</label>
                    <input
                        id="inventory-stock"
                        type="number"
                        name="stock"
                        class="form-control @error('stock') is-invalid @enderror"
                        value="{{ old('stock', $selectedProduct ? $currentStock : '') }}"
                        min="0"
                        step="1"
                        required
                        placeholder="0"
                    >
                    <span class="form-hint">This is the total pieces in stock, not plus or minus. Change the number, then save.</span>
                    @error('stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            <aside class="category-form__preview">
                @if ($selectedProduct)
                    <span class="label">Selected product</span>
                    <img src="{{ storefront_image($selectedProduct->imagePath()) }}" class="category-form__photo" alt="{{ $selectedProduct->name }}">
                    <strong class="inventory-product-name">{{ $selectedProduct->name }}</strong>
                    @if ($selectedProduct->sku)
                        <span class="form-hint">{{ $selectedProduct->sku }}</span>
                    @endif
                    <span class="form-hint">Current stock: {{ number_format($currentStock) }}</span>
                @else
                    <span class="label">Product</span>
                    <div class="category-form__placeholder">Choose a product to see its photo and current stock.</div>
                @endif
            </aside>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
