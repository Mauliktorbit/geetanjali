@extends('emails.layout')

@section('title', 'Order '.$order->order_number.' is on the way')

@section('content')
    <h1 style="margin:0 0 12px;font-size:24px;font-weight:600;">Your order is on the way</h1>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#444;font-family:Arial,Helvetica,sans-serif;">
        Hello {{ $customerName }}, order <strong>{{ $order->order_number }}</strong> has been {{ $statusLabel }}.
    </p>
    @if ($trackingNumber)
        <p style="margin:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#555;">
            Tracking number
        </p>
        <p style="margin:0 0 16px;font-size:22px;font-weight:700;color:#064E3B;letter-spacing:0.04em;">{{ $trackingNumber }}</p>
        @if ($shippingPartner)
            <p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#666;">Courier: {{ $shippingPartner }}</p>
        @endif
    @endif
    <p style="margin:0 0 22px;text-align:center;">
        <a href="{{ $trackUrl }}" style="display:inline-block;background:#064E3B;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:600;">
            Track this order
        </a>
    </p>
    <p style="margin:0;font-size:13px;line-height:1.55;color:#666;font-family:Arial,Helvetica,sans-serif;">
        We will email you again when it is delivered.
    </p>
@endsection
