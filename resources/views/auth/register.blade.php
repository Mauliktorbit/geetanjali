@extends('layouts.auth')

@section('title', 'Create Account | Geetanjali Jewellers')
@section('meta_description', 'Create your Geetanjali Jewellers account and discover timeless jewellery.')

@section('content')
    <div class="auth-main" data-auth-page>
        <div class="auth-form-col">
            <div class="auth-form-wrap">
                <h1 class="font-heading">Create Account</h1>
                <p class="auth-subtitle">Join the Geetanjali family and discover<br>timeless beauty</p>
                @include('frontend.components.gold-divider', ['align' => 'center'])

                @if (session('success'))
                    <div class="auth-alert auth-alert--success" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('register.submit') }}"
                    class="auth-form"
                    data-register-form
                    data-check-url="{{ route('register.check') }}"
                    novalidate
                >
                    @csrf

                    <div class="auth-field @error('name') is-invalid @enderror" data-field-wrap>
                        <label for="register-name">Full Name</label>
                        <div class="auth-input">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            <input
                                id="register-name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Enter your full name"
                                autocomplete="name"
                                maxlength="120"
                                required
                            >
                        </div>
                        <span class="auth-error" data-field-error @if (! $errors->has('name')) hidden @endif>{{ $errors->first('name') }}</span>
                    </div>

                    <div class="auth-field @error('email') is-invalid @enderror" data-field-wrap>
                        <label for="register-email">Email Address</label>
                        <div class="auth-input">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input
                                id="register-email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Enter your email"
                                autocomplete="email"
                                required
                            >
                        </div>
                        <span class="auth-error" data-field-error @if (! $errors->has('email')) hidden @endif>{{ $errors->first('email') }}</span>
                    </div>

                    <div class="auth-field @error('mobile') is-invalid @enderror" data-field-wrap>
                        <label for="register-mobile">Mobile Number</label>
                        <div class="auth-input">
                            <i class="bi bi-telephone" aria-hidden="true"></i>
                            <input
                                id="register-mobile"
                                type="tel"
                                name="mobile"
                                value="{{ old('mobile') }}"
                                placeholder="10-digit mobile number"
                                autocomplete="tel"
                                inputmode="numeric"
                                maxlength="10"
                                pattern="[6-9][0-9]{9}"
                                required
                            >
                        </div>
                        <span class="auth-error" data-field-error @if (! $errors->has('mobile')) hidden @endif>{{ $errors->first('mobile') }}</span>
                    </div>

                    <div class="auth-field @error('password') is-invalid @enderror" data-field-wrap>
                        <label for="register-password">Password</label>
                        <div class="auth-input">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input
                                id="register-password"
                                type="password"
                                name="password"
                                placeholder="Create a password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                                data-password-input
                            >
                            <button type="button" class="auth-eye" data-toggle-password aria-label="Show password">
                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span class="auth-error" data-field-error @if (! $errors->has('password')) hidden @endif>{{ $errors->first('password') }}</span>
                    </div>

                    <div class="auth-field" data-field-wrap>
                        <label for="register-password-confirmation">Confirm Password</label>
                        <div class="auth-input">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input
                                id="register-password-confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="Confirm your password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                                data-password-input
                            >
                            <button type="button" class="auth-eye" data-toggle-password aria-label="Show password">
                                <i class="bi bi-eye-slash" aria-hidden="true"></i>
                            </button>
                        </div>
                        <span class="auth-error" data-field-error hidden></span>
                    </div>

                    <div class="auth-field @error('terms') is-invalid @enderror" data-field-wrap>
                        <label class="auth-terms">
                            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
                            <span>
                                I agree to the
                                <a href="{{ route('pages.terms') }}" target="_blank" rel="noopener">Terms &amp; Conditions</a>
                                and
                                <a href="{{ route('pages.privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>
                            </span>
                        </label>
                        <span class="auth-error" data-field-error @if (! $errors->has('terms')) hidden @endif>{{ $errors->first('terms') }}</span>
                    </div>

                    <button type="submit" class="auth-btn">Register</button>
                </form>

                <p class="auth-switch">
                    Already have an account?
                    <a href="{{ route('login') }}">Sign In</a>
                </p>
            </div>
        </div>

        <div class="auth-visual" aria-hidden="true">
            <img
                src="{{ asset($image) }}"
                alt=""
                width="520"
                height="900"
                loading="eager"
            >
        </div>
    </div>
@endsection
