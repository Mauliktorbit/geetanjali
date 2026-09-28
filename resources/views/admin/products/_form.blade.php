@php
    $item = $item ?? null;
    $selectedCollectionIds = collect(old('collections', $item?->collections?->pluck('id')->all() ?? []))
        ->filter()
        ->map(fn ($id) => (string) $id)
        ->values();
    $jewelleryCategories = $categories ?? collect();
    $currentQty = 0;
    if ($item) {
        $currentQty = $item->relationLoaded('inventories')
            ? (int) $item->inventories->sum('available_stock')
            : (int) $item->inventories()->sum('available_stock');
    }
    $quantity = (int) old('quantity', $currentQty);
    $stockStatus = old('stock_status', $quantity > 0 ? 'in_stock' : 'out_of_stock');
    $mainPreview = (! old('remove_main_image') && $item?->main_image) ? storefront_image($item->main_image) : null;
    $galleryImages = collect(old('keep_gallery', $item?->gallery_images ?? []))->filter()->values();
@endphp

<input type="hidden" name="product_type" value="simple">
<input type="hidden" name="gallery_sync" value="1">
<input type="hidden" name="slug" value="{{ old('slug', $item->slug ?? '') }}">
<input type="hidden" name="remove_main_image" value="{{ old('remove_main_image') ? '1' : '0' }}" data-remove-main-image>

