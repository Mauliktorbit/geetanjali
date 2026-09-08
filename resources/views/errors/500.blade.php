@extends('frontend.layouts.app')

@section('title', 'Server Error — ' . config('brand.name'))

@section('content')
    <section class="py-5 text-center">
        <h1 class="display-6 text-brand-primary">500</h1>
        <p class="lead">Something went wrong on our side. Please try again later.</p>
        <x-button type="brand" :href="route('home')">Back to Home</x-button>
    </section>
@endsection
