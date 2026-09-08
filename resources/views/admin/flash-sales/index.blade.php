@extends('admin.layouts.app')

@section('title', 'FlashSales')

@section('content')
<div class="page-header">
    <div>
        <h1>FlashSales</h1>
        <p class="subtitle">Manage flash-sales</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.flash-sales.create') }}" class="btn btn-primary">Add FlashSale</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="card">
    <form method="GET" class="filters-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="form-control">
        <select name="status" class="form-control">
            <option value="">All status</option>
            <option value="1" @selected(request('status')==='1')>Active</option>
            <option value="0" @selected(request('status')==='0')>Inactive</option>
        </select>
        <select name="sort" class="form-control">
            <option value="created_at">Newest</option>
            <option value="name">Name</option>
        </select>
        <button class="btn btn-secondary">Filter</button>
    </form>

    <form method="POST" action="{{ route('admin.flash-sales.bulk') }}" id="bulk-form">
        @csrf
        <div class="bulk-bar">
            <select name="action" class="form-control" required>
                <option value="">Bulk action</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-secondary" onclick="return confirm('Apply bulk action?')">Apply</button>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" data-check-all></th>
                    <th>Title</th>
                    <th>Starts At</th>
                    <th>Ends At</th>
                    <th>Discount Percent</th>
                    <th>Is Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}"></td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->starts_at }}</td>
                    <td>{{ $item->ends_at }}</td>
                    <td>{{ $item->discount_percent }}</td>
                    <td>@include('admin.components.status-badge', ['status' => $item->is_active ? 'active' : 'inactive'])</td>
                        <td class="actions">
                            <a href="{{ route('admin.flash-sales.edit', $item) }}" class="btn btn-sm">Edit</a>
                            <a href="{{ route('admin.flash-sales.show', $item) }}" class="btn btn-sm btn-ghost">View</a>
                            <button form="delete-{{ $item->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?')">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="20">@include('admin.components.empty-state', ['title' => 'No flash-sales found', 'text' => 'Create your first FlashSale to get started.'])</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>

    @foreach($items as $item)
    <form id="delete-{{ $item->id }}" method="POST" action="{{ route('admin.flash-sales.destroy', $item) }}" class="d-none">@csrf @method('DELETE')</form>
    @endforeach

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection