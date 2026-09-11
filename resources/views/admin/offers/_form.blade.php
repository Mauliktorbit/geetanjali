@php
    $item = $item ?? null;
    $categories = $categories ?? \App\Models\OfferCategory::query()->ordered()->get();
    $category = old('category', $item?->category ?: ($categories->first()?->slug ?? '__new__'));
    $showNewCategory = $category === '__new__' || $categories->isEmpty();
    $ends = old('ends_at', $item?->ends_at?->format('d/m/Y'));
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="offer-category">Category *</label>
        <select id="offer-category" name="category" class="form-control @error('category') is-invalid @enderror" required>
            @forelse ($categories as $option)
                <option value="{{ $option->slug }}" @selected($category === $option->slug)>{{ $option->name }}</option>
            @empty
            @endforelse
            <option value="__new__" @selected($showNewCategory)>Add new category</option>
        </select>
        <div id="offer-new-category-wrap" class="mt-2" @if (! $showNewCategory) hidden @endif>
            <label for="offer-new-category" class="form-hint">New category name</label>
            <input
                id="offer-new-category"
                type="text"
                name="new_category"
                class="form-control @error('new_category') is-invalid @enderror"
                value="{{ old('new_category') }}"
                maxlength="80"
                placeholder="Wedding Offers"
                autocomplete="off"
                @if ($showNewCategory) required @endif
            >
        </div>
        @error('category')<span class="invalid-feedback">{{ $message }}</span>@enderror
        @error('new_category')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="offer-title">Headline *</label>
        <input
            id="offer-title"
            type="text"
            name="title"
            class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $item ? str_replace(["\r\n", "\n"], ' ', $item->title) : '') }}"
            required
            maxlength="120"
            placeholder="On Diamond Jewellery"
        >
        @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="offer-discount">Discount *</label>
        <input
            id="offer-discount"
            type="text"
            name="discount_display"
            class="form-control @error('discount_display') is-invalid @enderror"
            value="{{ old('discount_display', $item->discount_display ?? '') }}"
            required
            maxlength="20"
            placeholder="10%"
        >
        <span class="form-hint">Shown on the card, for example 10% or ₹500.</span>
        @error('discount_display')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="offer-ends">Valid till</label>
        <input
            id="offer-ends"
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
        @error('ends_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="offer-image">Photo {{ $item ? '' : '*' }}</label>
        <input
            id="offer-image"
            type="file"
            name="image"
            class="form-control @error('image') is-invalid @enderror"
            accept="image/jpeg,image/png,image/webp"
            data-preview
            @if (! $item) required @endif
        >
        @if ($item?->image)
            <div class="mt-2">
                <img src="{{ storefront_image($item->image) }}" alt="{{ $item->title }}" class="thumb-sm">
            </div>
        @endif
        @error('image')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group form-check">
        <label>
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
            Show on the website
        </label>
    </div>
</div>

<script>
(function () {
    var select = document.getElementById('offer-category');
    var wrap = document.getElementById('offer-new-category-wrap');
    var input = document.getElementById('offer-new-category');
    if (!select || !wrap || !input) {
        return;
    }
    function toggle() {
        var isNew = select.value === '__new__';
        wrap.hidden = !isNew;
        input.required = isNew;
        if (isNew) {
            input.focus();
        }
    }
    select.addEventListener('change', toggle);
})();
</script>
