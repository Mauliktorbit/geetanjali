@extends('layouts.auth')

@section('title', 'Forgot Password | Geetanjali Jewellers')

@section('content')
    <div class="auth-main auth-main--single" data-auth-page>
        <div class="auth-form-col">
            <div class="auth-form-wrap">
                <h1 class="font-heading">Forgot Password</h1>
                <p class="auth-subtitle">Enter your email and we will send a 6-digit OTP to reset your password</p>
                @include('frontend.components.gold-divider', ['align' => 'center'])

                @if (session('success'))
                    <div class="auth-alert auth-alert--success" role="status">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                    @csrf
                    <div class="auth-field">
                        <label for="forgot-email">Email Address</label>
                        <div class="auth-input">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input
                                id="forgot-email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Enter your email"
                                autocomplete="email"
                                required
                            >
                        </div>
                    </div>
                    <button type="submit" class="auth-btn">Send OTP</button>
                </form>

                <p class="auth-switch">
                    <a href="{{ route('login') }}">Back to Sign In</a>
                </p>
            </div>
        </div>
    </div>
@endsection
