@extends('layouts.app')

@section('title', 'Review Application')

@php use App\Models\Application; @endphp

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">BPLO Application Review</p>
            <h1>Review Application</h1>
            <p>
                {{ $application->business_name }}
                · Tracking Number: <strong>{{ $application->tracking_number }}</strong>
            </p>
        </div>
        <div class="bplo-header-actions">
            <x-status-badge :status="$application->status" />
            <a href="{{ route('bplo.applications.index') }}" class="btn btn-secondary">Back to Queue</a>
        </div>
    </div>

    <div class="bplo-detail-grid">
        <div class="card">
            <h2 class="section">Applicant Information</h2>
            <dl class="kv">
                <dt>Applicant Name</dt><dd>{{ $application->applicant_name }}</dd>
                <dt>Contact Number</dt><dd>{{ $application->contact_number }}</dd>
                <dt>Email</dt><dd>{{ $application->email }}</dd>
            </dl>
        </div>

        <div class="card">
            <h2 class="section">Business Information</h2>
            <dl class="kv">
                <dt>Business Name</dt><dd>{{ $application->business_name }}</dd>
                <dt>Business Address</dt><dd>{{ $application->business_address }}</dd>
                <dt>Barangay</dt><dd>{{ $application->barangay }}</dd>
                <dt>Nature</dt><dd>{{ $application->business_nature }}</dd>
                <dt>Ownership Type</dt><dd>{{ $application->ownership_type }}</dd>
                <dt>Employees</dt><dd>{{ $application->number_of_employees }}</dd>
                <dt>DTI / SEC</dt><dd>{{ $application->dti_sec_type ? $application->dti_sec_type.' — '.$application->dti_sec_number : '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="bplo-card-intro">
            <div>
                <h2 class="section">Requirements</h2>
                <p class="muted mb-0">Inspect each uploaded document and update its checklist status or correction note.</p>
            </div>
            <span class="bplo-result-count">{{ $application->requirements->count() }} {{ \Illuminate\Support\Str::plural('item', $application->requirements->count()) }}</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Document</th><th>File</th><th>Checklist</th><th>Flag</th></tr>
                </thead>
                <tbody>
                    @foreach ($application->requirements as $req)
                        <tr>
                            <td>{{ $req->requirementType?->name ?? '—' }}</td>
                            <td>
                                @if ($req->stored_path)
                                    <a href="{{ route('bplo.requirements.file', $req) }}">{{ $req->original_filename }}</a>
                                @else
                                    <span class="muted">No file</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $req->checklist_status === 'COMPLETE' ? 'badge-green' : ($req->checklist_status === 'FOR_CORRECTION' ? 'badge-yellow' : 'badge-gray') }}">
                                    {{ ucwords(strtolower(str_replace('_', ' ', $req->checklist_status))) }}
                                </span>
                                @if ($req->correction_note)<div class="muted" style="font-size:0.8rem;margin-top:4px">{{ $req->correction_note }}</div>@endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('bplo.applications.requirements.flag', [$application, $req]) }}" class="bplo-flag-form">
                                    @csrf
                                    <div class="bplo-flag-row">
                                        <select name="checklist_status">
                                            @foreach (['COMPLETE', 'MISSING', 'FOR_CORRECTION'] as $cs)
                                                <option value="{{ $cs }}" @selected($req->checklist_status === $cs)>{{ ucwords(strtolower(str_replace('_', ' ', $cs))) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm">Save</button>
                                    </div>
                                    <input type="text" name="correction_note" placeholder="Correction note (optional)" value="{{ $req->correction_note }}">
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2 class="section">Review Actions</h2>

        <div class="bplo-action-stack">
            @switch($application->status)
                @case(Application::STATUS_SUBMITTED)
                    <div class="bplo-primary-action-bar">
                        <div class="bplo-primary-action-copy">
                            <strong>New Application Received</strong>
                            <span>Start evaluating uploaded documents and business eligibility.</span>
                        </div>
                        <form method="POST" action="{{ route('bplo.applications.review', $application) }}">
                            @csrf <button type="submit" class="btn">Begin Review</button>
                        </form>
                    </div>
                    @break
                @case(Application::STATUS_UNDER_REVIEW)
                    <div class="bplo-primary-action-bar">
                        <div class="bplo-primary-action-copy">
                            <strong>Approve for Fee Payment</strong>
                            <span>Once all requirements are complete, approve this application so the applicant can settle assessed fees.</span>
                        </div>
                        <form method="POST" action="{{ route('bplo.applications.approve', $application) }}">
                            @csrf <button type="submit" class="btn btn-success">Approve for Payment</button>
                        </form>
                    </div>
                    @break
                @case(Application::STATUS_FOR_CORRECTION)
                    <div class="alert alert-warning mb-0">Waiting for the applicant to upload the corrected requirements and resubmit. No action needed until then.</div>
                    @break
                @case(Application::STATUS_APPROVED_FOR_PAYMENT)
                    <div class="alert alert-info mb-0">
                        <strong>Next step: the applicant pays.</strong> The applicant sees a <strong>Pay Now</strong> button on their
                        application page and settles the fees below through GCash, Maya, or QR Ph. Once they pay, the payment appears
                        in the <strong>Treasurer's Office</strong> verification queue.
                    </div>
                    @break
                @case(Application::STATUS_PAYMENT_PENDING)
                    <div class="alert alert-info mb-0">
                        <strong>The Treasurer is verifying this payment.</strong> No action is needed from BPLO. Once the Treasurer
                        confirms payment, this application becomes payment-verified and you can release the permit.
                    </div>
                    @break
                @case(Application::STATUS_PAYMENT_VERIFIED)
                    <div class="bplo-primary-action-bar">
                        <div class="bplo-primary-action-copy">
                            <strong>Payment Confirmed</strong>
                            <span>Release the Mayor's Permit to generate its official certificate, PDF copy, and QR verification token.</span>
                        </div>
                        <form method="POST" action="{{ route('bplo.applications.release', $application) }}">
                            @csrf <button type="submit" class="btn btn-success">Release Permit</button>
                        </form>
                    </div>
                    @break
                @case(Application::STATUS_PERMIT_RELEASED)
                    <div class="alert alert-success mb-0">Permit released. The applicant can now view, download, and print it.</div>
                    @break
                @case(Application::STATUS_REJECTED)
                    <div class="alert alert-error mb-0">This application was rejected. See the remarks below.</div>
                    @break
            @endswitch

            @if (in_array($application->status, [Application::STATUS_SUBMITTED, Application::STATUS_UNDER_REVIEW]))
                <div class="bplo-decision-grid">
                    @if ($application->status === Application::STATUS_UNDER_REVIEW)
                        <div class="bplo-decision-box">
                            <h3>Return for Correction</h3>
                            <p>Send the application back to the applicant with instructions to fix or re-upload flagged requirements.</p>
                            <form method="POST" action="{{ route('bplo.applications.return', $application) }}">
                                @csrf
                                <div class="form-group" style="margin-bottom:10px">
                                    <input type="text" name="remark" placeholder="Correction remark (required)" required>
                                </div>
                                <button type="submit" class="btn btn-secondary">Return for Correction</button>
                            </form>
                        </div>
                    @endif

                    <div class="bplo-decision-box">
                        <h3>Reject Application</h3>
                        <p>Permanently reject this submission with an official explanation recorded in the audit trail.</p>
                        <form method="POST" action="{{ route('bplo.applications.reject', $application) }}" onsubmit="return confirm('Reject this application?')">
                            @csrf
                            <div class="form-group" style="margin-bottom:10px">
                                <input type="text" name="remark" placeholder="Rejection remark (required)" required>
                            </div>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($breakdown)
        <div class="card">
            <h2 class="section">Fees to be Paid</h2>
            <div class="table-wrap table-compact">
                <table class="data">
                    <thead><tr><th>Fee</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @foreach ($breakdown->lines as $line)
                            <tr>
                                <td>{{ $line['name'] }}</td>
                                <td class="text-right">&#8369;{{ number_format($line['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <th>TOTAL</th>
                            <th class="text-right">&#8369;{{ number_format($breakdown->total, 2) }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <h2 class="section">Payments</h2>
        @if ($application->payments->isEmpty())
            <p class="muted mb-0">
                No payments yet.
                @if ($application->status === Application::STATUS_APPROVED_FOR_PAYMENT)
                    Waiting for the applicant to pay online.
                @endif
            </p>
        @else
            <div class="bplo-subcard-list">
                @foreach ($application->payments->sortByDesc('id') as $payment)
                    @php $receiving = config('permit.payment.accounts.'.$payment->method); @endphp
                    <div class="payment-entry {{ $payment->isAwaitingVerification() ? 'is-pending' : '' }}">
                        <div class="bplo-subcard-head">
                            <strong>{{ \App\Support\PaymentMethods::label($payment->method) }} · &#8369;{{ number_format((float) $payment->amount, 2) }}</strong>
                            <div class="bplo-chip-list">
                                <span class="badge {{ $payment->status === 'PAID' ? 'badge-green' : ($payment->status === 'FAILED' ? 'badge-red' : 'badge-yellow') }}">{{ ucfirst(strtolower($payment->status)) }}</span>
                                <span class="badge {{ $payment->verification_status === 'VERIFIED' ? 'badge-green' : 'badge-gray' }}">{{ ucwords(strtolower(str_replace('_', ' ', $payment->verification_status))) }}</span>
                            </div>
                        </div>

                        <dl class="kv">
                            <dt>Submitted</dt><dd>{{ $payment->created_at?->format('M d, Y g:i A') }}</dd>
                            <dt>Reference No.</dt><dd style="font-family:monospace;font-size:1rem;font-weight:700">{{ $payment->reference_number ?? '—' }}</dd>
                            @if ($payment->payer_name || $payment->payer_number)
                                <dt>Sender</dt><dd>{{ $payment->payer_name ?? '—' }} @if ($payment->payer_number) · {{ $payment->payer_number }} @endif</dd>
                            @endif
                            @if (! empty($receiving['number']))
                                <dt>Paid to</dt><dd>{{ $receiving['number'] }} @if (! empty($receiving['name'])) ({{ $receiving['name'] }}) @endif</dd>
                            @endif
                            @if ($payment->proof_path)
                                <dt>Receipt</dt>
                                <dd><a href="{{ route('bplo.payments.proof', $payment) }}" target="_blank" rel="noopener">View receipt ({{ $payment->proof_original_name }})</a></dd>
                            @endif
                            @if ($payment->verified_at)
                                <dt>Verified</dt><dd>{{ $payment->verified_at->format('M d, Y g:i A') }}</dd>
                            @endif
                            @if ($payment->rejection_reason)
                                <dt>Not accepted</dt><dd style="color:var(--red-dark)">{{ $payment->rejection_reason }}</dd>
                            @endif
                        </dl>

                        @if ($payment->isAwaitingVerification())
                            <div class="alert alert-info" style="margin:14px 0 0">
                                The Treasurer is verifying Ref No. <strong>{{ $payment->reference_number }}</strong>
                                (&#8369;{{ number_format((float) $payment->amount, 2) }}). You can release the permit once it is confirmed.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if ($application->permit)
        <div class="card">
            <h2 class="section">Permit</h2>
            <dl class="kv">
                <dt>Permit Number</dt><dd>{{ $application->permit->permit_number }}</dd>
                <dt>Status</dt>
                <dd><span class="badge {{ $application->permit->status === 'ACTIVE' ? 'badge-green' : 'badge-red' }}">{{ ucfirst(strtolower($application->permit->status)) }}</span></dd>
                <dt>Valid Until</dt><dd>{{ $application->permit->valid_until?->format('M d, Y') }}</dd>
            </dl>
            <div class="bplo-btn-bar">
                <form method="POST" action="{{ route('bplo.permits.regenerate', $application->permit) }}">
                    @csrf <button type="submit" class="btn btn-secondary">Regenerate Token &amp; PDF</button>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <h2 class="section">Status History</h2>
        <ul class="timeline">
            @foreach ($application->statusHistories as $history)
                <li>
                    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:4px">
                        <x-status-badge :status="$history->to_status" />
                        @if ($history->from_status)
                            <span class="muted">from {{ ucwords(strtolower(str_replace('_', ' ', $history->from_status))) }}</span>
                        @endif
                    </div>
                    @if ($history->note)<div>{{ $history->note }}</div>@endif
                    <div class="meta">{{ $history->created_at?->format('M d, Y g:i A') }} @if($history->actor_role) · {{ ucfirst($history->actor_role) }} @endif</div>
                </li>
            @endforeach
        </ul>
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
                        <div class="meta">{{ $remark->author?->name }} · {{ $remark->created_at?->format('M d, Y g:i A') }}</div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
