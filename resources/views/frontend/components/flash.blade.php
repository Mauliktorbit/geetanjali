{{-- Checkout uses a single auto-hiding toast instead of this banner. --}}
@if (session('success') && ! request()->routeIs('checkout.index'))
    <div class="site-container pt-3">
        <x-alert type="success" :message="session('success')" />
    </div>
@endif

@if (session('error') && ! request()->routeIs('checkout.index'))
    <div class="site-container pt-3">
        <x-alert type="danger" :message="session('error')" />
    </div>
@endif

@if (session('warning'))
    <div class="site-container pt-3">
        <x-alert type="warning" :message="session('warning')" />
    </div>
@endif

@if (session('info'))
    <div class="site-container pt-3">
        <x-alert type="info" :message="session('info')" />
    </div>
@endif

@if ($errors->any() && ! request()->routeIs('checkout.index'))
    <div class="site-container pt-3">
        <x-alert type="danger" title="Please correct the following:">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    </div>
@endif
