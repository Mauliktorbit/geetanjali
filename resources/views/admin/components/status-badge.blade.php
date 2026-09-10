@php
    $raw = strtolower(trim((string) ($status ?? 'default')));
    $normalized = str_replace([' ', '-'], '_', $raw);
    $map = [
        'requested' => 'warning',
        'approved' => 'info',
        'rejected' => 'cancelled',
        'pending' => 'pending',
        'processing' => 'processing',
        'shipped' => 'shipped',
        'delivered' => 'delivered',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        'canceled' => 'cancelled',
        'refunded' => 'refunded',
        'on_hold' => 'on-hold',
        'onhold' => 'on-hold',
        'active' => 'active',
        'inactive' => 'inactive',
        'failed' => 'failed',
        'paid' => 'paid',
        'unpaid' => 'unpaid',
        'success' => 'success',
        'warning' => 'warning',
        'danger' => 'danger',
        'error' => 'danger',
        'info' => 'info',
        'new' => 'warning',
        'read' => 'info',
        'replied' => 'success',
        'closed' => 'inactive',
    ];
    $badge = $map[$normalized] ?? $map[$raw] ?? 'default';
    $label = $label ?? str_replace(['_', '-'], ' ', (string) ($status ?? 'Unknown'));
@endphp
<span class="badge badge-{{ $badge }}">{{ $label }}</span>
