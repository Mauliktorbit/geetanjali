@extends('frontend.account.layout')

@section('title', 'Help & Support | Geetanjali Jewellers')

@section('account')
<section class="account-card">
    <h1 class="font-heading account-page-title">Help &amp; Support</h1>
    <p class="account-muted">Our consultants can help with sizing, hallmarks, shipping, and order updates.</p>
    <div class="account-help-links">
        <a class="account-ghost-btn" href="{{ route('contact') }}">Contact Support</a>
        <a class="account-ghost-btn" href="{{ route('pages.faq') }}">FAQs</a>
        <a class="account-ghost-btn" href="{{ route('pages.track-order') }}">Track Order</a>
        <a class="account-ghost-btn" href="{{ route('pages.shipping') }}">Shipping Policy</a>
    </div>
</section>
@endsection
