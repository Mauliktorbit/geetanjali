@extends('frontend.account.layout')

@section('title', 'Returns & Refunds | Geetanjali Jewellers')

@section('account')
<section class="account-card">
    <h1 class="font-heading account-page-title">Returns &amp; Refunds</h1>
    @forelse ($returns as $return)
        <article class="account-return">
            <div>
                <strong>{{ $return->return_number }}</strong>
                <small>Order #{{ $return->order?->order_number }} · {{ $return->created_at?->format('d M Y') }}</small>
            </div>
            <span class="account-status account-status--return">{{ ucfirst(str_replace('_', ' ', (string) $return->status)) }}</span>
        </article>
    @empty
        <p class="account-empty">You have no return requests. Eligible orders can be returned from the order page as per our policy.</p>
        <a class="account-ghost-btn" href="{{ route('pages.returns') }}">Read Return Policy</a>
    @endforelse
</section>
@endsection
