@php
    $title = $title ?? 'Nothing here yet';
    $text = $text ?? 'Get started by creating your first item.';
    $actionUrl = $actionUrl ?? ($action['url'] ?? null);
    $actionLabel = $actionLabel ?? ($action['label'] ?? null);
@endphp
<div class="empty-state">
    <div class="empty-state-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24">
            <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
        </svg>
    </div>
    <div class="empty-state-title">{{ $title }}</div>
    <p class="empty-state-text">{{ $text }}</p>
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-primary">{{ $actionLabel }}</a>
    @elseif (!empty($action) && is_string($action))
        {!! $action !!}
    @elseif (!empty($slot))
        {{ $slot }}
    @endif
</div>
