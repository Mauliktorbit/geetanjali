@php
    $items = $items ?? [];
@endphp
@if (count($items))
<nav class="breadcrumbs" aria-label="Breadcrumb">
    @foreach ($items as $index => $item)
        @php
            $label = is_array($item) ? ($item['label'] ?? '') : (string) $item;
            $url = is_array($item) ? ($item['url'] ?? null) : null;
            $isLast = $loop->last;
        @endphp
        @if ($index > 0)
            <span class="sep" aria-hidden="true">/</span>
        @endif
        @if (!$isLast && $url)
            <a href="{{ $url }}">{{ $label }}</a>
        @else
            <span class="current" @if($isLast) aria-current="page" @endif>{{ $label }}</span>
        @endif
    @endforeach
</nav>
@endif
