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

                <div class="auth-or">
                    <span>or continue with</span>
                </div>

                <div class="auth-social">
                    <button type="button" class="auth-social-btn" disabled title="Coming soon">
                        <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.8 1.1 7.9 3l5.7-5.7C34.2 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.2-.1-2.3-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3 0 5.8 1.1 7.9 3l5.7-5.7C34.2 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.3 26.7 36 24 36c-5.3 0-9.7-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.1-4.1 5.5l.1.1 6.2 5.2C39 37.3 44 33 44 24c0-1.2-.1-2.3-.4-3.5z"/></svg>
                        Continue with Google
                    </button>
                    <button type="button" class="auth-social-btn" disabled title="Coming soon">
                        <i class="bi bi-facebook" aria-hidden="true"></i>
                        Continue with Facebook
                    </button>
                </div>

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
