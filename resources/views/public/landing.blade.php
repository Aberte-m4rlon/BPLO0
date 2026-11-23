@extends('layouts.guest')

@section('title', config('permit.municipality').' — Automated Business Permit Application and Status Tracking System')

@section('content')
    <div class="page-header bplo-page-header bplo-dashboard-hero">
        <div class="bplo-hero-copy">
            <span class="bplo-hero-pill">{{ strtoupper(config('permit.municipality')) }} · BPLO ONLINE PORTAL</span>
            <h1>Business Permit <span class="bplo-hero-highlight">Application &amp; Verification</span></h1>
            <p>Apply for your business permit online, track your application, pay fees, and verify your permit with a QR code — all in one place for {{ config('permit.municipality') }}, {{ config('permit.province') }}.</p>
            <div class="hero-actions" style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ url('/register') }}" class="btn bplo-hero-secondary">Apply Online</a>
                <a href="{{ url('/track') }}" class="btn bplo-hero-primary">Track Application</a>
                <a href="{{ route('verify.lookup') }}" class="btn bplo-hero-primary">Verify Permit</a>
                <a href="{{ url('/login') }}" class="btn bplo-hero-primary">Login</a>
            </div>
        </div>
    </div>

    <section class="card" style="margin-bottom:22px;">
        <div class="card-head">
            <div>
                <p class="bplo-eyebrow">HOW IT WORKS</p>
                <h2 class="section">Four simple steps from application to a verified permit</h2>
            </div>
        </div>
        <div class="steps">
            <div class="step">
                <div class="num">01</div>
                <h3>Apply Online</h3>
                <p class="muted mb-0">Register, fill in your business details, and upload the required documents from any device.</p>
            </div>
            <div class="step">
                <div class="num">02</div>
                <h3>Track Status</h3>
                <p class="muted mb-0">Follow your application as the BPLO reviews it. Get notified of approvals or corrections.</p>
            </div>
            <div class="step">
                <div class="num">03</div>
                <h3>Pay Fees</h3>
                <p class="muted mb-0">Once approved, view your fee breakdown and settle the amount through the online payment flow.</p>
            </div>
            <div class="step">
                <div class="num">04</div>
                <h3>Download &amp; Verify</h3>
                <p class="muted mb-0">Download your official Mayor's Permit with a QR code anyone can scan to verify its authenticity.</p>
            </div>
        </div>
    </section>

    <section class="card" style="text-align:center;">
        <p class="bplo-eyebrow">GET STARTED</p>
        <h2 style="margin-bottom:6px;">Ready to get started?</h2>
        <p class="muted" style="max-width:520px;margin:0 auto 18px;">Apply for your {{ config('permit.municipality') }} business permit online, or verify an existing one.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="{{ url('/register') }}" class="btn btn-gold">Apply Online</a>
            <a href="{{ route('verify.lookup') }}" class="btn btn-secondary">Verify a Permit</a>
        </div>
    </section>
@endsection
