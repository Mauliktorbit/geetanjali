@extends('frontend.layouts.app')

@section('title', ($page['title'] ?? 'Page') . ' | Geetanjali Jewellers')
@section('meta_description', $page['intro'] ?? 'Geetanjali Jewellers information page.')

@section('content')
    <div class="static-page">
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        <div class="static-page__shell">
            <header class="static-page__header">
                <h1 class="font-heading">{{ $page['heading'] }}</h1>
                @include('frontend.components.gold-divider', ['align' => 'left'])
                <p>{{ $page['intro'] }}</p>
            </header>

            <div class="static-page__content">
                @foreach ($page['sections'] as $section)
                    <article class="static-page__block">
                        <h2 class="font-heading">{{ $section['heading'] }}</h2>
                        <p>{!! nl2br(e($section['body'])) !!}</p>
                    </article>
                @endforeach
            </div>

            <div class="static-page__actions">
                @if (($slug ?? '') === 'store-locator')
                    <a href="{{ $contact['map_directions'] ?? route('contact') }}" class="static-page__btn static-page__btn--primary" target="_blank" rel="noopener noreferrer">
                        Get Directions
                    </a>
                    <a href="{{ route('contact') }}" class="static-page__btn">Contact Us</a>
                @elseif (($slug ?? '') === 'help')
                    <a href="{{ route('pages.faq') }}" class="static-page__btn static-page__btn--primary">View FAQs</a>
                    <a href="{{ route('contact') }}" class="static-page__btn">Contact Us</a>
                @else
                    <a href="{{ route('contact') }}" class="static-page__btn static-page__btn--primary">Contact Us</a>
                    <a href="{{ route('home') }}" class="static-page__btn">Back to Home</a>
                @endif
            </div>
        </div>
    </div>
@endsection
