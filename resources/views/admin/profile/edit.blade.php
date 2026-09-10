@extends('admin.layouts.app')
@section('title', 'Profile')
@section('content')
@php
    $role = $item->roles->first()?->name ?? 'Administrator';
    $initials = collect(explode(' ', (string) $item->name))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');
@endphp
<div class="page-header">
    <div>
        <h1>Profile</h1>
        <p class="subtitle">Your name, email, phone and password for this admin account.</p>
    </div>
</div>
@include('admin.components.alerts')

<div class="card profile-hero">
    <span class="profile-hero__avatar" aria-hidden="true">{{ strtoupper($initials ?: 'AD') }}</span>
    <div>
        <h2 class="profile-hero__name">{{ $item->name }}</h2>
        <p class="profile-hero__meta">{{ $role }}</p>
        <p class="profile-hero__meta">{{ $item->email }}</p>
    </div>
</div>

<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.profile.update') }}" autocomplete="off">
        @csrf
        @method('PUT')
        <input type="text" name="username" value="" autocomplete="username" tabindex="-1" aria-hidden="true" class="sr-only">
        <div class="collection-form__fields">
            <div class="form-group">
                <label for="profile-name">Name *</label>
                <input id="profile-name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required maxlength="255" autocomplete="name">
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="profile-email">Email *</label>
                <input id="profile-email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $item->email) }}" required maxlength="255" autocomplete="email">
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="profile-phone">Phone</label>
                <input id="profile-phone" type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $item->phone ?: $item->mobile) }}" maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" autocomplete="tel">
                @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="profile-current-password">Current password</label>
                <input id="profile-current-password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="off">
                @error('current_password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <span class="form-hint">Needed only if you want to change the password.</span>
            </div>
            <div class="form-group">
                <label for="profile-password">New password</label>
                <input id="profile-password" type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" autocomplete="new-password">
                @error('new_password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <span class="form-hint">Leave blank to keep your current password. At least 8 characters.</span>
            </div>
            <div class="form-group">
                <label for="profile-password-confirmation">Confirm new password</label>
                <input id="profile-password-confirmation" type="password" name="new_password_confirmation" class="form-control" autocomplete="new-password">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save profile</button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Back</a>
        </div>
    </form>
</div>
@endsection
