@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Applicant Portal</p>
            <h1>Payment History</h1>
            <p>
                {{ $application->business_name }}
                @if ($application->tracking_number)
                    · Tracking <strong>{{ $application->tracking_number }}</strong>
                @endif
            </p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('applications.show', $application) }}" class="btn btn-secondary">Back to Status</a>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2 class="section">Submitted Payments</h2>
            <span class="bplo-result-count">{{ $application->payments->count() }} {{ \Illuminate\Support\Str::plural('record', $application->payments->count()) }}</span>
        </div>
        @if ($application->payments->isEmpty())
            <x-empty-state title="No payments recorded" message="Once you submit a payment for assessed fees, your receipt and verification logs will appear here." />
        @else
            <div class="bplo-subcard-list">
                @foreach ($application->payments as $payment)
                    <div class="bplo-subcard">
                        <div class="bplo-subcard-head">
                            <h3>&#8369;{{ number_format((float) $payment->amount, 2) }} — {{ \App\Support\PaymentMethods::label($payment->method) }}</h3>
                            <span class="badge {{ $payment->status === 'PAID' ? 'badge-green' : ($payment->status === 'FAILED' ? 'badge-red' : 'badge-yellow') }}">{{ ucfirst(strtolower($payment->status)) }}</span>
                        </div>
                        <dl class="kv">
                            <dt>Reference</dt><dd>{{ $payment->reference_number ?? '—' }}</dd>
                            <dt>Verification</dt><dd>{{ ucwords(strtolower(str_replace('_', ' ', $payment->verification_status))) }}</dd>
                            <dt>Payment Date</dt><dd>{{ $payment->payment_date?->format('M d, Y g:i A') ?? '—' }}</dd>
                            <dt>Verified At</dt><dd>{{ $payment->verified_at?->format('M d, Y g:i A') ?? '—' }}</dd>
                            @if ($payment->proof_path)
                                <dt>Receipt</dt><dd><a href="{{ route('payments.proof', $payment) }}" target="_blank" rel="noopener">View my receipt</a></dd>
                            @endif
                            @if ($payment->rejection_reason)
                                <dt>Not accepted</dt><dd style="color:var(--red-dark)">{{ $payment->rejection_reason }}</dd>
                            @endif
                        </dl>
                        @if ($payment->transactions->isNotEmpty())
                            <h4 class="bplo-subcard-section-title">Transaction Log</h4>
                            <ul class="timeline">
                                @foreach ($payment->transactions as $txn)
                                    <li>
                                        <strong>{{ $txn->event }}</strong> <span class="muted">({{ $txn->gateway }})</span>
                                        <div class="meta">{{ $txn->created_at?->format('M d, Y g:i A') }}</div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
