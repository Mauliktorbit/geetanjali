@props([
    'items' => [],
])

@php
    $items = $items ?: [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'About Us', 'url' => null],
    ];
@endphp

<div class="about-breadcrumb">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="d-flex flex-wrap align-items-center list-unstyled mb-0 gap-0">
                @foreach ($items as $index => $item)
                    <li class="d-inline-flex align-items-center">
                        @if ($index > 0)
                            <span class="separator" aria-hidden="true">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        @endif

                        @if (!empty($item['url']) && !$loop->last)
                            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                        @else
                            <span @if ($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
</div>
