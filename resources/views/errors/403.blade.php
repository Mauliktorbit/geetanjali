@extends('frontend.layouts.app')

@section('title', 'Forbidden — ' . config('brand.name'))

@section('content')
    <section class="py-5 text-center">
        <h1 class="display-6 text-brand-primary">403</h1>
        <p class="lead">You do not have permission to access this page.</p>
        <x-button type="brand" :href="route('home')">Back to Home</x-button>
    </section>
@endsection
