@once('app-flash-data')
@php
    $appFlash = array_filter([
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
        'status' => session('status'),
    ], static fn ($value) => filled($value));
@endphp
@if ($appFlash !== [])
    <script id="app-flash-data" type="application/json">@json($appFlash)</script>
@endif
@endonce
