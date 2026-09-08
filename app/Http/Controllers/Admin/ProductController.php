<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Http\Requests\Admin\ProductRequest;
use App\Imports\ProductsImport;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
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
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $brands = Brand::orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact('items', 'categories', 'brands'));
    }

    public function create()
    {
        return view('admin.products.create', $this->formData());
    }

    public function store(ProductRequest $request)
    {
        $data = $this->prepareProductData($request);
        $product = $this->service->create($data);

        return $this->success('Product created successfully.', 'admin.products.show', [$product]);
    }

    public function show(Product $product)
    {
        $product->load([
            'category', 'subcategory', 'brand', 'taxRate', 'shippingClass',
            'tags', 'variants.attributeValues', 'inventories.warehouse',
            'relatedProducts', 'frequentlyBoughtTogether',
        ]);

        return view('admin.products.show', ['item' => $product]);
    }

    public function edit(Product $product)
    {
        $product->load(['tags', 'variants.attributeValues', 'relatedProducts', 'frequentlyBoughtTogether']);

        return view('admin.products.edit', array_merge($this->formData(), ['item' => $product]));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $this->prepareProductData($request, $product);
        $this->service->update($product, $data);

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

        return $this->success('Bulk action applied.');
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
            'categories' => Category::orderBy('name')->get(['id', 'name', 'parent_id']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'taxRates' => TaxRate::orderBy('name')->get(['id', 'name']),
            'shippingClasses' => ShippingClass::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'attributes' => Attribute::with('values')->orderBy('name')->get(),
            'products' => Product::where('is_archived', false)->orderBy('name')->get(['id', 'name', 'sku']),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
        ];
    }

    protected function prepareProductData(ProductRequest $request, ?Product $product = null): array
    {
        $data = $request->validated();

        if ($request->hasFile('main_image')) {
            if ($product?->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $data['main_image'] = $request->file('main_image')->store('uploads/products', 'public');
        } else {
            unset($data['main_image']);
        }

        if ($request->hasFile('gallery_images')) {
            $gallery = $product?->gallery_images ?? [];
            foreach ($request->file('gallery_images') as $file) {
                $gallery[] = $file->store('uploads/products/gallery', 'public');
            }
            $data['gallery_images'] = $gallery;
        } else {
            unset($data['gallery_images']);
        }

        return $data;
    }
}
