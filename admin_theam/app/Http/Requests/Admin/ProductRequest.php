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
            'category_id' => ['nullable', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'product_type' => ['required', Rule::in(ProductType::all())],
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
            'main_image' => ['nullable', 'image', 'max:5120'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:5120'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'return_eligible' => ['nullable', 'boolean'],
            'return_days' => ['nullable', 'integer', 'min:0'],
            'warranty' => ['nullable', 'string', 'max:255'],
            'shipping_class_id' => ['nullable', 'exists:shipping_classes,id'],
            'estimated_delivery' => ['nullable', 'string', 'max:100'],
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
        $this->merge($merged);
    }
}
