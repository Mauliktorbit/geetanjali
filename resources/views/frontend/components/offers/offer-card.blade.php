@props(['offer' => []])

@php
    $theme = $offer['theme'] ?? 'dark';
    $image = storefront_image($offer['image'] ?? null);
@endphp

<article class="offer-card offer-card--{{ $theme }}">
    <div class="offer-card__body">
        <div class="offer-card__copy">
            @if (! empty($offer['label']))
                <span class="offer-card__label">{{ $offer['label'] }}</span>
            @endif
            <div class="offer-card__discount font-heading">
                <span class="offer-card__value">{{ $offer['discount_value'] ?? '' }}</span>
                <span class="offer-card__suffix">{{ $offer['discount_suffix'] ?? 'Off' }}</span>
            </div>
            <p class="offer-card__title">{!! nl2br(e($offer['title'] ?? '')) !!}</p>
            @if (! empty($offer['min_order']))
                <p class="offer-card__min">{{ $offer['min_order'] }}</p>
            @endif
            @if (! empty($offer['promo_code']))
                <button type="button" class="offer-card__code" data-copy-code="{{ $offer['promo_code'] }}">
                    Use Code: <strong>{{ $offer['promo_code'] }}</strong>
                </button>
            @endif
        </div>
        <div class="offer-card__media">
            <img
                src="{{ $image }}"
                alt="{{ $offer['image_alt'] ?? ($offer['title'] ?? 'Offer') }}"
                loading="lazy"
                width="280"
                height="220"
            >
        </div>
    </div>
    <div class="offer-card__footer">
        <span>
            <i class="bi bi-clock" aria-hidden="true"></i>
            Valid Till: {{ $offer['valid_until'] ?? 'Limited period' }}
        </span>
        <span>
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            T&amp;C Apply
        </span>
    </div>
</article>
