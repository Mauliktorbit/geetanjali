<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'product_type' => ['nullable', Rule::in(ProductType::all())],
            'regular_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:regular_price'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'hsn_sac' => ['nullable', 'string', 'max:50'],
            'min_order_qty' => ['nullable', 'integer', 'min:1'],
            'max_order_qty' => ['nullable', 'integer', 'min:1'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'main_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'return_eligible' => ['nullable', 'boolean'],
            'return_days' => ['nullable', 'integer', 'min:0'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:40'],
            'metal' => ['nullable', 'string', 'max:80'],
            'purity' => ['nullable', 'string', 'max:40'],
            'stone' => ['nullable', 'string', 'max:120'],
            'style' => ['nullable', 'string', 'max:80'],
            'occasion' => ['nullable', 'string', 'max:120'],
            'certification' => ['nullable', 'string', 'max:120'],
            'dimensions_text' => ['nullable', 'string', 'max:120'],
            'tax_note' => ['nullable', 'string', 'max:120'],
            'highlights' => ['nullable', 'array'],
            'highlights.*' => ['nullable', 'string', 'max:255'],
            'highlights_text' => ['nullable', 'string'],
            'care_instructions' => ['nullable', 'string', 'max:5000'],
            'sold_count' => ['required', 'integer', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'stock_status' => ['required', Rule::in(['in_stock', 'out_of_stock'])],
            'collections' => ['required', 'array', 'min:1'],
            'collections.*' => ['integer', Rule::exists('collections', 'id')],
            'keep_gallery' => ['nullable', 'array'],
            'keep_gallery.*' => ['nullable', 'string', 'max:500'],
            'remove_main_image' => ['nullable', 'boolean'],
            'gallery_sync' => ['nullable', 'boolean'],
            'shipping_class_id' => ['nullable', 'exists:shipping_classes,id'],
            'estimated_delivery' => ['nullable', 'string', 'max:100'],
            'shipping_information' => ['nullable', 'string', 'max:5000'],
            'return_policy' => ['nullable', 'string', 'max:5000'],
            'cod_available' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_new_arrival' => ['nullable', 'boolean'],
            'is_bestseller' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'related_products' => ['nullable', 'array'],
            'related_products.*' => ['integer', 'exists:products,id'],
            'frequently_bought_together' => ['nullable', 'array'],
            'frequently_bought_together.*' => ['integer', 'exists:products,id'],
            'attribute_matrix' => ['nullable', 'array'],
            'attribute_matrix.*' => ['array'],
            'attribute_matrix.*.*' => ['integer', 'exists:attribute_values,id'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.cost' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.barcode' => ['nullable', 'string', 'max:100'],
            'variants.*.weight' => ['nullable', 'numeric', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'variants.*.attribute_value_ids' => ['nullable', 'array'],
            'variants.*.attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please choose a category.',
            'collections.required' => 'Please choose where this product should appear.',
            'collections.min' => 'Please choose at least one collection.',
            'main_image.image' => 'The main photo must be a JPG, PNG or WebP image.',
            'main_image.uploaded' => 'The main photo could not be uploaded. Please use a JPG, PNG or WebP under 10 MB.',
            'main_image.max' => 'The main photo must be 10 MB or smaller.',
            'gallery_images.*.image' => 'Each extra photo must be a JPG, PNG or WebP image.',
            'gallery_images.*.uploaded' => 'An extra photo could not be uploaded. Please use JPG, PNG or WebP files under 10 MB.',
            'gallery_images.*.max' => 'Each extra photo must be 10 MB or smaller.',
            'sold_count.required' => 'Enter how many pieces have been sold, or 0.',
            'sold_count.integer' => 'Sold count must be a whole number.',
            'sold_count.min' => 'Sold count cannot be negative.',
            'quantity.required' => 'Enter the available quantity, or 0.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity cannot be negative.',
            'stock_status.required' => 'Choose whether this product is in stock.',
            'stock_status.in' => 'Choose In Stock or Out of Stock.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $status = (string) $this->input('stock_status', 'out_of_stock');
            $quantity = (int) $this->input('quantity', 0);

            if ($status === 'in_stock' && $quantity < 1) {
                $validator->errors()->add('quantity', 'Enter a quantity of at least 1 for in-stock products, or mark it Out of Stock.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $booleans = [
            'return_eligible', 'cod_available', 'is_featured',
            'is_new_arrival', 'is_bestseller', 'is_active',
        ];

        $merged = [];
        foreach ($booleans as $key) {
            $merged[$key] = $this->boolean($key);
        }

        if ($this->exists('highlights_text')) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $this->input('highlights_text', '')) ?: [];
            $merged['highlights'] = array_values(array_filter(array_map('trim', $lines)));
        }

        $merged['product_type'] = $this->input('product_type') ?: ProductType::SIMPLE;

        $collections = $this->input('collections', []);
        if (! is_array($collections)) {
            $collections = filled($collections) ? [$collections] : [];
        }
        $merged['collections'] = array_values(array_filter($collections));
        $merged['sold_count'] = max(0, (int) $this->input('sold_count', 0));
        $merged['quantity'] = max(0, (int) $this->input('quantity', 0));
        $merged['stock_status'] = $this->input('stock_status') === 'in_stock' ? 'in_stock' : 'out_of_stock';
        $merged['remove_main_image'] = $this->boolean('remove_main_image');
        $merged['gallery_sync'] = $this->boolean('gallery_sync');

        if ($merged['stock_status'] === 'out_of_stock') {
            $merged['quantity'] = 0;
        }

        $this->merge($merged);
        $this->dropEmptyUploads('gallery_images');
    }

    private function dropEmptyUploads(string $key): void
    {
        $files = $this->file($key);
        if (! is_array($files)) {
            return;
        }

        $kept = array_values(array_filter($files, function ($file) {
            return $file && $file->getError() !== UPLOAD_ERR_NO_FILE;
        }));

        $this->files->set($key, $kept);
    }
}
