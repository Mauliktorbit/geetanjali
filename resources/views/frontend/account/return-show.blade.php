@extends('frontend.account.layout')

@section('title', $return->return_number.' | Geetanjali Jewellers')

@section('account')
@php $journey = $return->journey(true); @endphp
<section class="account-card">
    <div class="account-card__head">
        <h1 class="font-heading account-page-title">{{ $journey['product'] !== '—' ? $journey['product'] : $return->return_number }}</h1>
        <span class="account-status account-status--{{ $journey['tone'] === 'success' ? 'delivered' : ($journey['tone'] === 'danger' ? 'cancelled' : ($journey['tone'] === 'info' ? 'shipped' : 'return')) }}">{{ \App\Enums\ReturnStatus::customerLabel((string) $return->status) }}</span>
    </div>
    <p class="account-muted">{{ $return->return_number }} · Order #{{ $return->order?->order_number ?: '—' }}</p>

    <div class="account-return-banner account-return-banner--{{ $journey['tone'] }}">
        <strong>{{ $journey['title'] }}</strong>
        <p>{{ $journey['text'] }}</p>
    </div>

    <ol class="account-journey">
        @foreach ($journey['steps'] as $step)
            <li class="account-journey__step is-{{ $step['state'] }}">
                <span class="account-journey__dot" aria-hidden="true">
                    @if ($step['state'] === 'done')
                        <i class="bi bi-check-lg"></i>
                    @elseif ($step['state'] === 'failed')
                        <i class="bi bi-x-lg"></i>
                    @else
                        {{ $loop->iteration }}
                    @endif
                </span>
                <div>
                    <strong>{{ $step['title'] }}</strong>
                    <p>{{ $step['text'] }}</p>
                    <small>
                        @if ($step['date'])
                            {{ $step['date']->format('d M Y, h:i A') }}
                        @elseif ($step['state'] === 'current')
                            In progress
                        @elseif ($step['state'] === 'upcoming')
                            Waiting
                        @endif
                    </small>
                </div>
            </li>
        @endforeach
    </ol>

    <ul class="account-order-items">
        @forelse ($return->items as $line)
            @php $orderItem = $line->orderItem; @endphp
            <li>
                <span>
                    <strong>{{ $orderItem?->product_name ?: 'Product' }}</strong>
                    <small>Qty {{ $line->quantity }}</small>
                </span>
                <em>{{ money($line->refund_amount) }}</em>
            </li>
        @empty
            <li>
                <span><strong>{{ $journey['product'] }}</strong></span>
                <em>{{ $journey['amount'] }}</em>
            </li>
        @endforelse
    </ul>

    <div class="account-totals">
        <div class="is-total"><span>Refund amount</span><strong>{{ $journey['amount'] }}</strong></div>
    </div>

    <div class="account-order-actions">
        @if ($return->order)
            <a class="account-ghost-btn" href="{{ route('account.orders.show', $return->order->order_number) }}">View order</a>
        @endif
        <a class="account-text-link" href="{{ route('account.returns') }}">Back to returns</a>
    </div>
</section>
@endsection
