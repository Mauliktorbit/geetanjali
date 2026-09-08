@extends('admin.layouts.app')
@section('title', 'Add SeoRedirect')
@section('content')
<div class="page-header">
    <div>
        <h1>Add SeoRedirect</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'SeoRedirects','url'=>route('admin.seo-redirects.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.seo-redirects.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>From Path</label>
                <input type="text" name="from_path" class="form-control" value="{{ old('from_path', $item->from_path ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>To Path</label>
                <input type="text" name="to_path" class="form-control" value="{{ old('to_path', $item->to_path ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Status Code</label>
                <input type="number" name="status_code" class="form-control" value="{{ old('status_code', $item->status_code ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.seo-redirects.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection