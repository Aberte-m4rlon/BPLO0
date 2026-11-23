@extends('layouts.app')

@section('title', 'Application Status')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Applicant Portal</p>
            <h1>Application Status</h1>
            <p>
                {{ $application->business_name ?? 'Business Application' }}
                · Tracking Number: <strong>{{ $application->tracking_number ?? '— (draft)' }}</strong>
            </p>
        </div>
        <div class="bplo-header-actions">
            <x-status-badge :status="$application->status" />
            <a href="{{ url('/dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    @if (in_array($application->status, [\App\Models\Application::STATUS_DRAFT, \App\Models\Application::STATUS_FOR_CORRECTION]))
        <div class="alert alert-info">
            This application is editable. <a href="{{ route('applications.edit', $application) }}">Continue editing</a>.
        </div>
    @endif

    @if ($application->status === \App\Models\Application::STATUS_APPROVED_FOR_PAYMENT)
        @php
            $rejected = $application->payments->sortByDesc('id')->first();
            $rejected = $rejected && $rejected->status === 'FAILED' && $rejected->rejection_reason ? $rejected : null;
        @endphp
        @if ($rejected)
            <div class="alert alert-error flex-between">
                <span><strong>Your payment was not accepted.</strong> Reason: {{ $rejected->rejection_reason }}</span>
                <a href="{{ route('applications.pay', $application) }}" class="btn btn-danger">Pay Again</a>
            </div>
        @else
            <div class="alert alert-success flex-between">
                <span>Your application is approved. Please settle the fees to proceed.</span>
                <a href="{{ route('applications.pay', $application) }}" class="btn btn-success">Pay Now</a>
            </div>
        @endif
    @endif

    @if ($application->status === \App\Models\Application::STATUS_PAYMENT_PENDING)
        <div class="alert alert-warning">
            <strong>Payment received, waiting for verification.</strong>
            The BPLO is checking your payment against the GCash, Maya, or QR Ph records. You'll get a notification once it is confirmed.
        </div>
    @endif

    @if ($application->status === \App\Models\Application::STATUS_PAYMENT_VERIFIED)
        <div class="alert alert-success">
            <strong>Payment verified.</strong> The BPLO is preparing your Mayor's Permit. You'll be notified when it is released.
        </div>
    @endif

    @if ($application->status === \App\Models\Application::STATUS_PERMIT_RELEASED && $application->permit)
        <div class="alert alert-success flex-between">
            <span>Your Mayor's Permit has been released.</span>
            <a href="{{ route('applications.permit', $application) }}" class="btn btn-success">View Permit</a>
        </div>
    @endif

    <div class="bplo-detail-grid">
        <div class="card">
            <h2 class="section">Status Timeline</h2>
            @if ($application->statusHistories->isEmpty())
                <x-empty-state title="No status changes recorded yet" message="Application updates and milestone timestamps will appear here once submitted." />
            @else
                <ul class="timeline">
                    @foreach ($application->statusHistories as $history)
                        <li>
                            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:4px">
                                <x-status-badge :status="$history->to_status" />
                                @if ($history->from_status)
                                    <span class="muted">from {{ ucwords(strtolower(str_replace('_', ' ', $history->from_status))) }}</span>
                                @endif
                            </div>
                            @if ($history->note)
                                <div>{{ $history->note }}</div>
                            @endif
                            <div class="meta">{{ $history->created_at?->format('M d, Y g:i A') }} @if($history->actor_role) · {{ ucfirst($history->actor_role) }} @endif</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($application->remarks->isNotEmpty())
            <div class="card">
                <h2 class="section">Remarks</h2>
                <ul class="timeline">
                    @foreach ($application->remarks->sortByDesc('created_at') as $remark)
                        <li>
                            <div style="margin-bottom:4px">
                                <span class="badge {{ $remark->type === 'REJECTION' ? 'badge-red' : 'badge-yellow' }}">{{ ucwords(strtolower(str_replace('_', ' ', $remark->type))) }}</span>
                            </div>
                            <div>{{ $remark->body }}</div>
                            <div class="meta">{{ $remark->created_at?->format('M d, Y g:i A') }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-head">
            <h2 class="section">Payments</h2>
            @if ($application->payments->isNotEmpty())
                <a href="{{ route('applications.payments', $application) }}" class="btn btn-sm btn-secondary">View Full History</a>
            @endif
        </div>
        @if ($application->payments->isEmpty())
            <x-empty-state title="No payments recorded" message="Fee payments will appear here once fees are assessed and submitted." />
        @else
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>Date</th><th>Method</th><th>Amount</th><th>Status</th><th>Verification</th><th>Reference</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($application->payments->sortByDesc('created_at') as $payment)
                            <tr>
                                <td>{{ $payment->created_at?->format('M d, Y g:i A') }}</td>
                                <td>{{ strtoupper($payment->method) }}</td>
                                <td>&#8369;{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $payment->status === 'PAID' ? 'badge-green' : ($payment->status === 'FAILED' ? 'badge-red' : 'badge-yellow') }}">
                                        {{ ucfirst(strtolower($payment->status)) }}
                                    </span>
                                </td>
                                <td>{{ ucwords(strtolower(str_replace('_', ' ', $payment->verification_status))) }}</td>
                                <td>{{ $payment->reference_number ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
