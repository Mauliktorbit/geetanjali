@extends('admin.layouts.app')
@section('title', 'Enquiry Details')
@section('content')
<div class="page-header">
    <div>
        <h1>Enquiry</h1>
        <p class="subtitle">{{ $item->name }} · {{ $item->created_at?->format('d M Y, h:i A') }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-ghost">Back</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Message</h2>
            @include('admin.components.status-badge', ['status' => $item->status])
        </div>
        <div class="detail-grid">
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
                <span class="value">{{ $item->phone ?: '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="label">Subject</span>
                <span class="value">{{ $item->subject ?: '—' }}</span>
            </div>
            <div class="detail-item" style="grid-column:1 / -1">
                <span class="label">Message</span>
                <span class="value">{{ $item->message }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Update status</h2>
        </div>
        <form method="POST" action="{{ route('admin.enquiries.update', $item) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    @foreach(\App\Models\Enquiry::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $item->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Admin notes</label>
                <textarea name="admin_notes" class="form-control" rows="5">{{ old('admin_notes', $item->admin_notes) }}</textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
