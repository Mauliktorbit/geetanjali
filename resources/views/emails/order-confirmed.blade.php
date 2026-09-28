@extends('emails.layout')

@section('title', 'Order '.$order->order_number.' confirmed')

@section('content')
    <h1 style="margin:0 0 12px;font-size:24px;font-weight:600;">Order confirmed</h1>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#444;font-family:Arial,Helvetica,sans-serif;">
        Hello {{ $customerName }}, thank you for your order. We are preparing your jewellery with care.
    </p>
    <p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#555;">
        Order number <strong style="color:#064E3B;">{{ $order->order_number }}</strong>
    </p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#444;margin:0 0 16px;">
        @foreach ($order->items as $item)
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #eadfca;">{{ $item->product_name }} × {{ $item->quantity }}</td>
                <td style="padding:8px 0;border-bottom:1px solid #eadfca;text-align:right;">₹{{ number_format((float) $item->total) }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="padding:8px 0;">Subtotal</td>
            <td style="padding:8px 0;text-align:right;">₹{{ number_format((float) $order->subtotal) }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;">{{ $order->discountLineLabel() }}</td>
            <td style="padding:4px 0;text-align:right;color:#16803c;">- ₹{{ number_format((float) $order->discount_amount) }}</td>
        </tr>
        <tr>
            <td style="padding:4px 0;">Shipping</td>
            <td style="padding:4px 0;text-align:right;">{{ $order->shippingAmountLabel() }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0 0;font-weight:700;color:#064E3B;">Order Total</td>
            <td style="padding:10px 0 0;text-align:right;font-weight:700;color:#064E3B;">₹{{ number_format((float) $order->grand_total) }}</td>
        </tr>
    </table>
    <p style="margin:0 0 22px;text-align:center;">
        <a href="{{ $trackUrl }}" style="display:inline-block;background:#064E3B;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:600;">
            Track this order
        </a>
    </p>
    <p style="margin:0;font-size:13px;line-height:1.55;color:#666;font-family:Arial,Helvetica,sans-serif;">
        You can also view it anytime in <a href="{{ $accountUrl }}" style="color:#064E3B;">My Orders</a>.
    </p>
@endsection
