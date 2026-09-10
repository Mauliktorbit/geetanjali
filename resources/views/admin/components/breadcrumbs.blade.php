@php
    $items = $items ?? [];
    $global = ! empty($global);
    $renderBreadcrumbs = false;

    if ($global) {
        \App\Support\AdminBreadcrumb::shown(true);
        $renderBreadcrumbs = count($items) > 0;
    } elseif (! \App\Support\AdminBreadcrumb::shown() && count($items) > 0) {
        $renderBreadcrumbs = true;
    }
@endphp
@if ($renderBreadcrumbs)
<nav class="admin-breadcrumbs" aria-label="Breadcrumb">
    @foreach ($items as $index => $item)
        @php
            $label = is_array($item) ? ($item['label'] ?? '') : (string) $item;
            $url = is_array($item) ? ($item['url'] ?? null) : null;
            $isLast = $loop->last;
        @endphp
        @if ($index === 0)
            @if (! $isLast && $url)
                <a href="{{ $url }}" class="admin-breadcrumbs__home" title="Dashboard">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/></svg>
                    <span>{{ $label }}</span>
                </a>
            @else
                <span class="admin-breadcrumbs__home current" aria-current="page">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/></svg>
                    <span>{{ $label }}</span>
                </span>
            @endif
        @else
            <span class="sep" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
            </span>
            @if (! $isLast && $url)
                <a href="{{ $url }}">{{ $label }}</a>
            @else
                <span class="current" @if ($isLast) aria-current="page" @endif>{{ $label }}</span>
            @endif
        @endif
    @endforeach
</nav>
@endif
