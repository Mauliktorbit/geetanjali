<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public function __construct(protected ProductService $productService) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $sku = trim((string) ($row['sku'] ?? ''));
            $existing = $sku !== '' ? Product::where('sku', $sku)->first() : null;

            $categoryId = null;
            if (! empty($row['category'])) {
                $categoryId = Category::where('name', $row['category'])->value('id')
                    ?? Category::where('slug', Str::slug($row['category']))->value('id');
            }

            $brandId = null;
            if (! empty($row['brand'])) {
                $brandId = Brand::where('name', $row['brand'])->value('id')
                    ?? Brand::where('slug', Str::slug($row['brand']))->value('id');
            }

            $data = [
                'name' => $name,
                'sku' => $sku ?: null,
                'barcode' => $row['barcode'] ?? null,
                'product_type' => $row['type'] ?? $row['product_type'] ?? 'simple',
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'regular_price' => (float) ($row['regular_price'] ?? $row['price'] ?? 0),
                'sale_price' => isset($row['sale_price']) ? (float) $row['sale_price'] : null,
                'cost_price' => isset($row['cost_price']) ? (float) $row['cost_price'] : null,
                'hsn_sac' => $row['hsn_sac'] ?? null,
                'short_description' => $row['short_description'] ?? null,
                'description' => $row['description'] ?? null,
                'is_active' => $this->toBool($row['active'] ?? $row['is_active'] ?? true),
            ];

            if ($existing) {
                $this->productService->update($existing, $data);
            } else {
                $this->productService->create($data);
            }
        }
    }

    protected function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'yes', 'true', 'y', 'active'], true);
    }
}
