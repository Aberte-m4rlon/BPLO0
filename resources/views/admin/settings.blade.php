@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">System Administration</p>
            <h1>System Settings</h1>
            <p>Read-only municipal configuration values loaded from the deployment environment.</p>
        </div>
    </div>

    <div class="alert alert-info">
        These values are read-only. Environment-level settings are changed only by an operator updating
        <code>.env</code> on the server. Requirement types, fee types, and fee rates are edited through their own screens.
    </div>

    <div class="bplo-detail-grid">
        <div class="card">
            <h2 class="section">Municipality Identity</h2>
            <dl class="kv">
                <dt>Municipality</dt><dd>{{ $permit['municipality'] }}</dd>
                <dt>Province</dt><dd>{{ $permit['province'] }}</dd>
            </dl>
        </div>

        <div class="card">
            <h2 class="section">Numbering &amp; Validity</h2>
            <dl class="kv">
                <dt>Tracking Prefix</dt><dd>{{ $permit['tracking_prefix'] }}</dd>
                <dt>Permit Prefix</dt><dd>{{ $permit['permit_prefix'] }}</dd>
                <dt>Permit Validity</dt><dd>{{ $permit['valid_months'] }} {{ \Illuminate\Support\Str::plural('month', (int) $permit['valid_months']) }}</dd>
            </dl>
        </div>
    </div>

    <div class="bplo-detail-grid">
        <div class="card">
            <h2 class="section">Email &amp; SMS Notifications</h2>
            <dl class="kv">
                <dt>Mail Driver</dt><dd>{{ strtoupper($mail['driver']) }}</dd>
                <dt>Mail From</dt><dd>{{ $mail['from_name'] }} ({{ $mail['from_address'] }})</dd>
                <dt>SMS Driver</dt><dd>{{ strtoupper($permit['sms']['driver']) }}</dd>
                <dt>SMS Sender ID</dt><dd>{{ $permit['sms']['sender_id'] }}</dd>
            </dl>
        </div>

        <div class="card">
            <h2 class="section">Document Uploads</h2>
            <dl class="kv">
                <dt>Allowed Mimes</dt><dd>{{ strtoupper(implode(', ', (array) $permit['uploads']['mimes'])) }}</dd>
                <dt>Max File Size</dt><dd>{{ $permit['uploads']['max_kb'] }} KB ({{ round((int) $permit['uploads']['max_kb'] / 1024, 1) }} MB)</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <h2 class="section">Payment Configuration</h2>
        <dl class="kv">
            <dt>Mode</dt><dd>{{ ucfirst($permit['payment']['mode']) }}</dd>
            <dt>Enabled Methods</dt>
            <dd>
                <div class="bplo-chip-list">
                    @foreach ($permit['payment']['methods'] as $method => $enabled)
                        <span class="badge {{ $enabled ? 'badge-green' : 'badge-gray' }}">{{ strtoupper($method) }}</span>
                    @endforeach
                </div>
            </dd>
            <dt>Webhook Secret</dt><dd>{{ $permit['payment']['webhook_secret'] ? '•••••• (configured)' : 'Not configured' }}</dd>
        </dl>
    </div>
@endsection
