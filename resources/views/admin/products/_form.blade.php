@php $item = $item ?? null; @endphp
<div class="form-section">
    <h3>Basic info</h3>
    <div class="form-grid">
        <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required></div>
        <div class="form-group"><label>Slug</label><input type="text" name="slug" class="form-control" value="{{ old('slug', $item->slug ?? '') }}"></div>
        <div class="form-group"><label>SKU</label><input type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku ?? '') }}"></div>
        <div class="form-group"><label>Barcode</label><input type="text" name="barcode" class="form-control" value="{{ old('barcode', $item->barcode ?? '') }}"></div>
        <div class="form-group">
            <label>Product type *</label>
            <select name="product_type" id="product_type" class="form-control" required>
                @foreach(\App\Enums\ProductType::labels() as $key => $label)
                    <option value="{{ $key }}" @selected(old('product_type', $item->product_type ?? 'simple')===$key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" class="form-control">
                <option value="">—</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id ?? '')==$cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Subcategory</label>
            <select name="subcategory_id" class="form-control">
                <option value="">—</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('subcategory_id', $item->subcategory_id ?? '')==$cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Brand</label>
            <select name="brand_id" class="form-control">
                <option value="">—</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(old('brand_id', $item->brand_id ?? '')==$brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group span-2"><label>Short description</label><textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $item->short_description ?? '') }}</textarea></div>
        <div class="form-group span-2"><label>Description</label><textarea name="description" class="form-control" rows="5">{{ old('description', $item->description ?? '') }}</textarea></div>
    </div>
</div>

<div class="form-section">
    <h3>Pricing & tax</h3>
    <div class="form-grid">
        <div class="form-group"><label>Regular price *</label><input type="number" step="0.01" name="regular_price" class="form-control" value="{{ old('regular_price', $item->regular_price ?? '') }}" required></div>
        <div class="form-group"><label>Sale price</label><input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price', $item->sale_price ?? '') }}"></div>
        <div class="form-group"><label>Cost price</label><input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price', $item->cost_price ?? '') }}"></div>
        <div class="form-group">
            <label>Tax rate</label>
            <select name="tax_rate_id" class="form-control">
                <option value="">—</option>
                @foreach($taxRates as $tax)
                    <option value="{{ $tax->id }}" @selected(old('tax_rate_id', $item->tax_rate_id ?? '')==$tax->id)>{{ $tax->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group"><label>HSN/SAC</label><input type="text" name="hsn_sac" class="form-control" value="{{ old('hsn_sac', $item->hsn_sac ?? '') }}"></div>
        <div class="form-group"><label>Min order qty</label><input type="number" name="min_order_qty" class="form-control" value="{{ old('min_order_qty', $item->min_order_qty ?? 1) }}"></div>
        <div class="form-group"><label>Max order qty</label><input type="number" name="max_order_qty" class="form-control" value="{{ old('max_order_qty', $item->max_order_qty ?? '') }}"></div>
    </div>
</div>

<div class="form-section">
    <h3>Shipping & dimensions</h3>
    <div class="form-grid">
        <div class="form-group"><label>Weight</label><input type="number" step="0.001" name="weight" class="form-control" value="{{ old('weight', $item->weight ?? '') }}"></div>
        <div class="form-group"><label>Length</label><input type="number" step="0.01" name="length" class="form-control" value="{{ old('length', $item->length ?? '') }}"></div>
        <div class="form-group"><label>Width</label><input type="number" step="0.01" name="width" class="form-control" value="{{ old('width', $item->width ?? '') }}"></div>
        <div class="form-group"><label>Height</label><input type="number" step="0.01" name="height" class="form-control" value="{{ old('height', $item->height ?? '') }}"></div>
        <div class="form-group">
            <label>Shipping class</label>
            <select name="shipping_class_id" class="form-control">
                <option value="">—</option>
                @foreach($shippingClasses as $sc)
                    <option value="{{ $sc->id }}" @selected(old('shipping_class_id', $item->shipping_class_id ?? '')==$sc->id)>{{ $sc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group"><label>Estimated delivery</label><input type="text" name="estimated_delivery" class="form-control" value="{{ old('estimated_delivery', $item->estimated_delivery ?? '') }}"></div>
        <div class="form-group form-check"><label><input type="checkbox" name="cod_available" value="1" @checked(old('cod_available', $item->cod_available ?? true))> COD available</label></div>
    </div>
</div>

<div class="form-section">
    <h3>Media</h3>
    <div class="form-grid">
        <div class="form-group">
            <label>Main image</label>
            <input type="file" name="main_image" class="form-control" accept="image/*">
            @if(!empty($item?->main_image))
                <img src="{{ asset('storage/'.$item->main_image) }}" class="thumb-sm mt-2" alt="">
            @endif
        </div>
        <div class="form-group">
            <label>Gallery images</label>
            <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
        </div>
        <div class="form-group"><label>Video URL</label><input type="url" name="video_url" class="form-control" value="{{ old('video_url', $item->video_url ?? '') }}"></div>
    </div>
</div>

<div class="form-section">
    <h3>SEO & policies</h3>
    <div class="form-grid">
        <div class="form-group"><label>SEO title</label><input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $item->seo_title ?? '') }}"></div>
        <div class="form-group"><label>SEO keywords</label><input type="text" name="seo_keywords" class="form-control" value="{{ old('seo_keywords', $item->seo_keywords ?? '') }}"></div>
        <div class="form-group span-2"><label>SEO description</label><textarea name="seo_description" class="form-control" rows="2">{{ old('seo_description', $item->seo_description ?? '') }}</textarea></div>
        <div class="form-group form-check"><label><input type="checkbox" name="return_eligible" value="1" @checked(old('return_eligible', $item->return_eligible ?? true))> Return eligible</label></div>
        <div class="form-group"><label>Return days</label><input type="number" name="return_days" class="form-control" value="{{ old('return_days', $item->return_days ?? 7) }}"></div>
        <div class="form-group"><label>Warranty</label><input type="text" name="warranty" class="form-control" value="{{ old('warranty', $item->warranty ?? '') }}"></div>
        <div class="form-group"><label>Published at</label><input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($item->published_at ?? null)->format('Y-m-d\TH:i')) }}"></div>
    </div>
