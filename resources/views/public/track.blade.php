@extends('layouts.guest')

@section('title', 'Track Application — '.config('permit.municipality'))

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Public Status Lookup</p>
            <h1>Track Your Application</h1>
            <p>Check the live approval stage, remarks, and permit issuance status using your tracking number.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ url('/') }}" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>

    <div class="card">
        <h2 class="section">Application Lookup</h2>
        <p class="bplo-form-intro">Enter your tracking number and either the email or last name used on the application.</p>
        <form method="POST" action="{{ url('/track') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="tracking_number">Tracking Number</label>
                    <input type="text" id="tracking_number" name="tracking_number" value="{{ old('tracking_number', $tracking_number ?? '') }}" placeholder="{{ config('permit.tracking_prefix') }}-{{ date('Y') }}-000001" required>
                    @error('tracking_number') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="verifier">Email or Last Name</label>
                    <input type="text" id="verifier" name="verifier" value="{{ old('verifier') }}" required>
                    @error('verifier') <div class="field-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="bplo-btn-bar">
                <button type="submit" class="btn">Track Application</button>
            </div>
        </form>
    </div>

    @isset($notFound)
        @if ($notFound)
            <div class="alert alert-warning">No application matched that tracking number and verifier.</div>
        @endif
    @endisset

    @if (!empty($application))
        <div class="card">
            <div class="card-head">
                <div>
                    <h2 class="section">Current Status</h2>
                    <p class="muted mb-0">Tracking Number: <strong>{{ $application->tracking_number }}</strong> · Business: {{ $application->business_name }}</p>
                </div>
                <x-status-badge :status="$application->status" />
            </div>
        </div>

        <div class="bplo-detail-grid">
            <div class="card">
                <h2 class="section">Timeline</h2>
                <ul class="timeline">
                    @foreach ($application->statusHistories as $history)
                        <li>
                            <x-status-badge :status="$history->to_status" />
                            <div class="meta">{{ $history->created_at?->format('M d, Y g:i A') }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card">
                <h2 class="section">Payment &amp; Permit Summary</h2>
                @php($latestPayment = $application->payments->sortByDesc('created_at')->first())
                <dl class="kv">
                    <dt>Payment Status</dt>
                    <dd>
                        @if ($latestPayment)
                            <span class="badge {{ $latestPayment->status === 'PAID' ? 'badge-green' : ($latestPayment->status === 'FAILED' ? 'badge-red' : 'badge-yellow') }}">{{ ucfirst(strtolower($latestPayment->status)) }}</span>
                        @else
                            <span class="muted">No payment recorded yet</span>
                        @endif
                    </dd>
                    <dt>Payment Verification</dt>
                    <dd>{{ $latestPayment ? ucwords(strtolower(str_replace('_', ' ', $latestPayment->verification_status))) : '—' }}</dd>
                    <dt>Issued Permit</dt>
                    <dd>
                        @if ($application->permit)
                            <strong>{{ $application->permit->permit_number }}</strong>
                            <span class="badge {{ $application->permit->status === 'ACTIVE' ? 'badge-green' : 'badge-red' }}" style="margin-left:6px">{{ ucfirst(strtolower($application->permit->status)) }}</span>
                        @else
                            <span class="muted">No permit released yet</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        @if ($application->remarks->isNotEmpty())
            <div class="card">
                <h2 class="section">Remarks &amp; Correction Notes</h2>
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
    @endif

    <div class="card" id="verify">
        <div class="bplo-primary-action-bar">
            <div class="bplo-primary-action-copy">
                <strong>Need to check an issued QR permit?</strong>
                <span>Scan the QR code on an issued Mayor's Permit or enter the verification code online.</span>
            </div>
            <a href="{{ route('verify.lookup') }}" class="btn btn-secondary">Verify a Permit</a>
        </div>
    </div>
@endsection
