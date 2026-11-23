@props([
    'log',
])

@php
    $actorName = $log->actor?->name ?? 'System';
    $actorInitial = strtoupper(substr($actorName, 0, 1));
    $actionLabel = strtoupper(str_replace(['.', '_'], ' ', (string) $log->action));
    $subjectLabel = trim(class_basename($log->subject_type ?? '').' '.$log->subject_id) ?: '—';
@endphp

<article class="adm-activity-mobile-card">
    <div class="adm-activity-mobile-top">
        <span class="adm-actor-cell">
            <span class="adm-actor-dot" aria-hidden="true">{{ $actorInitial }}</span>
            <span class="adm-actor-name">{{ $actorName }}</span>
        </span>
        <span class="badge adm-action-badge">{{ $actionLabel }}</span>
    </div>
    <div class="adm-activity-mobile-meta">
        <span class="adm-activity-mobile-subject">{{ $subjectLabel }}</span>
        <time class="adm-activity-mobile-time">{{ $log->created_at?->format('M d, Y g:i A') }}</time>
    </div>
</article>
