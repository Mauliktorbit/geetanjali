@extends('frontend.layouts.app')

@section('title', 'Contact Us | Geetanjali Jewellers')
@section('meta_description', 'Contact Geetanjali Jewellers in Ahmedabad for jewellery enquiries, store visits and support.')

@section('content')
    @php
        $phoneHref = preg_replace('/\s+/', '', $contact['phone'] ?? '');
    @endphp

    <div class="contact-page">
        <section
            class="contact-hero"
            aria-labelledby="contact-heading"
            style="--contact-hero-image: url('{{ asset($heroImage) }}');"
        >
            <div
                class="contact-hero__visual"
                role="img"
                aria-label="Gold pearl earrings on silk"
            ></div>

            <div class="container contact-hero__inner">
                <div class="contact-hero__content">
                    @include('frontend.components.breadcrumb', ['items' => $breadcrumb])

                    <h1 id="contact-heading" class="font-heading">Contact Us</h1>
                    @include('frontend.components.gold-divider', ['align' => 'left'])
                    <p>We'd love to hear from you. Get in touch with us for any enquiries.</p>
                </div>
            </div>
        </section>

        <div class="contact-shell">
            @if (session('success'))
                <div class="contact-flash" role="status">{{ session('success') }}</div>
            @endif

            <div class="contact-grid">
                {{-- Get In Touch --}}
                <article class="contact-card">
                    <h2 class="font-heading">Get In Touch</h2>

                    <ul class="contact-points">
                        <li>
                            <span class="contact-points__icon" aria-hidden="true">
                                <i class="bi bi-geo-alt-fill"></i>
                            </span>
                            <div>
                                <strong>Visit Our Store</strong>
                                <p>{{ $contact['address'] }}</p>
                            </div>
                        </li>
                        <li>
                            <span class="contact-points__icon" aria-hidden="true">
                                <i class="bi bi-telephone-fill"></i>
                            </span>
                            <div>
                                <strong>Call Us</strong>
                                <p><a href="tel:{{ $phoneHref }}">{{ $contact['phone'] }}</a></p>
                            </div>
                        </li>
                        <li>
                            <span class="contact-points__icon" aria-hidden="true">
                                <i class="bi bi-envelope-fill"></i>
                            </span>
                            <div>
                                <strong>Email Us</strong>
                                <p><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></p>
                            </div>
                        </li>
                        <li>
                            <span class="contact-points__icon" aria-hidden="true">
                                <i class="bi bi-clock-fill"></i>
                            </span>
                            <div>
                                <strong>Business Hours</strong>
                                <p>{{ $contact['hours'] }}</p>
                                <p>{{ $contact['hours_sunday'] ?? 'Sunday: Closed' }}</p>
                            </div>
                        </li>
                    </ul>
                </article>

                {{-- Send Us a Message --}}
                <article class="contact-card">
                    <h2 class="font-heading">Send Us a Message</h2>

                    <form class="contact-form" method="post" action="{{ route('contact.store') }}" novalidate>
                        @csrf
                        <div class="contact-form__row">
                            <div>
                                <label class="visually-hidden" for="contact-name">Your Name</label>
                                <input
                                    id="contact-name"
                                    type="text"
                                    name="name"
                                    placeholder="Your Name"
                                    value="{{ old('name') }}"
                                    required
                                    maxlength="100"
                                    autocomplete="name"
                                >
                                @error('name') <p class="contact-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="visually-hidden" for="contact-email">Your Email</label>
                                <input
                                    id="contact-email"
                                    type="email"
                                    name="email"
                                    placeholder="Your Email"
                                    value="{{ old('email') }}"
                                    required
                                    maxlength="150"
                                    autocomplete="email"
                                >
                                @error('email') <p class="contact-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="visually-hidden" for="contact-phone">Your Phone</label>
                            <input
                                id="contact-phone"
                                type="tel"
                                name="phone"
                                placeholder="10-digit mobile number"
                                value="{{ old('phone') }}"
                                maxlength="10"
                                inputmode="numeric"
                                pattern="[6-9][0-9]{9}"
                                autocomplete="tel"
                            >
                            @error('phone') <p class="contact-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="visually-hidden" for="contact-subject">Subject</label>
                            <input
                                id="contact-subject"
                                type="text"
                                name="subject"
                                placeholder="Subject"
                                value="{{ old('subject') }}"
                                maxlength="150"
                            >
                            @error('subject') <p class="contact-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="visually-hidden" for="contact-message">Your Message</label>
                            <textarea
                                id="contact-message"
                                name="message"
                                rows="5"
                                placeholder="Your Message"
                                required
                                maxlength="2000"
                            >{{ old('message') }}</textarea>
                            @error('message') <p class="contact-error">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="contact-submit">
                            <i class="bi bi-send-fill" aria-hidden="true"></i>
                            Send Message
                        </button>
                    </form>
                </article>

                {{-- Find Us --}}
                <article class="contact-card contact-card--map">
                    <h2 class="font-heading">Find Us</h2>

                    <div class="contact-map">
                        <iframe
                            title="Geetanjali Jewellers store location map"
                            src="{{ $contact['map_embed'] }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                        ></iframe>
                    </div>

                    <a
                        class="contact-directions"
                        href="{{ $contact['map_directions'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        Get Directions
                    </a>
                </article>
            </div>
        </div>

        <section class="contact-trust" aria-label="Shopping benefits">
            <div class="contact-trust__inner">
                @foreach ($trustItems as $item)
                    <div class="contact-trust__item">
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                        <div>
                            <strong>{{ $item['title'] }}</strong>
                            <span>{{ $item['subtitle'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
