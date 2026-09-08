@props(['terms' => []])

<section class="offers-terms" aria-labelledby="offers-terms-heading">
    <div class="offers-container">
        <h2 id="offers-terms-heading" class="font-heading">Terms &amp; Conditions</h2>
        @include('frontend.components.gold-divider', ['align' => 'center'])

        <div class="offers-terms__grid">
            @foreach ($terms as $term)
                <div class="offers-terms__item">
                    <i class="bi {{ $term['icon'] }}" aria-hidden="true"></i>
                    <p>{!! nl2br(e($term['text'] ?? '')) !!}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
