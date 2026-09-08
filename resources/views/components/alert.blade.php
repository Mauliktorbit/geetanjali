@props([
    'type' => 'info',
    'message' => null,
    'title' => null,
    'dismissible' => true,
])

@php
    $alertClass = 'alert alert-' . $type;
    if ($dismissible) {
        $alertClass .= ' alert-dismissible fade show';
    }
@endphp

<div {{ $attributes->merge(['class' => $alertClass, 'role' => 'alert']) }}>
    @if ($title)
        <strong>{{ $title }}</strong>
    @endif

    @if ($message)
        <div>{{ $message }}</div>
    @endif

    {{ $slot }}

    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
