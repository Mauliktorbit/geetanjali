@extends('admin.layouts.app')
@section('title', 'Update Stock')
@section('content')
<div class="page-header">
    <div>
        <h1>Update Stock</h1>
        <p class="subtitle">Add pieces to the current stock. The new total is calculated for you.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Inventory', 'url' => route('admin.inventory.index')], ['label' => 'Update Stock']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.inventory.adjust.store') }}" data-stock-add-form>
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
                    <label for="inventory-current">Current Stock</label>
                    <input
                        id="inventory-current"
                        type="text"
                        class="form-control"
                        value="{{ $selectedProduct ? $currentStock : '' }}"
                        placeholder="Choose a product first"
                        readonly
                        tabindex="-1"
                        data-current-stock="{{ $selectedProduct ? (int) $currentStock : '' }}"
                    >
                </div>

                <div class="form-group">
                    <label for="inventory-stock-add">Stock to Add *</label>
                    <input
                        id="inventory-stock-add"
                        type="number"
                        name="stock_to_add"
                        class="form-control @error('stock_to_add') is-invalid @enderror"
                        value="{{ old('stock_to_add') }}"
                        min="1"
                        step="1"
                        required
                        placeholder="0"
                        inputmode="numeric"
                        @disabled(! $selectedProduct)
                        data-stock-to-add
                    >
                    <span class="form-hint">Enter how many pieces to add. This is added to the current stock.</span>
                    @error('stock_to_add')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="inventory-new-total">New Total Stock</label>
                    <input
                        id="inventory-new-total"
                        type="text"
                        class="form-control inventory-new-total"
                        value="{{ $selectedProduct ? (int) $currentStock + (int) old('stock_to_add', 0) : '' }}"
                        placeholder="—"
                        readonly
                        tabindex="-1"
                        data-new-total-stock
                    >
                    <span class="form-hint">Current stock + stock to add.</span>
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
            <button class="btn btn-primary" type="submit" @disabled(! $selectedProduct)>Save</button>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
(function () {
  var form = document.querySelector('[data-stock-add-form]');
  if (!form) return;
  var current = form.querySelector('#inventory-current');
  var add = form.querySelector('[data-stock-to-add]');
  var total = form.querySelector('[data-new-total-stock]');
  if (!current || !add || !total) return;

  function updateTotal() {
    var currentStock = parseInt(current.getAttribute('data-current-stock') || current.value, 10);
    if (isNaN(currentStock)) {
      total.value = '';
      return;
    }
    var addStock = parseInt(add.value, 10);
    if (isNaN(addStock) || addStock < 0) addStock = 0;
    total.value = String(currentStock + addStock);
  }

  add.addEventListener('input', updateTotal);
  add.addEventListener('change', updateTotal);
  updateTotal();
})();
</script>
@endpush
