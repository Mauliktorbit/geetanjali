@extends('admin.layouts.app')
@section('title', 'Customers')
@section('content')
<div class="page-header">
    <div>
        <h1>Customers</h1>
        <p class="subtitle">People who buy from the website.</p>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, email or phone">
        <select name="status" class="form-control" aria-label="Status">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(in_array(request('status'), ['inactive', 'blocked'], true))>Inactive</option>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Spent</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>
                        <a href="{{ route('admin.customers.show', $item) }}" class="orders-table__number">{{ $item->name }}</a>
                    </td>
                    <td>{{ $item->email ?: '—' }}</td>
                    <td>{{ $item->phone ?: '—' }}</td>
                    <td>{{ number_format((int) $item->total_orders) }}</td>
                    <td class="orders-table__total">{{ money($item->total_spent) }}</td>
                    <td>@include('admin.components.status-badge', ['status' => $item->statusBadge(), 'label' => $item->statusLabel()])</td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.customers.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View {{ $item->name }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.customers.toggle', $item) }}" data-no-loading>
                                @csrf
                                <button
                                    class="btn btn-sm btn-icon {{ $item->is_blocked ? 'btn-secondary' : 'btn-ghost' }}"
                                    type="submit"
                                    title="{{ $item->is_blocked ? 'Activate' : 'Deactivate' }}"
                                    aria-label="{{ $item->is_blocked ? 'Activate' : 'Deactivate' }} {{ $item->name }}"
                                    onclick="return confirm({{ json_encode($item->is_blocked ? 'Activate '.$item->name.' so they can log in again?' : 'Deactivate '.$item->name.' and block website login?') }})"
                                >
                                    @if ($item->is_blocked)
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                                    @endif
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">@include('admin.components.empty-state', ['title' => 'No customers', 'text' => 'Customers appear here when they register or place an order on the website.'])</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
