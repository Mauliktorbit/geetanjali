@extends('admin.layouts.app')

@section('title', $definition['title'])

@section('content')
<div class="page-header">
    <div>
        <h1>{{ $definition['title'] }}</h1>
        <p class="subtitle">Edit the content shown on the website {{ strtolower($definition['title']) }} page.</p>
        @include('admin.components.breadcrumbs', ['items' => [['label' => $definition['title']]]])
    </div>
    <div class="page-actions">
        <a href="{{ $previewUrl }}" class="btn btn-ghost" target="_blank" rel="noopener">View on website</a>
    </div>
</div>

@include('admin.components.alerts')

<div class="card product-form-card">
    <form method="POST" action="{{ route('admin.policies.update', $policy) }}" class="product-form policy-form">
        @csrf
        @method('PUT')

        <section class="form-section">
            <div class="form-section__head">
                <h3>Page content</h3>
                <p>This heading, intro and sections appear on the storefront page.</p>
            </div>

            <div class="form-grid">
                <div class="form-group full">
                    <label for="policy-title">Page title *</label>
                    <input
                        id="policy-title"
                        type="text"
                        name="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $payload['title']) }}"
                        required
                        maxlength="160"
                    >
                    @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group full">
                    <label for="policy-intro">Intro *</label>
                    <textarea
                        id="policy-intro"
                        name="intro"
                        class="form-control @error('intro') is-invalid @enderror"
                        rows="3"
                        required
                        maxlength="2000"
                    >{{ old('intro', $payload['intro']) }}</textarea>
                    <span class="form-hint">Short paragraph under the page title.</span>
                    @error('intro')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </section>

        <section class="form-section">
            <div class="form-section__head">
                <h3>Sections</h3>
                <p>Add headings and details. Empty rows are ignored.</p>
            </div>
            @error('sections')<p class="invalid-feedback" style="margin-bottom: 0.75rem;">{{ $message }}</p>@enderror

            @php
                $oldSections = old('sections');
                $sections = is_array($oldSections) ? $oldSections : $payload['sections'];
                while (count($sections) < $sectionRows) {
                    $sections[] = ['heading' => '', 'body' => ''];
                }
            @endphp

            <div class="policy-sections">
                @foreach ($sections as $index => $section)
                    <div class="policy-section-row">
                        <div class="form-group">
                            <label for="policy-heading-{{ $index }}">Section {{ $index + 1 }} heading</label>
                            <input
                                id="policy-heading-{{ $index }}"
                                type="text"
                                name="sections[{{ $index }}][heading]"
                                class="form-control"
                                value="{{ $section['heading'] ?? '' }}"
                                maxlength="160"
                            >
                        </div>
                        <div class="form-group">
                            <label for="policy-body-{{ $index }}">Section {{ $index + 1 }} text</label>
                            <textarea
                                id="policy-body-{{ $index }}"
                                name="sections[{{ $index }}][body]"
                                class="form-control"
                                rows="4"
                            >{{ $section['body'] ?? '' }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="form-section">
            <div class="form-section__head">
                <h3>SEO &amp; visibility</h3>
                <p>Optional search listing text. Leave blank to use the page title and intro.</p>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="policy-seo-title">SEO title</label>
                    <input
                        id="policy-seo-title"
                        type="text"
                        name="seo_title"
                        class="form-control"
                        value="{{ old('seo_title', $payload['seo_title']) }}"
                        maxlength="180"
                    >
                </div>
                <div class="form-group full">
                    <label for="policy-seo-description">SEO description</label>
                    <textarea
                        id="policy-seo-description"
                        name="seo_description"
                        class="form-control"
                        rows="3"
                        maxlength="320"
                    >{{ old('seo_description', $payload['seo_description']) }}</textarea>
                </div>
                <div class="form-group form-check">
                    <label>
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $payload['is_active']) ? 'checked' : '' }}>
                        Show this page on the website
                    </label>
                </div>
            </div>
        </section>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save {{ $definition['title'] }}</button>
        </div>
    </form>
</div>
@endsection
