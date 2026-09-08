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

class InventoryController extends AdminController
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected InventoryRepository $repository
    ) {}

    public function index(Request $request)
    {
        $items = $this->repository->paginate($request->all());
        $items->load(['product', 'warehouse', 'variant']);
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);

        return view('admin.inventory.index', compact('items', 'warehouses'));
    }

    public function adjustForm()
    {
        return view('admin.inventory.adjust', [
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku']),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function adjust(InventoryAdjustmentRequest $request)
    {
        $data = $request->validated();
        $this->inventoryService->adjustStock(
            (int) $data['product_id'],
            $data['product_variant_id'] ?? null,
            (int) $data['warehouse_id'],
            (int) $data['quantity_change'],
            $data['reason'],
            $data['type'] ?? 'adjustment'
        );

        return $this->success('Stock adjusted.', 'admin.inventory.index');
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
