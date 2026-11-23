@props([
    'count' => 0,
    'items' => [],
    'unreadDbCount' => 0,
])

@php
    $u = auth()->user();
    $home = $u ? $u->homePath() : '/dashboard';
    $displayCount = $count > 99 ? '99+' : (string) $count;
@endphp

<div class="notif-bell-wrap" data-notif-wrap>
    <button type="button"
            class="bell notif-bell {{ $count > 0 ? 'has-alerts' : '' }}"
            data-notif-toggle
            title="{{ $count > 0 ? $count.' notifications' : 'Notifications' }}"
            aria-label="{{ $count > 0 ? 'Notifications ('.$count.' unread)' : 'Notifications' }}"
            aria-haspopup="true"
            aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.95" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9 M13.73 21a2 2 0 0 1-3.46 0" />
        </svg>
        @if ($count > 0)
            <span class="count notif-count">{{ $displayCount }}</span>
            <span class="notif-pulse-ring" aria-hidden="true"></span>
        @endif
    </button>

    <div class="notif-popover no-print" data-notif-popover hidden role="region" aria-label="Notifications list">
        <div class="notif-popover-head">
            <div class="notif-popover-title">
                <strong>Notifications</strong>
                @if ($count > 0)
                    <span class="notif-head-pill">{{ $displayCount }}</span>
                @endif
            </div>
            @if ($unreadDbCount > 0)
                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit" class="notif-mark-btn">Mark read</button>
                </form>
            @endif
        </div>

        <div class="notif-popover-body">
            @forelse ($items as $item)
                <a href="{{ $item['url'] }}" class="notif-item {{ ! empty($item['unread']) ? 'is-unread' : '' }}">
                    <span class="notif-item-dot tone-{{ $item['tone'] ?? 'info' }}" aria-hidden="true"></span>
                    <span class="notif-item-copy">
                        <strong>{{ $item['title'] }}</strong>
                        @if (! empty($item['body']))
                            <small>{{ $item['body'] }}</small>
                        @endif
                    </span>
                    <span class="notif-item-time">{{ $item['time'] ?? '' }}</span>
                </a>
            @empty
                <div class="notif-empty">
                    <strong>All caught up</strong>
                    <small>No pending alerts or unread notifications.</small>
                </div>
            @endforelse
        </div>

        <div class="notif-popover-foot">
            <a href="{{ url($home) }}">Open Workspace →</a>
        </div>
    </div>
</div>