<div class="product-form product-form--wide">
    <div class="product-form__body">
    <div class="product-form__main">
        <div class="form-section">
            <div class="form-section__head">
                <h3>Photos</h3>
                <p>The main photo is the first image customers see.</p>
            </div>
            <div class="product-photos">
            <div class="product-photos__fields">
                <div class="form-group" data-image-field="main">
                    <label for="main_image">Main photo</label>
                    <input
                        id="main_image"
                        type="file"
                        name="main_image"
                        class="form-control @error('main_image') is-invalid @enderror"
                        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                        data-main-image-input
                        data-preview-target="#product-image-preview"
                        onchange="window.previewAdminImage && window.previewAdminImage(this)"
                    >
                    <span class="form-hint">JPG, PNG or WebP, up to 10 MB.</span>
                    @error('main_image')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group" data-image-field="gallery">
                    <label for="gallery_images">More photos</label>
                    <input
                        id="gallery_images"
                        type="file"
                        name="gallery_images[]"
                        class="form-control @error('gallery_images') is-invalid @enderror @error('gallery_images.0') is-invalid @enderror"
                        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                        multiple
                        data-gallery-input
                        onchange="window.previewAdminImage && window.previewAdminImage(this)"
                    >
                    <span class="form-hint">Optional extras, up to 10 MB each.</span>
                    @error('gallery_images')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    @error('gallery_images.0')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <aside class="category-form__preview{{ $mainPreview ? ' is-previewing' : '' }}" id="product-image-preview" data-image-preview data-main-preview>
                <span class="label">{{ $mainPreview ? 'Current image' : 'Image preview' }}</span>
                <img
                    class="category-form__photo"
                    alt="Product photo preview"
                    data-preview-image
                    data-main-preview-img
                    @if ($mainPreview) src="{{ $mainPreview }}" @else style="display:none" @endif
                >
                <div class="category-form__placeholder" data-preview-placeholder @if ($mainPreview) style="display:none" @endif>Choose an image to preview it here.</div>
                <div class="gallery-preview" data-gallery-previews>
                    @foreach ($galleryImages as $image)
                        <div class="gallery-preview-item" data-gallery-item>
                            <input type="hidden" name="keep_gallery[]" value="{{ $image }}">
                            <img src="{{ storefront_image($image) }}" alt="">
                            <button type="button" class="media-preview__remove" data-remove-gallery aria-label="Remove photo">×</button>
                        </div>
                    @endforeach
                </div>
            </aside>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section__head">
                <h3>Product</h3>
                <p>Name and text shown on the product page.</p>
            </div>
            <div class="form-grid">
                <div class="form-group full">
                    <label for="name">Product name *</label>
                    <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name ?? '') }}" required placeholder="Kundan Emerald Drop Earrings">
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="sku">SKU</label>
                    <input id="sku" type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $item->sku ?? '') }}" placeholder="Leave blank to auto-create">
                    @error('sku')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="badge">Badge</label>
                    <input id="badge" type="text" name="badge" class="form-control" value="{{ old('badge', $item->badge ?? '') }}" placeholder="BEST SELLER">
                    <span class="form-hint">Optional ribbon on the photo.</span>
                </div>
                <div class="form-group full">
                    <label for="short_description">Short description</label>
                    <textarea id="short_description" name="short_description" class="form-control" rows="2" placeholder="Handcrafted kundan earrings with a lightweight, skin-friendly finish...">{{ old('short_description', $item->short_description ?? '') }}</textarea>
                </div>
                <div class="form-group full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" placeholder="These earrings are a statement piece of traditional craftsmanship...">{{ old('description', $item->description ?? '') }}</textarea>
                </div>
                <div class="form-group full">
                    <label for="highlights_text">Highlights</label>
                    <textarea id="highlights_text" name="highlights_text" class="form-control" rows="3" placeholder="Premium finish&#10;Lightweight and skin-friendly">{{ old('highlights_text', implode("\n", $item->highlights ?? [])) }}</textarea>
                    <span class="form-hint">One point per line. Shown as green ticks.</span>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section__head">
                <h3>Jewellery details</h3>
                <p>Shown beside the price on the product page.</p>
            </div>
            <div class="form-grid form-grid--3">
                <div class="form-group">
                    <label for="metal">Material</label>
                    <input id="metal" type="text" name="metal" class="form-control" value="{{ old('metal', $item->metal ?? '') }}" placeholder="Gold-plated alloy">
                </div>
                <div class="form-group">
                    <label for="stone">Stone</label>
                    <input id="stone" type="text" name="stone" class="form-control" value="{{ old('stone', $item->stone ?? '') }}" placeholder="Kundan, Pearl">
                </div>
                <div class="form-group">
                    <label for="style">Style</label>
                    <input id="style" type="text" name="style" class="form-control" value="{{ old('style', $item->style ?? '') }}" placeholder="Traditional">
                </div>
                <div class="form-group">
                    <label for="occasion">Occasion</label>
                    <input id="occasion" type="text" name="occasion" class="form-control" value="{{ old('occasion', $item->occasion ?? '') }}" placeholder="Bridal, Festive">
                </div>
                <div class="form-group">
                    <label for="weight">Weight (grams)</label>
                    <input id="weight" type="number" step="0.001" min="0" name="weight" class="form-control" value="{{ old('weight', $item->weight ?? '') }}" placeholder="18.350">
                </div>
            </div>
        </div>

        <div class="form-section" data-stock-fields>
            <div class="form-section__head">
                <h3>Price &amp; stock</h3>
                <p>Sale price is optional. In Stock needs a quantity of 1 or more.</p>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="regular_price">Regular price (₹) *</label>
                    <input id="regular_price" type="number" step="0.01" min="0" name="regular_price" class="form-control @error('regular_price') is-invalid @enderror" value="{{ old('regular_price', $item->regular_price ?? '') }}" required placeholder="2499">
                    @error('regular_price')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="sale_price">Sale price (₹)</label>
                    <input id="sale_price" type="number" step="0.01" min="0" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price', $item->sale_price ?? '') }}" placeholder="Optional offer">
                    @error('sale_price')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity *</label>
                    <input id="quantity" type="number" min="0" step="1" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ $quantity }}" required data-stock-quantity>
                    @error('quantity')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <span class="field-label">Stock status *</span>
                    <div class="choice-list" role="radiogroup" aria-label="Stock status">
                        <label class="choice-chip">
                            <input type="radio" name="stock_status" value="in_stock" data-stock-status @checked($stockStatus === 'in_stock')>
                            In Stock
                        </label>
                        <label class="choice-chip">
                            <input type="radio" name="stock_status" value="out_of_stock" data-stock-status @checked($stockStatus !== 'in_stock')>
                            Out of Stock
                        </label>
                    </div>
                    @error('stock_status')<span class="invalid-feedback" style="display:block;">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    <aside class="product-form__side">
        <div class="form-section">
            <div class="form-section__head">
                <h3>Publish</h3>
                <p>Show this product on the website.</p>
            </div>
            <div class="form-group form-check">
                <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
                <label for="is_active">Publish on website</label>
            </div>
            <div class="product-flag-list">
                <label class="form-check">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $item->is_featured ?? false))>
                    <span>Featured</span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="is_new_arrival" value="1" @checked(old('is_new_arrival', $item->is_new_arrival ?? false))>
                    <span>New arrival</span>
                </label>
                <label class="form-check">
                    <input type="checkbox" name="is_bestseller" value="1" @checked(old('is_bestseller', $item->is_bestseller ?? false))>
                    <span>Bestseller</span>
                </label>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section__head">
                <h3>Where it appears</h3>
                <p>Category and collections are required.</p>
            </div>
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                    <option value="">Select category</option>
                    @foreach ($jewelleryCategories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id ?? request('category_id')) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <span id="collections-label">Collections *</span>
                <div class="collection-picker collection-picker--list" data-collection-picker>
                    @if (($collections ?? collect())->isEmpty())
                        <p class="form-hint">No collections yet. Add one under Admin → Collections first.</p>
                    @else
                        <div class="collection-check-list" role="group" aria-labelledby="collections-label">
                            @foreach ($collections as $collection)
                                <label class="collection-check">
                                    <input
                                        type="checkbox"
                                        name="collections[]"
                                        value="{{ $collection->id }}"
                                        data-label="{{ $collection->name }}"
                                        @checked($selectedCollectionIds->contains((string) $collection->id))
                                    >
                                    <span>{{ $collection->name }}{{ isset($collection->is_active) && ! $collection->is_active ? ' (Inactive)' : '' }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <div class="collection-picker__chips" data-collection-chips></div>
                </div>
                @error('collections')
                    <span class="invalid-feedback" style="display:block;">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </aside>
    </div>
</div>
