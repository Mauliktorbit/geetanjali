@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
])

@php
    $inputId = $attributes->get('id', $name);
    $hasError = $errors->has($name);
@endphp

<div class="mb-3">
    @if ($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->class(['form-control', 'is-invalid' => $hasError])->merge([]) }}
    >

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
