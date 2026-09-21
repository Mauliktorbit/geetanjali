<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;
use App\Exports\ProductsExport;
use App\Services\StorefrontCatalogService;
use App\Http\Requests\Admin\ProductRequest;
use App\Imports\ProductsImport;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ShippingClass;
use App\Models\Tag;
use App\Models\TaxRate;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends AdminController
{
    public function __construct(
        protected ProductService $service,
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $items = $this->service->paginate($request->all());
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        $collections = StorefrontCatalogService::adminCollections();

        return view('admin.products.index', compact('items', 'categories', 'collections'));
    }

    public function create()
    {
        return view('admin.products.create', $this->formData());
    }

    public function store(ProductRequest $request)
    {
        $data = $this->prepareProductData($request);
        $quantity = (int) ($data['quantity'] ?? 0);
        $product = $this->service->create($data);
        $this->inventoryService->setProductAvailableStock($product->id, $quantity);

        return $this->success('Product created successfully.', 'admin.products.show', [$product]);
    }

    public function show(Product $product)
    {
        $product->load([
            'category', 'subcategory', 'brand', 'taxRate', 'shippingClass',
            'tags', 'variants.attributeValues', 'inventories.warehouse',
            'collections',
            'relatedProducts', 'frequentlyBoughtTogether',
        ]);

        return view('admin.products.show', ['item' => $product]);
    }

    public function edit(Product $product)
    {
        $product->load(['tags', 'variants.attributeValues', 'relatedProducts', 'frequentlyBoughtTogether', 'collections', 'inventories']);

        $data = $this->formData();
        if ($product->category_id && ! $data['categories']->contains('id', $product->category_id)) {
            $current = Category::query()->whereKey($product->category_id)->get(['id', 'name', 'slug']);
            $data['categories'] = $data['categories']->concat($current);
        }

        return view('admin.products.edit', array_merge($data, ['item' => $product]));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $this->prepareProductData($request, $product);
        $quantity = (int) ($data['quantity'] ?? 0);
        $this->service->update($product, $data);
        $this->inventoryService->setProductAvailableStock($product->id, $quantity);

        return $this->success('Product updated successfully.', 'admin.products.show', [$product]);
    }

    public function destroy(Product $product)
    {
        $this->service->delete($product);

        return $this->success('Product deleted successfully.', 'admin.products.index');
    }

    public function duplicate(Product $product)
    {
        $copy = $this->service->duplicate($product);

        return $this->success('Product duplicated.', 'admin.products.edit', [$copy]);
    }

    public function archive(Product $product)
    {
        $this->service->archive($product);

        return $this->success('Product archived.');
    }

    public function activate(Product $product)
    {
        $product->update(['is_active' => true, 'is_archived' => false]);

        return $this->success('Product activated.');
    }

    public function deactivate(Product $product)
    {
        $product->update(['is_active' => false]);

        return $this->success('Product deactivated.');
    }

    public function bulkUpdate(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (! $ids) {
            return $this->error('Please select at least one product.');
        }

        match ($action) {
            'delete' => $this->service->bulkDelete($ids),
            'activate' => $this->service->bulkUpdateStatus($ids, true),
            'deactivate' => $this->service->bulkUpdateStatus($ids, false),
            'archive' => $this->service->bulkUpdate($ids, ['is_archived' => true, 'is_active' => false]),
            'price' => $this->service->bulkUpdatePrice(
                $ids,
                (float) $request->input('regular_price'),
                $request->filled('sale_price') ? (float) $request->input('sale_price') : null
            ),
            'category' => $this->service->bulkUpdateCategory(
                $ids,
                (int) $request->input('category_id'),
                $request->filled('subcategory_id') ? (int) $request->input('subcategory_id') : null
            ),
            'tax' => $this->service->bulkUpdateTax($ids, (int) $request->input('tax_rate_id')),
            'stock' => $this->service->bulkUpdateStock(
                $ids,
                (int) $request->input('warehouse_id'),
                (int) $request->input('stock'),
                $this->inventoryService
            ),
            default => null,
        };

        if (! in_array($action, ['delete', 'activate', 'deactivate', 'archive', 'price', 'category', 'tax', 'stock'], true)) {
            return $this->error('Invalid bulk action.');
        }

        $message = match ($action) {
            'delete' => 'Selected products deleted.',
            'activate' => 'Selected products published.',
            'deactivate' => 'Selected products unpublished.',
            default => 'Bulk action applied.',
        };

        return $this->success($message);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
        ]);

        Excel::import(new ProductsImport($this->service), $request->file('file'));

        return $this->success('Products imported successfully.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'products-' . now()->format('Ymd-His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $export = new ProductsExport($request->all());

        if ($format === 'csv') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename);
    }

    protected function formData(): array
    {
        return [
            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'taxRates' => TaxRate::orderBy('name')->get(['id', 'name']),
            'shippingClasses' => ShippingClass::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'collections' => StorefrontCatalogService::adminCollections(),
            'attributes' => Attribute::with('values')->orderBy('name')->get(),
            'products' => Product::where('is_archived', false)->orderBy('name')->get(['id', 'name', 'sku']),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
        ];
    }

    protected function prepareProductData(ProductRequest $request, ?Product $product = null): array
    {
        $data = $request->validated();

        $data['product_type'] = $data['product_type'] ?? ProductType::SIMPLE;
        $data['min_order_qty'] = $data['min_order_qty'] ?? 1;
        $data['tax_note'] = filled($data['tax_note'] ?? null) ? $data['tax_note'] : 'Inclusive of all taxes';
        $data['estimated_delivery'] = filled($data['estimated_delivery'] ?? null) ? $data['estimated_delivery'] : '3–5 business days';
        $data['return_eligible'] = true;
        $data['return_days'] = $data['return_days'] ?? 15;
        $data['cod_available'] = $data['cod_available'] ?? true;
        if ($product === null) {
            $data['published_at'] = $data['published_at'] ?? now();
        } else {
            unset($data['published_at']);
        }

        $badge = strtoupper((string) ($data['badge'] ?? ''));
        if ($badge !== '') {
            $data['is_bestseller'] = str_contains($badge, 'BEST');
            $data['is_new_arrival'] = str_contains($badge, 'NEW');
            $data['is_featured'] = str_contains($badge, 'LIMITED') || ($data['is_bestseller'] ?? false);
        }

        $collectionIds = array_values(array_filter(array_map('intval', $data['collections'] ?? [])));
        if ($collectionIds !== []) {
            $slugs = Collection::query()->whereIn('id', $collectionIds)->pluck('slug');
            $data['is_new_arrival'] = $slugs->contains('new-arrivals') || ($data['is_new_arrival'] ?? false);
        }

        if ($request->hasFile('main_image') && $request->file('main_image')->isValid()) {
            if ($product?->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $data['main_image'] = $request->file('main_image')->store('uploads/products', 'public');
        } elseif (! empty($data['remove_main_image'])) {
            if ($product?->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $data['main_image'] = null;
        } else {
            unset($data['main_image']);
        }

        $gallery = array_values(array_filter(
            ! empty($data['gallery_sync'])
                ? (array) ($data['keep_gallery'] ?? [])
                : ($product?->gallery_images ?? [])
        ));

        if ($request->hasFile('gallery_images')) {
            foreach ((array) $request->file('gallery_images') as $file) {
                if ($file && $file->isValid()) {
                    $gallery[] = $file->store('uploads/products/gallery', 'public');
                }
            }
        }

        if ($product) {
            foreach (array_diff($product->gallery_images ?? [], $gallery) as $removed) {
                Storage::disk('public')->delete($removed);
            }
        }

        $data['gallery_images'] = array_values(array_unique($gallery));
        unset($data['keep_gallery'], $data['remove_main_image'], $data['gallery_sync']);

        $data['sold_count'] = max(0, (int) ($data['sold_count'] ?? 0));
        $data['care_instructions'] = filled($data['care_instructions'] ?? null) ? trim((string) $data['care_instructions']) : null;
        $data['shipping_information'] = filled($data['shipping_information'] ?? null) ? trim((string) $data['shipping_information']) : null;
        $data['return_policy'] = filled($data['return_policy'] ?? null) ? trim((string) $data['return_policy']) : null;

        return $data;
    }
}
