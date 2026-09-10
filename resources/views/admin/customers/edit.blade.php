@extends('admin.layouts.app')
@section('title', 'Edit Customer')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Customer</h1>
        <p class="subtitle">Update name, email, phone or status.</p>
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.customers.update', $item) }}">
        @csrf
        @method('PUT')
        @include('admin.customers._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.customers.show', $item) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
