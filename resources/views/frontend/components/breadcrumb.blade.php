@props([
    'items' => [],
    'flush' => false,
])

@php
    $items = $items ?: [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'About Us', 'url' => null],
    ];
@endphp

<div class="about-breadcrumb{{ $flush ? ' about-breadcrumb--flush' : '' }}">
    @if ($flush)
        <nav aria-label="Breadcrumb">
            @include('frontend.components.breadcrumb-list', ['items' => $items])
        </nav>
    @else
        <div class="site-container">
            <nav aria-label="Breadcrumb">
                @include('frontend.components.breadcrumb-list', ['items' => $items])
            </nav>
        </div>
    @endif
</div>
