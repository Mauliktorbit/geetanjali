@php
    $item = $item ?? null;
    $categories = $categories ?? \App\Models\OfferCategory::query()->ordered()->get();
    $category = old('category', $item?->category ?: ($categories->first()?->slug ?? '__new__'));
    $showNewCategory = $category === '__new__' || $categories->isEmpty();
    $label = old('label', $item?->label ?? 'Flat');
    $theme = old('theme', $item?->theme ?? 'dark');
    $starts = old('starts_at', $item?->starts_at?->format('d/m/Y') ?: now()->format('d/m/Y'));
    $ends = old('ends_at', $item?->ends_at?->format('d/m/Y'));
    $currentPhotoUrl = (! old('remove_image') && $item?->image) ? storefront_image($item->image) : null;
@endphp

<div class="product-form offer-form-layout">
    <div class="offer-form-main">
    <section class="form-section">
        <div class="form-section__head">
            <h3>What customers see</h3>
            <p>These details appear on the Offers page card.</p>
        </div>

        <div class="form-grid">
            <div class="form-group full">
                <label class="field-label">Offer label *</label>
                <div class="choice-list">
                    @foreach (\App\Models\Offer::cardLabels() as $option)
                        <label class="choice-chip">
                            <input type="radio" name="label" value="{{ $option }}" @checked($label === $option) required>
                            {{ $option }}
                        </label>
                    @endforeach
                </div>
                <span class="form-hint">Shown above the discount, for example FLAT 50% OFF.</span>
                @error('label')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="offer-discount">Discount *</label>
                <input
                    id="offer-discount"
                    type="text"
                    name="discount_display"
                    class="form-control @error('discount_display') is-invalid @enderror"
                    value="{{ old('discount_display', $item?->discount_display ?? '') }}"
                    required
                    maxlength="20"
                    placeholder="50%"
                >
                <span class="form-hint">Example: 50% or ₹500.</span>
                @error('discount_display')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="offer-suffix">Discount word *</label>
                <input
                    id="offer-suffix"
                    type="text"
                    name="discount_suffix"
                    class="form-control @error('discount_suffix') is-invalid @enderror"
                    value="{{ old('discount_suffix', $item?->discount_suffix ?? 'Off') }}"
                    required
                    maxlength="20"
                    placeholder="Off"
                >
                <span class="form-hint">Usually “Off”.</span>
                @error('discount_suffix')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group full">
                <label for="offer-title">Headline *</label>
                <input
                    id="offer-title"
                    type="text"
                    name="title"
                    class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title', $item ? str_replace(["\r\n", "\n"], ' ', $item->title) : '') }}"
                    required
                    maxlength="120"
                    placeholder="Rakhi Special"
                >
                <span class="form-hint">Short name on the card, for example Rakhi Special.</span>
                @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="offer-min">Minimum order (₹)</label>
                <input
                    id="offer-min"
                    type="number"
                    name="minimum_order"
                    class="form-control @error('minimum_order') is-invalid @enderror"
                    value="{{ old('minimum_order', $item?->minimum_order ?? '') }}"
                    min="0"
                    step="1"
                    placeholder="5000"
                >
                <span class="form-hint">Leave blank if there is no minimum. Shown as “Min. order ₹5,000”.</span>
                @error('minimum_order')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="field-label">Card style *</label>
                <div class="choice-list">
                    @foreach (\App\Models\Offer::themes() as $value => $themeLabel)
                        <label class="choice-chip">
                            <input type="radio" name="theme" value="{{ $value }}" @checked($theme === $value) required>
                            {{ $themeLabel }}
                        </label>
                    @endforeach
                </div>
                @error('theme')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </section>

    <section class="form-section">
        <div class="form-section__head">
            <h3>Photo</h3>
            <p>Used on the right side of the offer card.</p>
        </div>

        <div class="form-group" data-offer-photo-field>
            <label for="offer-image">Offer photo {{ $item ? '' : '*' }}</label>
            <input
                id="offer-image"
                type="file"
                name="image"
                class="form-control @error('image') is-invalid @enderror"
                accept="image/jpeg,image/png,image/webp"
                data-no-preview
                @if (! $item) required @endif
            >
            <span class="form-hint">JPG, PNG or WebP, up to 4 MB. A new file replaces the current photo at once.</span>
            <input type="hidden" name="remove_image" id="offer-remove-image" value="{{ old('remove_image') ? '1' : '0' }}">
            <div class="current-upload mt-2" data-offer-photo-preview @if (! $currentPhotoUrl) hidden @endif>
                <img
                    src="{{ $currentPhotoUrl }}"
                    alt="{{ $item?->title ?? 'Offer photo' }}"
                    class="thumb-lg"
                    data-offer-photo-img
                >
                <button type="button" class="btn btn-ghost btn-sm" data-remove-offer-image>
                    Remove photo
                </button>
            </div>
            @error('image')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </section>

    <section class="form-section">
        <div class="form-section__head">
            <h3>Listing</h3>
            <p>Choose where and when this offer should appear.</p>
        </div>

        <div class="form-grid">
            <div class="form-group full">
                <label for="offer-category">Category *</label>
                <select id="offer-category" name="category" class="form-control @error('category') is-invalid @enderror" required>
                    @forelse ($categories as $option)
                        <option value="{{ $option->slug }}" @selected($category === $option->slug)>{{ $option->name }}</option>
                    @empty
                    @endforelse
                    <option value="__new__" @selected($showNewCategory)>Add new category</option>
                </select>
                <div id="offer-new-category-wrap" class="mt-2" @if (! $showNewCategory) hidden @endif>
                    <label for="offer-new-category" class="form-hint">New category name</label>
                    <input
                        id="offer-new-category"
                        type="text"
                        name="new_category"
                        class="form-control @error('new_category') is-invalid @enderror"
                        value="{{ old('new_category') }}"
                        maxlength="80"
                        placeholder="Wedding Offers"
                        autocomplete="off"
                        @if ($showNewCategory) required @endif
                    >
                </div>
                @error('category')<span class="invalid-feedback">{{ $message }}</span>@enderror
                @error('new_category')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="offer-starts">Starts</label>
                <input
                    id="offer-starts"
                    type="text"
                    name="starts_at"
                    class="form-control @error('starts_at') is-invalid @enderror"
                    value="{{ $starts }}"
                    inputmode="numeric"
                    maxlength="10"
                    placeholder="DD/MM/YYYY"
                    data-input-kind="datedmy"
                    autocomplete="off"
                >
                <span class="form-hint">Example: 17/09/2026</span>
                @error('starts_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="offer-ends">Valid till</label>
                <input
                    id="offer-ends"
                    type="text"
                    name="ends_at"
                    class="form-control @error('ends_at') is-invalid @enderror"
                    value="{{ $ends }}"
                    inputmode="numeric"
                    maxlength="10"
                    placeholder="DD/MM/YYYY"
                    data-input-kind="datedmy"
                    autocomplete="off"
                >
                <span class="form-hint">Example: 26/12/2026. Leave blank for no end date.</span>
                @error('ends_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group form-check full">
                <label>
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item?->is_active ?? true))>
                    Show on the website
                </label>
            </div>
        </div>
    </section>
    </div>

    <aside class="offer-form-preview" aria-label="Offer card preview">
        <div class="form-section__head">
            <h3>Card preview</h3>
            <p>This is how the offer will look on the website.</p>
        </div>
        <article class="offer-preview-card offer-preview-card--{{ $theme }}" data-offer-preview-card>
            <div class="offer-preview-card__body">
                <div class="offer-preview-card__copy">
                    <span class="offer-preview-card__label" data-offer-preview-label>{{ strtoupper($label) }}</span>
                    <div class="offer-preview-card__discount">
                        <span class="offer-preview-card__value" data-offer-preview-value>{{ old('discount_display', $item?->discount_display ?? '50%') }}</span>
                        <span class="offer-preview-card__suffix" data-offer-preview-suffix>{{ old('discount_suffix', $item?->discount_suffix ?? 'Off') }}</span>
                    </div>
                    <p class="offer-preview-card__title" data-offer-preview-title>{{ old('title', $item ? str_replace(["\r\n", "\n"], ' ', $item->title) : 'Rakhi Special') }}</p>
                    <p class="offer-preview-card__min" data-offer-preview-min @if (! old('minimum_order', $item?->minimum_order ?? null)) hidden @endif>
                        @if (old('minimum_order', $item?->minimum_order ?? null))
                            Min. order {{ money(old('minimum_order', $item?->minimum_order)) }}
                        @endif
                    </p>
                </div>
                <div class="offer-preview-card__media" data-offer-preview-media @if (! $currentPhotoUrl) hidden @endif>
                    <img src="{{ $currentPhotoUrl }}" alt="" data-offer-preview-img>
                </div>
            </div>
            <div class="offer-preview-card__footer">
                <span data-offer-preview-until>Valid Till: {{ $item?->validUntilLabel() ?: 'Limited period' }}</span>
                <span>T&amp;C Apply</span>
            </div>
        </article>
    </aside>
