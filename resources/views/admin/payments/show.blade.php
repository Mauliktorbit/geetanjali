@extends('admin.layouts.app')
@section('title', 'Payment #'.$item->id)
@section('content')
@php
    $methodLabel = \App\Services\CheckoutService::PAYMENTS[$item->payment_method] ?? ucfirst(str_replace('_', ' ', (string) $item->payment_method));
    $refundable = max(0, (float) $item->amount - (float) $item->refund_amount);
@endphp
<div class="page-header">
    <div>
        <h1>Payment #{{ $item->id }}</h1>
        <p class="subtitle">{{ $item->order?->order_number ?: 'Order payment' }} · {{ money($item->amount) }}</p>
    </div>
    <div class="page-actions">
        @if ($item->order)
            <a href="{{ route('admin.orders.show', $item->order) }}" class="btn btn-ghost">View order</a>
        @endif
        <a href="{{ route('admin.payments.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card">
    <div class="detail-list">
        <div class="detail-item">
            <span class="label">Order</span>
            <span class="value">
                @if ($item->order)
                    <a href="{{ route('admin.orders.show', $item->order) }}">{{ $item->order->order_number }}</a>
                @else
                    —
                @endif
            </span>
        </div>
        <div class="detail-item">
            <span class="label">Customer</span>
            <span class="value">{{ $item->customer?->name ?: $item->order?->customer_name ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Method</span>
            <span class="value">{{ $methodLabel }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Amount</span>
            <span class="value">{{ money($item->amount) }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">@include('admin.components.status-badge', ['status' => \App\Enums\PaymentStatus::badge((string) $item->status), 'label' => \App\Enums\PaymentStatus::label((string) $item->status)])</span>
        </div>
        <div class="detail-item">
            <span class="label">Transaction ID</span>
            <span class="value">{{ $item->transaction_id ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Gateway</span>
            <span class="value">{{ $item->gateway ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Paid on</span>
            <span class="value">{{ $item->paid_at?->format('d M Y, h:i A') ?: '—' }}</span>
        </div>
        @if ($item->failure_reason)
            <div class="detail-item">
                <span class="label">Failure reason</span>
                <span class="value">{{ $item->failure_reason }}</span>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <h2 style="margin: 0 0 1rem; font-size: 1.05rem;">Update status</h2>
    <form method="POST" action="{{ route('admin.payments.status', $item) }}" class="form-grid">
        @csrf
        <div class="form-group">
            <label for="payment-status">Status</label>
            <select id="payment-status" name="status" class="form-control" required>
                @foreach (\App\Enums\PaymentStatus::labels() as $key => $label)
                    <option value="{{ $key }}" @selected($item->status === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="failure-reason">Failure reason</label>
            <input id="failure-reason" name="failure_reason" class="form-control" value="{{ old('failure_reason', $item->failure_reason) }}" placeholder="Required if marked failed">
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit" onclick="return confirm('Update this payment status?')">Save status</button>
        </div>
    </form>
</div>

@if ($refundable > 0 && $item->status === \App\Enums\PaymentStatus::PAID)
<div class="card">
    <h2 style="margin: 0 0 1rem; font-size: 1.05rem;">Refund</h2>
    <p class="subtitle" style="margin-top: 0;">Refundable amount: {{ money($refundable) }}</p>
    <form method="POST" action="{{ route('admin.payments.refund', $item) }}" class="form-grid">
        @csrf
        <div class="form-group">
            <label for="refund-amount">Amount *</label>
            <input id="refund-amount" type="number" step="0.01" min="0.01" max="{{ $refundable }}" name="amount" class="form-control" value="{{ old('amount', $refundable) }}" required>
        </div>
        <div class="form-group">
            <label for="refund-reason">Reason</label>
            <input id="refund-reason" name="reason" class="form-control" value="{{ old('reason') }}" placeholder="Optional note">
        </div>
        <div class="form-actions">
            <button class="btn btn-danger" type="submit" onclick="return confirm('Process this refund?')">Refund</button>
        </div>
    </form>
</div>
@endif

<div class="card category-products-card">
    <div class="category-products-card__head">
        <h2>Refunds</h2>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Refund</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($item->refunds as $refund)
                <tr>
                    <td>{{ $refund->refund_number }}</td>
                    <td>{{ money($refund->amount) }}</td>
                    <td>{{ ucfirst((string) $refund->status) }}</td>
                    <td>{{ $refund->processed_at?->format('d M Y') ?: $refund->created_at?->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">@include('admin.components.empty-state', ['title' => 'No refunds', 'text' => 'Refunds for this payment will show here.'])</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
