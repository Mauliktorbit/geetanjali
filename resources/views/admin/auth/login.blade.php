@extends('admin.layouts.guest')

@section('title', 'Admin Login')
@section('subtitle', 'Sign in to manage your store')

@section('content')
<form method="POST" action="{{ route('admin.login.submit') }}" class="guest-form" data-loading>
    @csrf
    <div class="form-group">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" class="form-control" value="{{ old('email', 'admin@geetanjali.test') }}" required autofocus autocomplete="username" placeholder="you@store.com">
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="••••••••">
        @error('password')<div class="field-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group form-check">
        <label class="form-check-label">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            Remember me
        </label>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Sign in to dashboard</button>
</form>
@endsection
