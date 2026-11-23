@props([
    'value' => 0,
    'label' => '',
    'icon' => 'users',
    'tone' => 'blue', // blue | purple | green | amber | teal
    'trend' => '+0%',
])

@php
    $iconPath = match ($icon) {
        'users' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M22 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75',
        'file-text' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8',
        'clipboard-check' => 'M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2 M9 2h6a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z M9 14l2 2 4-4',
        'shield-check' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z M9 12l2 2 4-4',
        'credit-card' => 'M3 6h18v12H3z M3 10h18 M7 14.5h3',
        'wallet' => 'M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1 M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4',
        'trending-up' => 'M22 7 13.5 15.5 8.5 10.5 2 17 M16 7h6v6',
        'clock' => 'M12 6v6l4 2 M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0z',
        'check-circle' => 'M22 11.08V12a10 10 0 1 1-5.93-9.14 M22 4 12 14.01l-3-3',
        'alert-triangle' => 'M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z M12 9v4 M12 17h.01',
        default => 'M20 6 9 17l-5-5',
    };
@endphp

<div class="stat-card adm-stat-card tone-{{ $tone }}">
    <span class="adm-stat-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.95" stroke-linecap="round" stroke-linejoin="round">
            <path d="{{ $iconPath }}" />
        </svg>
    </span>

    <div class="adm-stat-content">
        <div class="adm-stat-value">{{ $value }}</div>
        <div class="adm-stat-label">{{ $label }}</div>
    </div>

    <span class="adm-stat-trend" aria-label="Change {{ $trend }}">
        <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 19V5 M5 12l7-7 7 7" />
        </svg>
        {{ $trend }}
    </span>
</div>
