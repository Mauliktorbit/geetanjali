@extends('admin.layouts.app')
@section('title', $item->order_number)
@section('content')
<div class="page-header">
    <div>
        <h1>Order {{ $item->order_number }}</h1>
        @include('admin.components.breadcrumbs', ['items'=>[['label'=>'Orders','url'=>route('admin.orders.index')],['label'=>$item->order_number]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.orders.edit',$item) }}" class="btn btn-secondary">Edit</a>
        <a href="{{ route('admin.orders.packing-slip',$item) }}" class="btn btn-ghost">Packing slip</a>
        <form method="POST" action="{{ route('admin.orders.invoice',$item) }}">@csrf<button class="btn btn-secondary" onclick="return confirm('Generate invoice?')">Generate invoice</button></form>
        <form method="POST" action="{{ route('admin.orders.resend-confirmation',$item) }}">@csrf<button class="btn btn-ghost" onclick="return confirm('Resend confirmation?')">Resend confirmation</button></form>
        <form method="POST" action="{{ route('admin.orders.cancel',$item) }}">@csrf
            <input type="hidden" name="reason" value="Cancelled by admin">
            <button class="btn btn-danger" onclick="return confirm('Cancel this order?')">Cancel order</button>
        </form>
    </div>
</div>
@include('admin.components.alerts')

<div class="grid-2">
    <div class="card">
        <h3>Summary</h3>
        <p>Status: @include('admin.components.status-badge',['status'=>$item->status]) | Payment: {{ $item->payment_status }}</p>
        <p>Customer: {{ $item->customer_name }} · {{ $item->customer_email }} · {{ $item->customer_phone }}</p>
        <p>Source: {{ $item->source }} | Method: {{ $item->payment_method }}</p>
        <p>Subtotal {{ money($item->subtotal) }} · Discount {{ money($item->discount_amount) }} · Tax {{ money($item->tax_amount) }} · Shipping {{ money($item->shipping_charge) }}</p>
        <p><strong>Grand total {{ money($item->grand_total) }}</strong> · Paid {{ money($item->paid_amount) }}</p>

        <form method="POST" action="{{ route('admin.orders.status',$item) }}" class="filters-bar mt-3">@csrf
            <select name="status" class="form-control" required>
                @foreach($statuses as $k=>$l)<option value="{{ $k }}" @selected($item->status===$k)>{{ $l }}</option>@endforeach
            </select>
            <input name="note" class="form-control" placeholder="Note">
            <button class="btn btn-primary" onclick="return confirm('Update status?')">Update status</button>
        </form>

        <form method="POST" action="{{ route('admin.orders.discount',$item) }}" class="filters-bar mt-2">@csrf
            <input type="number" step="0.01" name="discount_amount" class="form-control" placeholder="Discount" required>
            <input name="coupon_code" class="form-control" placeholder="Coupon">
            <button class="btn btn-secondary">Apply discount</button>
        </form>

        <form method="POST" action="{{ route('admin.orders.refund',$item) }}" class="filters-bar mt-2">@csrf
            <input type="number" step="0.01" name="amount" class="form-control" placeholder="Refund amount" required>
            <input name="reason" class="form-control" placeholder="Reason">
            <button class="btn btn-danger" onclick="return confirm('Process refund?')">Refund</button>
        </form>
    </div>

    <div class="card">
        <h3>Shipping / Courier</h3>
        <p>{{ $item->shipping_city }} {{ $item->shipping_state }} {{ $item->shipping_pincode }}</p>
        <p>Partner: {{ $item->shipping_partner }} · Tracking: {{ $item->tracking_number }}</p>
        <form method="POST" action="{{ route('admin.orders.assign-courier',$item) }}" class="filters-bar">@csrf
            <select name="courier_id" class="form-control" required>
                @foreach($couriers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
            <button class="btn btn-secondary">Assign courier</button>
        </form>
        <form method="POST" action="{{ route('admin.orders.tracking',$item) }}" class="filters-bar mt-2">@csrf
            <input name="tracking_number" class="form-control" placeholder="Tracking #" required>
            <input name="shipping_partner" class="form-control" placeholder="Partner">
            <button class="btn btn-secondary">Add tracking</button>
        </form>
        <form method="POST" action="{{ route('admin.orders.address',$item) }}" class="mt-3">@csrf
            <div class="form-grid">
                <div class="form-group"><label>City</label><input name="shipping_city" class="form-control" value="{{ $item->shipping_city }}"></div>
                <div class="form-group"><label>State</label><input name="shipping_state" class="form-control" value="{{ $item->shipping_state }}"></div>
                <div class="form-group"><label>Pincode</label><input name="shipping_pincode" class="form-control" value="{{ $item->shipping_pincode }}"></div>
                <div class="form-group"><label>Line 1</label><input name="shipping_address[line1]" class="form-control" value="{{ $item->shipping_address['line1'] ?? '' }}"></div>
            </div>
            <button class="btn btn-ghost">Update address</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <h3>Items</h3>
    <table class="data-table">
        <thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead>
        <tbody>
        @foreach($item->items as $line)
            <tr>
                <td>{{ $line->product_name }} {{ $line->variant_label }}</td>
                <td>{{ $line->sku }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.orders.items.qty',[$item,$line]) }}" class="inline-form">@csrf
                        <input type="number" name="quantity" value="{{ $line->quantity }}" min="1" class="form-control" style="width:80px;display:inline-block">
                        <button class="btn btn-sm">Update</button>
                    </form>
                </td>
                <td>{{ money($line->unit_price) }}</td>
                <td>{{ money($line->total) }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.orders.items.remove',[$item,$line]) }}">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Remove item?')">Remove</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <form method="POST" action="{{ route('admin.orders.items.add',$item) }}" class="filters-bar mt-3">@csrf
        <select name="product_id" class="form-control">
            @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
        </select>
        <input type="number" name="quantity" value="1" min="1" class="form-control">
        <input type="number" step="0.01" name="unit_price" class="form-control" placeholder="Override price">
        <button class="btn btn-secondary">Add item</button>
    </form>
</div>

<div class="grid-2 mt-4">
    <div class="card">
        <h3>Timeline</h3>
        <ul class="timeline">
            @forelse($item->statusHistories as $h)
                <li><strong>{{ $h->from_status ?? '—' }} → {{ $h->to_status }}</strong> · {{ $h->created_at }} · {{ $h->user?->name }}<br>{{ $h->note }}</li>
            @empty<li>No history</li>@endforelse
        </ul>
        <form method="POST" action="{{ route('admin.orders.notes',$item) }}" class="mt-3">@csrf
            <textarea name="note" class="form-control" rows="2" required></textarea>
            <label class="form-check"><input type="checkbox" name="is_internal" value="1" checked> Internal</label>
            <button class="btn btn-secondary mt-2">Add note</button>
        </form>
        @foreach($item->notes as $note)
            <p class="mt-2"><em>{{ $note->user?->name }}</em>: {{ $note->note }}</p>
        @endforeach
    </div>
    <div class="card">
        <h3>Payments / Contact</h3>
        <ul>@forelse($item->payments as $pay)<li>{{ money($pay->amount) }} · {{ $pay->payment_method }} · {{ $pay->status }}</li>@empty<li>No payments</li>@endforelse</ul>
        <form method="POST" action="{{ route('admin.payments.record') }}" class="filters-bar">@csrf
            <input type="hidden" name="order_id" value="{{ $item->id }}">
            <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
            <input name="payment_method" class="form-control" value="manual" required>
            <button class="btn btn-secondary">Record payment</button>
        </form>
        <form method="POST" action="{{ route('admin.orders.contact',$item) }}" class="mt-3">@csrf
            <select name="channel" class="form-control"><option value="email">Email</option><option value="sms">SMS</option><option value="whatsapp">WhatsApp</option></select>
            <input name="subject" class="form-control" placeholder="Subject">
            <textarea name="message" class="form-control" rows="2" required></textarea>
            <button class="btn btn-ghost mt-2">Contact customer</button>
        </form>
        <form method="POST" action="{{ route('admin.orders.approve-return',$item) }}" class="mt-3">@csrf
            <input name="note" class="form-control" placeholder="Return note">
            <button class="btn btn-secondary" onclick="return confirm('Approve return?')">Approve return</button>
        </form>
    </div>
</div>
@endsection
