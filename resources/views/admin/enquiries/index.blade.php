@extends('admin.layouts.app')
@section('title', $tab === 'subscriptions' ? 'Subscriptions' : 'Enquiries')
@section('content')
<div class="page-header">
    <div>
        <h1>Enquiries</h1>
        <p class="subtitle">Contact messages and newsletter subscriptions from the website.</p>
    </div>
</div>
@include('admin.components.alerts')

<div class="inbox-tabs">
    <a href="{{ route('admin.enquiries.index') }}" class="inbox-tabs__link{{ $tab === 'enquiries' ? ' is-active' : '' }}">
        Contact messages
        @if ($newCount > 0)
            <span>{{ $newCount }}</span>
        @endif
    </a>
    <a href="{{ route('admin.enquiries.index', ['tab' => 'subscriptions']) }}" class="inbox-tabs__link{{ $tab === 'subscriptions' ? ' is-active' : '' }}">
        Subscriptions
        @if ($subscriberCount > 0)
            <span>{{ $subscriberCount }}</span>
        @endif
    </a>
</div>

<div class="card product-list-card">
    @if ($tab === 'subscriptions')
        <form method="GET" class="filters-bar product-list-filters">
            <input type="hidden" name="tab" value="subscriptions">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search email">
            <button class="btn btn-secondary" type="submit">Search</button>
            @if (request('search'))
                <a href="{{ route('admin.enquiries.index', ['tab' => 'subscriptions']) }}" class="btn btn-ghost">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Date</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <strong class="orders-table__customer">{{ $item->email }}</strong>
                        </td>
                        <td>{{ $item->created_at?->format('d M Y, h:i A') ?: '—' }}</td>
                        <td class="actions col-actions">
                            <form method="POST" action="{{ route('admin.subscribers.destroy', $item) }}" data-no-loading>
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Remove this subscriber?')">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">@include('admin.components.empty-state', ['title' => 'No subscribers yet', 'text' => 'When a customer joins the newsletter, their email will appear here.'])</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @else
        <form method="GET" class="filters-bar product-list-filters">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, email, phone">
            <select name="status" class="form-control" aria-label="Status">
                <option value="">All</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-secondary" type="submit">Filter</button>
            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.enquiries.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($items as $item)
                    <tr class="{{ $item->statusKey() === 'new' ? 'is-unread-row' : '' }}">
                        <td>
                            <a href="{{ route('admin.enquiries.show', $item) }}" class="orders-table__number">{{ $item->name }}</a>
                            @if ($item->statusKey() === 'new')
                                <div class="category-product-empty">New</div>
                            @endif
                        </td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->phone ?: '—' }}</td>
                        <td>{{ $item->messagePreview() }}</td>
                        <td>{{ $item->created_at?->format('d M Y') ?: '—' }}</td>
                        <td class="actions col-actions">
                            <div class="action-group">
                                <a href="{{ route('admin.enquiries.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View enquiry">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">@include('admin.components.empty-state', ['title' => 'No enquiries yet', 'text' => 'When a customer sends a contact message, it will appear here.'])</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
