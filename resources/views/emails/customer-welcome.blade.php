@extends('emails.layout')

@section('title', 'Welcome to '.config('brand.name'))

@section('content')
    <h1 style="margin:0 0 12px;font-size:24px;font-weight:600;">Welcome to Geetanjali</h1>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#444;font-family:Arial,Helvetica,sans-serif;">
        Hello {{ $customerName }}, your account is ready. You can save pieces to your wishlist, check out faster, and track every order from your account.
    </p>
    <p style="margin:0 0 22px;text-align:center;">
        <a href="{{ $shopUrl }}" style="display:inline-block;background:#064E3B;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:600;">
            Start shopping
        </a>
    </p>
    <p style="margin:0;font-size:13px;line-height:1.55;color:#666;font-family:Arial,Helvetica,sans-serif;">
        If you did not create this account, please ignore this email or contact us.
    </p>
@endsection
