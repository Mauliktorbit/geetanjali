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
<input type="hidden" name="min_order_qty" value="1">
<input type="hidden" name="gallery_sync" value="1">
<input type="hidden" name="remove_main_image" value="{{ old('remove_main_image') ? '1' : '0' }}" data-remove-main-image>

<div class="product-form">
    <div class="form-section">
        <div class="form-section__head">
            <h3>Photos</h3>
            <p>Shown in the product gallery and “Click to Zoom”.</p>
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
                <span class="form-hint">JPG, PNG or WebP, up to 10 MB. This is the first large image on the product page.</span>
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
                <span class="form-hint">Optional extra photos (JPG, PNG or WebP, up to 10 MB each). They appear as thumbnails under the main photo.</span>
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
            <h3>Name & description</h3>
            <p>Title, short text under the price, and the Description tab.</p>
        </div>
        <div class="form-grid">
            <div class="form-group full">
                <label for="name">Product name *</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name ?? '') }}" required placeholder="Kundan Emerald Drop Earrings">
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="sku">SKU</label>
                <input id="sku" type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $item->sku ?? '') }}" placeholder="GJ-KE-2201">
                <span class="form-hint">Shown in the Product Details tab. Leave blank to auto-generate.</span>
                @error('sku')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="badge">Badge</label>
                <input id="badge" type="text" name="badge" class="form-control" value="{{ old('badge', $item->badge ?? '') }}" placeholder="BEST SELLER">
                <span class="form-hint">Ribbon on the photo, e.g. BEST SELLER, NEW, LIMITED.</span>
            </div>
            <div class="form-group full">
                <label for="short_description">Short description</label>
                <textarea id="short_description" name="short_description" class="form-control" rows="2" placeholder="Handcrafted kundan earrings with a lightweight, skin-friendly finish...">{{ old('short_description', $item->short_description ?? '') }}</textarea>
            </div>
            <div class="form-group full">
                <label for="description">Full description</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="These earrings are a statement piece of traditional craftsmanship...">{{ old('description', $item->description ?? '') }}</textarea>
            </div>
            <div class="form-group full">
                <label for="highlights_text">Highlights</label>
                <textarea id="highlights_text" name="highlights_text" class="form-control" rows="5" placeholder="Handcrafted fashion jewellery with a premium finish&#10;Lightweight, skin-friendly and made for everyday wear&#10;Secure screw-back closure for added comfort">{{ old('highlights_text', implode("\n", $item->highlights ?? [])) }}</textarea>
                <span class="form-hint">One point per line. These become the green ticks under Description.</span>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section__head">
            <h3>Price & sales</h3>
            <p>The amount shown on the product page, plus the sold count next to reviews.</p>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="regular_price">Regular price (₹) *</label>
                <input id="regular_price" type="number" step="0.01" min="0" name="regular_price" class="form-control @error('regular_price') is-invalid @enderror" value="{{ old('regular_price', $item->regular_price ?? '') }}" required placeholder="2499">
                @error('regular_price')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="sale_price">Sale price (₹)</label>
                <input id="sale_price" type="number" step="0.01" min="0" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price', $item->sale_price ?? '') }}" placeholder="Optional offer price">
                <span class="form-hint">If filled, this is the big price and regular price is struck through.</span>
                @error('sale_price')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="tax_note">Tax note</label>
                <input id="tax_note" type="text" name="tax_note" class="form-control" value="{{ old('tax_note', $item->tax_note ?? 'Inclusive of all taxes') }}">
            </div>
            <div class="form-group">
                <label for="sold_count">Sold count *</label>
                <input id="sold_count" type="number" min="0" step="1" name="sold_count" class="form-control @error('sold_count') is-invalid @enderror" value="{{ old('sold_count', $item->sold_count ?? 0) }}" required>
                <span class="form-hint">Shown as “Sold 132” next to reviews. Enter this value yourself; it is not calculated from orders.</span>
                @error('sold_count')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="form-section" data-stock-fields>
        <div class="form-section__head">
            <h3>Stock</h3>
            <p>Quantity is the source of truth. In Stock requires a quantity of 1 or more.</p>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input id="quantity" type="number" min="0" step="1" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ $quantity }}" required data-stock-quantity>
                <span class="form-hint">Available pieces customers can add to cart.</span>
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

    <div class="form-section">
        <div class="form-section__head">
            <h3>Product details</h3>
            <p>These rows appear in the Product Details tab and beside the price on the product page.</p>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="metal">Material / finish</label>
                <input id="metal" type="text" name="metal" class="form-control" value="{{ old('metal', $item->metal ?? '') }}" placeholder="Gold-plated alloy">
                <span class="form-hint">Example: Gold-plated, Oxidized, Silver-plated, Antique.</span>
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
                <label for="weight">Product weight (grams)</label>
                <input id="weight" type="number" step="0.001" min="0" name="weight" class="form-control" value="{{ old('weight', $item->weight ?? '') }}" placeholder="18.350">
            </div>
            <div class="form-group">
                <label for="dimensions_text">Dimensions</label>
                <input id="dimensions_text" type="text" name="dimensions_text" class="form-control" value="{{ old('dimensions_text', $item->dimensions_text ?? '') }}" placeholder="Length 4.2 cm">
            </div>
            <div class="form-group">
                <label for="occasion">Occasion</label>
                <input id="occasion" type="text" name="occasion" class="form-control" value="{{ old('occasion', $item->occasion ?? '') }}" placeholder="Bridal, Festive">
            </div>
            <div class="form-group">
                <label for="certification">Quality note</label>
                <input id="certification" type="text" name="certification" class="form-control" value="{{ old('certification', $item->certification ?? '') }}" placeholder="Quality-checked finish">
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section__head">
            <h3>Listing</h3>
            <p>Where this product appears on the website.</p>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                    <option value="">Select category</option>
                    @foreach ($jewelleryCategories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id ?? request('category_id')) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <span class="form-hint">Choose a category from Admin → Categories.</span>
                @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group full">
                <span id="collections-label">Show on jewellery collections *</span>
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
                <span class="form-hint">Tick one or more collections. Selected names appear below and can be removed.</span>
                @error('collections')
                    <span class="invalid-feedback" style="display:block;">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group form-check">
                <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
                <label for="is_active">Publish on website</label>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section__head">
            <h3>Care instructions</h3>
            <p>Shown on the Care Instructions tab of the product page.</p>
        </div>
        <div class="form-grid">
            <div class="form-group full">
                <label for="care_instructions">Care instructions</label>
                <textarea id="care_instructions" name="care_instructions" class="form-control @error('care_instructions') is-invalid @enderror" rows="5" placeholder="Keep away from chemicals, perfumes and sprays&#10;Store separately in a soft jewellery pouch or box&#10;Wipe gently with a soft dry cloth after use">{{ old('care_instructions', $item->care_instructions ?? '') }}</textarea>
                <span class="form-hint">One instruction per line. Leave blank to use the standard care notes.</span>
                @error('care_instructions')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section__head">
            <h3>Shipping & returns</h3>
            <p>Shown on the Shipping & Returns tab of the product page.</p>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="estimated_delivery">Estimated delivery</label>
                <input id="estimated_delivery" type="text" name="estimated_delivery" class="form-control" value="{{ old('estimated_delivery', $item->estimated_delivery ?? '3–5 business days') }}" placeholder="3–5 business days">
            </div>
            <div class="form-group full">
                <label for="shipping_information">Shipping information</label>
                <textarea id="shipping_information" name="shipping_information" class="form-control @error('shipping_information') is-invalid @enderror" rows="4" placeholder="Shipping charges are calculated at checkout. Orders are dispatched within 1–2 business days.">{{ old('shipping_information', $item->shipping_information ?? '') }}</textarea>
                @error('shipping_information')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group full">
                <label for="return_policy">Return policy</label>
                <textarea id="return_policy" name="return_policy" class="form-control @error('return_policy') is-invalid @enderror" rows="4" placeholder="Returns accepted within 15 days on unused jewellery, subject to the return policy.">{{ old('return_policy', $item->return_policy ?? '') }}</textarea>
                @error('return_policy')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</div>
