@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">System Administration</p>
            <h1>Audit Logs</h1>
            <p>Review operational events, actor history, and system changes across all BPLO portals.</p>
        </div>
    </div>

    <div class="card">
        <div class="bplo-card-intro">
            <div>
                <h2 class="section">Activity Trail</h2>
                <p class="muted mb-0">Filter recorded actions by event type, actor ID, or calendar date.</p>
            </div>
            <span class="bplo-result-count">{{ $logs->total() }} {{ \Illuminate\Support\Str::plural('entry', $logs->total()) }}</span>
        </div>

        <form method="GET" action="{{ url('/admin/audit-logs') }}" class="toolbar">
            <div class="form-group">
                <label for="action">Action</label>
                <select id="action" name="action">
                    <option value="">All Actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucwords(strtolower(str_replace(['.', '_'], ' ', $action))) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="actor">Actor ID</label>
                <input type="number" id="actor" name="actor" value="{{ request('actor') }}" placeholder="e.g. 1">
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="{{ request('date') }}">
            </div>
            <div class="form-group bplo-filter-actions">
                <button type="submit" class="btn">Filter</button>
                <a href="{{ url('/admin/audit-logs') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        @if ($logs->isEmpty())
            <x-empty-state title="No matching log entries" message="Try clearing one of your filters or selecting another date." />
        @else
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>When</th><th>Actor</th><th>Action</th><th>Subject</th><th>IP Address</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>{{ $log->created_at?->format('M d, Y g:i A') }}</td>
                                <td>{{ $log->actor?->name ?? 'System' }}</td>
                                <td><span class="badge badge-blue">{{ ucwords(strtolower(str_replace(['.', '_'], ' ', $log->action))) }}</span></td>
                                <td>{{ class_basename($log->subject_type ?? '') }} {{ $log->subject_id }}</td>
                                <td>{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$logs" />
        @endif
    </div>
@endsection