</div>

<script>
(function () {
    var select = document.getElementById('offer-category');
    var wrap = document.getElementById('offer-new-category-wrap');
    var input = document.getElementById('offer-new-category');
    if (select && wrap && input) {
        function toggle() {
            var isNew = select.value === '__new__';
            wrap.hidden = !isNew;
            input.required = isNew;
            if (isNew) {
                input.focus();
            }
        }
        select.addEventListener('change', toggle);
    }

    var fileInput = document.getElementById('offer-image');
    var removeInput = document.getElementById('offer-remove-image');
    var preview = document.querySelector('[data-offer-photo-preview]');
    var previewImg = document.querySelector('[data-offer-photo-img]');
    var removeBtn = document.querySelector('[data-remove-offer-image]');
    var cardImg = document.querySelector('[data-offer-preview-img]');
    var cardMedia = document.querySelector('[data-offer-preview-media]');
    var originalSrc = previewImg && previewImg.getAttribute('src') ? previewImg.getAttribute('src') : '';
    var objectUrl = '';

    function clearObjectUrl() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = '';
        }
    }

    function setCardImage(src) {
        if (!cardImg || !cardMedia) {
            return;
        }
        if (src) {
            cardImg.src = src;
            cardMedia.hidden = false;
        } else {
            cardImg.removeAttribute('src');
            cardMedia.hidden = true;
        }
    }

    function showPreview(src) {
        if (!src) {
            return;
        }
        if (preview && previewImg) {
            previewImg.src = src;
            preview.hidden = false;
        }
        setCardImage(src);
    }

    function hidePreview() {
        clearObjectUrl();
        if (preview) {
            preview.hidden = true;
        }
        if (previewImg) {
            previewImg.removeAttribute('src');
        }
        if (fileInput) {
            fileInput.value = '';
        }
        if (removeInput) {
            removeInput.value = originalSrc ? '1' : '0';
        }
        setCardImage('');
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', hidePreview);
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files[0];
            if (!file || !file.type || file.type.indexOf('image/') !== 0) {
                return;
            }
            clearObjectUrl();
            objectUrl = URL.createObjectURL(file);
            if (removeInput) {
                removeInput.value = '0';
            }
            showPreview(objectUrl);
        });
    }

    var previewCard = document.querySelector('[data-offer-preview-card]');
    var previewLabel = document.querySelector('[data-offer-preview-label]');
    var previewValue = document.querySelector('[data-offer-preview-value]');
    var previewSuffix = document.querySelector('[data-offer-preview-suffix]');
    var previewTitle = document.querySelector('[data-offer-preview-title]');
    var previewMin = document.querySelector('[data-offer-preview-min]');
    var previewUntil = document.querySelector('[data-offer-preview-until]');
    var discountInput = document.getElementById('offer-discount');
    var suffixInput = document.getElementById('offer-suffix');
    var titleInput = document.getElementById('offer-title');
    var minInput = document.getElementById('offer-min');
    var endsInput = document.getElementById('offer-ends');
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    function formatUntil(value) {
        var parts = String(value || '').split('/');
        if (parts.length !== 3) {
            return 'Limited period';
        }
        var day = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10) - 1;
        var year = parseInt(parts[2], 10);
        if (!day || month < 0 || month > 11 || !year) {
            return 'Limited period';
        }
        return String(day).padStart(2, '0') + ' ' + months[month] + ' ' + year;
    }

    function formatMin(value) {
        var amount = parseFloat(value);
        if (!amount || amount <= 0) {
            return '';
        }
        return 'Min. order ₹' + amount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function syncPreview() {
        var selectedLabel = document.querySelector('input[name="label"]:checked');
        var selectedTheme = document.querySelector('input[name="theme"]:checked');
        if (previewLabel && selectedLabel) {
            previewLabel.textContent = selectedLabel.value;
        }
        if (previewValue && discountInput) {
            previewValue.textContent = discountInput.value.trim() || '50%';
        }
        if (previewSuffix && suffixInput) {
            previewSuffix.textContent = suffixInput.value.trim() || 'Off';
        }
        if (previewTitle && titleInput) {
            previewTitle.textContent = titleInput.value.trim() || 'Rakhi Special';
        }
        if (previewMin && minInput) {
            var minText = formatMin(minInput.value);
            previewMin.textContent = minText;
            previewMin.hidden = !minText;
        }
        if (previewUntil && endsInput) {
            previewUntil.textContent = 'Valid Till: ' + formatUntil(endsInput.value);
        }
        if (previewCard && selectedTheme) {
            previewCard.classList.remove('offer-preview-card--dark', 'offer-preview-card--light');
            previewCard.classList.add('offer-preview-card--' + selectedTheme.value);
        }
    }

    document.querySelectorAll('input[name="label"], input[name="theme"]').forEach(function (input) {
        input.addEventListener('change', syncPreview);
    });
    [discountInput, suffixInput, titleInput, minInput, endsInput].forEach(function (input) {
        if (input) {
            input.addEventListener('input', syncPreview);
        }
    });
    syncPreview();
})();
</script>
