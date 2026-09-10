@extends('admin.layouts.app')
@section('title', 'Create Order')
@section('content')
<div class="page-header">
    <div>
        <h1>Create Order</h1>
        <p class="subtitle">Add customer details, delivery address and products.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Orders', 'url' => route('admin.orders.index')], ['label' => 'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.orders.store') }}">
        @csrf
        <div class="collection-form__fields">
        <div class="form-group">
            <label for="customer-name">Customer name *</label>
            <input id="customer-name" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required maxlength="255">
            @error('customer_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label for="customer-phone">Phone *</label>
            <input id="customer-phone" type="tel" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" required maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" autocomplete="tel">
            @error('customer_phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group">
            <label for="customer-email">Email</label>
            <input id="customer-email" type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
        </div>
        <div class="form-group">
            <label for="address-line">Address</label>
            <input id="address-line" name="shipping_address[line1]" class="form-control" value="{{ old('shipping_address.line1') }}">
        </div>
        <div class="form-group">
            <label for="shipping-city">City</label>
            <input id="shipping-city" name="shipping_city" class="form-control" value="{{ old('shipping_city') }}">
        </div>
        <div class="form-group">
            <label for="shipping-state">State</label>
            <input id="shipping-state" name="shipping_state" class="form-control" value="{{ old('shipping_state') }}">
        </div>
        <div class="form-group">
            <label for="shipping-pincode">Pincode</label>
            <input id="shipping-pincode" name="shipping_pincode" class="form-control" value="{{ old('shipping_pincode') }}" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
        </div>
        <div class="form-group">
            <label for="payment-status">Payment</label>
            <select id="payment-status" name="payment_status" class="form-control">
                <option value="pending" @selected(old('payment_status', 'pending') === 'pending')>Pending</option>
                <option value="paid" @selected(old('payment_status') === 'paid')>Paid</option>
            </select>
        </div>

        <div class="form-group">
            <label>Products *</label>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody data-order-items>
                        <tr>
                            <td>
                                <select name="items[0][product_id]" class="form-control" required>
                                    <option value="">Select product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ (float) ($product->sale_price ?: $product->regular_price) }}">
                                            {{ $product->name }}{{ $product->sku ? ' ('.$product->sku.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <span class="form-hint">Price is taken from the product automatically.</span>
            @error('items')<span class="invalid-feedback">{{ $message }}</span>@enderror
            @error('items.0.product_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Create order</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
