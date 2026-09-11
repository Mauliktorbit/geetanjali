@extends('frontend.account.layout')

@section('title', 'My Reviews | Geetanjali Jewellers')

@section('account')
<section class="account-card">
    <h1 class="font-heading account-page-title">My Reviews</h1>
    <p class="account-muted">After an order is delivered, you can rate the jewellery. Approved reviews appear on the product page.</p>

    @forelse ($pendingOrders as $order)
        @php $products = $pendingByOrder[$order->id] ?? collect(); @endphp
        <article class="account-review-pending">
            <div class="account-review-pending__head">
                <strong>Order #{{ $order->order_number }}</strong>
                <small>Delivered {{ $order->delivered_at?->format('d M Y') ?: $order->updated_at?->format('d M Y') }}</small>
            </div>
            @foreach ($products as $product)
                <form method="POST" action="{{ route('account.reviews.store') }}" class="account-form account-review-form">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                    <div class="account-review-form__product">
                        <img src="{{ $product['image'] }}" alt="">
                        <span>{{ $product['name'] }}</span>
                    </div>
                    <fieldset class="account-star-pick">
                        <legend>Your rating</legend>
                        @for ($star = 5; $star >= 1; $star--)
                            <input type="radio" name="rating" id="rating-{{ $order->id }}-{{ $product['id'] }}-{{ $star }}" value="{{ $star }}" required @checked((string) old('rating') === (string) $star && (string) old('product_id') === (string) $product['id'])>
                            <label for="rating-{{ $order->id }}-{{ $product['id'] }}-{{ $star }}" title="{{ $star }} star{{ $star > 1 ? 's' : '' }}">{{ $star }}</label>
                        @endfor
                    </fieldset>
                    <button type="submit" class="account-solid-btn">Submit rating</button>
                </form>
            @endforeach
        </article>
    @empty
        @if ($reviews->isEmpty())
            <p class="account-empty">No reviews yet. After your jewellery is delivered, we will ask you to rate it.</p>
        @endif
    @endforelse
</section>

<section class="account-card">
    <h2 class="font-heading account-subhead">Submitted reviews</h2>
    @forelse ($reviews as $review)
        <article class="account-review-row">
            <div>
                <strong>{{ $review->productName() }}</strong>
                <small>{{ (int) $review->rating }} / 5 · {{ $review->created_at?->format('d M Y') }} · {{ $review->statusLabel() }}</small>
                <p>{{ $review->comment ?: '—' }}</p>
            </div>
            <span class="account-status account-status--{{ $review->status === 'approved' ? 'delivered' : ($review->status === 'rejected' ? 'cancelled' : 'processing') }}">{{ $review->statusLabel() }}</span>
        </article>
    @empty
        <p class="account-empty">You have not submitted a review yet.</p>
    @endforelse
    <div class="account-pagination">{{ $reviews->links() }}</div>
</section>
@endsection
