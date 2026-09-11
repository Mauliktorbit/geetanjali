@php
    $label = $label ?? 'Metric';
    $value = $value ?? '0';
    $meta = $meta ?? null;
    $trend = $trend ?? null; // 'up' | 'down' | null
    $trendText = $trendText ?? null;
    $variant = $variant ?? 'default'; // default|success|warning|danger|info
    $icon = $icon ?? null;
    $url = $url ?? null;
    $variantClass = $variant !== 'default' ? 'kpi-' . $variant : '';
    $tag = $url ? 'a' : 'div';
@endphp
<{{ $tag }} @if ($url) href="{{ $url }}" @endif class="kpi-card {{ $url ? 'kpi-card--link' : '' }} {{ $variantClass }}">
    @if ($icon)
        <div class="kpi-icon">{!! $icon !!}</div>
    @endif
    <div class="kpi-label">{{ $label }}</div>
    <div class="kpi-value">{{ $value }}</div>
    @if ($meta || $trendText)
        <div class="kpi-meta">
            @if ($trend === 'up')
                <span class="kpi-trend-up">▲ {{ $trendText }}</span>
            @elseif ($trend === 'down')
                <span class="kpi-trend-down">▼ {{ $trendText }}</span>
            @elseif ($trendText)
                <span>{{ $trendText }}</span>
            @endif
            @if ($meta)
                <span>{{ $meta }}</span>
            @endif
        </div>
    @endif
</{{ $tag }}>
