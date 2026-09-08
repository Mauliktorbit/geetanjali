@extends('admin.layouts.app')
@section('title', 'Add Customer')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Customer</h1>
        <p class="subtitle">Creates a website login account as well as a customer profile</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Customers', 'url' => route('admin.customers.index')], ['label' => 'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.customers.store') }}">
        @csrf
        @include('admin.customers._form')
        <div class="form-actions">
            <button class="btn btn-primary">Save customer</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
