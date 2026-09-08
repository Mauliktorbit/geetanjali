@extends('frontend.account.layout')

@section('title', 'Payment Methods | Geetanjali Jewellers')

@section('account')
<section class="account-card">
    <h1 class="font-heading account-page-title">Payment Methods</h1>
    <p class="account-muted">We only store the card brand and last 4 digits — never the full card number.</p>

    @forelse ($paymentMethods as $method)
        <article class="account-pay-row">
            <div>
                <strong>{{ $method->maskedNumber() }}</strong>
                <small>{{ $method->holder_name }}@if($method->expiry_month && $method->expiry_year) · {{ sprintf('%02d/%d', $method->expiry_month, $method->expiry_year) }}@endif</small>
            </div>
            <div class="account-pay-row__meta">
                @if ($method->is_default)
                    <span class="account-pill">Default</span>
                @endif
                <form method="POST" action="{{ route('account.payments.destroy', $method) }}" onsubmit="return confirm('Remove this card?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="account-icon-btn" aria-label="Remove card"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </article>
    @empty
        <p class="account-empty">No saved payment methods yet.</p>
    @endforelse
</section>

<section class="account-card account-card--form">
    <h2 class="font-heading">Add a card</h2>
    <form method="POST" action="{{ route('account.payments.store') }}" class="account-form">
        @csrf
        <label>Brand
            <select name="brand" required>
                <option value="visa">Visa</option>
                <option value="mastercard">Mastercard</option>
                <option value="rupay">RuPay</option>
                <option value="amex">Amex</option>
            </select>
        </label>
        <label>Last 4 digits
            <input type="text" name="last_four" maxlength="4" inputmode="numeric" required>
        </label>
        <label>Name on card
            <input type="text" name="holder_name" value="{{ $user->name }}" required>
        </label>
        <label>Expiry month
            <input type="number" name="expiry_month" min="1" max="12" required>
        </label>
        <label>Expiry year
            <input type="number" name="expiry_year" min="{{ now()->year }}" required>
        </label>
        <label class="account-check">
            <input type="checkbox" name="is_default" value="1"> Set as default
        </label>
        <button type="submit" class="account-solid-btn">Save Card</button>
    </form>
</section>
@endsection
