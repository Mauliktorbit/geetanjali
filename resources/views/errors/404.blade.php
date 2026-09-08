@extends('frontend.layouts.app')

@section('title', 'Page Not Found — ' . config('brand.name'))

@section('content')
    <section class="py-5 text-center">
        <h1 class="display-6 text-brand-primary">404</h1>
        <p class="lead">The page you are looking for could not be found.</p>
        <x-button type="brand" :href="route('home')">Back to Home</x-button>
    </section>
@endsection
