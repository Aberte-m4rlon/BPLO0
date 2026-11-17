@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
    <div class="page-header bplo-page-header bplo-dashboard-hero">
        <div class="bplo-hero-copy">
            <span class="bplo-hero-pill">APPLICANT PORTAL</span>
            <h1>Good day, <span class="bplo-hero-highlight">{{ auth()->user()->name }}</span>!</h1>
            <p>Submit business permit applications, upload requirements, and track approvals.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ url('/applications/create') }}" class="btn bplo-hero-secondary">New Application</a>
            <a href="{{ url('/payments') }}" class="btn bplo-hero-primary">Payment History</a>
        </div>
    </div>

    <div class="card">
        <h2 class="section">Applications</h2>
        @if ($applications->isEmpty())
            <x-empty-state icon="📝" title="No applications yet"
                message="Start your first business permit application — it only takes a few minutes.">
                <a href="{{ url('/applications/create') }}" class="btn">New Application</a>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Tracking No.</th>
                            <th>Business</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($applications as $application)
                            <tr>
                                <td>{{ $application->tracking_number ?? '—' }}</td>
                                <td>{{ $application->business_name }}</td>
                                <td><x-status-badge :status="$application->status" /></td>
                                <td>{{ $application->updated_at?->format('M d, Y g:i A') }}</td>
                                <td>
                                    @if (in_array($application->status, [\App\Models\Application::STATUS_DRAFT, \App\Models\Application::STATUS_FOR_CORRECTION]))
                                        <a href="{{ route('applications.edit', $application) }}" class="btn btn-sm btn-secondary">Edit</a>
                                    @endif
                                    <a href="{{ route('applications.show', $application) }}" class="btn btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card">
        <h2 class="section">Notifications</h2>
        @if ($notifications->isEmpty())
            <x-empty-state icon="🔔" title="No notifications"
                message="Status updates about your applications will show up here." />
        @else
            <ul class="timeline">
                @foreach ($notifications as $notification)
                    <li>
                        <strong>{{ $notification->title }}</strong>
                        <div>{{ $notification->body }}</div>
                        <div class="meta">{{ $notification->created_at?->format('M d, Y g:i A') }}</div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
