@extends('layouts.app')

@section('title', 'Permit Details')

@section('content')
    @php
        $isExpired = $permit->valid_until !== null && $permit->valid_until->isPast();
        $isRevoked = $permit->status === \App\Models\Permit::STATUS_REVOKED;
        if ($isRevoked) {
            $indicator = ['Revoked', 'badge-red'];
        } elseif ($isExpired) {
            $indicator = ['Expired', 'badge-yellow'];
        } else {
            $indicator = ['Active', 'badge-green'];
        }
    @endphp

    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Inspection &amp; Monitoring</p>
            <h1>Permit Details</h1>
            <p>{{ $permit->business_name }} · Permit No. <strong>{{ $permit->permit_number }}</strong></p>
        </div>
        <div class="bplo-header-actions">
            <span class="badge {{ $indicator[1] }}">{{ $indicator[0] }}</span>
            <a href="{{ url('/inspector/permits') }}" class="btn btn-secondary">Back to Permits</a>
        </div>
    </div>

    <div class="bplo-detail-grid">
        <div class="card">
            <h2 class="section">Business Information</h2>
            <dl class="kv">
                <dt>Business Name</dt><dd>{{ $permit->business_name }}</dd>
                <dt>Owner</dt><dd>{{ $permit->owner_name }}</dd>
                <dt>Business Address</dt><dd>{{ $permit->business_address }}</dd>
                <dt>Nature</dt><dd>{{ $permit->business_nature }}</dd>
                <dt>Status</dt>
                <dd><span class="badge {{ $indicator[1] }}">{{ ucfirst(strtolower($permit->status)) }}</span></dd>
                <dt>Issued</dt><dd>{{ $permit->issued_at?->format('M d, Y') ?? '—' }}</dd>
                <dt>Valid Until</dt><dd>{{ $permit->valid_until?->format('M d, Y') ?? '—' }}</dd>
            </dl>
        </div>

        <div class="card">
            <h2 class="section">Verification</h2>
            <p class="muted">Open the public verification page for this permit (also what the printed QR code points to).</p>
            <dl class="kv">
                <dt>Verification Link</dt>
                <dd style="word-break:break-all">{{ $permit->verifyUrl() }}</dd>
            </dl>
            <div class="bplo-btn-bar">
                <a href="{{ $permit->verifyUrl() }}" class="btn" target="_blank" rel="noopener">Open Verification Page</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2 class="section">Scan History</h2>
            <span class="bplo-result-count">{{ $permit->qrVerifications->count() }} {{ \Illuminate\Support\Str::plural('scan', $permit->qrVerifications->count()) }}</span>
        </div>
        @if ($permit->qrVerifications->isEmpty())
            <x-empty-state title="No scans recorded yet" message="QR verification scan events will appear here whenever this permit is verified." />
        @else
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>Time</th><th>Result</th><th>IP Address</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($permit->qrVerifications as $scan)
                            @php
                                $badge = match ($scan->result) {
                                    'VALID' => 'badge-green',
                                    'EXPIRED' => 'badge-yellow',
                                    'REVOKED', 'NOT_FOUND' => 'badge-red',
                                    default => 'badge-gray',
                                };
                            @endphp
                            <tr>
                                <td>{{ $scan->created_at?->format('M d, Y g:i A') }}</td>
                                <td><span class="badge {{ $badge }}">{{ ucwords(strtolower(str_replace('_', ' ', $scan->result))) }}</span></td>
                                <td>{{ $scan->ip_address ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="bplo-page-back-bar">
        <a href="{{ url('/inspector/permits') }}" class="btn btn-secondary">Back to Permits</a>
    </div>
@endsection
