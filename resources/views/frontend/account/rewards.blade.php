@extends('frontend.account.layout')

@section('title', 'Reward Points | Geetanjali Jewellers')

@section('account')
<section class="account-card">
    <h1 class="font-heading account-page-title">Reward Points</h1>
    <p class="account-reward-balance">{{ number_format((int) $customer->reward_points) }} <small>points</small></p>
    @forelse ($transactions as $txn)
        <div class="account-pay-row">
            <div>
                <strong>{{ ucfirst($txn->type) }} · {{ $txn->points }} pts</strong>
                <small>{{ $txn->reason ?: 'Reward activity' }} · {{ $txn->created_at?->format('d M Y') }}</small>
            </div>
            <span>{{ $txn->balance_after }}</span>
        </div>
    @empty
        <p class="account-empty">Earn points on delivered orders. Your balance will appear here.</p>
    @endforelse
</section>
@endsection
