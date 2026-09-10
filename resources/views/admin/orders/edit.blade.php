@extends('admin.layouts.app')
@section('title', 'Edit '.$item->order_number)
@section('content')
@php
    $ship = is_array($item->shipping_address) ? $item->shipping_address : [];
    $line1 = old('shipping_address.line1', $ship['line1'] ?? $ship['address_line1'] ?? '');
@endphp
<div class="page-header">
    <div>
        <h1>Edit {{ $item->order_number }}</h1>
        <p class="subtitle">Update customer and delivery details.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Orders', 'url' => route('admin.orders.index')], ['label' => $item->order_number, 'url' => route('admin.orders.show', $item)], ['label' => 'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.orders.update', $item) }}">
        @csrf
        @method('PUT')
        <div class="collection-form__fields">
        <div class="form-group">
            <label for="customer-name">Customer name *</label>
            <input id="customer-name" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $item->customer_name) }}" required maxlength="255">
            @error('customer_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label for="customer-phone">Phone</label>
            <input id="customer-phone" type="tel" name="customer_phone" class="form-control" value="{{ old('customer_phone', $item->customer_phone) }}" maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" autocomplete="tel">
        </div>
        <div class="form-group">
            <label for="customer-email">Email</label>
            <input id="customer-email" type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $item->customer_email) }}">
        </div>
        <div class="form-group">
            <label for="address-line">Address</label>
            <input id="address-line" name="shipping_address[line1]" class="form-control" value="{{ $line1 }}">
        </div>
        <div class="form-group">
            <label for="shipping-city">City</label>
            <input id="shipping-city" name="shipping_city" class="form-control" value="{{ old('shipping_city', $item->shipping_city) }}">
        </div>
        <div class="form-group">
            <label for="shipping-state">State</label>
            <input id="shipping-state" name="shipping_state" class="form-control" value="{{ old('shipping_state', $item->shipping_state) }}">
        </div>
        <div class="form-group">
            <label for="shipping-pincode">Pincode</label>
            <input id="shipping-pincode" name="shipping_pincode" class="form-control" value="{{ old('shipping_pincode', $item->shipping_pincode) }}" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
        </div>
        <div class="form-group">
            <label for="payment-status">Payment</label>
            <select id="payment-status" name="payment_status" class="form-control">
                <option value="pending" @selected(old('payment_status', $item->payment_status === 'paid' ? 'paid' : 'pending') === 'pending')>Pending</option>
                <option value="paid" @selected(old('payment_status', $item->payment_status === 'paid' ? 'paid' : 'pending') === 'paid')>Paid</option>
            </select>
        </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.orders.show', $item) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
