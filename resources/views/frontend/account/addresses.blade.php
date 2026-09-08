@extends('frontend.account.layout')

@section('title', 'Addresses | Geetanjali Jewellers')

@section('account')
<div class="account-card" data-account-addresses>
    <div class="account-card__head">
        <h1 class="font-heading account-page-title">Addresses</h1>
        <button type="button" class="account-ghost-btn account-ghost-btn--small" data-address-add>+ Add New Address</button>
    </div>

    <div class="account-address-grid">
        @forelse ($addresses as $address)
            <article class="account-address">
                <div class="account-address__top">
                    <strong>{{ ucfirst($address->label ?: 'home') }}</strong>
                    @if ($address->is_default)
                        <span class="account-pill">Default</span>
                    @endif
                    <span class="account-address__actions">
                        <button type="button" data-address-edit data-id="{{ $address->id }}" aria-label="Edit address">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Remove this address?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" aria-label="Delete address"><i class="bi bi-trash"></i></button>
                        </form>
                    </span>
                </div>
                <p>{{ $address->name }}</p>
                <p>{{ $address->address_line1 }}{{ $address->address_line2 ? ', '.$address->address_line2 : '' }}</p>
                <p>{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                <p>{{ $address->phone }}</p>
            </article>
        @empty
            <p class="account-empty">No saved addresses yet. Add one for faster checkout.</p>
        @endforelse
    </div>
</div>

<script type="application/json" id="checkout-address-data">@json($addressPayloads)</script>

<div class="checkout-modal" data-address-modal hidden>
    <div class="checkout-modal__panel" role="dialog" aria-modal="true" aria-labelledby="account-address-title">
        <div class="checkout-modal__head">
            <h3 class="font-heading" id="account-address-title" data-address-modal-title>Add New Address</h3>
            <button type="button" class="checkout-modal__close" data-address-close aria-label="Close">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <form
            method="POST"
            action="{{ route('account.addresses.store') }}"
            class="checkout-modal__form"
            data-address-form
            data-store-url="{{ route('account.addresses.store') }}"
            data-default-name="{{ $user->name }}"
            data-default-phone="{{ $user->mobile ?: $user->phone }}"
        >
            @csrf
            <input type="hidden" name="_method" value="POST" data-address-method>
            <div class="checkout-modal__body checkout-form-grid">
                <label>Label
                    <select name="label" required data-field="label">
                        <option value="home">Home</option>
                        <option value="office">Office</option>
                        <option value="other">Other</option>
                    </select>
                </label>
                <label>Full name
                    <input type="text" name="name" value="{{ $user->name }}" required data-field="name">
                </label>
                <label class="is-full">Phone
                    <input type="tel" name="phone" value="{{ $user->mobile ?: $user->phone }}" required data-field="phone">
                </label>
                <label class="is-full">Address
                    <input type="text" name="address_line1" required data-field="address_line1">
                </label>
                <label class="is-full">Landmark (optional)
                    <input type="text" name="address_line2" data-field="address_line2">
                </label>
                <label>City
                    <input type="text" name="city" required data-field="city">
                </label>
                <label>State
                    <select name="state" required data-field="state">
                        <option value="">Select state</option>
                        @foreach ($states as $state)
                            <option value="{{ $state }}">{{ $state }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Pincode
                    <input type="text" name="pincode" maxlength="6" required data-field="pincode">
                </label>
                <label class="is-check">
                    <input type="checkbox" name="is_default" value="1" data-field="is_default"> Set as default
                </label>
            </div>
            <div class="checkout-modal__foot">
                <button type="submit" class="checkout-place checkout-place--small">Save Address</button>
            </div>
        </form>
    </div>
</div>
@endsection
