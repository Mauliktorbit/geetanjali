@extends('admin.layouts.app')
@section('title', 'Collection Details')
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $item->name }}</h1>
        <p class="subtitle">Collection details</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.collections.edit', $item) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('admin.collections.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
<div class="card">
    <div class="detail-grid">
        <div class="detail-item">
            <span class="label">Name</span>
            <span class="value">{{ $item->name }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Slug</span>
            <span class="value">{{ $item->slug }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Type</span>
            <span class="value">{{ ucfirst($item->type) }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">@include('admin.components.status-badge', ['status' => $item->is_active ? 'active' : 'inactive'])</span>
        </div>
        <div class="detail-item">
            <span class="label">Sort Order</span>
            <span class="value">{{ $item->sort_order }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Description</span>
            <span class="value">{{ $item->description ?: '—' }}</span>
        </div>
        @if($item->image)
        <div class="detail-item">
            <span class="label">Image</span>
            <span class="value"><img src="{{ asset('storage/'.$item->image) }}" alt="" class="thumb-sm"></span>
        </div>
        @endif
        @if($item->banner)
        <div class="detail-item">
            <span class="label">Banner</span>
            <span class="value"><img src="{{ asset('storage/'.$item->banner) }}" alt="" class="thumb-sm"></span>
        </div>
        @endif
    </div>
</div>
@endsection
