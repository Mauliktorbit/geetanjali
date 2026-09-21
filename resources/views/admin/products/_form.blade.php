@php
    $item = $item ?? null;
    $selectedCollectionIds = collect(old('collections', $item?->collections?->pluck('id')->all() ?? []))
        ->filter()
        ->map(fn ($id) => (string) $id)
        ->values();
    $jewelleryCategories = $categories ?? collect();
@endphp

<input type="hidden" name="product_type" value="simple">
<input type="hidden" name="min_order_qty" value="1">

<div class="product-form">
    <div class="form-section">
        <div class="form-section__head">
            <h3>Photos</h3>
            <p>Shown in the product gallery and “Click to Zoom”.</p>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="main_image">Main photo</label>
                <input id="main_image" type="file" name="main_image" class="form-control @error('main_image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                <span class="form-hint">JPG, PNG or WebP, up to 10 MB. This is the first large image on the product page.</span>
                @error('main_image')<span class="invalid-feedback">{{ $message }}</span>@enderror
                @if (!empty($item?->main_image))
                    <img src="{{ storefront_image($item->main_image) }}" class="thumb-preview" alt="Current main photo">
                @endif
            </div>
            <div class="form-group">
                <label for="gallery_images">More photos</label>
                <input id="gallery_images" type="file" name="gallery_images[]" class="form-control @error('gallery_images') is-invalid @enderror @error('gallery_images.0') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" multiple>
                <span class="form-hint">Optional extra photos (JPG, PNG or WebP, up to 10 MB each). They appear as thumbnails under the main photo.</span>
                @error('gallery_images')<span class="invalid-feedback">{{ $message }}</span>@enderror
                @error('gallery_images.0')<span class="invalid-feedback">{{ $message }}</span>@enderror
                @if (!empty($item?->gallery_images))
                    <div class="gallery-preview">
                        @foreach ($item->gallery_images as $image)
                            <img src="{{ storefront_image($image) }}" class="thumb-preview" alt="">
                        @endforeach
                    </div>
                @endif
            </div>
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
                <input id="sku" type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku ?? '') }}" placeholder="GJ-KE-2201">
                <span class="form-hint">Shown in the Product Details tab. Leave blank to auto-generate.</span>
            </div>
            <div class="form-group">
                <label for="badge">Badge</label>
                <input id="badge" type="text" name="badge" class="form-control" value="{{ old('badge', $item->badge ?? '') }}" placeholder="BEST SELLER">
                <span class="form-hint">Ribbon on the photo, e.g. BEST SELLER, NEW, LIMITED.</span>
            </div>
            <div class="form-group full">
                <label for="short_description">Short description</label>
                <textarea id="short_description" name="short_description" class="form-control" rows="2" placeholder="Exquisite kundan earrings crafted in 22K gold with emerald drops...">{{ old('short_description', $item->short_description ?? '') }}</textarea>
            </div>
            <div class="form-group full">
                <label for="description">Full description</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="These earrings are a masterpiece of traditional craftsmanship...">{{ old('description', $item->description ?? '') }}</textarea>
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
            <h3>Price</h3>
            <p>The amount shown on the product page.</p>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="regular_price">Regular price (₹) *</label>
                <input id="regular_price" type="number" step="0.01" min="0" name="regular_price" class="form-control @error('regular_price') is-invalid @enderror" value="{{ old('regular_price', $item->regular_price ?? '') }}" required placeholder="124500">
                @error('regular_price')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="sale_price">Sale price (₹)</label>
                <input id="sale_price" type="number" step="0.01" min="0" name="sale_price" class="form-control" value="{{ old('sale_price', $item->sale_price ?? '') }}" placeholder="Optional offer price">
                <span class="form-hint">If filled, this is the big price and regular price is struck through.</span>
            </div>
            <div class="form-group">
                <label for="tax_note">Tax note</label>
                <input id="tax_note" type="text" name="tax_note" class="form-control" value="{{ old('tax_note', $item->tax_note ?? 'Inclusive of all taxes') }}">
            </div>
            <div class="form-group">
                <label for="sold_count">Sold count</label>
                <input id="sold_count" type="number" min="0" name="sold_count" class="form-control" value="{{ old('sold_count', $item->sold_count ?? 0) }}">
                <span class="form-hint">Shown as “Sold 120+” next to reviews.</span>
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section__head">
            <h3>Product details</h3>
            <p>These rows appear in the Product Details tab and beside the price.</p>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="metal">Metal</label>
                <input id="metal" type="text" name="metal" class="form-control" value="{{ old('metal', $item->metal ?? '') }}" placeholder="22K Yellow Gold">
            </div>
            <div class="form-group">
                <label for="purity">Purity</label>
                <input id="purity" type="text" name="purity" class="form-control" value="{{ old('purity', $item->purity ?? '') }}" placeholder="22K">
            </div>
            <div class="form-group">
                <label for="stone">Stone</label>
                <input id="stone" type="text" name="stone" class="form-control" value="{{ old('stone', $item->stone ?? '') }}" placeholder="Emerald, Kundan">
            </div>
            <div class="form-group">
                <label for="style">Style</label>
                <input id="style" type="text" name="style" class="form-control" value="{{ old('style', $item->style ?? '') }}" placeholder="Traditional">
            </div>
            <div class="form-group">
                <label for="weight">Net weight (grams)</label>
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
                <label for="certification">Certification</label>
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
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">Select category</option>
                    @foreach ($jewelleryCategories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id ?? request('category_id')) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <span class="form-hint">Choose a category from Admin → Categories.</span>
            </div>
            <div class="form-group">
                <label id="collections-label">Show on Jewellery collection</label>
                <div class="collection-picker" data-collection-picker>
                    <button
                        type="button"
                        class="form-control collection-picker__toggle"
                        data-collection-toggle
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-labelledby="collections-label"
                    >
                        <span data-collection-label>Select collection</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="collection-picker__menu" data-collection-menu hidden>
                        @foreach ($collections ?? [] as $collection)
                            <label class="collection-picker__option">
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
                    <div class="collection-picker__chips" data-collection-chips></div>
                </div>
                <span class="form-hint">Tick one or more collections from Admin → Collections. Selected names appear below.</span>
                @error('collections')
                    <span class="form-hint" style="color: #b91c1c;">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="estimated_delivery">Estimated delivery</label>
                <input id="estimated_delivery" type="text" name="estimated_delivery" class="form-control" value="{{ old('estimated_delivery', $item->estimated_delivery ?? '3–5 business days') }}">
            </div>
            <div class="form-group form-check">
                <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
                <label for="is_active">Publish on website</label>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var root = document.querySelector('[data-collection-picker]');
    if (!root) {
        return;
    }

    var toggle = root.querySelector('[data-collection-toggle]');
    var label = root.querySelector('[data-collection-label]');
    var menu = root.querySelector('[data-collection-menu]');
    var chips = root.querySelector('[data-collection-chips]');
    var checks = root.querySelectorAll('input[type="checkbox"][name="collections[]"]');

    function selectedInputs() {
        return Array.prototype.filter.call(checks, function (input) {
            return input.checked;
        });
    }

    function closeMenu() {
        root.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        menu.hidden = true;
    }

    function openMenu() {
        root.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        menu.hidden = false;
    }

    function render() {
        var selected = selectedInputs();
        if (selected.length === 0) {
            label.textContent = 'Select collection';
        } else if (selected.length === 1) {
            label.textContent = selected[0].getAttribute('data-label');
        } else {
            label.textContent = selected.length + ' collections selected';
        }

        chips.replaceChildren();
        selected.forEach(function (input) {
            var chip = document.createElement('span');
            chip.className = 'collection-picker__chip';

            var text = document.createElement('span');
            text.textContent = input.getAttribute('data-label') || '';

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('aria-label', 'Remove ' + (input.getAttribute('data-label') || 'collection'));
            remove.textContent = '×';
            remove.addEventListener('click', function () {
                input.checked = false;
                render();
            });

            chip.appendChild(text);
            chip.appendChild(remove);
            chips.appendChild(chip);
        });

        chips.hidden = selected.length === 0;
    }

    toggle.addEventListener('click', function () {
        if (root.classList.contains('is-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    document.addEventListener('click', function (event) {
        if (!root.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });

    Array.prototype.forEach.call(checks, function (input) {
        input.addEventListener('change', render);
    });

    render();
})();
</script>
@endpush
