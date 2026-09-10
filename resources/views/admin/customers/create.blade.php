@extends('admin.layouts.app')
@section('title', 'Add Customer')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Customer</h1>
        <p class="subtitle">Name, email and phone. A website login is created automatically.</p>
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.customers.store') }}">
        @csrf
        @include('admin.customers._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save customer</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
