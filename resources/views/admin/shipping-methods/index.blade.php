@extends('admin.layouts.app')

@section('title', 'Shipping rules')

@section('content')
<div class="page-header">
    <div>
        <h1>Shipping rules</h1>
        <p class="subtitle">Charges and delivery times shown at checkout.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Shipping rules']]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.shipping-methods.create') }}" class="btn btn-primary">Add shipping rule</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name">
        <select name="status" class="form-control" aria-label="Status">
            <option value="">All</option>
            <option value="1" @selected(request('status') === '1')>Active</option>
            <option value="0" @selected(request('status') === '0')>Inactive</option>
        </select>
        @include('admin.components.sort-fields')
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    @include('admin.components.sortable-th', ['column' => 'rule', 'label' => 'Rule'])
                    @include('admin.components.sortable-th', ['column' => 'charge', 'label' => 'Charge'])
                    @include('admin.components.sortable-th', ['column' => 'delivery', 'label' => 'Delivery time'])
                    @include('admin.components.sortable-th', ['column' => 'free', 'label' => 'Free above'])
                    @include('admin.components.sortable-th', ['column' => 'status', 'label' => 'Status'])
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->name }}</strong>
                    </td>
                    <td>{{ (float) $item->rate > 0 ? '₹'.number_format((float) $item->rate, 0) : 'Free' }}</td>
                    <td>{{ $item->estimated_delivery ?: '—' }}</td>
                    <td>{{ $item->free_shipping_threshold ? '₹'.number_format((float) $item->free_shipping_threshold, 0) : '—' }}</td>
                    <td>@include('admin.components.status-badge', ['status' => $item->is_active ? 'active' : 'inactive'])</td>
                    <td class="actions">
                        <a href="{{ route('admin.shipping-methods.edit', $item) }}" class="btn btn-sm">Edit</a>
                        <button form="delete-{{ $item->id }}" class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Delete this shipping rule?')">Delete</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">@include('admin.components.empty-state', ['title' => 'No shipping rules yet', 'text' => 'Add a rule such as Standard or Express delivery.'])</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @foreach ($items as $item)
        <form id="delete-{{ $item->id }}" method="POST" action="{{ route('admin.shipping-methods.destroy', $item) }}" class="d-none">@csrf @method('DELETE')</form>
    @endforeach

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
