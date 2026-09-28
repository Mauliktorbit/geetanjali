<div class="notify-panel" data-dropdown-menu>
    <div class="notify-panel__head">
        <strong>Notifications</strong>
        <div class="notify-panel__actions">
            <form method="POST" action="{{ route('admin.notifications.read-all') }}" data-no-loading data-notify-read-all @if (($adminNotifyCount ?? 0) < 1) hidden @endif>
                @csrf
                <button type="submit" class="notify-panel__clear">Mark all read</button>
            </form>
            <form method="POST" action="{{ route('admin.notifications.destroy-all') }}" data-no-loading data-notify-clear-all @if (($adminNotifications ?? collect())->isEmpty()) hidden @endif>
                @csrf
                @method('DELETE')
                <button type="submit" class="notify-panel__clear notify-panel__clear--danger" data-confirm="Clear all notifications? This cannot be undone." data-confirm-danger>Clear all</button>
            </form>
        </div>
    </div>
    <div class="notify-panel__list" id="admin-notify-list">
        @forelse (($adminNotifications ?? []) as $note)
            <div class="notify-item-row{{ $note->is_read ? '' : ' is-unread' }}">
                <form method="POST" action="{{ route('admin.notifications.read', $note) }}" data-no-loading class="notify-item-row__open">
                    @csrf
                    <button type="submit" class="notify-item">
                        <span class="notify-item__icon notify-item__icon--{{ $note->type === 'return_requested' ? 'return' : ($note->type === 'order_created' ? 'order' : ($note->type === 'enquiry_created' || $note->type === 'newsletter_subscribed' ? 'enquiry' : ($note->type === 'low_stock' ? 'stock' : 'info'))) }}">
                            @if ($note->type === 'return_requested')
                                <svg viewBox="0 0 24 24"><path d="M9 14 4 9l5-5"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                            @elseif ($note->type === 'order_created')
                                <svg viewBox="0 0 24 24"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                            @elseif ($note->type === 'low_stock')
                                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            @endif
                        </span>
                        <span class="notify-item__copy">
                            <strong>{{ $note->title }}</strong>
                            @if ($note->message)
                                <small>{{ $note->message }}</small>
                            @endif
                            <em>{{ $note->timeAgo() }}</em>
                        </span>
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.notifications.destroy', $note) }}" data-no-loading class="notify-item-row__clear">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="notify-item__dismiss" title="Clear notification" aria-label="Clear notification" data-confirm="Clear this notification?" data-confirm-danger>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </form>
            </div>
        @empty
            <p class="notify-panel__empty">No notifications yet. New orders and returns will appear here.</p>
        @endforelse
    </div>
    <a class="notify-panel__all" href="{{ route('admin.notifications.index') }}">View all notifications</a>
</div>
