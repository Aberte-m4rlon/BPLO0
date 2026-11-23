@extends('layouts.app')

@section('title', 'Mayor\'s Permit — '.$permit->permit_number)

@section('content')
    <div class="page-header no-print">
        <div>
            <p class="bplo-eyebrow">OFFICIAL BUSINESS PERMIT</p>
            <h1>Mayor's Permit</h1>
            <p class="muted mb-0">View, print, or download your official QR-verified business permit certificate.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('applications.show', $application) }}" class="btn btn-secondary">← Back</a>
            <button type="button" class="btn btn-secondary" onclick="window.print()">Print Permit</button>
            <a href="{{ route('applications.permit.download', $application) }}" class="btn">Download PDF</a>
        </div>
    </div>

    <div class="card permit-certificate">
        <div class="permit-cert-frame">
            <div class="permit-cert-corners" aria-hidden="true"></div>

            <div class="permit-cert-header">
                <div class="permit-cert-brand">
                    <span class="permit-cert-seal" aria-hidden="true">BP</span>
                    <div>
                        <p class="permit-cert-republic">Republic of the Philippines · Province of {{ config('permit.province') }}</p>
                        <h2>{{ config('permit.municipality') }}</h2>
                        <p class="permit-cert-office">Office of the Municipal Mayor · Business Permits &amp; Licensing Office</p>
                    </div>
                </div>
                <span class="badge {{ strtoupper((string) $permit->status) === 'ACTIVE' ? 'badge-green' : 'badge-red' }} permit-cert-status">
                    {{ strtoupper((string) $permit->status) }}
                </span>
            </div>

            <div class="permit-cert-banner">
                <span>OFFICIAL BUSINESS PERMIT &amp; LICENSE TO OPERATE</span>
                <h3>MAYOR'S PERMIT</h3>
            </div>

            <div class="permit-cert-body">
                <div class="permit-cert-details">
                    <div class="permit-cert-number-bar">
                        <span>Permit Number</span>
                        <strong>{{ $permit->permit_number }}</strong>
                    </div>

                    <div class="permit-cert-grant-box">
                        <span class="permit-cert-grant-kicker">THIS PERMIT IS HEREBY GRANTED TO</span>
                        <div class="permit-cert-biz">{{ $permit->business_name }}</div>
                    </div>

                    <dl class="kv permit-cert-kv">
                        <dt>Proprietor / Owner</dt><dd>{{ $permit->owner_name }}</dd>
                        <dt>Business Address</dt><dd>{{ $permit->business_address }}</dd>
                        <dt>Nature of Business</dt><dd>{{ $permit->business_nature }}</dd>
                    </dl>

                    <div class="permit-cert-validity-grid">
                        <div class="permit-validity-cell">
                            <span>Date Issued</span>
                            <strong>{{ $permit->issued_at?->format('F d, Y') }}</strong>
                        </div>
                        <div class="permit-validity-cell is-highlight">
                            <span>Valid Until</span>
                            <strong>{{ $permit->valid_until?->format('F d, Y') }}</strong>
                        </div>
                    </div>

                    <div class="permit-cert-notice">
                        This certifies that the business establishment above has complied with municipal requirements and paid the prescribed fees. Display conspicuously at the business premises.
                    </div>
                </div>

                <div class="permit-cert-qr-panel">
                    <a href="{{ $verifyUrl }}" target="_blank" rel="noopener" class="permit-cert-qr-box" title="Open the verification page" aria-label="Open the permit verification page">
                        {!! $qrSvg !!}
                    </a>
                    <strong>Official QR Verification</strong>
                    <p class="muted mb-0">Scan with a phone camera or click the QR code to verify authenticity online.</p>
                </div>
            </div>

            <div class="permit-cert-signatures">
                <div class="permit-cert-sig">
                    <div class="permit-cert-sig-line"></div>
                    <strong>BPLO Licensing Officer</strong>
                    <span>Business Permits &amp; Licensing Office</span>
                </div>
                <div class="permit-cert-sig">
                    <div class="permit-cert-sig-line"></div>
                    <strong>Municipal Mayor</strong>
                    <span>{{ config('permit.municipality') }}, {{ config('permit.province') }}</span>
                </div>
            </div>

            <div class="permit-cert-verify-bar">
                <label for="verify-url" class="no-print">Verification Link</label>
                <div class="permit-cert-verify-row no-print">
                    <input id="verify-url" type="text" value="{{ $verifyUrl }}" readonly onclick="this.select()">
                    <a href="{{ $verifyUrl }}" target="_blank" rel="noopener" class="btn btn-sm">Open Verification Page</a>
                    <button type="button" class="btn btn-sm btn-secondary" id="copy-verify-url">Copy Link</button>
                </div>
                <p class="permit-print-only-url">Verify authenticity online at: <strong>{{ $verifyUrl }}</strong></p>
            </div>
        </div>

        @if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:|/|$)#i', $verifyUrl))
            <div class="alert alert-warning no-print" style="margin-top:16px">
                This QR code points to <strong>localhost</strong>, so it only opens on this computer.
                Set <code>PERMIT_PUBLIC_URL</code> to an address phones can reach.
            </div>
        @endif
    </div>

    <script>
        document.getElementById('copy-verify-url').addEventListener('click', function () {
            var input = document.getElementById('verify-url');
            input.select();
            var btn = this;
            var done = function () { btn.textContent = 'Copied!'; };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).then(done);
            } else {
                document.execCommand('copy');
                done();
            }
        });
    </script>
@endsection
