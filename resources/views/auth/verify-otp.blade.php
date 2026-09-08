@extends('layouts.auth')

@section('title', 'Verify OTP | Geetanjali Jewellers')

@section('content')
    <div class="auth-main auth-main--single" data-auth-page>
        <div class="auth-form-col">
            <div class="auth-form-wrap">
                <h1 class="font-heading">Verify OTP</h1>
                <p class="auth-subtitle">Enter the 6-digit code we sent to<br><strong>{{ $maskedEmail }}</strong></p>
                @include('frontend.components.gold-divider', ['align' => 'center'])

                @if (session('success'))
                    <div class="auth-alert auth-alert--success" role="status">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.otp.verify') }}" class="auth-form">
                    @csrf
                    <div class="auth-field">
                        <label for="reset-otp">One-time password</label>
                        <div class="auth-input auth-otp-input">
                            <input
                                id="reset-otp"
                                type="text"
                                name="otp"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                maxlength="6"
                                autocomplete="one-time-code"
                                placeholder="••••••"
                                required
                                data-otp-input
                            >
                        </div>
                    </div>
                    <button type="submit" class="auth-btn">Verify OTP</button>
                </form>

                <form method="POST" action="{{ route('password.otp.resend') }}" class="auth-resend">
                    @csrf
                    <p>Didn’t receive the code?
                        <button type="submit">Resend OTP</button>
                    </p>
                </form>

                <p class="auth-switch">
                    <a href="{{ route('password.request') }}">Use a different email</a>
                </p>
            </div>
        </div>
    </div>
@endsection
