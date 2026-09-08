@props([
    'type' => 'primary',
    'href' => null,
    'tag' => 'button',
])

@php
    $classes = match ($type) {
        'brand' => 'btn btn-brand',
        'brand-outline' => 'btn btn-brand-outline',
        default => 'btn btn-' . $type,
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <{{ $tag }} {{ $attributes->merge(['type' => $attributes->get('type', 'button'), 'class' => $classes]) }}>
        {{ $slot }}
    </{{ $tag }}>
@endif