</div>

<div class="form-section">
    <h3>Flags & tags</h3>
    <div class="form-grid">
        <div class="form-group form-check"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))> Active</label></div>
        <div class="form-group form-check"><label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $item->is_featured ?? false))> Featured</label></div>
        <div class="form-group form-check"><label><input type="checkbox" name="is_new_arrival" value="1" @checked(old('is_new_arrival', $item->is_new_arrival ?? false))> New arrival</label></div>
        <div class="form-group form-check"><label><input type="checkbox" name="is_bestseller" value="1" @checked(old('is_bestseller', $item->is_bestseller ?? false))> Bestseller</label></div>
        <div class="form-group span-2">
            <label>Tags</label>
            <select name="tags[]" class="form-control" multiple size="5">
                @foreach($tags as $tag)
                    <option value="{{ $tag->id }}" @selected(collect(old('tags', $item?->tags?->pluck('id')->all() ?? []))->contains($tag->id))>{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Related products</label>
            <select name="related_products[]" class="form-control" multiple size="6">
                @foreach($products as $p)
                    @if(!$item || $p->id !== $item->id)
                        <option value="{{ $p->id }}" @selected(collect(old('related_products', $item?->relatedProducts?->pluck('id')->all() ?? []))->contains($p->id))>{{ $p->name }} ({{ $p->sku }})</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Frequently bought together</label>
            <select name="frequently_bought_together[]" class="form-control" multiple size="6">
                @foreach($products as $p)
                    @if(!$item || $p->id !== $item->id)
                        <option value="{{ $p->id }}" @selected(collect(old('frequently_bought_together', $item?->frequentlyBoughtTogether?->pluck('id')->all() ?? []))->contains($p->id))>{{ $p->name }} ({{ $p->sku }})</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="form-section" id="variants-section">
    <h3>Variants (variable products)</h3>
    <p class="subtitle">Select attribute values, then generate combinations.</p>
    <div id="attr-matrix" class="form-grid">
        @foreach($attributes as $attr)
            <div class="form-group">
                <label>{{ $attr->name }}</label>
                <select class="form-control attr-values" data-attr="{{ $attr->id }}" multiple size="4">
                    @foreach($attr->values as $val)
                        <option value="{{ $val->id }}">{{ $val->value }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-secondary" id="generate-variants">Generate variants</button>
    <div class="table-responsive mt-3">
        <table class="data-table" id="variants-table">
            <thead><tr><th>Name</th><th>SKU</th><th>Price</th><th>Sale</th><th>Cost</th><th>Stock</th><th>Active</th></tr></thead>
            <tbody>
            @foreach(old('variants', $item?->variants ?? []) as $i => $variant)
                @php $v = is_array($variant) ? (object)$variant : $variant; @endphp
                <tr>
                    <td>
                        <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $v->id ?? '' }}">
                        <input type="text" name="variants[{{ $i }}][name]" class="form-control" value="{{ $v->name ?? '' }}">
                        @foreach(($v->attribute_value_ids ?? ($v->attributeValues?->pluck('id')->all() ?? [])) as $avid)
                            <input type="hidden" name="variants[{{ $i }}][attribute_value_ids][]" value="{{ $avid }}">
                        @endforeach
                    </td>
                    <td><input type="text" name="variants[{{ $i }}][sku]" class="form-control" value="{{ $v->sku ?? '' }}"></td>
                    <td><input type="number" step="0.01" name="variants[{{ $i }}][price]" class="form-control" value="{{ $v->price ?? '' }}"></td>
                    <td><input type="number" step="0.01" name="variants[{{ $i }}][sale_price]" class="form-control" value="{{ $v->sale_price ?? '' }}"></td>
                    <td><input type="number" step="0.01" name="variants[{{ $i }}][cost]" class="form-control" value="{{ $v->cost ?? '' }}"></td>
                    <td><input type="number" name="variants[{{ $i }}][stock]" class="form-control" value="{{ $v->stock ?? 0 }}"></td>
                    <td><input type="checkbox" name="variants[{{ $i }}][is_active]" value="1" @checked($v->is_active ?? true)></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
