@php
    $item = $item ?? null;
    $previewPath = $item?->image ? storefront_image($item->image) : null;
@endphp

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
                accept="image/jpeg,image/png,image/webp"
                data-preview
            >
            <span class="form-hint">JPG, PNG or WebP, up to 4 MB. Used as the large photo at the top of the page.</span>
            @error('image')<span class="invalid-feedback">{{ $message }}</span>@enderror
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

    <aside class="category-form__preview">
        <span class="label">{{ $item?->image ? 'Current image' : 'Image preview' }}</span>
        @if ($previewPath)
            <img src="{{ $previewPath }}" class="category-form__photo" alt="{{ $item->name ?? 'Collection' }}">
        @else
            <div class="category-form__placeholder">Choose an image to preview it here.</div>
        @endif
    </aside>
</div>
