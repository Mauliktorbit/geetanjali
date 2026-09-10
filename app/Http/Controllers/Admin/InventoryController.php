<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\InventoryAdjustmentRequest;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Repositories\InventoryRepository;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class InventoryController extends AdminController
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected InventoryRepository $repository
    ) {}

    public function index(Request $request)
    {
        $items = $this->repository->paginateProducts($request->all());

        return view('admin.inventory.index', compact('items'));
    }

    public function show(Product $product)
    {
        $product->load('category:id,slug,name');
        $inventories = Inventory::query()
            ->with('warehouse')
            ->where('product_id', $product->id)
            ->get();

        $stock = (int) $inventories->sum('available_stock');
        $reserved = (int) $inventories->sum('reserved_stock');
        $status = $stock <= 0 ? 'out' : ($stock <= 5 ? 'low' : 'in');
        $statusLabel = $status === 'out' ? 'Out of stock' : ($status === 'low' ? 'Low stock' : 'In stock');
        $badge = $status === 'out' ? 'inactive' : ($status === 'low' ? 'warning' : 'active');

        $movements = StockMovement::query()
            ->with('user')
            ->where('product_id', $product->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.inventory.show', [
            'item' => $product,
            'inventories' => $inventories,
            'stock' => $stock,
            'reserved' => $reserved,
            'statusLabel' => $statusLabel,
            'badge' => $badge,
            'movements' => $movements,
        ]);
    }

    public function adjustForm(Request $request, ?Product $product = null)
    {
        if ($product === null && $request->filled('product_id')) {
            $product = Product::query()->find($request->query('product_id'));
            if ($product) {
                return redirect()->route('admin.inventory.adjust', $product);
            }
        }

        $products = Product::query()->with('category:id,slug')->orderBy('name')->get(['id', 'name', 'sku', 'main_image', 'gallery_images', 'category_id']);
        if ($product) {
            $product->loadMissing('category:id,slug');
            if (! $products->contains('id', $product->id)) {
                $products = $products->prepend($product)->unique('id')->values();
            }
        }

        $currentStock = $product
            ? $this->inventoryService->availableStockForProduct((int) $product->id)
            : 0;

        return view('admin.inventory.adjust', [
            'products' => $products,
            'selectedProduct' => $product,
            'selectedProductId' => $product?->id,
            'currentStock' => $currentStock,
        ]);
    }

    public function adjust(InventoryAdjustmentRequest $request)
    {
        $data = $request->validated();
        $productId = (int) $data['product_id'];
        $newStock = (int) $data['stock'];
        $currentStock = $this->inventoryService->availableStockForProduct($productId);
        $change = $newStock - $currentStock;

        if ($change === 0) {
            return $this->success('Stock is already '.$newStock.'.', 'admin.inventory.index');
        }

        try {
            $this->inventoryService->adjustStock(
                $productId,
                null,
                $this->inventoryService->defaultWarehouseId(),
                $change,
                'Stock update'
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('Stock updated to '.$newStock.'.', 'admin.inventory.index');
    }

    public function transferForm()
    {
        return view('admin.inventory.transfer', [
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'from_warehouse_id' => ['required', 'exists:warehouses,id', 'different:to_warehouse_id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->inventoryService->transferStock(
            (int) $data['product_id'],
            $data['product_variant_id'] ?? null,
            (int) $data['from_warehouse_id'],
            (int) $data['to_warehouse_id'],
            (int) $data['quantity'],
            $data['reason'] ?? 'Stock transfer'
        );

        return $this->success('Stock transferred.', 'admin.inventory.index');
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'warehouse', 'user'])->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $items = $query->paginate(25)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);
        $products = Product::orderBy('name')->limit(300)->get(['id', 'name', 'sku']);

        return view('admin.inventory.movements', compact('items', 'warehouses', 'products'));
    }

    public function valuation(Request $request)
    {
        $items = Inventory::with(['product', 'warehouse'])
            ->when($request->warehouse_id, fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->get()
            ->map(function (Inventory $inv) {
                $cost = (float) ($inv->product?->cost_price ?? 0);
                $inv->valuation = round($cost * (int) $inv->current_stock, 2);

                return $inv;
            });

        $total = $items->sum('valuation');
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);

        return view('admin.inventory.valuation', compact('items', 'total', 'warehouses'));
    }

    public function ageing(Request $request)
    {
        $items = Inventory::with(['product', 'warehouse'])
            ->where('available_stock', '>', 0)
            ->get()
            ->map(function (Inventory $inv) {
                $lastIn = StockMovement::where('product_id', $inv->product_id)
                    ->where('warehouse_id', $inv->warehouse_id)
                    ->where('quantity_change', '>', 0)
                    ->latest()
                    ->value('created_at');
                $inv->last_in_at = $lastIn;
                $inv->age_days = $lastIn ? now()->diffInDays($lastIn) : null;

                return $inv;
            })
            ->sortByDesc('age_days')
            ->values();

        return view('admin.inventory.ageing', compact('items'));
    }

    public function lowStock()
    {
        $items = $this->repository->lowStockItems();

        return view('admin.inventory.low-stock', compact('items'));
    }

    public function outOfStock()
    {
        $items = $this->repository->outOfStockItems();

        return view('admin.inventory.out-of-stock', compact('items'));
    }
}
