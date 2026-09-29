@extends('frontend.layouts.app')

@section('title')
    @yield('title', 'My Account | Geetanjali Jewellers')
@endsection

@section('content')
@php
    $accountSection = $accountSection ?? 'dashboard';
    $phone = $user->mobile ?: $user->phone ?: $customer->phone;
@endphp
<div class="account-page">
    @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

    <div class="account-shell">
        @include('frontend.account.partials.sidebar')

        <div class="account-main">
            @if ($errors->any())
                <div class="account-flash account-flash--error" data-auto-dismiss>{{ $errors->first() }}</div>
            @endif

            @yield('account')
        </div>
    </div>
</div>
@endsection
