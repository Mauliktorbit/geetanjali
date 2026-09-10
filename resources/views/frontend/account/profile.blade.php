@extends('frontend.account.layout')

@section('title', 'Account Details | Geetanjali Jewellers')

@section('account')
<section class="account-card account-card--form">
    <h1 class="font-heading account-page-title">Account Details</h1>
    <form method="POST" action="{{ route('account.profile.update') }}" class="account-form">
        @csrf
        @method('PUT')
        <label>Full name
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
        </label>
        <label>Email
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </label>
        <label>Phone
            <input type="tel" name="mobile" value="{{ old('mobile', $user->mobile ?: $user->phone) }}" maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" autocomplete="tel" required>
        </label>
        <label>New password
            <input type="password" name="password" autocomplete="new-password">
        </label>
        <label>Confirm password
            <input type="password" name="password_confirmation" autocomplete="new-password">
        </label>
        <button type="submit" class="account-solid-btn">Save Changes</button>
    </form>
</section>
@endsection
