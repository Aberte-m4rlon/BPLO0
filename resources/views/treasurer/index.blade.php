@extends('layouts.app')

@section('title', 'Payments')

@section('content')
    <div class="page-header adm-panel">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Municipal Treasurer's Office · {{ config('permit.municipality') }}</p>
            <h1>Payments Queue</h1>
            <p>Verify payment receipts and confirm municipal collections for approved permit applications.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('treasurer.payments.export', request()->only('q', 'status')) }}" class="btn btn-sm adm-pill-btn">Export CSV ↓</a>
            <a href="{{ route('treasurer.analytics') }}" class="btn btn-sm btn-secondary">Analytics</a>
            <a href="{{ url('/treasurer/dashboard') }}" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>

    @isset($summary)
        <div class="trs-summary-bar" aria-label="Collection queue totals">
            <div class="trs-summary-chip">
                <small>Verified Collected</small>
                <strong>&#8369;{{ number_format((float) $summary['collected_total'], 2) }} <small>({{ $summary['paid_count'] }} paid)</small></strong>
            </div>
            <div class="trs-summary-chip">
                <small>Awaiting Verification</small>
                <strong>&#8369;{{ number_format((float) $summary['awaiting_total'], 2) }} <small>({{ $summary['awaiting_count'] }} queued)</small></strong>
            </div>
            <div class="trs-summary-chip">
                <small>Rejected / Failed</small>
                <strong>{{ $summary['failed_count'] }} {{ \Illuminate\Support\Str::plural('record', $summary['failed_count']) }}</strong>
            </div>
        </div>
    @endisset

    <div class="card adm-panel">
        <div class="bplo-card-intro">
            <div>
                <h2 class="section">Submitted Payments</h2>
                <p class="muted mb-0">Filter submissions by tracking number, reference number, or payment status.</p>
            </div>
            <span class="bplo-result-count">{{ $payments->total() }} {{ \Illuminate\Support\Str::plural('payment', $payments->total()) }}</span>
        </div>

        <form method="GET" action="{{ url('/treasurer/payments') }}" class="toolbar">
            <div class="form-group">
                <label for="q">Search</label>
                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Tracking / reference no.">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(strtolower(str_replace('_', ' ', $status))) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group bplo-filter-actions">
                <button type="submit" class="btn">Apply</button>
                <a href="{{ url('/treasurer/payments') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-wrap adm-activity-table-wrap">
            <table class="data adm-activity-table">
                <thead>
                    <tr><th>Tracking No.</th><th>Business</th><th>Method</th><th>Reference No.</th><th class="text-right">Amount</th><th>Status</th><th>Submitted</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->tracking_number }}</td>
                            <td>{{ $payment->application?->business_name ?? '—' }}</td>
                            <td>{{ \App\Support\PaymentMethods::label($payment->method) }}</td>
                            <td style="font-family:monospace">{{ $payment->reference_number ?? '—' }}</td>
                            <td class="text-right">&#8369;{{ number_format((float) $payment->amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $payment->status === 'PAID' ? 'badge-green' : ($payment->status === 'FAILED' ? 'badge-red' : 'badge-yellow') }}">
                                    {{ ucfirst(strtolower($payment->status)) }}
                                </span>
                            </td>
                            <td>{{ $payment->created_at?->format('M d, Y g:i A') }}</td>
                            <td><a href="{{ route('treasurer.payments.show', $payment) }}" class="btn btn-sm">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="muted">No payments match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="adm-activity-mobile-list" aria-label="Submitted payments cards">
            @forelse ($payments as $payment)
                <article class="adm-activity-mobile-card">
                    <div class="adm-activity-mobile-top">
                        <strong class="adm-actor-name">{{ $payment->application?->business_name ?? $payment->tracking_number }}</strong>
                        <span class="badge {{ $payment->status === 'PAID' ? 'badge-green' : ($payment->status === 'FAILED' ? 'badge-red' : 'badge-yellow') }}">
                            {{ ucfirst(strtolower($payment->status)) }}
                        </span>
                    </div>
                    <div class="adm-activity-mobile-meta">
                        <span>{{ $payment->tracking_number }} · {{ \App\Support\PaymentMethods::label($payment->method) }}</span>
                        <strong>&#8369;{{ number_format((float) $payment->amount, 2) }}</strong>
                    </div>
                    <div class="adm-activity-mobile-meta">
                        <span>{{ $payment->created_at?->format('M d, Y g:i A') }}</span>
                        <a href="{{ route('treasurer.payments.show', $payment) }}" class="btn btn-sm">View</a>
                    </div>
                </article>
            @empty
                <p class="muted mb-0">No payments match your filters.</p>
            @endforelse
        </div>

        <x-pagination :paginator="$payments" />
    </div>
@endsection
