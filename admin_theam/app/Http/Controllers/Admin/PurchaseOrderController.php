<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Repositories\PurchaseOrderRepository;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends AdminController
{
    public function __construct(
        protected PurchaseOrderRepository $repository,
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $items = $this->repository->paginate($request->all());
        $items->load(['supplier', 'warehouse']);

        return view('admin.purchase-orders.index', compact('items'));
    }

    public function create()
    {
        return view('admin.purchase-orders.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatePo($request);
        $items = $data['items'];
        unset($data['items']);

        $po = DB::transaction(function () use ($data, $items) {
            $data['po_number'] = $this->generatePoNumber();
            $data['created_by'] = Auth::id();
            $data['status'] = $data['status'] ?? 'draft';

            $totals = $this->calcTotals($items, (float) ($data['shipping_cost'] ?? 0));
            $po = PurchaseOrder::create(array_merge($data, $totals));

            foreach ($items as $row) {
                $lineTotal = ((float) $row['unit_cost'] * (int) $row['quantity']) + (float) ($row['tax_amount'] ?? 0);
                $po->items()->create([
                    'product_id' => $row['product_id'],
                    'product_variant_id' => $row['product_variant_id'] ?? null,
                    'quantity' => $row['quantity'],
                    'received_quantity' => 0,
                    'unit_cost' => $row['unit_cost'],
                    'tax_amount' => $row['tax_amount'] ?? 0,
                    'total' => $lineTotal,
                ]);
            }

            return $po;
        });

        return $this->success('Purchase order created.', 'admin.purchase-orders.show', [$po]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items.product', 'creator']);

        return view('admin.purchase-orders.show', ['item' => $purchaseOrder]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items');

        return view('admin.purchase-orders.edit', array_merge($this->formData(), ['item' => $purchaseOrder]));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'], true)) {
            return $this->error('Cannot edit a received or cancelled PO.');
        }

        $data = $this->validatePo($request);
        $items = $data['items'];
        unset($data['items']);

        DB::transaction(function () use ($purchaseOrder, $data, $items) {
            $totals = $this->calcTotals($items, (float) ($data['shipping_cost'] ?? 0));
            $purchaseOrder->update(array_merge($data, $totals));
            $purchaseOrder->items()->delete();

            foreach ($items as $row) {
                $lineTotal = ((float) $row['unit_cost'] * (int) $row['quantity']) + (float) ($row['tax_amount'] ?? 0);
                $purchaseOrder->items()->create([
                    'product_id' => $row['product_id'],
                    'product_variant_id' => $row['product_variant_id'] ?? null,
                    'quantity' => $row['quantity'],
                    'received_quantity' => 0,
                    'unit_cost' => $row['unit_cost'],
                    'tax_amount' => $row['tax_amount'] ?? 0,
                    'total' => $lineTotal,
                ]);
            }
        });

        return $this->success('Purchase order updated.', 'admin.purchase-orders.show', [$purchaseOrder]);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return $this->error('Cannot delete a received PO.');
        }

        $purchaseOrder->delete();

        return $this->success('Purchase order deleted.', 'admin.purchase-orders.index');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.receive_qty' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            foreach ($request->input('items') as $row) {
                /** @var PurchaseOrderItem $item */
                $item = $purchaseOrder->items()->findOrFail($row['id']);
                $qty = min((int) $row['receive_qty'], (int) $item->quantity - (int) $item->received_quantity);

                if ($qty <= 0) {
                    continue;
                }

                $this->inventoryService->adjustStock(
                    (int) $item->product_id,
                    $item->product_variant_id,
                    (int) $purchaseOrder->warehouse_id,
                    $qty,
                    'PO receive ' . $purchaseOrder->po_number,
                    'purchase',
                    PurchaseOrder::class,
                    $purchaseOrder->id
                );

                $item->update(['received_quantity' => (int) $item->received_quantity + $qty]);
            }

            $purchaseOrder->load('items');
            $allReceived = $purchaseOrder->items->every(
                fn ($i) => (int) $i->received_quantity >= (int) $i->quantity
            );
            $anyReceived = $purchaseOrder->items->some(fn ($i) => (int) $i->received_quantity > 0);

            $purchaseOrder->update([
                'status' => $allReceived ? 'received' : ($anyReceived ? 'partially_received' : $purchaseOrder->status),
            ]);
        });

        return $this->success('Stock received.');
    }

    protected function formData(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku', 'cost_price']),
        ];
    }

    protected function validatePo(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'status' => ['nullable', 'string', 'max:50'],
            'order_date' => ['nullable', 'date'],
            'expected_date' => ['nullable', 'date'],
            'payment_due_date' => ['nullable', 'date'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    protected function calcTotals(array $items, float $shipping): array
    {
        $subtotal = 0;
        $tax = 0;
        foreach ($items as $row) {
            $subtotal += (float) $row['unit_cost'] * (int) $row['quantity'];
            $tax += (float) ($row['tax_amount'] ?? 0);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($tax, 2),
            'shipping_cost' => round($shipping, 2),
            'total' => round($subtotal + $tax + $shipping, 2),
        ];
    }

    protected function generatePoNumber(): string
    {
        do {
            $number = 'PO-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        } while (PurchaseOrder::where('po_number', $number)->exists());

        return $number;
    }
}
