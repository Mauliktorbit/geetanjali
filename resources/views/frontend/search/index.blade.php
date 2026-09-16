@extends('frontend.layouts.app')

@section('title', $query !== '' ? 'Search “'.$query.'” | Geetanjali Jewellers' : 'Search | Geetanjali Jewellers')
@section('meta_description', 'Search Geetanjali Jewellers for kundan, gold, diamond and bridal jewellery.')

@section('content')
<div class="new-arrivals-page search-page">
    @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

    <section class="na-listing" id="collection-products" aria-label="Search results">
        <div class="na-container">
            <header class="search-page__head">
                <h1 class="font-heading">
                    @if ($query !== '')
                        Search results
                    @else
                        Search jewellery
                    @endif
                </h1>
                @if ($query !== '')
                    <p>Showing matches for <strong>“{{ $query }}”</strong>@if ($products->total()) — {{ $products->total() }} {{ $products->total() === 1 ? 'piece' : 'pieces' }}@endif</p>
                @else
                    <p>Type a design, category, or metal — for example earrings, kundan, or 22k.</p>
                @endif
            </header>

            @if ($tooShort)
                <div class="na-empty">
                    <h2 class="font-heading">Please type a little more</h2>
                    <p>Enter at least 2 characters to search our jewellery.</p>
                </div>
            @elseif ($query === '')
                <div class="na-empty">
                    <h2 class="font-heading">What are you looking for?</h2>
                    <p>Use the search box above to find necklaces, earrings, rings, and more.</p>
                </div>
            @elseif ($products->count() > 0)
                <div class="na-grid">
                    @foreach ($products as $product)
                        @include('frontend.components.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="na-pagination">
                    {{ $products->fragment('collection-products')->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="na-empty">
                    <h2 class="font-heading">No jewellery found</h2>
                    <p>We could not find a match for “{{ $query }}”. Try another word, or browse a collection.</p>
                    <a href="{{ route('products.new-arrivals') }}" class="na-btn">Browse New Arrivals</a>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
