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
            @if (session('success'))
                <div class="account-flash account-flash--success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="account-flash account-flash--error">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="account-flash account-flash--error">{{ $errors->first() }}</div>
            @endif

            @yield('account')
        </div>
    </div>
</div>
@endsection
