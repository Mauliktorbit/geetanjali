@php
    $item = $item ?? null;
    $type = old('discount_type', $item?->typeKey() ?: 'percent');
    $starts = old('starts_at', $item?->starts_at?->format('Y-m-d'));
    $ends = old('ends_at', $item?->ends_at?->format('Y-m-d'));
    $audience = old('customer_audience', $item?->customerAudience() ?: 'all');
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="coupon-code">Coupon code *</label>
        <input
            id="coupon-code"
            type="text"
            name="code"
            class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $item->code ?? '') }}"
            required
            maxlength="40"
            placeholder="WELCOME10"
            autocomplete="off"
        >
        <span class="form-hint">Customers type this at checkout. Letters and numbers only.</span>
        @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-name">Offer name *</label>
        <input
            id="coupon-name"
            type="text"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $item->name ?? '') }}"
            required
            maxlength="120"
            placeholder="Welcome 10% off"
        >
        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-type">Discount type *</label>
        <select id="coupon-type" name="discount_type" class="form-control @error('discount_type') is-invalid @enderror" required>
            <option value="percent" @selected($type === 'percent')>Percent (%)</option>
            <option value="fixed" @selected($type === 'fixed')>Fixed amount (₹)</option>
        </select>
        @error('discount_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-value">Discount value *</label>
        <input
            id="coupon-value"
            type="number"
            name="discount_value"
            class="form-control @error('discount_value') is-invalid @enderror"
            value="{{ old('discount_value', $item->discount_value ?? '') }}"
            required
            min="0.01"
            step="0.01"
            placeholder="10"
        >
        <span class="form-hint">For percent, enter 10 for 10% off. For fixed, enter the rupee amount.</span>
        @error('discount_value')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-starts">Start date</label>
        <input
            id="coupon-starts"
            type="date"
            name="starts_at"
            class="form-control @error('starts_at') is-invalid @enderror"
            value="{{ $starts }}"
            data-date-range-start
            autocomplete="off"
        >
        <span class="form-hint">Open the calendar and pick the first day this coupon can be used.</span>
        @error('starts_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-ends">End date</label>
        <input
            id="coupon-ends"
            type="date"
            name="ends_at"
            class="form-control @error('ends_at') is-invalid @enderror"
            value="{{ $ends }}"
            @if ($starts) min="{{ $starts }}" @endif
            data-date-range-end
            autocomplete="off"
        >
        <span class="form-hint">Pick the last day it can be used. Leave blank if it should not expire.</span>
        @error('ends_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-min">Minimum order (₹)</label>
        <input
            id="coupon-min"
            type="number"
            name="minimum_cart"
            class="form-control @error('minimum_cart') is-invalid @enderror"
            value="{{ old('minimum_cart', $item->minimum_cart ?? '') }}"
            min="0"
            step="1"
            placeholder="Optional"
        >
        <span class="form-hint">Leave blank if any order amount can use this coupon.</span>
        @error('minimum_cart')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-max-discount">Maximum discount (₹)</label>
        <input
            id="coupon-max-discount"
            type="number"
            name="maximum_discount"
            class="form-control @error('maximum_discount') is-invalid @enderror"
            value="{{ old('maximum_discount', $item->maximum_discount ?? '') }}"
            min="0.01"
            step="0.01"
            placeholder="No cap"
        >
        <span class="form-hint">Cap how many rupees can be taken off. Useful for percent coupons, for example 10% off up to ₹2,000. Leave blank for no cap.</span>
        @error('maximum_discount')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-usage-limit">Total uses</label>
        <input
            id="coupon-usage-limit"
            type="number"
            name="usage_limit"
            class="form-control @error('usage_limit') is-invalid @enderror"
            value="{{ old('usage_limit', $item?->usage_limit ?? '') }}"
            min="1"
            step="1"
            placeholder="Unlimited"
        >
        <span class="form-hint">How many times this code can be used in total. Leave blank for no overall limit.</span>
        @error('usage_limit')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-per-customer">Uses per customer</label>
        <input
            id="coupon-per-customer"
            type="number"
            name="per_customer_limit"
            class="form-control @error('per_customer_limit') is-invalid @enderror"
            value="{{ old('per_customer_limit', $item?->per_customer_limit ?: '') }}"
            min="1"
            step="1"
            placeholder="Unlimited"
        >
        <span class="form-hint">How many times one customer can use this code. Leave blank for no per-customer limit.</span>
        @error('per_customer_limit')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-audience">Who can use this coupon *</label>
        <select
            id="coupon-audience"
            name="customer_audience"
            class="form-control @error('customer_audience') is-invalid @enderror"
            required
        >
            <option value="all" @selected($audience === 'all')>All customers</option>
            <option value="new" @selected($audience === 'new')>New customers (first order)</option>
            <option value="returning" @selected($audience === 'returning')>Returning customers (already ordered)</option>
        </select>
        <span class="form-hint">Choose whether the code is for new users, old users, or everyone.</span>
        @error('customer_audience')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group form-check">
        <label>
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
            Active — customers can use this coupon
        </label>
    </div>
</div>

@php
    $categories = $categories ?? collect();
    $collections = $collections ?? collect();
    $products = $products ?? collect();
    $selectedCategoryIds = collect(old('included_categories', $item?->included_categories ?? []))->map(fn ($id) => (int) $id);
    $selectedCollectionIds = collect(old('included_collections', $item?->included_collections ?? []))->map(fn ($id) => (int) $id);
    $selectedProductIds = collect(old('included_products', $item?->included_products ?? []))->map(fn ($id) => (int) $id);
@endphp

<div class="coupon-scope">
    <div class="coupon-scope__head">
        <h3>Where this coupon can be used</h3>
        <p>Leave all boxes unchecked to allow the whole store. If you select any categories, collections, or products, the coupon only applies to matching items.</p>
    </div>

    <div class="form-group full">
        <span id="coupon-categories-label">Categories</span>
        @if ($categories->isEmpty())
            <p class="form-hint">No categories yet. Add them under Admin → Categories.</p>
        @else
            <div class="collection-check-list" role="group" aria-labelledby="coupon-categories-label">
                @foreach ($categories as $category)
                    <label class="collection-check">
                        <input type="checkbox" name="included_categories[]" value="{{ $category->id }}" @checked($selectedCategoryIds->contains((int) $category->id))>
                        <span>{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>
        @endif
        <span class="form-hint">Tick the jewellery types this code should work on.</span>
        @error('included_categories')<span class="invalid-feedback" style="display:block;">{{ $message }}</span>@enderror
        @error('included_categories.*')<span class="invalid-feedback" style="display:block;">{{ $message }}</span>@enderror
    </div>

    <div class="form-group full">
        <span id="coupon-collections-label">Collections</span>
        @if ($collections->isEmpty())
            <p class="form-hint">No collections yet. Add them under Admin → Collections.</p>
        @else
            <div class="collection-check-list" role="group" aria-labelledby="coupon-collections-label">
                @foreach ($collections as $collection)
                    <label class="collection-check">
                        <input type="checkbox" name="included_collections[]" value="{{ $collection->id }}" @checked($selectedCollectionIds->contains((int) $collection->id))>
                        <span>{{ $collection->name }}{{ isset($collection->is_active) && ! $collection->is_active ? ' (Inactive)' : '' }}</span>
                    </label>
                @endforeach
            </div>
        @endif
        <span class="form-hint">Limit the coupon to products in these collections.</span>
        @error('included_collections')<span class="invalid-feedback" style="display:block;">{{ $message }}</span>@enderror
        @error('included_collections.*')<span class="invalid-feedback" style="display:block;">{{ $message }}</span>@enderror
    </div>

    <div class="form-group full">
        <span id="coupon-products-label">Products</span>
        @if ($products->isEmpty())
            <p class="form-hint">No products yet. Add them under Admin → Products.</p>
        @else
            <div class="collection-check-list collection-check-list--products" role="group" aria-labelledby="coupon-products-label">
                @foreach ($products as $product)
                    <label class="collection-check">
                        <input type="checkbox" name="included_products[]" value="{{ $product->id }}" @checked($selectedProductIds->contains((int) $product->id))>
                        <span>{{ $product->name }}{{ $product->sku ? ' ('.$product->sku.')' : '' }}</span>
                    </label>
                @endforeach
            </div>
        @endif
        <span class="form-hint">Optionally restrict the code to specific products only.</span>
        @error('included_products')<span class="invalid-feedback" style="display:block;">{{ $message }}</span>@enderror
        @error('included_products.*')<span class="invalid-feedback" style="display:block;">{{ $message }}</span>@enderror
    </div>

    <div class="form-group form-check full">
        <label>
            <input type="checkbox" name="exclude_sale_items" value="1" @checked(old('exclude_sale_items', $item?->exclude_sale_items ?? false))>
            Do not apply to products that are already discounted
        </label>
        <span class="form-hint">When ticked, the coupon skips items that already have a sale price lower than the regular price.</span>
    </div>
</div>
