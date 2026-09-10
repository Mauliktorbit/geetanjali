@extends('frontend.layouts.app')

@section('title', 'Track Order | Geetanjali Jewellers')
@section('meta_description', 'Track your Geetanjali Jewellers order status.')

@section('content')
    <div class="static-page">
        @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

        <div class="static-page__shell">
            <header class="static-page__header">
                <h1 class="font-heading">{{ $page['heading'] }}</h1>
                @include('frontend.components.gold-divider', ['align' => 'left'])
                <p>{{ $page['intro'] }}</p>
            </header>

            <form class="static-page__form" method="post" action="{{ route('pages.track-order.submit') }}">
                @csrf
                <div>
                    <label for="order_id">Order ID</label>
                    <input id="order_id" type="text" name="order_id" value="{{ old('order_id', $orderId) }}" required maxlength="50" placeholder="e.g. GJ123456">
                    @error('order_id') <p class="static-page__error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="track_phone">Phone (optional)</label>
                    <input id="track_phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" placeholder="10-digit mobile number">
                    @error('phone') <p class="static-page__error">{{ $message }}</p> @enderror
                </div>
                <button type="submit">Track Order</button>
            </form>

            @if ($statusMessage)
                <div class="static-page__alert">{{ $statusMessage }}</div>
            @endif

            <div class="static-page__content">
                @foreach ($page['sections'] as $section)
                    <article class="static-page__block">
                        <h2 class="font-heading">{{ $section['heading'] }}</h2>
                        <p>{!! nl2br(e($section['body'])) !!}</p>
                    </article>
                @endforeach
            </div>

            <div class="static-page__actions">
                <a href="{{ route('contact') }}" class="static-page__btn static-page__btn--primary">Contact Support</a>
                <a href="{{ route('account.index') }}" class="static-page__btn">My Account</a>
            </div>
        </div>
    </div>
@endsection
