<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected array $filters = []) {}

    public function query()
    {
        $query = Product::query()->with(['category', 'brand', 'taxRate']);

        if (! empty($this->filters['ids'])) {
            $query->whereIn('id', $this->filters['ids']);
        }
        if (! empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }
        if (! empty($this->filters['brand_id'])) {
            $query->where('brand_id', $this->filters['brand_id']);
        }
        if (isset($this->filters['is_active'])) {
            $query->where('is_active', (bool) $this->filters['is_active']);
        }
        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'SKU', 'Barcode', 'Type', 'Category', 'Brand',
            'Regular Price', 'Sale Price', 'Cost Price', 'HSN/SAC',
            'Tax Rate', 'Active', 'Featured', 'Archived', 'Created At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->sku,
            $product->barcode,
            $product->product_type,
            $product->category?->name,
            $product->brand?->name,
            $product->regular_price,
            $product->sale_price,
            $product->cost_price,
            $product->hsn_sac,
            $product->taxRate?->name,
            $product->is_active ? 'Yes' : 'No',
            $product->is_featured ? 'Yes' : 'No',
            $product->is_archived ? 'Yes' : 'No',
            $product->created_at?->toDateTimeString(),
        ];
    }
}
