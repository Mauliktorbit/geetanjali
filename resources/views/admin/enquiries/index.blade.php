@extends('admin.layouts.app')

@section('title', 'Enquiries')

@section('content')
<div class="page-header">
    <div>
        <h1>Enquiries</h1>
        <p class="subtitle">Contact form messages from the website</p>
    </div>
</div>

@include('admin.components.alerts')

<div class="card">
    <form method="GET" class="filters-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, message…" class="form-control">
        <select name="status" class="form-control">
            <option value="">All status</option>
            @foreach(\App\Models\Enquiry::STATUSES as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary">Filter</button>
    </form>

    <form method="POST" action="{{ route('admin.enquiries.bulk') }}" id="bulk-form">
        @csrf
        <div class="bulk-bar">
            <select name="action" class="form-control" required>
                <option value="">Bulk action</option>
                <option value="read">Mark as read</option>
                <option value="replied">Mark as replied</option>
                <option value="closed">Close</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-secondary" onclick="return confirm('Apply bulk action?')">Apply</button>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" data-check-all></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}"></td>
                        <td><strong>{{ $item->name }}</strong></td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone ?: '—' }}</td>
                        <td>{{ $item->subject ?: '—' }}</td>
                        <td>@include('admin.components.status-badge', ['status' => $item->status])</td>
                        <td>{{ $item->created_at?->format('d M Y, h:i A') }}</td>
                        <td class="actions">
                            <a href="{{ route('admin.enquiries.show', $item) }}" class="btn btn-sm">View</a>
                            <button form="delete-{{ $item->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete this enquiry?')">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">@include('admin.components.empty-state', ['title' => 'No enquiries yet', 'text' => 'Website contact form messages will appear here.'])</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>

    @foreach($items as $item)
    <form id="delete-{{ $item->id }}" method="POST" action="{{ route('admin.enquiries.destroy', $item) }}" class="d-none">@csrf @method('DELETE')</form>
    @endforeach

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
