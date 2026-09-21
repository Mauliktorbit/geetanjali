@extends('admin.layouts.app')
@section('title', 'Add Collection')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Collection</h1>
        <p class="subtitle">Enter the collection name, details and banner image for the website page.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Collections', 'url' => route('admin.collections.index')], ['label' => 'Add']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.collections.store') }}" enctype="multipart/form-data" data-unsaved-guard>
        @csrf
        @include('admin.collections._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('admin.collections.index') }}" class="btn btn-ghost" data-unsaved-cancel>Cancel</a>
        </div>
    </form>
</div>
@endsection
