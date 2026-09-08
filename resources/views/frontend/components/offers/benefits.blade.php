@props(['benefits' => []])

<section class="offers-benefits" aria-label="Offer benefits">
    <div class="offers-container">
        <div class="offers-benefits__grid">
            @foreach ($benefits as $item)
                <div class="offers-benefits__item">
                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                    <div>
                        <h3>{{ $item['title'] }}</h3>
                        <p>{{ $item['subtitle'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
