@extends('admin.layouts.app')
@section('title', 'Edit shipping rule')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit shipping rule</h1>
        <p class="subtitle">Changes apply the next time a customer checks out.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Shipping rules', 'url' => route('admin.shipping-methods.index')], ['label' => $item->name]]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.shipping-methods.update', $item) }}">
        @csrf
        @method('PUT')
        @include('admin.shipping-methods._form', ['item' => $item])
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update rule</button>
            <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
