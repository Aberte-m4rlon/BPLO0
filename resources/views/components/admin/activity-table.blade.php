@props([
    'activities',
])

<div class="table-wrap adm-activity-table-wrap">
    <table class="data adm-activity-table">
        <thead>
            <tr>
                <th>WHEN</th>
                <th>ACTOR</th>
                <th>ACTION</th>
                <th>SUBJECT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($activities as $log)
                @php
                    $actorName = $log->actor?->name ?? 'System';
                    $actorInitial = strtoupper(substr($actorName, 0, 1));
                    $actionLabel = strtoupper(str_replace(['.', '_'], ' ', (string) $log->action));
                @endphp
                <tr>
                    <td class="adm-activity-when">{{ $log->created_at?->format('M d, Y g:i A') }}</td>
                    <td>
                        <span class="adm-actor-cell">
                            <span class="adm-actor-dot" aria-hidden="true">{{ $actorInitial }}</span>
                            <span>{{ $actorName }}</span>
                        </span>
                    </td>
                    <td>
                        <span class="badge adm-action-badge">{{ $actionLabel }}</span>
                    </td>
                    <td class="adm-activity-subject">{{ trim(class_basename($log->subject_type ?? '').' '.$log->subject_id) ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
