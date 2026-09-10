@props(['categories' => []])

<nav class="kundan-cat-nav" aria-label="Kundan categories">
    <div class="site-container">
        <ul class="kundan-cat-nav__list">
            @foreach ($categories as $category)
                <li>
                    <a
                        href="{{ $category['url'] }}"
                        class="kundan-cat-nav__item {{ !empty($category['active']) ? 'is-active' : '' }}"
                        @if (!empty($category['active'])) aria-current="page" @endif
                    >
                        <span class="kundan-cat-nav__circle">
                            <img
                                src="{{ storefront_image($category['image']) }}"
                                alt=""
                                loading="lazy"
                                width="90"
                                height="90"
                            >
                        </span>
                        <span class="kundan-cat-nav__label">{{ $category['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
