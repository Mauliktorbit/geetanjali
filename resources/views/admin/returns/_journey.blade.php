@php
    $journey = $item->journey(false);
@endphp
<div class="return-banner return-banner--{{ $journey['tone'] }}">
    <strong>{{ $journey['title'] }}</strong>
    <p>{{ $journey['text'] }}</p>
</div>

<div class="return-journey" role="list">
    @foreach ($journey['steps'] as $step)
        <article class="return-journey__step is-{{ $step['state'] }}" role="listitem">
            <span class="return-journey__dot" aria-hidden="true">
                @if ($step['state'] === 'done')
                    <svg viewBox="0 0 24 24"><path d="M5 12.5 9.5 17 19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @elseif ($step['state'] === 'failed')
                    <svg viewBox="0 0 24 24"><path d="M7 7l10 10M17 7 7 17" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
                @else
                    <em>{{ $loop->iteration }}</em>
                @endif
            </span>
            <h3>{{ $step['title'] }}</h3>
            <p>{{ $step['text'] }}</p>
            <time>
                @if ($step['date'])
                    {{ $step['date']->format('d M Y') }}
                    <span>{{ $step['date']->format('h:i A') }}</span>
                @elseif ($step['state'] === 'current')
                    In progress
                @elseif ($step['state'] === 'upcoming')
                    Waiting
                @else
                    —
                @endif
            </time>
        </article>
    @endforeach
</div>
