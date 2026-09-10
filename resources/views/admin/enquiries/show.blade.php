@extends('admin.layouts.app')
@section('title', $item->name)
@section('content')
<div class="page-header">
    <div>
        <h1>{{ $item->name }}</h1>
        <p class="subtitle">Contact message</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Enquiries', 'url' => route('admin.enquiries.index')], ['label' => $item->name]]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>
@include('admin.components.alerts')

<div class="card">
    <div class="detail-list">
        <div class="detail-item">
            <span class="label">Status</span>
            <span class="value">@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])</span>
        </div>
        <div class="detail-item">
            <span class="label">Date</span>
            <span class="value">{{ $item->created_at?->format('d M Y, h:i A') ?: '—' }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Name</span>
            <span class="value">{{ $item->name }}</span>
        </div>
        <div class="detail-item">
            <span class="label">Email</span>
            <span class="value"><a href="mailto:{{ $item->email }}">{{ $item->email }}</a></span>
        </div>
        <div class="detail-item">
            <span class="label">Phone</span>
            <span class="value">
                @if ($item->phone)
                    <a href="tel:{{ $item->phone }}">{{ $item->phone }}</a>
                @else
                    —
                @endif
            </span>
        </div>
        @if ($item->subject)
            <div class="detail-item">
                <span class="label">Subject</span>
                <span class="value">{{ $item->subject }}</span>
            </div>
        @endif
        <div class="detail-item">
            <span class="label">Message</span>
            <span class="value">{{ $item->message }}</span>
        </div>
    </div>
</div>
@endsection
