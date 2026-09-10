<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Requests\Admin\OrderRequest;
use App\Models\CommunicationLog;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ReturnService;
use App\Services\ShipmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends AdminController
{
    public function __construct(
        protected OrderService $orderService,
        protected InvoiceService $invoiceService,
        protected PaymentService $paymentService,
        protected ShipmentService $shipmentService,
        protected ReturnService $returnService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->all();
        $items = app(\App\Repositories\OrderRepository::class)->paginate($filters);

        return view('admin.orders.index', [
            'items' => $items,
            'statuses' => OrderStatus::simpleLabels(),
        ]);
    }

    public function create()
    {
        return view('admin.orders.create', [
            'products' => Product::query()
                ->where('is_active', true)
                ->where('is_archived', false)
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'regular_price', 'sale_price']),
        ]);
    }

    public function store(OrderRequest $request)
    {
        $data = $request->validated();
        $items = $data['items'] ?? [];
        $paymentStatus = $data['payment_status'] ?? 'pending';
        unset($data['items'], $data['payment_status']);

        $shipping = $data['shipping_address'] ?? [];
        $data['shipping_address'] = [
            'line1' => $shipping['line1'] ?? null,
            'city' => $data['shipping_city'] ?? null,
            'state' => $data['shipping_state'] ?? null,
            'pincode' => $data['shipping_pincode'] ?? null,
        ];
        $data['source'] = 'manual';

        $order = $this->orderService->createOrder($data, $items);

        if ($paymentStatus === 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'paid_amount' => $order->grand_total,
            ]);
        }

        return $this->success('Order created successfully.', 'admin.orders.show', [$order]);
    }

    public function show(Order $order)
    {
        $order->load(['items.product.category']);

        return view('admin.orders.show', [
            'item' => $order,
            'statuses' => OrderStatus::simpleLabels(),
        ]);
    }

    public function edit(Order $order)
    {
        $order->load('items');

        return view('admin.orders.edit', [
            'item' => $order,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $pincode = digits_only($request->input('shipping_pincode'));
        $request->merge([
            'customer_phone' => indian_mobile($request->input('customer_phone')),
            'shipping_pincode' => $pincode === '' ? null : $pincode,
        ]);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email'],
            'customer_phone' => indian_mobile_rules(true),
            'shipping_address.line1' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_pincode' => indian_pincode_rules(false),
            'payment_status' => ['required', 'in:pending,paid'],
        ], [
            'customer_phone.required' => 'Please enter the customer phone number.',
            'customer_phone.regex' => 'Enter a valid 10-digit mobile number.',
            'shipping_pincode.regex' => 'Enter a valid 6-digit pincode.',
        ]);

        $shipping = $order->shipping_address ?? [];
        if (! is_array($shipping)) {
            $shipping = [];
        }
        $shipping['line1'] = $data['shipping_address']['line1'] ?? ($shipping['line1'] ?? $shipping['address_line1'] ?? null);
        $shipping['city'] = $data['shipping_city'] ?? ($shipping['city'] ?? null);
        $shipping['state'] = $data['shipping_state'] ?? ($shipping['state'] ?? null);
        $shipping['pincode'] = $data['shipping_pincode'] ?? ($shipping['pincode'] ?? null);

        $order->update([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'shipping_address' => $shipping,
            'shipping_city' => $data['shipping_city'] ?? null,
            'shipping_state' => $data['shipping_state'] ?? null,
            'shipping_pincode' => $data['shipping_pincode'] ?? null,
            'payment_status' => $data['payment_status'],
            'paid_amount' => $data['payment_status'] === 'paid' ? $order->grand_total : 0,
        ]);

        return $this->success('Order updated.', 'admin.orders.show', [$order]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(OrderStatus::simpleLabels()))],
        ]);

        $this->orderService->updateStatus($order, $request->input('status'));

        return $this->success('Order status updated.');
    }

    public function addItem(Request $request, Order $order)
    {
        $data = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'product_name' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->orderService->addItem($order, $data);

        return $this->success('Item added.');
    }

    public function removeItem(Order $order, OrderItem $item)
    {
        $this->orderService->removeItem($order, $item);

        return $this->success('Item removed.');
    }

    public function changeQty(Request $request, Order $order, OrderItem $item)
    {
        $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $this->orderService->changeQuantity($order, $item, (int) $request->input('quantity'));

        return $this->success('Quantity updated.');
    }

    public function applyDiscount(Request $request, Order $order)
    {
        $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $this->orderService->applyDiscount(
            $order,
            (float) $request->input('discount_amount'),
            $request->input('coupon_code')
        );

        return $this->success('Discount applied.');
    }

    public function changeAddress(Request $request, Order $order)
    {
        $pincode = digits_only($request->input('shipping_pincode'));
        $request->merge([
            'shipping_pincode' => $pincode === '' ? null : $pincode,
        ]);

        $data = $request->validate([
            'billing_address' => ['nullable', 'array'],
            'shipping_address' => ['nullable', 'array'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_country' => ['nullable', 'string', 'max:100'],
            'shipping_pincode' => indian_pincode_rules(false),
        ]);

        $shipping = $data['shipping_address'] ?? [];
        $data['shipping_city'] = $data['shipping_city'] ?? ($shipping['city'] ?? $order->shipping_city);
        $data['shipping_state'] = $data['shipping_state'] ?? ($shipping['state'] ?? $order->shipping_state);
        $data['shipping_country'] = $data['shipping_country'] ?? ($shipping['country'] ?? $order->shipping_country);
        $data['shipping_pincode'] = $data['shipping_pincode'] ?? ($shipping['pincode'] ?? $order->shipping_pincode);

        $order->update($data);

        return $this->success('Address updated.');
    }

    public function generateInvoice(Order $order)
    {
        $invoice = $this->invoiceService->generateTaxInvoice($order);

        return $this->success('Invoice ' . $invoice->invoice_number . ' generated.');
    }

    public function packingSlip(Order $order)
    {
        $order->load(['items', 'customer']);
        $pdf = Pdf::loadView('pdf.packing-slip', ['order' => $order]);

        return $pdf->download('packing-slip-' . $order->order_number . '.pdf');
    }

    public function assignCourier(Request $request, Order $order)
    {
        $request->validate(['courier_id' => ['required', 'exists:couriers,id']]);
        $shipment = $order->shipments()->latest()->first()
            ?? $this->shipmentService->createShipment($order, ['courier_id' => $request->input('courier_id')]);

        if (! $shipment->courier_id) {
            $this->shipmentService->assignCourier($shipment, (int) $request->input('courier_id'));
        }

        return $this->success('Courier assigned.');
    }

    public function addTracking(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
            'shipping_partner' => ['nullable', 'string', 'max:100'],
        ]);

        $order->update([
            'tracking_number' => $request->input('tracking_number'),
            'shipping_partner' => $request->input('shipping_partner', $order->shipping_partner),
        ]);

        $shipment = $order->shipments()->latest()->first();
        if ($shipment) {
            $this->shipmentService->addTracking($shipment, $request->input('tracking_number'));
        }

        return $this->success('Tracking updated.');
    }

    public function cancel(Request $request, Order $order)
    {
        $this->orderService->cancelOrder($order, $request->input('reason'));

        return $this->success('Order cancelled.');
    }

    public function approveReturn(Request $request, Order $order)
    {
        $return = $order->returns()->latest()->first();
        if (! $return) {
            return $this->error('No return request found for this order.');
        }

        $this->returnService->approve($return, $request->input('note'));

        return $this->success('Return approved.');
    }

    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'string'],
            'reason' => ['nullable', 'string'],
        ]);

        $this->paymentService->processRefund(
            $order,
            (float) $request->input('amount'),
            null,
            $request->input('method', 'original'),
            $request->input('reason')
        );

        return $this->success('Refund processed.');
    }

    public function resendConfirmation(Order $order)
    {
        CommunicationLog::create([
            'customer_id' => $order->customer_id,
            'channel' => 'email',
            'type' => 'order_confirmation',
            'subject' => 'Order Confirmation ' . $order->order_number,
            'message' => 'Order confirmation resent for ' . $order->order_number,
            'status' => 'queued',
            'sent_by' => Auth::id(),
            'meta' => ['order_id' => $order->id],
        ]);

        return $this->success('Confirmation queued for resend.');
    }

    public function contactCustomer(Request $request, Order $order)
    {
        $request->validate([
            'channel' => ['required', 'in:email,sms,whatsapp'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        CommunicationLog::create([
            'customer_id' => $order->customer_id,
            'channel' => $request->input('channel'),
            'type' => 'order_contact',
            'subject' => $request->input('subject', 'Regarding order ' . $order->order_number),
            'message' => $request->input('message'),
            'status' => 'queued',
            'sent_by' => Auth::id(),
            'meta' => ['order_id' => $order->id],
        ]);

        return $this->success('Message queued.');
    }

    public function addNote(Request $request, Order $order)
    {
        $request->validate([
            'note' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        OrderNote::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'note' => $request->input('note'),
            'is_internal' => $request->boolean('is_internal', true),
        ]);

        return $this->success('Note added.');
    }
}
