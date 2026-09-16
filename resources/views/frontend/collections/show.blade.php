@extends('frontend.layouts.app')

@section('title', $collection->name.' | Geetanjali Jewellers')
@section('meta_description', 'Explore '.$collection->name.' jewellery from Geetanjali Jewellers.')

@section('content')
    <div class="kundan-page">
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        <section class="kundan-listing" id="collection-products" aria-label="{{ $collection->name }} products">
            <div class="site-container">
                <div class="kundan-products">
                    <div class="kundan-products__header">
                        <div>
                            <h1 class="font-heading">{{ $collection->name }}</h1>
                            <p class="kundan-products__count">
                                @if ($products->total() > 0)
                                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products
                                @else
                                    Showing 0 products
                                @endif
                            </p>
                        </div>
                    </div>

                    @if ($products->count() > 0)
                        <div class="kundan-grid">
                            @foreach ($products as $product)
                                @include('frontend.components.product-card', ['product' => $product])
                            @endforeach
                        </div>

                        <div class="kundan-pagination">
                            {{ $products->fragment('collection-products')->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <div class="kundan-empty">
                            <p>Products for this collection will appear here once they are assigned in the admin.</p>
                            <a href="{{ route('home') }}" class="btn-apply-filters">Continue shopping</a>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @include('frontend.components.collection.service-strip', ['services' => $services])
    </div>
@endsection
