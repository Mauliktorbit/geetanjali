@extends('admin.layouts.app')

@section('title', 'Collections')

@section('content')
<div class="page-header">
    <div>
        <h1>Collections</h1>
        <p class="subtitle">Name the jewellery collections shown on the website, such as Kundan or Bridal.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => 'Collections']]])
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.collections.create') }}" class="btn btn-primary">Add Collection</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="card product-list-card">
    <form method="GET" class="filters-bar product-list-filters">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name" class="form-control">
        <select name="status" class="form-control" aria-label="Status">
            <option value="">All status</option>
            <option value="1" @selected(request('status') === '1')>Active</option>
            <option value="0" @selected(request('status') === '0')>Inactive</option>
        </select>
        <button class="btn btn-secondary" type="submit">Search</button>
        @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.collections.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('admin.collections.bulk') }}" id="bulk-form" data-no-loading>
        @csrf
        <div class="bulk-bar">
            <select name="action" class="form-control" required>
                <option value="">Bulk action</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-secondary" type="submit" onclick="return confirm('Apply this action to the selected collections?')">Apply</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-check"><input type="checkbox" data-check-all form="bulk-form" aria-label="Select all"></th>
                    <th>Name</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                @php
                    $protected = \App\Services\StorefrontCatalogService::isProtectedSlug($item->slug);
                @endphp
                <tr>
                    <td class="col-check">
                        <input type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulk-form" aria-label="Select {{ $item->name }}">
                    </td>
                    <td>
                        <strong>{{ $item->name }}</strong>
                        @if ($protected)
                            <span class="collection-site-tag">Website page</span>
                        @endif
                    </td>
                    <td>
                        @include('admin.components.status-badge', [
                            'status' => $item->is_active ? 'active' : 'inactive',
                            'label' => $item->is_active ? 'Active' : 'Inactive',
                        ])
                    </td>
                    <td class="actions col-actions">
                        <div class="action-group">
                            <a href="{{ route('admin.collections.show', $item) }}" class="btn btn-sm btn-icon btn-ghost" title="View" aria-label="View {{ $item->name }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('admin.collections.edit', $item) }}" class="btn btn-sm btn-icon" title="Edit" aria-label="Edit {{ $item->name }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.collections.toggle', $item) }}" data-no-loading>
                                @csrf
                                <button
                                    class="btn btn-sm btn-icon {{ $item->is_active ? 'btn-ghost' : 'btn-secondary' }}"
                                    type="submit"
                                    title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"
                                    aria-label="{{ $item->is_active ? 'Deactivate' : 'Activate' }} {{ $item->name }}"
                                    onclick="return confirm('{{ $item->is_active ? 'Hide '.$item->name.' from the website?' : 'Show '.$item->name.' on the website?' }}')"
                                >
                                    @if ($item->is_active)
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                    @endif
                                </button>
                            </form>
                            @if (! $protected)
                                <form method="POST" action="{{ route('admin.collections.destroy', $item) }}" data-no-loading>
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-icon btn-danger" type="submit" title="Delete" aria-label="Delete {{ $item->name }}" onclick="return confirm('Delete {{ $item->name }}? Products stay listed and are only removed from this collection.')">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">@include('admin.components.empty-state', ['title' => 'No collections yet', 'text' => 'Add a collection name, such as Festive or Everyday Gold.'])</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
