@extends('admin.layouts.app')
@section('title', 'Bulk Update Stock')
@section('content')
<div class="page-header">
    <div>
        <h1>Bulk Update Stock</h1>
        <p class="subtitle">Add pieces to many products at once. Leave a row blank to skip it.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Inventory', 'url' => route('admin.inventory.index')], ['label' => 'Bulk Update Stock']]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventory.index', request()->only(['search', 'status', 'sort'])) }}" class="btn btn-ghost">Back to inventory</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product or SKU" class="form-control">
        <select name="status" class="form-control" aria-label="Stock status" onchange="this.form.submit()">
            <option value="">All stock</option>
            <option value="in" @selected(request('status') === 'in')>In stock</option>
            <option value="low" @selected(request('status') === 'low')>Low stock</option>
            <option value="out" @selected(request('status') === 'out')>Out of stock</option>
        </select>
        <select name="sort" class="form-control" aria-label="Sort inventory" onchange="this.form.submit()">
            @foreach ($sortOptions as $value => $label)
                <option value="{{ $value }}" @selected($currentSort === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary" type="submit">Search</button>
        @if (request()->hasAny(['search', 'status', 'sort']))
            <a href="{{ route('admin.inventory.bulk') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('admin.inventory.bulk.store') }}" data-no-loading data-bulk-stock-form>
        @csrf
        @foreach (request()->only(['search', 'status', 'sort']) as $key => $value)
            @if ($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        @if ($items->count())
            <div class="bulk-bar">
                <label class="bulk-stock-label" for="inventory-fill-amount">Fill all rows</label>
                <input
                    id="inventory-fill-amount"
                    type="number"
                    class="form-control"
                    min="1"
                    step="1"
                    placeholder="0"
                    inputmode="numeric"
                    data-fill-amount
                >
                <button type="button" class="btn btn-secondary" data-fill-all>Apply to all rows</button>
                <span class="form-hint">This only fills the form. Click Save to update stock.</span>
            </div>
        @endif

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-image">Image</th>
                        <th>Product</th>
                        <th>Current Stock</th>
                        <th>Stock to Add</th>
                        <th>New Total Stock</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($items as $index => $item)
                    @php
                        $stock = (int) ($item->stock_qty ?? 0);
                        $oldAdd = old('items.'.$index.'.stock_to_add');
                    @endphp
                    <tr data-bulk-stock-row>
                        <td class="col-image">
                            <img src="{{ storefront_image($item->imagePath()) }}" class="thumb-sm" alt="{{ $item->name }}">
                        </td>
                        <td>
                            <strong data-inventory-name>{{ $item->name }}</strong>
                            @if ($item->sku)
                                <div class="category-product-empty">{{ $item->sku }}</div>
                            @endif
                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->id }}">
                        </td>
                        <td>
                            <strong data-current-stock="{{ $stock }}">{{ number_format($stock) }}</strong>
                        </td>
                        <td>
                            <input
                                type="number"
                                name="items[{{ $index }}][stock_to_add]"
                                class="form-control inventory-add-input"
                                min="1"
                                step="1"
                                value="{{ $oldAdd }}"
                                placeholder="0"
                                inputmode="numeric"
                                data-stock-to-add
                            >
                        </td>
                        <td>
                            <strong class="inventory-new-total" data-new-total-stock>{{ $oldAdd ? number_format($stock + (int) $oldAdd) : number_format($stock) }}</strong>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">@include('admin.components.empty-state', ['title' => 'No products yet', 'text' => 'Add a product first, then update its stock here.'])</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->count())
            <div class="form-actions inventory-bulk-actions">
                <button class="btn btn-primary" type="submit" data-confirm="Add the entered stock to these products?">Save stock updates</button>
                <a href="{{ route('admin.inventory.index', request()->only(['search', 'status', 'sort'])) }}" class="btn btn-ghost">Cancel</a>
            </div>
        @endif
    </form>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
@push('scripts')
<script>
(function () {
  var form = document.querySelector('[data-bulk-stock-form]');
  if (!form) return;

  function updateRow(row) {
    var currentEl = row.querySelector('[data-current-stock]');
    var addEl = row.querySelector('[data-stock-to-add]');
    var totalEl = row.querySelector('[data-new-total-stock]');
    if (!currentEl || !addEl || !totalEl) return;
    var current = parseInt(currentEl.getAttribute('data-current-stock'), 10) || 0;
    var add = parseInt(addEl.value, 10);
    if (isNaN(add) || add < 0) add = 0;
    totalEl.textContent = (current + add).toLocaleString();
  }

  form.querySelectorAll('[data-bulk-stock-row]').forEach(function (row) {
    var addEl = row.querySelector('[data-stock-to-add]');
    if (!addEl) return;
    addEl.addEventListener('input', function () { updateRow(row); });
    addEl.addEventListener('change', function () { updateRow(row); });
    updateRow(row);
  });

  var fillBtn = form.querySelector('[data-fill-all]');
  var fillAmount = form.querySelector('[data-fill-amount]');
  if (fillBtn && fillAmount) {
    fillBtn.addEventListener('click', function () {
      var value = parseInt(fillAmount.value, 10);
      if (isNaN(value) || value < 1) return;
      form.querySelectorAll('[data-stock-to-add]').forEach(function (input) {
        input.value = String(value);
        var row = input.closest('[data-bulk-stock-row]');
        if (row) updateRow(row);
      });
    });
  }
})();
</script>
@endpush
