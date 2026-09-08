@props([
    'tabs' => [],
    'active' => 'all',
])

<nav class="offers-filters" aria-label="Offer categories">
    <ul class="offers-filters__list">
        @foreach ($tabs as $tab)
            <li>
                <a
                    href="{{ route('offers.index', $tab['key'] === 'all' ? [] : ['category' => $tab['key']]) }}"
                    class="offers-filter-pill {{ $active === $tab['key'] ? 'is-active' : '' }}"
                    @if ($active === $tab['key']) aria-current="page" @endif
                >
                    {{ $tab['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
