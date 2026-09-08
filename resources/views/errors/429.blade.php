@extends('frontend.layouts.app')

@section('title', 'Too Many Requests — ' . config('brand.name'))

@section('content')
    <section class="py-5 text-center">
        <h1 class="display-6 text-brand-primary">429</h1>
        <p class="lead">Too many requests. Please wait a moment and try again.</p>
        <x-button type="brand" :href="route('home')">Back to Home</x-button>
    </section>
@endsection
