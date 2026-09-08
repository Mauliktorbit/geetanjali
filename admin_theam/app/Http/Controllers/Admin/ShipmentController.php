<?php

namespace App\Http\Controllers\Admin;

use App\Models\Courier;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShipmentController extends AdminController
{
    public function __construct(protected ShipmentService $shipmentService) {}

    public function index(Request $request)
    {
        $query = Shipment::with(['order', 'courier'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('awb_number', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', "%{$search}%"));
            });
        }

        $items = $query->paginate(20)->withQueryString();
        $couriers = Courier::orderBy('name')->get(['id', 'name']);

        return view('admin.shipments.index', compact('items', 'couriers'));
    }

    public function create(Request $request)
    {
        return view('admin.shipments.create', [
            'orders' => Order::whereNotIn('status', ['cancelled', 'delivered'])->latest()->limit(100)->get(['id', 'order_number', 'customer_name']),
            'couriers' => Courier::where('is_active', true)->orderBy('name')->get(),
            'order_id' => $request->input('order_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'courier_id' => ['nullable', 'exists:couriers,id'],
            'cod_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = Order::findOrFail($data['order_id']);
        $shipment = $this->shipmentService->createShipment($order, $data);

        return $this->success('Shipment created.', 'admin.shipments.show', [$shipment]);
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['order.items', 'courier']);
        $couriers = Courier::where('is_active', true)->orderBy('name')->get();

        return view('admin.shipments.show', ['item' => $shipment, 'couriers' => $couriers]);
    }

    public function assign(Request $request, Shipment $shipment)
    {
        $request->validate(['courier_id' => ['required', 'exists:couriers,id']]);
        $this->shipmentService->assignCourier($shipment, (int) $request->input('courier_id'));

        return $this->success('Courier assigned.');
    }

    public function track(Request $request, Shipment $shipment)
    {
        $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $this->shipmentService->addTracking(
            $shipment,
            $request->input('tracking_number'),
            array_filter(['status' => $request->input('status')])
        );

        return $this->success('Tracking updated.');
    }

    public function cancel(Request $request, Shipment $shipment)
    {
        $this->shipmentService->cancel($shipment, $request->input('reason'));

        return $this->success('Shipment cancelled.');
    }

    public function reverse(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'courier_id' => ['nullable', 'exists:couriers,id'],
        ]);

        $order = Order::findOrFail($data['order_id']);
        $shipment = $this->shipmentService->reversePickup($order, $data);

        return $this->success('Reverse pickup created.', 'admin.shipments.show', [$shipment]);
    }

    public function label(Shipment $shipment)
    {
        if ($shipment->label_path && Storage::disk('local')->exists($shipment->label_path)) {
            return Storage::disk('local')->download($shipment->label_path);
        }

        return response(
            "Shipping Label\nAWB: {$shipment->awb_number}\nTracking: {$shipment->tracking_number}\nOrder: {$shipment->order?->order_number}",
            200,
            ['Content-Type' => 'text/plain']
        );
    }
}
