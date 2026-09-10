@php
    $item = $item ?? null;
    $previewPath = $item
        ? \App\Services\StorefrontCatalogService::categoryImage($item->slug, $item->image)
        : null;
@endphp

<div class="category-form">
    <div class="category-form__fields">
        <div class="form-group">
            <label for="category-name">Category name *</label>
            <input
                id="category-name"
                type="text"
                name="name"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $item->name ?? '') }}"
                required
                maxlength="120"
                placeholder="Earrings"
                autofocus
            >
            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="category-image">Image</label>
            <input
                id="category-image"
                type="file"
                name="image"
                class="form-control @error('image') is-invalid @enderror"
                accept="image/jpeg,image/png,image/webp"
                data-preview
            >
            <span class="form-hint">JPG, PNG or WebP, up to 4 MB. This photo is used on the website.</span>
            @error('image')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <aside class="category-form__preview">
        <span class="label">{{ $item ? 'Current image' : 'Image preview' }}</span>
        @if ($previewPath)
            <img src="{{ storefront_image($previewPath) }}" class="category-form__photo" alt="{{ $item->name ?? 'Category' }}">
        @else
            <div class="category-form__placeholder">Choose an image to preview it here.</div>
        @endif
    </aside>
</div>
