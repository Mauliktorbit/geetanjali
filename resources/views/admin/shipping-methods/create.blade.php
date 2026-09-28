@extends('admin.layouts.app')
@section('title', 'Add shipping rule')
@section('content')
<div class="page-header">
    <div>
        <h1>Add shipping rule</h1>
        <p class="subtitle">This option will appear as a delivery method at checkout.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Shipping rules', 'url' => route('admin.shipping-methods.index')], ['label' => 'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.shipping-methods.store') }}">
        @csrf
        @include('admin.shipping-methods._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save rule</button>
            <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
