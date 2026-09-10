@extends('admin.layouts.app')
@section('title', 'Edit Collection')
@section('content')
<div class="page-header">
    <div>
        <h1>Edit Collection</h1>
        <p class="subtitle">Update the collection name, details and banner image shown on the website.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Collections', 'url' => route('admin.collections.index')], ['label' => 'Edit']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.collections.update', $item) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.collections._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Update</button>
            <a href="{{ route('admin.collections.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
