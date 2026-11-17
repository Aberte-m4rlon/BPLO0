@props([
    'value' => 0,
    'label' => '',
    'icon' => null,
    'accent' => 'default', // default | green | amber | red | mint
])

@php
    $iconPath = match ($icon ?? $accent) {
        'document', '📋', '📄', '📑' => 'M6 3.75h8.5L19 8.25v12H6z M14 4v4.5h4.5 M9 12h6 M9 15.5h6',
        'clock', '⏳' => 'M12 3.5a8.5 8.5 0 1 0 0 17 8.5 8.5 0 0 0 0-17z M12 7v5l3.5 2',
        'review', '🔍' => 'M7 4.5h10v15H7z M9.5 9h5 M9.5 12.5h5 M9.5 16h3 M4.5 7.5v12h10',
        'correction' => 'M5 7.5h12.5l-3-3 M19 16.5H6.5l3 3 M17.5 7.5A7 7 0 0 1 19 16.5',
        'payment', '💰' => 'M3.5 6.5h17v12h-17z M3.5 10h17 M7 14.5h4',
        'release' => 'M4 12h15 M13 6l6 6-6 6 M6 5.5v13',
        'permit' => 'M6 3.75h12v16.5H6z M9 8h6 M9 12h6 M9 16h3',
        'users', '👥' => 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M23 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75',
        'green', '✅' => 'M5 12.5l4 4L19 7',
        'amber', '⚠️' => 'M12 4l9 16H3L12 4z M12 9v5 M12 17h.01',
        'red', '⛔' => 'M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16z M12 8v5 M12 16h.01',
        default => 'M5 12.5l4 4L19 7',
    };
@endphp

<div class="stat-card accent-{{ $accent }}">
    <span class="stat-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="{{ $iconPath }}" />
        </svg>
    </span>
    <div class="stat-body">
        <div class="value">{!! $value !!}</div>
        <div class="label">{{ $label }}</div>
    </div>
</div>
