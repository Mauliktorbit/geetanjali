@extends('admin.layouts.app')
@section('title', 'Add Faq')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Faq</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Faqs','url'=>route('admin.faqs.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.faqs.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Question</label>
                <input type="text" name="question" class="form-control" value="{{ old('question', $item->question ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Answer</label>
                <textarea name="answer" class="form-control" rows="4" required>{{ old('answer', $item->answer ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $item->category ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $item->sort_order ?? '') }}" >
            </div>
            <div class="form-group form-check">
                <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}> Is Active</label>
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection