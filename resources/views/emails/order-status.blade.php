@extends('emails.layout')

@section('title', 'Order '.$order->order_number.' '.$heading)

@section('content')
    <h1 style="margin:0 0 12px;font-size:24px;font-weight:600;">{{ $heading }}</h1>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#444;font-family:Arial,Helvetica,sans-serif;">
        Hello {{ $customerName }}, {{ $intro }}
    </p>
    <p style="margin:0 0 22px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#555;">
        Order number <strong style="color:#064E3B;">{{ $order->order_number }}</strong>
    </p>
    <p style="margin:0 0 22px;text-align:center;">
        <a href="{{ $ctaUrl }}" style="display:inline-block;background:#064E3B;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:600;">
            {{ $ctaLabel }}
        </a>
    </p>
@endsection
