@php
    $column = (string) ($column ?? '');
    $label = (string) ($label ?? $column);
    $class = trim((string) ($class ?? ''));
    $default = strtolower((string) ($default ?? 'asc')) === 'desc' ? 'desc' : 'asc';
    $state = admin_sort_state($column);
@endphp
<th class="{{ $class }}">
    <a class="th-sort{{ $state ? ' is-'.$state : '' }}" href="{{ admin_sort_url($column, $default) }}">
        {{ $label }}
        <span class="th-sort__icon" aria-hidden="true"></span>
    </a>
</th>
