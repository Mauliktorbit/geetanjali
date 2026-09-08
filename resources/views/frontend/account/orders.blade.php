@extends('frontend.account.layout')

@section('title', 'My Orders | Geetanjali Jewellers')

@section('account')
<section class="account-card">
    <h1 class="font-heading account-page-title">My Orders</h1>
    @forelse ($orders as $order)
        @php $row = app(\App\Services\AccountService::class)->presentOrder($order); @endphp
        <a class="account-order" href="{{ route('account.orders.show', $order->order_number) }}">
            <span class="account-order__thumb">
                @if ($row['thumb'])
                    <img src="{{ asset($row['thumb']) }}" alt="">
                @else
                    <i class="bi bi-gem" aria-hidden="true"></i>
                @endif
            </span>
            <span class="account-order__copy">
                <strong>#{{ $row['number'] }}</strong>
                <small>{{ $row['date'] }} · {{ $row['items'] }} {{ \Illuminate\Support\Str::plural('item', $row['items']) }}</small>
            </span>
            <span class="account-order__price">₹{{ number_format($row['total']) }}</span>
            <span class="account-status account-status--{{ $row['tone'] }}">{{ $row['status_label'] }}</span>
        </a>
    @empty
        <p class="account-empty">No orders yet. Discover hallmarked jewellery made to last.</p>
        <a class="account-ghost-btn" href="{{ route('home') }}">Continue Shopping</a>
    @endforelse

    @if ($orders->hasPages())
        <div class="account-pagination">{{ $orders->links() }}</div>
    @endif
</section>
@endsection
