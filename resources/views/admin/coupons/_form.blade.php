@php
    $item = $item ?? null;
    $type = old('discount_type', $item?->typeKey() ?: 'percent');
    $starts = old('starts_at', $item?->starts_at?->format('d/m/Y'));
    $ends = old('ends_at', $item?->ends_at?->format('d/m/Y'));
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
        <label for="coupon-starts">Starts</label>
        <input
            id="coupon-starts"
            type="text"
            name="starts_at"
            class="form-control @error('starts_at') is-invalid @enderror"
            value="{{ $starts }}"
            inputmode="numeric"
            maxlength="10"
            placeholder="DD/MM/YYYY"
            data-input-kind="datedmy"
            autocomplete="off"
        >
        <span class="form-hint">Example: 11/09/2026</span>
        @error('starts_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="coupon-ends">Ends</label>
        <input
            id="coupon-ends"
            type="text"
            name="ends_at"
            class="form-control @error('ends_at') is-invalid @enderror"
            value="{{ $ends }}"
            inputmode="numeric"
            maxlength="10"
            placeholder="DD/MM/YYYY"
            data-input-kind="datedmy"
            autocomplete="off"
        >
        <span class="form-hint">Example: 31/12/2026</span>
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

    <div class="form-group form-check">
        <label>
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
            Active — customers can use this coupon
        </label>
    </div>
</div>
