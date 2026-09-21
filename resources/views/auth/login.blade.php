@extends('layouts.auth')

@section('title', 'Login | Geetanjali Jewellers')
@section('meta_description', 'Sign in to your Geetanjali Jewellers account.')

@section('content')
    <div class="auth-main" data-auth-page>
        <div class="auth-form-col">
            <div class="auth-form-wrap">
                <h1 class="font-heading">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your account and continue<br>your Geetanjali journey</p>
                @include('frontend.components.gold-divider', ['align' => 'center'])

                @if (str_contains((string) session('url.intended'), '/checkout'))
                    <div class="auth-alert auth-alert--success" role="status">
                        Please sign in to continue to checkout.
                    </div>
                @endif

                @if (session('success'))
                    <div class="auth-alert auth-alert--success" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="auth-alert" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="auth-form" novalidate>
                    @csrf

                    <div class="auth-field">
                        <label for="login-email">Email Address</label>
                        <div class="auth-input">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            <input
                                id="login-email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Enter your email"
                                autocomplete="email"
                                required
                            >
                        </div>
                        @error('email') <span class="auth-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="auth-field">
                        <label for="login-password">Password</label>
                        <div class="auth-input">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input
                                id="login-password"
                                type="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                                data-password-input
                            >
                            <button type="button" class="auth-eye" data-toggle-password aria-label="Show password">
                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password') <span class="auth-error">{{ $message }}</span> @enderror
                        <div class="auth-forgot">
                            <label class="auth-remember">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember', true))>
                                Remember me
                            </label>
                            <a href="{{ route('password.request') }}">Forgot Password?</a>
                        </div>
                    </div>

                    <button type="submit" class="auth-btn">Sign In</button>
                </form>

                <p class="auth-switch">
                    Don't have an account?
                    <a href="{{ route('register') }}">Register Now</a>
                </p>
            </div>
        </div>

        <div class="auth-visual" aria-hidden="true">
            <img
                src="{{ asset($image) }}"
                alt=""
                width="520"
                height="720"
                loading="eager"
            >
        </div>
    </div>
@endsection
