@extends('admin.layouts.app')
@section('title','Settings')
@section('content')
<div class="page-header"><div><h1>Settings</h1></div></div>
@include('admin.components.alerts')
<div class="card">
<div class="filters-bar">
<a href="{{ route('admin.settings.index',['tab'=>'general']) }}" class="btn {{ $tab==='general'?'btn-primary':'btn-ghost' }}">General</a>
<a href="{{ route('admin.settings.index',['tab'=>'order']) }}" class="btn {{ $tab==='order'?'btn-primary':'btn-ghost' }}">Orders</a>
<a href="{{ route('admin.settings.index',['tab'=>'product']) }}" class="btn {{ $tab==='product'?'btn-primary':'btn-ghost' }}">Products</a>
<a href="{{ route('admin.settings.index',['tab'=>'customer']) }}" class="btn {{ $tab==='customer'?'btn-primary':'btn-ghost' }}">Customers</a>
<a href="{{ route('admin.settings.index',['tab'=>'invoice']) }}" class="btn {{ $tab==='invoice'?'btn-primary':'btn-ghost' }}">Invoice</a>
</div>
@if($tab==='general')
<form method="POST" action="{{ route('admin.settings.general') }}">@csrf
<div class="form-grid">
@foreach(['store_name','store_email','store_phone','store_address','store_city','store_state','store_country','store_pincode','store_gstin','currency','timezone'] as $key)
<div class="form-group"><label>{{ str_replace('_',' ',ucfirst($key)) }}</label><input name="{{ $key }}" class="form-control" value="{{ old($key, $general[$key] ?? '') }}"></div>
@endforeach
</div><button class="btn btn-primary">Save</button></form>
@elseif($tab==='order')
<form method="POST" action="{{ route('admin.settings.order') }}">@csrf
<div class="form-grid">
<div class="form-group"><label>Order prefix</label><input name="order_prefix" class="form-control" value="{{ $order['order_prefix'] ?? '' }}"></div>
<div class="form-group"><label>Min order amount</label><input type="number" step="0.01" name="min_order_amount" class="form-control" value="{{ $order['min_order_amount'] ?? '' }}"></div>
<div class="form-group"><label>COD max</label><input type="number" step="0.01" name="cod_max_amount" class="form-control" value="{{ $order['cod_max_amount'] ?? '' }}"></div>
<div class="form-group form-check"><label><input type="checkbox" name="auto_confirm_orders" value="1" @checked($order['auto_confirm_orders'] ?? false)> Auto confirm</label></div>
<div class="form-group form-check"><label><input type="checkbox" name="allow_guest_checkout" value="1" @checked($order['allow_guest_checkout'] ?? true)> Guest checkout</label></div>
<div class="form-group form-check"><label><input type="checkbox" name="cod_enabled" value="1" @checked($order['cod_enabled'] ?? true)> COD enabled</label></div>
</div><button class="btn btn-primary">Save</button></form>
@elseif($tab==='product')
<form method="POST" action="{{ route('admin.settings.product') }}">@csrf
<div class="form-grid">
<div class="form-group"><label>Low stock threshold</label><input type="number" name="low_stock_threshold" class="form-control" value="{{ $product['low_stock_threshold'] ?? 5 }}"></div>
<div class="form-group"><label>Weight unit</label><input name="weight_unit" class="form-control" value="{{ $product['weight_unit'] ?? 'kg' }}"></div>
<div class="form-group"><label>Dimension unit</label><input name="dimension_unit" class="form-control" value="{{ $product['dimension_unit'] ?? 'cm' }}"></div>
<div class="form-group form-check"><label><input type="checkbox" name="allow_backorders" value="1" @checked($product['allow_backorders'] ?? false)> Allow backorders</label></div>
<div class="form-group form-check"><label><input type="checkbox" name="show_out_of_stock" value="1" @checked($product['show_out_of_stock'] ?? true)> Show out of stock</label></div>
</div><button class="btn btn-primary">Save</button></form>
@elseif($tab==='customer')
<form method="POST" action="{{ route('admin.settings.customer') }}">@csrf
<div class="form-grid">
<div class="form-group"><label>Points per currency</label><input type="number" step="0.01" name="points_per_currency" class="form-control" value="{{ $customer['points_per_currency'] ?? 1 }}"></div>
<div class="form-group form-check"><label><input type="checkbox" name="reward_points_enabled" value="1" @checked($customer['reward_points_enabled'] ?? true)> Reward points</label></div>
<div class="form-group form-check"><label><input type="checkbox" name="wallet_enabled" value="1" @checked($customer['wallet_enabled'] ?? true)> Wallet</label></div>
<div class="form-group form-check"><label><input type="checkbox" name="require_email_verification" value="1" @checked($customer['require_email_verification'] ?? false)> Require email verification</label></div>
</div><button class="btn btn-primary">Save</button></form>
@else
<form method="POST" action="{{ route('admin.settings.invoice') }}">@csrf
<div class="form-grid">
<div class="form-group"><label>Invoice prefix</label><input name="invoice_prefix" class="form-control" value="{{ $invoice['invoice_prefix'] ?? '' }}"></div>
<div class="form-group span-2"><label>Footer</label><textarea name="invoice_footer" class="form-control">{{ $invoice['invoice_footer'] ?? '' }}</textarea></div>
<div class="form-group span-2"><label>Terms</label><textarea name="invoice_terms" class="form-control">{{ $invoice['invoice_terms'] ?? '' }}</textarea></div>
<div class="form-group span-2"><label>Bank details</label><textarea name="bank_details" class="form-control">{{ $invoice['bank_details'] ?? '' }}</textarea></div>
<div class="form-group form-check"><label><input type="checkbox" name="show_hsn" value="1" @checked($invoice['show_hsn'] ?? true)> Show HSN</label></div>
<div class="form-group form-check"><label><input type="checkbox" name="show_bank_details" value="1" @checked($invoice['show_bank_details'] ?? false)> Show bank details</label></div>
</div><button class="btn btn-primary">Save</button></form>
@endif
</div>
@endsection