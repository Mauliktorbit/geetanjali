@extends('frontend.layouts.app')

@section('title', 'Page Expired — ' . config('brand.name'))

@section('content')
    <section class="py-5 text-center">
        <h1 class="display-6 text-brand-primary">419</h1>
        <p class="lead">Your session has expired. Please refresh and try again.</p>
        <x-button type="brand" :href="url()->previous() ?: route('home')">Go Back</x-button>
    </section>
@endsection
