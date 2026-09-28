@php $item = $item ?? null; @endphp

<div class="form-grid">
    <div class="form-group full">
        <label for="name">Name *</label>
        <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name ?? '') }}" required placeholder="Standard Delivery">
        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="rate">Charge (₹) *</label>
        <input id="rate" type="number" step="0.01" min="0" name="rate" class="form-control @error('rate') is-invalid @enderror" value="{{ old('rate', $item->rate ?? 0) }}" required placeholder="0">
        <span class="form-hint">Enter 0 for free delivery.</span>
        @error('rate')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="estimated_delivery">Delivery time</label>
        <input id="estimated_delivery" type="text" name="estimated_delivery" class="form-control" value="{{ old('estimated_delivery', $item->estimated_delivery ?? '3–5 business days') }}" placeholder="3–5 business days">
    </div>
    <div class="form-group">
        <label for="free_shipping_threshold">Free shipping above (₹)</label>
        <input id="free_shipping_threshold" type="number" step="0.01" min="0" name="free_shipping_threshold" class="form-control" value="{{ old('free_shipping_threshold', $item->free_shipping_threshold ?? '') }}" placeholder="Optional">
        <span class="form-hint">Leave blank if this rule is never free by cart total.</span>
    </div>
    <div class="form-group">
        <label for="min_order_amount">Minimum order (₹)</label>
        <input id="min_order_amount" type="number" step="0.01" min="0" name="min_order_amount" class="form-control" value="{{ old('min_order_amount', $item->min_order_amount ?? '') }}" placeholder="Optional">
        <span class="form-hint">Hide this option when the cart is below this amount.</span>
    </div>
    <div class="form-group form-check">
        <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
        <label for="is_active">Show at checkout</label>
    </div>
</div>
