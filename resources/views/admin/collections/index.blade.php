@extends('admin.layouts.app')

@section('title', 'Collections')

@section('content')
<div class="page-header">
    <div>
        <h1>Collections</h1>
        <p class="subtitle">Jewellery collections shown on the storefront</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.collections.create') }}" class="btn btn-primary">Add Collection</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="card">
    <form method="GET" class="filters-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search collections…" class="form-control">
        <select name="status" class="form-control">
            <option value="">All status</option>
            <option value="1" @selected(request('status')==='1')>Active</option>
            <option value="0" @selected(request('status')==='0')>Inactive</option>
        </select>
        <select name="sort" class="form-control">
            <option value="sort_order" @selected(request('sort')==='sort_order')>Display order</option>
            <option value="created_at" @selected(request('sort', 'created_at')==='created_at')>Newest</option>
            <option value="name" @selected(request('sort')==='name')>Name</option>
        </select>
        <button class="btn btn-secondary">Filter</button>
    </form>

    <form method="POST" action="{{ route('admin.collections.bulk') }}" id="bulk-form">
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
                        <th>Collection</th>
                        <th>Type</th>
                        <th>Slug</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $item->id }}"></td>
                        <td>
                            <div class="flex items-center gap-2">
                                @if($item->image)
                                    <img src="{{ asset('storage/'.$item->image) }}" alt="" class="thumb-sm">
                                @endif
                                <strong>{{ $item->name }}</strong>
                            </div>
                        </td>
                        <td>{{ ucfirst($item->type) }}</td>
                        <td>{{ $item->slug }}</td>
                        <td>{{ $item->sort_order }}</td>
                        <td>@include('admin.components.status-badge', ['status' => $item->is_active ? 'active' : 'inactive'])</td>
                        <td class="actions">
                            <a href="{{ route('admin.collections.edit', $item) }}" class="btn btn-sm">Edit</a>
                            <a href="{{ route('admin.collections.show', $item) }}" class="btn btn-sm btn-ghost">View</a>
                            <button form="delete-{{ $item->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete this collection?')">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">@include('admin.components.empty-state', ['title' => 'No collections found', 'text' => 'Create Kundan, Bridal or custom jewellery collections.'])</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>

    @foreach($items as $item)
    <form id="delete-{{ $item->id }}" method="POST" action="{{ route('admin.collections.destroy', $item) }}" class="d-none">@csrf @method('DELETE')</form>
    @endforeach

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
