@php
    $item = $item ?? null;
    $protected = $item && \App\Services\StorefrontCatalogService::isProtectedSlug($item->slug);
    $previewPath = (! old('remove_image') && $item?->image) ? storefront_image($item->image) : null;
@endphp

<input type="hidden" name="remove_image" value="{{ old('remove_image') ? '1' : '0' }}" data-remove-image>

<div class="collection-form">
    <div class="collection-form__fields">
        <div class="form-group">
            <label for="collection-name">Collection name *</label>
            <input
                id="collection-name"
                type="text"
                name="name"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $item->name ?? '') }}"
                required
                maxlength="160"
                placeholder="Festive Collection"
                autofocus
            >
            <span class="form-hint">This name appears in the website menu.</span>
            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="collection-slug">URL slug</label>
            <input
                id="collection-slug"
                type="text"
                name="slug"
                class="form-control @error('slug') is-invalid @enderror"
                value="{{ old('slug', $item->slug ?? '') }}"
                maxlength="160"
                placeholder="festive-collection"
                @if ($protected) readonly @endif
            >
            <span class="form-hint">{{ $protected ? 'This collection powers a website page, so the slug cannot be changed.' : 'Leave blank to generate from the name. Use lowercase letters, numbers and hyphens.' }}</span>
            @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="collection-details">Details</label>
            <textarea
                id="collection-details"
                name="description"
                class="form-control @error('description') is-invalid @enderror"
                rows="5"
                maxlength="2000"
                placeholder="Write a short description for this collection. It is shown on the website page."
            >{{ old('description', $item->description ?? '') }}</textarea>
            <span class="form-hint">Shown on the collection page banner and intro section.</span>
            @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="collection-image">Banner image</label>
            <input
                id="collection-image"
                type="file"
                name="image"
                class="form-control @error('image') is-invalid @enderror"
                accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                data-preview
                data-preview-target="#collection-image-preview"
                onchange="window.previewAdminImage && window.previewAdminImage(this)"
            >
            <span class="form-hint">JPG, PNG or WebP, up to 4 MB. Used as the large photo at the top of the page.</span>
            @error('image')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="collection-sort">Display order</label>
            <input
                id="collection-sort"
                type="number"
                min="0"
                max="9999"
                step="1"
                name="sort_order"
                class="form-control @error('sort_order') is-invalid @enderror"
                value="{{ old('sort_order', $item->sort_order ?? '') }}"
                placeholder="1"
            >
            <span class="form-hint">Lower numbers appear first in the menu. Leave blank on a new collection to add it at the end.</span>
            @error('sort_order')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="collection-seo-title">SEO title</label>
            <input
                id="collection-seo-title"
                type="text"
                name="seo_title"
                class="form-control @error('seo_title') is-invalid @enderror"
                value="{{ old('seo_title', $item->seo_title ?? '') }}"
                maxlength="180"
                placeholder="Festive Collection | Geetanjali Jewellers"
            >
            @error('seo_title')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="collection-seo-description">SEO description</label>
            <textarea
                id="collection-seo-description"
                name="seo_description"
                class="form-control @error('seo_description') is-invalid @enderror"
                rows="3"
                maxlength="320"
                placeholder="Explore festive fashion jewellery from Geetanjali Jewellers."
            >{{ old('seo_description', $item->seo_description ?? '') }}</textarea>
            @error('seo_description')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group form-check">
            <input
                id="collection-active"
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $item->is_active ?? true))
            >
            <label for="collection-active">Active on website</label>
            <span class="form-hint">Turn this off to hide the collection from the menu and collection page.</span>
        </div>
    </div>

    <aside class="category-form__preview{{ $previewPath ? ' is-previewing' : '' }}" id="collection-image-preview" data-image-preview>
        <span class="label">{{ $previewPath ? 'Current image' : 'Image preview' }}</span>
        <img
            @if ($previewPath) src="{{ $previewPath }}" @else style="display:none" @endif
            class="category-form__photo"
            alt="{{ $item->name ?? 'Collection' }}"
            data-preview-image
        >
        <div class="category-form__placeholder" data-preview-placeholder @if ($previewPath) style="display:none" @endif>Choose an image to preview it here.</div>
        <button type="button" class="btn btn-ghost btn-sm" data-clear-preview @if (! $previewPath) hidden @endif>Remove image</button>
    </aside>
</div>
