@extends('layouts.app')

@section('title', 'Verify Payment')

@section('content')
    @php
        $receiving = config('permit.payment.accounts.'.$payment->method);
        $breakdown = $payment->breakdown_json ?? [];
        $lines = $breakdown['lines'] ?? [];
    @endphp

    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Municipal Treasurer's Office</p>
            <h1>Verify Payment</h1>
            <p>
                {{ $application?->business_name ?? 'Payment Record' }}
                · Tracking Number: <strong>{{ $payment->tracking_number }}</strong>
            </p>
        </div>
        <div class="bplo-header-actions">
            <span class="badge {{ $payment->status === 'PAID' ? 'badge-green' : ($payment->status === 'FAILED' ? 'badge-red' : 'badge-yellow') }}">{{ ucfirst(strtolower($payment->status)) }}</span>
            <span class="badge {{ $payment->verification_status === 'VERIFIED' ? 'badge-green' : 'badge-gray' }}">{{ ucwords(strtolower(str_replace('_', ' ', $payment->verification_status))) }}</span>
            <a href="{{ url('/treasurer/payments') }}" class="btn btn-secondary">Back to Payments</a>
        </div>
    </div>

    <div class="{{ $application ? 'bplo-detail-grid' : '' }}">
        @if ($application)
            <div class="card">
                <h2 class="section">Business Information</h2>
                <dl class="kv">
                    <dt>Business Name</dt><dd>{{ $application->business_name }}</dd>
                    <dt>Business Address</dt><dd>{{ $application->business_address }}</dd>
                    <dt>Applicant</dt><dd>{{ $application->applicant_name }}</dd>
                    <dt>Application Status</dt><dd><x-status-badge :status="$application->status" /></dd>
                </dl>
            </div>
        @endif

        <div class="card">
            <h2 class="section">Payment Details</h2>
            <dl class="kv">
                <dt>Method</dt><dd>{{ \App\Support\PaymentMethods::label($payment->method) }}</dd>
                <dt>Amount</dt><dd>&#8369;{{ number_format((float) $payment->amount, 2) }}</dd>
                <dt>Reference No.</dt><dd style="font-family:monospace;font-size:1rem;font-weight:700">{{ $payment->reference_number ?? '—' }}</dd>
                <dt>Submitted</dt><dd>{{ $payment->created_at?->format('M d, Y g:i A') }}</dd>
                @if ($payment->payer_name || $payment->payer_number)
                    <dt>Sender</dt><dd>{{ $payment->payer_name ?? '—' }} @if ($payment->payer_number) · {{ $payment->payer_number }} @endif</dd>
                @endif
                @if (! empty($receiving['number']))
                    <dt>Paid to</dt><dd>{{ $receiving['number'] }} @if (! empty($receiving['name'])) ({{ $receiving['name'] }}) @endif</dd>
                @endif
                @if ($payment->proof_path)
                    <dt>Receipt</dt>
                    <dd><a href="{{ route('treasurer.payments.proof', $payment) }}" target="_blank" rel="noopener">View receipt ({{ $payment->proof_original_name }})</a></dd>
                @endif
                @if ($payment->verified_at)
                    <dt>Verified</dt><dd>{{ $payment->verified_at->format('M d, Y g:i A') }}</dd>
                @endif
                @if ($payment->rejection_reason)
                    <dt>Not accepted</dt><dd style="color:var(--red-dark)">{{ $payment->rejection_reason }}</dd>
                @endif
            </dl>
        </div>
    </div>

    @if (! empty($lines))
        <div class="card">
            <h2 class="section">Fee Breakdown</h2>
            <div class="table-wrap table-compact">
                <table class="data">
                    <thead><tr><th>Fee</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @foreach ($lines as $line)
                            <tr>
                                <td>{{ $line['name'] ?? '—' }}</td>
                                <td class="text-right">&#8369;{{ number_format((float) ($line['amount'] ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <th>TOTAL</th>
                            <th class="text-right">&#8369;{{ number_format((float) ($breakdown['total'] ?? $payment->amount), 2) }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <h2 class="section">Verification Actions</h2>
        @if ($payment->isAwaitingVerification())
            <div class="bplo-action-stack">
                <div class="alert alert-info mb-0">
                    Open the {{ \App\Support\PaymentMethods::label($payment->method) }} account
                    @if (! empty($receiving['number'])) <strong>{{ $receiving['number'] }}</strong> @endif
                    and check its transaction history for Ref No. <strong>{{ $payment->reference_number }}</strong>
                    with an amount of <strong>&#8369;{{ number_format((float) $payment->amount, 2) }}</strong>.
                </div>

                <div class="bplo-primary-action-bar">
                    <div class="bplo-primary-action-copy">
                        <strong>Confirm Received Collection</strong>
                        <span>Once verified against wallet or bank records, confirm payment so BPLO can release the permit.</span>
                    </div>
                    <form method="POST" action="{{ route('treasurer.payments.confirm', $payment) }}"
                          onsubmit="return confirm('Confirm that ₱{{ number_format((float) $payment->amount, 2) }} with Ref No. {{ $payment->reference_number }} was received?')">
                        @csrf
                        <button type="submit" class="btn btn-success">Confirm Payment</button>
                    </form>
                </div>

                <div class="bplo-decision-box">
                    <h3>Reject Payment Submission</h3>
                    <p>Return to the applicant with a clear reason so they can submit a valid reference number or receipt.</p>
                    <form method="POST" action="{{ route('treasurer.payments.reject', $payment) }}"
                          onsubmit="return confirm('Reject this payment and ask the applicant to pay again?')">
                        @csrf
                        <div class="form-group" style="margin-bottom:10px">
                            <input type="text" name="reason" placeholder="Reason, e.g. Ref No. not found or wrong amount" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Reject Payment</button>
                    </form>
                    @error('reason') <div class="field-error">{{ $message }}</div> @enderror
                </div>
            </div>
        @elseif ($payment->verification_status === 'VERIFIED')
            <div class="alert alert-success mb-0">This payment has been verified. BPLO can now release the permit.</div>
        @else
            <p class="muted mb-0">This payment is not awaiting verification. No action is available.</p>
        @endif
    </div>

    <div class="bplo-page-back-bar">
        <a href="{{ url('/treasurer/payments') }}" class="btn btn-secondary">Back to Payments</a>
    </div>
@endsection
