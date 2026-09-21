@props(['promos' => []])

<section class="bridal-promos" aria-label="Bridal promotions">
    <div class="site-container">
        <div class="bridal-promos__grid">
            @foreach ($promos as $promo)
                <article class="bridal-promo bridal-promo--{{ $promo['theme'] ?? 'dark' }}">
                    <div class="bridal-promo__content">
                        <h3 class="bridal-promo__title">
                            @foreach (preg_split("/\r\n|\n|\r/", (string) ($promo['heading'] ?? '')) as $line)
                                @if (trim($line) !== '')
                                    <span>{{ $line }}</span>
                                @endif
                            @endforeach
                        </h3>
                        <p>{{ $promo['description'] ?? '' }}</p>
                        <a href="{{ $promo['cta_url'] ?? '#' }}" class="bridal-btn bridal-btn--promo">
                            {{ $promo['cta_label'] ?? 'Learn More' }}
                        </a>
                    </div>
                    <div class="bridal-promo__media" aria-hidden="true">
                        <img
                            src="{{ asset($promo['image'] ?? '') }}"
                            alt=""
                            loading="lazy"
                            width="480"
                            height="320"
                        >
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
