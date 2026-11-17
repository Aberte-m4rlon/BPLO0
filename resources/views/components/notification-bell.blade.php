@props(['count' => 0])

@php
    $u = auth()->user();
    $home = $u ? $u->homePath() : '/dashboard';
@endphp

<a href="{{ url($home) }}" class="bell" title="Notifications" aria-label="Notifications">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9 M13.73 21a2 2 0 0 1-3.46 0" />
    </svg>
    @if ($count > 0)
        <span class="count">{{ $count }}</span>
    @endif
</a>
