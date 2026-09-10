@extends('admin.layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="page-header">
    <div>
        <h1>Notifications</h1>
        <p class="subtitle">Orders and return requests from customers.</p>
    </div>
    <div class="page-actions">
        <form method="POST" action="{{ route('admin.notifications.read-all') }}" data-no-loading>
            @csrf
            <button class="btn btn-secondary" type="submit">Mark all read</button>
        </form>
    </div>
</div>
@include('admin.components.alerts')

<div class="card product-list-card">
    <div class="notify-page">
        @forelse ($items as $item)
            <form method="POST" action="{{ route('admin.notifications.read', $item) }}" data-no-loading>
                @csrf
                <button type="submit" class="notify-page__row{{ $item->is_read ? '' : ' is-unread' }}">
                    <span class="notify-item__icon notify-item__icon--{{ $item->type === 'return_requested' ? 'return' : ($item->type === 'order_created' ? 'order' : 'info') }}">
                        @if ($item->type === 'return_requested')
                            <svg viewBox="0 0 24 24"><path d="M9 14 4 9l5-5"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                        @elseif ($item->type === 'order_created')
                            <svg viewBox="0 0 24 24"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                        @else
                            <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        @endif
                    </span>
                    <span class="notify-item__copy">
                        <strong>{{ $item->title }}</strong>
                        <small>{{ $item->message ?: 'Open to view details.' }}</small>
                        <em>{{ $item->timeAgo() }}{{ $item->is_read ? '' : ' · New' }}</em>
                    </span>
                </button>
            </form>
        @empty
            @include('admin.components.empty-state', ['title' => 'No notifications', 'text' => 'When a customer places an order or asks for a return, it will show here.'])
        @endforelse
    </div>
    <div class="pagination-wrap">{{ $items->links('admin.components.pagination') }}</div>
</div>
@endsection
