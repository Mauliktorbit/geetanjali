@props([
    'filters' => [],
    'formId' => 'na-filter-form',
])

@php
    $categories = [
        'rings' => 'Rings',
        'earrings' => 'Earrings',
        'necklaces' => 'Necklaces',
        'bangles' => 'Bangles',
        'mangalsutra' => 'Mangalsutra',
        'bridal' => 'Bridal',
        'kundan' => 'Kundan',
        'diamond' => 'Diamond',
    ];
    $metals = [
        '18k' => '18K Gold',
        '22k' => '22K Gold',
        '24k' => '24K Gold',
    ];
    $stones = [
        'diamond' => 'Diamond',
        'emerald' => 'Emerald',
        'ruby' => 'Ruby',
        'kundan' => 'Kundan',
        'pearl' => 'Pearl',
    ];
    $occasions = [
        'wedding' => 'Wedding',
        'engagement' => 'Engagement',
        'festival' => 'Festival',
        'daily-wear' => 'Daily Wear',
    ];
@endphp

<form
    id="{{ $formId }}"
    class="na-filter-panel"
    method="get"
    action="{{ route('products.new-arrivals') }}"
>
    <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'newest' }}">
    <input type="hidden" name="view" value="{{ $filters['view'] ?? 'grid' }}">

    <fieldset class="na-filter-group">
        <legend>Category</legend>
        @foreach ($categories as $key => $label)
            <label class="na-check">
                <input type="checkbox" name="category[]" value="{{ $key }}" @checked(in_array($key, $filters['category'] ?? [], true))>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </fieldset>

    <fieldset class="na-filter-group">
        <legend>Metal</legend>
        @foreach ($metals as $key => $label)
            <label class="na-check">
                <input type="checkbox" name="metal[]" value="{{ $key }}" @checked(in_array($key, $filters['metal'] ?? [], true))>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </fieldset>

    <fieldset class="na-filter-group">
        <legend>Stone</legend>
        @foreach ($stones as $key => $label)
            <label class="na-check">
                <input type="checkbox" name="stone[]" value="{{ $key }}" @checked(in_array($key, $filters['stone'] ?? [], true))>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </fieldset>

    <fieldset class="na-filter-group">
        <legend>Price Range</legend>
        <div class="na-price-inputs">
            <label>
                <span class="visually-hidden">Minimum price</span>
                <input type="number" name="min_price" min="0" step="1000" placeholder="Min" value="{{ $filters['min_price'] ?? '' }}">
            </label>
            <span aria-hidden="true">–</span>
            <label>
                <span class="visually-hidden">Maximum price</span>
                <input type="number" name="max_price" min="0" step="1000" placeholder="Max" value="{{ $filters['max_price'] ?? '' }}">
            </label>
        </div>
    </fieldset>

    <fieldset class="na-filter-group">
        <legend>Occasion</legend>
        @foreach ($occasions as $key => $label)
            <label class="na-check">
                <input type="checkbox" name="occasion[]" value="{{ $key }}" @checked(in_array($key, $filters['occasion'] ?? [], true))>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </fieldset>

    <div class="na-filter-actions">
        <button type="submit" class="na-btn na-btn--primary">Apply Filter</button>
        <a href="{{ route('products.new-arrivals') }}" class="na-btn na-btn--ghost">Clear All</a>
    </div>
</form>
