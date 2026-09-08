@props([
    'align' => 'left',
])

<div class="gold-divider {{ $align === 'center' ? 'gold-divider--center' : '' }}" aria-hidden="true">
    <span class="gold-divider__line"></span>
    <span class="gold-divider__ornament">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2.5l1.2 3.8 3.9.1-3.1 2.4 1.1 3.8L12 10.8 8.9 12.6l1.1-3.8-3.1-2.4 3.9-.1L12 2.5z"/>
            <circle cx="12" cy="12" r="2.2" fill="none" stroke="currentColor" stroke-width="1.2"/>
            <path d="M12 15.2l.8 2.4 2.5.1-2 1.5.7 2.4-2-1.5-2 1.5.7-2.4-2-1.5 2.5-.1.8-2.4z" opacity=".85"/>
        </svg>
    </span>
    <span class="gold-divider__line"></span>
</div>
