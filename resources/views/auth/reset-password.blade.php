@extends('layouts.auth')

@section('title', 'Reset Password | Geetanjali Jewellers')

@section('content')
    <div class="auth-main auth-main--single" data-auth-page>
        <div class="auth-form-col">
            <div class="auth-form-wrap">
                <h1 class="font-heading">Set New Password</h1>
                <p class="auth-subtitle">Create a new password for<br><strong>{{ $maskedEmail }}</strong></p>
                @include('frontend.components.gold-divider', ['align' => 'center'])

                @if (session('success'))
                    <div class="auth-alert auth-alert--success" role="status">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                    @csrf
                    <div class="auth-field">
                        <label for="reset-password">New Password</label>
                        <div class="auth-input">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input
                                id="reset-password"
                                type="password"
                                name="password"
                                placeholder="Minimum 8 characters"
                                autocomplete="new-password"
                                minlength="8"
                                required
                                data-password-input
                            >
                            <button type="button" class="auth-eye" data-toggle-password aria-label="Show password">
                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label for="reset-password-confirm">Confirm Password</label>
                        <div class="auth-input">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input
                                id="reset-password-confirm"
                                type="password"
                                name="password_confirmation"
                                placeholder="Re-enter new password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                                data-password-input
                            >
                            <button type="button" class="auth-eye" data-toggle-password aria-label="Show password">
                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="auth-btn">Update Password</button>
                </form>

                <p class="auth-switch">
                    <a href="{{ $loginUrl }}">{{ $loginLabel }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection
