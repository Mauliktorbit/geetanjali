@extends('admin.layouts.app')
@section('title', 'Add Campaign')
@section('content')
<div class="page-header">
    <div>
        <h1>Add Campaign</h1>
        @include('admin.components.breadcrumbs', ['items' => [['label'=>'Campaigns','url'=>route('admin.campaigns.index')], ['label'=>'Create']]])
    </div>
</div>
@include('admin.components.alerts')
<div class="card">
    <form method="POST" action="{{ route('admin.campaigns.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Channel</label>
                <input type="text" name="channel" class="form-control" value="{{ old('channel', $item->channel ?? '') }}" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <input type="text" name="type" class="form-control" value="{{ old('type', $item->type ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject', $item->subject ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" class="form-control" rows="4" >{{ old('content', $item->content ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label>Status</label>
                <input type="text" name="status" class="form-control" value="{{ old('status', $item->status ?? '') }}" >
            </div>
            <div class="form-group">
                <label>Scheduled At</label>
                <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at', $item->scheduled_at ?? '') }}" >
            </div>
        <div class="form-actions">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.campaigns.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection