@extends('layouts.guest')

@section('title', 'Verify Permit — '.config('permit.municipality'))

@php
    $map = [
        'VALID' => ['verify-valid', '✓ VALID PERMIT', 'This permit record is registered in the '.config('permit.municipality').' system.'],
        'EXPIRED' => ['verify-expired', '⚠ EXPIRED PERMIT', 'This permit record is registered but is no longer active.'],
        'REVOKED' => ['verify-revoked', '✕ REVOKED PERMIT', 'This permit record has been revoked and is no longer valid.'],
        'NOT_FOUND' => ['verify-notfound', '✕ INVALID / NOT FOUND', 'This QR code does not correspond to a valid registered permit record.'],
    ];
    [$class, $label, $message] = $map[$result] ?? $map['NOT_FOUND'];
@endphp

@section('content')
    <div class="bplo-verify-wrap">
        <p class="bplo-eyebrow text-center">Official Permit Verification</p>
        <h1>Permit Verification</h1>
        <div class="verify-result {{ $class }}">
            <div class="status">{{ $label }}</div>
            <p class="mb-0">{{ $message }}</p>
        </div>

        @if ($permit && $result !== 'NOT_FOUND')
            {{-- Data-minimized per RA 10173: no email, contact, or DTI/SEC data. --}}
            <div class="card">
                <h2 class="section">Registered Permit Record</h2>
                <dl class="kv">
                    <dt>Permit Number</dt><dd>{{ $permit->permit_number }}</dd>
                    <dt>Business Name</dt><dd>{{ $permit->business_name }}</dd>
                    <dt>Business Address</dt><dd>{{ $permit->business_address }}</dd>
                    <dt>Status</dt>
                    <dd>
                        <span class="badge {{ $result === 'VALID' ? 'badge-green' : ($result === 'EXPIRED' ? 'badge-yellow' : 'badge-red') }}">
                            {{ ucfirst(strtolower($permit->status)) }}
                        </span>
                    </dd>
                    <dt>Date Issued</dt><dd>{{ $permit->issued_at?->format('F d, Y') }}</dd>
                    <dt>Valid Until</dt><dd>{{ $permit->valid_until?->format('F d, Y') }}</dd>
                </dl>
            </div>
        @endif

        <div class="bplo-error-actions" style="margin-top: 18px;">
            <a href="{{ route('verify.lookup') }}" class="btn">Verify Another Permit</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Back to {{ config('permit.municipality') }}</a>
        </div>
    </div>
@endsection
