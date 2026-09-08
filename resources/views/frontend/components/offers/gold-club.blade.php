@props(['goldClub' => []])

<section class="offers-gold-club" aria-labelledby="gold-club-heading">
    <div class="offers-container">
        <div class="offers-gold-club__inner">
            <div class="offers-gold-club__media">
                <div class="offers-gold-club__card-wrap">
                    <img
                        src="{{ asset($goldClub['image'] ?? 'public/assets/images/offers/gold-card.jpg') }}"
                        alt="Geetanjali Gold Club membership"
                        loading="lazy"
                        width="280"
                        height="180"
                    >
                </div>
            </div>

            <div class="offers-gold-club__content">
                <p class="offers-gold-club__kicker font-heading">{{ $goldClub['kicker'] ?? 'Not a Member Yet?' }}</p>
                <h2 id="gold-club-heading" class="font-heading">{{ $goldClub['title'] ?? 'Join Geetanjali Gold Club' }}</h2>
                <p>{{ $goldClub['description'] ?? '' }}</p>
            </div>

            <div class="offers-gold-club__action">
                <a href="{{ $goldClub['cta_url'] ?? '#' }}" class="offers-join-btn">
                    {{ $goldClub['cta_label'] ?? 'Join Now →' }}
                </a>
            </div>
        </div>
    </div>
</section>
