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
                accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                data-preview
                data-preview-target="#category-image-preview"
                onchange="window.previewAdminImage && window.previewAdminImage(this)"
            >
            <span class="form-hint">JPG, PNG or WebP, up to 4 MB. This photo is used on the website.</span>
            @error('image')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <aside class="category-form__preview{{ $previewPath ? ' is-previewing' : '' }}" id="category-image-preview" data-image-preview>
        <span class="label">{{ $item ? 'Current image' : 'Image preview' }}</span>
        @if ($previewPath)
            <img src="{{ storefront_image($previewPath) }}" class="category-form__photo" alt="{{ $item->name ?? 'Category' }}" data-preview-image>
            <div class="category-form__placeholder" data-preview-placeholder style="display:none">Choose an image to preview it here.</div>
        @else
            <img class="category-form__photo" alt="Category image preview" data-preview-image style="display:none">
            <div class="category-form__placeholder" data-preview-placeholder>Choose an image to preview it here.</div>
        @endif
    </aside>
</div>
