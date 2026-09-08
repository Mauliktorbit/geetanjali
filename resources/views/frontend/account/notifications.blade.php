@extends('frontend.account.layout')

@section('title', 'Notifications | Geetanjali Jewellers')

@section('account')
<section class="account-card account-card--form">
    <h1 class="font-heading account-page-title">Notifications</h1>
    <form method="POST" action="{{ route('account.notifications.update') }}" class="account-toggle-form">
        @csrf
        @method('PUT')
        <label class="account-toggle">
            <span>
                <strong>Email Alerts</strong>
                <small>Account and security emails</small>
            </span>
            <input type="checkbox" name="email" value="1" @checked($prefs['email'])>
        </label>
        <label class="account-toggle">
            <span>
                <strong>Order Updates</strong>
                <small>Shipping, delivery, and invoice alerts</small>
            </span>
            <input type="checkbox" name="order_updates" value="1" @checked($prefs['order_updates'])>
        </label>
        <label class="account-toggle">
            <span>
                <strong>Offers &amp; Promotions</strong>
                <small>Festive offers and Gold Club updates</small>
            </span>
            <input type="checkbox" name="offers" value="1" @checked($prefs['offers'])>
        </label>
        <button type="submit" class="account-solid-btn">Save Preferences</button>
    </form>
</section>
@endsection
