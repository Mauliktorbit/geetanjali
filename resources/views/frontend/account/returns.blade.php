@extends('frontend.account.layout')

@section('title', 'Returns & Refunds | Geetanjali Jewellers')

@section('account')
<section class="account-card">
    <h1 class="font-heading account-page-title">Returns &amp; Refunds</h1>
    @forelse ($returns as $return)
        @php
            $journey = $return->journey(true);
            $product = $journey['product'];
        @endphp
        <a class="account-return" href="{{ route('account.returns.show', $return->return_number) }}">
            <div>
                <strong>{{ $product !== '—' ? $product : $return->return_number }}</strong>
                <small>
                    {{ $return->return_number }}
                    · Order #{{ $return->order?->order_number ?: '—' }}
                    · {{ $journey['amount'] }}
                </small>
                <small>{{ $journey['title'] }}@if ($journey['latest_date']) · {{ $journey['latest_date']->format('d M Y') }}@endif</small>
            </div>
            <span class="account-status account-status--{{ $journey['tone'] === 'success' ? 'delivered' : ($journey['tone'] === 'danger' ? 'cancelled' : ($journey['tone'] === 'info' ? 'shipped' : 'return')) }}">{{ \App\Enums\ReturnStatus::customerLabel((string) $return->status) }}</span>
        </a>
    @empty
        <p class="account-empty">You have no return requests. After an order is delivered, you can request a return from the order page.</p>
        <a class="account-ghost-btn" href="{{ route('account.orders') }}">View orders</a>
    @endforelse
</section>
@endsection
